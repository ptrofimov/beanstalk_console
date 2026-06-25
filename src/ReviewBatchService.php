<?php

/**
 * Coordinates review batch queue operations and manifest writes.
 */
class ReviewBatchService {

    private $interface;
    private $storage;

    /**
     * Stores the queue interface and review storage dependencies.
     *
     * @param BeanstalkInterface $interface Queue interface wrapper.
     * @param ReviewBatchStorage $storage Review batch storage.
     */
    public function __construct($interface, $storage) {
        $this->interface = $interface;
        $this->storage = $storage;
    }

    /**
     * Copies jobs into the review tube, removes them from the source state, and records manifest rows.
     * If copying succeeds but source deletion fails, the review copy is recorded as copy_delete_error.
     *
     * @param array $batch Review batch metadata.
     * @param int $chunkSize Maximum jobs to process in this chunk.
     * @return array Updated review batch metadata.
     * @throws InvalidArgumentException If storage cannot record the chunk result.
     */
    public function processPreparationChunk($batch, $chunkSize) {
        $bodySnapshotRows = array();

        for ($i = 0; $i < $chunkSize; $i++) {
            if ((int)$batch['processed'] >= (int)$batch['target_count']) {
                $batch['status'] = 'complete';
                break;
            }

            $job = null;
            $stats = array();
            $reviewId = null;
            $sourceDeleted = false;
            try {
                // Claim the next source job only long enough to copy it into the isolated review tube.
                $job = $this->peekJobByState($batch['source_tube'], $batch['source_state']);
                if (!$job) {
                    $batch['status'] = 'complete';
                    break;
                }

                $stats = $this->interface->_client->statsJob($job);
                $priority = isset($stats['pri']) ? (int)$stats['pri'] : Pheanstalk::DEFAULT_PRIORITY;
                $ttr = isset($stats['ttr']) ? (int)$stats['ttr'] : Pheanstalk::DEFAULT_TTR;
                $reviewId = $this->interface->addJob($batch['review_tube'], $job->getData(), $priority, 0, $ttr);

                // Only after the review copy exists do we remove the original from the source state.
                $this->interface->_client->delete($job);
                $sourceDeleted = true;
                $row = $this->buildReviewJobRow($job, $stats, $reviewId, 'moved');
                $this->storage->appendJob($batch['id'], $row);
                if (!empty($batch['include_body_snapshot'])) {
                    $bodySnapshotRows[] = $this->buildBodySnapshotRow($row, $job);
                }
                $batch['processed'] = (int)$batch['processed'] + 1;
            } catch (Exception $e) {
                $batch['errors'] = (int)$batch['errors'] + 1;
                $batch['status'] = 'error';
                $batch['error_message'] = $e->getMessage();
                // If the copy exists but source cleanup failed, keep it visible for inspection/deletion.
                $status = ($reviewId !== null && !$sourceDeleted) ? 'copy_delete_error' : 'error';
                $row = $this->buildReviewJobErrorRow($job, $stats, $reviewId, $e->getMessage(), $status);
                $this->storage->appendJob($batch['id'], $row);
                if (!empty($batch['include_body_snapshot']) && $job && $reviewId !== null) {
                    $bodySnapshotRows[] = $this->buildBodySnapshotRow($row, $job);
                }
                break;
            }
        }

        if (!empty($batch['include_body_snapshot'])) {
            // Snapshot writes are batched so the body file/index are touched once per chunk.
            $this->storage->appendBodySnapshotRows($batch['id'], $bodySnapshotRows);
        }

        if ((int)$batch['processed'] >= (int)$batch['target_count'] && $batch['status'] === 'processing') {
            $batch['status'] = 'complete';
        }

        $this->storage->saveBatch($batch);
        return $batch;
    }

    /**
     * Applies one review operation to one review-copy job and records the outcome.
     * If an operation creates another queue copy but review-copy deletion fails, records a cleanup status.
     *
     * @param array $batch Review batch metadata.
     * @param array $manifestJob Collapsed manifest row for the review-copy job.
     * @param array $operation Operation metadata.
     * @return bool True when the queue operation succeeds, false when it is recorded as missing or failed.
     * @throws InvalidArgumentException If storage cannot record the operation outcome.
     */
    public function operateReviewJob($batch, $manifestJob, $operation) {
        $operationType = isset($operation['operation']) ? $operation['operation'] : '';
        $delay = isset($operation['delay']) ? (int)$operation['delay'] : 0;
        $targetTube = isset($operation['target_tube']) ? $operation['target_tube'] : '';
        $priority = isset($manifestJob['pri']) ? (int)$manifestJob['pri'] : Pheanstalk::DEFAULT_PRIORITY;
        $ttr = isset($manifestJob['ttr']) ? (int)$manifestJob['ttr'] : Pheanstalk::DEFAULT_TTR;
        $baseRow = $this->buildOperationRow($manifestJob);

        try {
            $reviewJob = $this->interface->_client->peek((int)$manifestJob['review_id']);
            if ($operationType === 'move_all_moved') {
                // Write the destination copy first. If destination is the source tube, this is a return.
                $targetId = $this->interface->addJob($targetTube, $reviewJob->getData(), $priority, $delay, $ttr);
                try {
                    $this->interface->_client->delete($reviewJob);
                } catch (Exception $e) {
                    // The destination job now exists, so keep the remaining review copy visible for cleanup.
                    $errorRow = array(
                        'status' => $targetTube === $batch['source_tube'] ? 'return_delete_error' : 'move_delete_error',
                        'error_message' => $e->getMessage(),
                    );
                    if ($targetTube === $batch['source_tube']) {
                        $errorRow['returned_id'] = $targetId;
                        $errorRow['return_delay'] = $delay;
                    } else {
                        $errorRow['target_tube'] = $targetTube;
                        $errorRow['target_id'] = $targetId;
                        $errorRow['target_delay'] = $delay;
                    }
                    $this->storage->updateJob($batch['id'], array_merge($baseRow, $errorRow));
                    return false;
                }
                if ($targetTube === $batch['source_tube']) {
                    $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                        'returned_id' => $targetId,
                        'return_delay' => $delay,
                        'status' => 'returned',
                    )));
                } else {
                    $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                        'target_tube' => $targetTube,
                        'target_id' => $targetId,
                        'target_delay' => $delay,
                        'status' => 'moved_to_tube',
                    )));
                }
                return true;
            }

            if ($operationType === 'duplicate_all_moved') {
                // Duplicate intentionally leaves the review copy in place for continued inspection/cleanup.
                $targetId = $this->interface->addJob($targetTube, $reviewJob->getData(), $priority, $delay, $ttr);
                $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                    'target_tube' => $targetTube,
                    'target_id' => $targetId,
                    'target_delay' => $delay,
                    'status' => 'duplicated',
                )));
                return true;
            }

            if ($operationType === 'delete_all_moved') {
                // Delete is one-way for the review copy; no source job is recreated.
                $this->interface->_client->delete($reviewJob);
                $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                    'status' => 'deleted',
                )));
                return true;
            }
        } catch (Exception $e) {
            if ($this->isBeanstalkNotFound($e)) {
                // A missing review copy is already gone for deletes, but blocks returns.
                $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                    'status' => $operationType === 'delete_all_moved' ? 'deleted' : 'missing_review_job',
                    'error_message' => $e->getMessage(),
                )));
            } else {
                $this->storage->updateJob($batch['id'], array_merge($baseRow, array(
                    'status' => 'operation_error',
                    'error_message' => $e->getMessage(),
                )));
            }
        }

        return false;
    }

    /**
     * Peeks the next job in the selected state for the source tube.
     *
     * @param string $tube Source tube name.
     * @param string $state Source state: ready, delayed, or buried.
     * @return Pheanstalk_Job|false
     * @throws Exception If beanstalkd returns an unexpected error.
     */
    private function peekJobByState($tube, $state) {
        try {
            switch ($state) {
                case 'ready':
                    return $this->interface->_client->useTube($tube)->peekReady();
                case 'delayed':
                    return $this->interface->_client->useTube($tube)->peekDelayed();
                case 'buried':
                    return $this->interface->_client->useTube($tube)->peekBuried();
            }
        } catch (Exception $e) {
            if ($this->isBeanstalkNotFound($e)) {
                return false;
            }
            throw $e;
        }
        return false;
    }

    /**
     * Builds the audit row describing one review job and its original stats.
     *
     * @param Pheanstalk_Job $job Original beanstalkd job.
     * @param array $stats Original job stats.
     * @param int $reviewId Review-copy job id.
     * @param string $status Manifest status.
     * @return array
     */
    private function buildReviewJobRow($job, $stats, $reviewId, $status) {
        $fields = array('pri', 'age', 'delay', 'ttr', 'time-left', 'file', 'reserves', 'timeouts', 'releases', 'buries', 'kicks');
        $row = array(
            'original_id' => $job->getId(),
            'review_id' => $reviewId,
            'status' => $status,
        );
        foreach ($fields as $field) {
            if (isset($stats[$field])) {
                $row[$field] = is_numeric($stats[$field]) ? (int)$stats[$field] : $stats[$field];
            }
        }
        if (isset($stats['age'])) {
            $row['job_created_at'] = date('c', time() - (int)$stats['age']);
        }
        return $row;
    }

    /**
     * Builds a compact audit row for the job that stopped review preparation.
     *
     * @param Pheanstalk_Job|null $job Original beanstalkd job, when one was available.
     * @param array $stats Original job stats, when available.
     * @param int|null $reviewId Review-copy job id, when one was created.
     * @param string $message Error message.
     * @param string $status Manifest status.
     * @return array
     */
    private function buildReviewJobErrorRow($job, $stats, $reviewId, $message, $status = 'error') {
        if ($job) {
            $row = $this->buildReviewJobRow($job, $stats, $reviewId, $status);
        } else {
            $row = array('status' => $status);
            if ($reviewId !== null) {
                $row['review_id'] = $reviewId;
            }
        }
        $row['error_message'] = $message;
        return $row;
    }

    /**
     * Builds the body snapshot row stored beside the audit manifest.
     *
     * @param array $row Manifest row for the reviewed job.
     * @param Pheanstalk_Job $job Original beanstalkd job.
     * @return array
     */
    private function buildBodySnapshotRow($row, $job) {
        $row['status'] = 'snapshot';
        $row['body_encoding'] = 'base64';
        $row['body_base64'] = base64_encode($job->getData());
        return $row;
    }

    /**
     * Builds manifest fields shared by review return/delete operation outcomes.
     *
     * @param array $manifestJob Collapsed manifest row for the review-copy job.
     * @return array
     */
    private function buildOperationRow($manifestJob) {
        return array(
            'original_id' => isset($manifestJob['original_id']) ? $manifestJob['original_id'] : null,
            'review_id' => isset($manifestJob['review_id']) ? $manifestJob['review_id'] : null,
        );
    }

    /**
     * Detects beanstalkd NOT_FOUND errors.
     *
     * @param Exception $e Queue exception.
     * @return bool
     */
    private function isBeanstalkNotFound($e) {
        return strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) !== false;
    }
}
