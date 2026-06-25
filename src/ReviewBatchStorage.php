<?php

/**
 * Stores review batch metadata, append-only audit manifests, materialized current state, body snapshots,
 * and long-running operation state.
 */
class ReviewBatchStorage {

    private $path;
    private $error;

    /**
     * Resolves the review storage directory from config and verifies it is usable.
     *
     * @param array $config Application configuration.
     * @throws InvalidArgumentException If the storage directory cannot be created or written.
     */
    public function __construct($config) {
        if (!empty($config['review']['storagePath'])) {
            $this->path = $config['review']['storagePath'];
        } else {
            $this->path = dirname($config['storage']) . DIRECTORY_SEPARATOR . 'review-batches';
        }

        if (!$this->isAvailable()) {
            throw new InvalidArgumentException($this->error);
        }
    }

    /**
     * Ensures the review storage directory exists and is writable.
     *
     * @return bool
     */
    public function isAvailable() {
        if (!is_dir($this->path) && !mkdir($this->path, 0755, true)) {
            $this->error = 'Review batch storage directory could not be created: ' . $this->path;
            return false;
        }
        if (!is_writable($this->path)) {
            $this->error = 'Review batch storage directory must be writable: ' . $this->path;
            return false;
        }
        return true;
    }

    /**
     * Returns the last storage availability error message.
     *
     * @return string|null
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Creates metadata and empty manifest files for a new review batch.
     *
     * @param array $batch Review batch metadata.
     * @return array
     * @throws InvalidArgumentException If batch storage files cannot be written.
     */
    public function createBatch($batch) {
        $this->saveBatch($batch);
        $this->writeFile($this->jobsFile($batch['id']), '');
        $this->writeCurrentState($batch['id'], $this->emptyCurrentState(0));
        if (!empty($batch['include_body_snapshot'])) {
            $this->writeFile($this->bodySnapshotFile($batch['id']), '');
            $this->writeFile($this->bodySnapshotIndexFile($batch['id']), '');
        }
        return $batch;
    }

    /**
     * Persists review batch metadata.
     *
     * @param array $batch Review batch metadata.
     * @return void
     * @throws InvalidArgumentException If metadata cannot be encoded or written.
     */
    public function saveBatch($batch) {
        $this->writeJsonFile($this->batchFile($batch['id']), $batch);
    }

    /**
     * Updates ownership fields on an existing review batch.
     *
     * @param string $batchId Batch id.
     * @param string $sessionId New owner session id.
     * @param string $ip New owner IP.
     * @return array Updated batch metadata.
     * @throws InvalidArgumentException If the batch is missing or metadata cannot be saved.
     */
    public function takeOverBatch($batchId, $sessionId, $ip) {
        $batch = $this->loadBatch($batchId);
        if (!$batch) {
            throw new InvalidArgumentException('Review batch not found');
        }
        $batch['previous_owner_session_id'] = isset($batch['owner_session_id']) ? $batch['owner_session_id'] : '';
        $batch['previous_owner_ip'] = isset($batch['owner_ip']) ? $batch['owner_ip'] : '';
        $batch['owner_session_id'] = $sessionId;
        $batch['owner_ip'] = $ip;
        $batch['owner_taken_over_at'] = date('c');
        $this->saveBatch($batch);
        return $batch;
    }

    /**
     * Loads review batch metadata by batch id.
     *
     * @param string $id Batch id.
     * @return array|false
     */
    public function loadBatch($id) {
        $file = $this->batchFile($id);
        if (!is_file($file)) {
            $file = $this->legacyBatchFile($id);
        }
        if (!is_file($file)) {
            return false;
        }
        return $this->readJsonFile($file);
    }

    /**
     * Lists all stored review batches, newest first.
     *
     * @return array
     */
    public function listBatches() {
        $result = array();
        $files = glob($this->path . DIRECTORY_SEPARATOR . '*.batch.json');
        if (!is_array($files)) {
            $files = array();
        }
        $legacyFiles = glob($this->path . DIRECTORY_SEPARATOR . '*.json');
        if (!is_array($legacyFiles)) {
            $legacyFiles = array();
        }
        foreach ($legacyFiles as $file) {
            if (substr($file, -11) === '.batch.json' || substr($file, -13) === '.current.json' || substr($file, -15) === '.operation.json') {
                continue;
            }
            $files[] = $file;
        }
        foreach ($files as $file) {
            $batch = $this->readJsonFile($file);
            if (is_array($batch) && isset($batch['id'])) {
                $result[$batch['id']] = $batch;
            }
        }
        $result = array_values($result);
        usort($result, array($this, 'sortBatchesNewestFirst'));
        return $result;
    }

    /**
     * Appends one audit event row to the review manifest and syncs the materialized current state.
     *
     * @param string $batchId Batch id.
     * @param array $row Manifest row.
     * @return void
     * @throws InvalidArgumentException If the row cannot be encoded, appended, or reflected in current state.
     */
    public function appendJob($batchId, $row) {
        if (!isset($row['event_at'])) {
            $row['event_at'] = date('c');
        }
        $current = $this->loadCurrentStateForAppend($batchId);
        $line = $this->encodeJson($row) . "\n";
        $this->appendFile($this->jobsFile($batchId), $line);
        $this->applyCurrentStateRow($current, $row);
        $current['audit_size'] = $this->getAuditSize($batchId);
        $current['updated_at'] = date('c');
        $this->writeCurrentState($batchId, $current);
    }

    /**
     * Appends a chunk of body snapshot rows captured during review preparation.
     *
     * @param string $batchId Batch id.
     * @param array $rows Body snapshot rows.
     * @return void
     * @throws InvalidArgumentException If the snapshot or index cannot be encoded, locked, or written.
     */
    public function appendBodySnapshotRows($batchId, $rows) {
        if (!count($rows)) {
            return;
        }

        $snapshotFile = $this->bodySnapshotFile($batchId);
        $indexData = '';
        $snapshotHandle = fopen($snapshotFile, 'ab');
        if (!$snapshotHandle) {
            throw new InvalidArgumentException('Review batch body snapshot could not be opened: ' . $snapshotFile);
        }
        if (!flock($snapshotHandle, LOCK_EX)) {
            fclose($snapshotHandle);
            throw new InvalidArgumentException('Review batch body snapshot could not be locked: ' . $snapshotFile);
        }

        try {
            if (fseek($snapshotHandle, 0, SEEK_END) !== 0) {
                throw new InvalidArgumentException('Review batch body snapshot could not be sought: ' . $snapshotFile);
            }
            $offset = ftell($snapshotHandle);
            if ($offset === false) {
                throw new InvalidArgumentException('Review batch body snapshot offset could not be read: ' . $snapshotFile);
            }

            foreach ($rows as $row) {
                if (!isset($row['snapshot_at'])) {
                    $row['snapshot_at'] = date('c');
                }
                $line = $this->encodeJson($row) . "\n";
                $length = strlen($line);
                $this->writeAll($snapshotHandle, $line, $snapshotFile);
                if (isset($row['review_id'])) {
                    $indexData .= $this->encodeJson(array(
                        'review_id' => (int)$row['review_id'],
                        'offset' => $offset,
                        'length' => $length,
                    )) . "\n";
                }
                $offset += $length;
            }

            if (!fflush($snapshotHandle)) {
                throw new InvalidArgumentException('Review batch body snapshot could not be flushed: ' . $snapshotFile);
            }
        } catch (Exception $e) {
            flock($snapshotHandle, LOCK_UN);
            fclose($snapshotHandle);
            throw $e;
        }

        flock($snapshotHandle, LOCK_UN);
        fclose($snapshotHandle);

        if ($indexData !== '') {
            $this->appendFile($this->bodySnapshotIndexFile($batchId), $indexData);
        }
    }

    /**
     * Records a new audit event for an existing reviewed job.
     *
     * @param string $batchId Batch id.
     * @param array $row Manifest row.
     * @return void
     * @throws InvalidArgumentException If the row cannot be encoded or appended.
     */
    public function updateJob($batchId, $row) {
        $this->appendJob($batchId, $row);
    }

    /**
     * Returns a paginated collapsed view of the review manifest.
     *
     * @param string $batchId Batch id.
     * @param int $offset Zero-based row offset.
     * @param int $limit Maximum number of rows.
     * @return array
     */
    public function getJobs($batchId, $offset, $limit) {
        $summary = $this->getBatchSummary($batchId, $offset, $limit);
        return $summary['jobs'];
    }

    /**
     * Counts reviewed jobs after collapsing manifest events by job.
     *
     * @param string $batchId Batch id.
     * @return int
     */
    public function countJobs($batchId) {
        $summary = $this->getBatchSummary($batchId, 0, 0);
        return $summary['total'];
    }

    /**
     * Returns collapsed jobs plus aggregate counts from materialized current state.
     *
     * @param string $batchId Batch id.
     * @param int $offset Zero-based row offset.
     * @param int|null $limit Maximum rows, or null for all remaining rows.
     * @return array
     */
    public function getBatchSummary($batchId, $offset = 0, $limit = null) {
        $current = $this->loadCurrentState($batchId);
        $jobs = array_values($current['jobs']);
        $total = (int)$current['total'];
        $movedCount = (int)$current['moved_count'];

        if ($limit === null) {
            $pageJobs = array_slice($jobs, $offset);
        } else {
            $pageJobs = array_slice($jobs, $offset, $limit);
        }

        $pageSelectableCount = 0;
        foreach ($pageJobs as $job) {
            if (isset($job['status'], $job['review_id']) && $job['status'] === 'moved' && (int)$job['review_id'] > 0) {
                $pageSelectableCount++;
            }
        }

        return array(
            'jobs' => $pageJobs,
            'total' => $total,
            'moved_count' => $movedCount,
            'page_selectable_count' => $pageSelectableCount,
        );
    }

    /**
     * Returns the next moved review jobs for chunked operations from materialized current state.
     *
     * @param string $batchId Batch id.
     * @param int $limit Maximum number of moved jobs to return.
     * @return array
     */
    public function getMovedJobs($batchId, $limit) {
        $current = $this->loadCurrentState($batchId);
        $jobs = array_values($current['jobs']);
        $result = array();
        foreach ($jobs as $job) {
            if (isset($job['status']) && $job['status'] === 'moved') {
                $result[] = $job;
                if (count($result) >= $limit) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Finds collapsed manifest rows for multiple review job ids from materialized current state.
     *
     * @param string $batchId Batch id.
     * @param array $reviewIds Review-copy job ids.
     * @return array Rows keyed by review-copy job id.
     */
    public function getJobsByReviewIds($batchId, $reviewIds) {
        $filter = array();
        foreach ($reviewIds as $reviewId) {
            $reviewId = (int)$reviewId;
            if ($reviewId > 0) {
                $filter[$reviewId] = true;
            }
        }
        if (!count($filter)) {
            return array();
        }

        $result = array();
        $current = $this->loadCurrentState($batchId);
        $jobs = $current['jobs'];
        foreach ($jobs as $job) {
            if (isset($job['review_id']) && isset($filter[(int)$job['review_id']])) {
                $result[(int)$job['review_id']] = $job;
            }
        }
        return $result;
    }

    /**
     * Returns the audit manifest file path for a batch.
     *
     * @param string $batchId Batch id.
     * @return string
     */
    public function getJobsFile($batchId) {
        return $this->jobsFile($batchId);
    }

    /**
     * Returns the batch metadata file path.
     *
     * @param string $batchId Batch id.
     * @return string
     */
    public function getBatchFile($batchId) {
        return $this->batchFile($batchId);
    }

    /**
     * Returns the body snapshot file path for a batch.
     *
     * @param string $batchId Batch id.
     * @return string
     */
    public function getBodySnapshotFile($batchId) {
        return $this->bodySnapshotFile($batchId);
    }

    /**
     * Returns body snapshot rows keyed by review job id.
     *
     * @param string $batchId Batch id.
     * @param array|null $reviewIds Optional review-copy job ids to load.
     * @return array
     */
    public function getBodySnapshots($batchId, $reviewIds = null) {
        $file = $this->bodySnapshotFile($batchId);
        $snapshots = array();
        $filter = array();

        if (!is_file($file)) {
            return $snapshots;
        }
        if (is_array($reviewIds)) {
            foreach ($reviewIds as $reviewId) {
                $filter[(int)$reviewId] = true;
            }
            if (!count($filter)) {
                return $snapshots;
            }
            $indexedSnapshots = $this->getBodySnapshotsByIndex($batchId, $filter);
            if ($indexedSnapshots !== false && count($indexedSnapshots) === count($filter)) {
                return $indexedSnapshots;
            }
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            return $snapshots;
        }

        while (($line = fgets($handle)) !== false) {
            $row = $this->decodeBodySnapshotLine($line);
            if (!is_array($row) || !isset($row['review_id'])) {
                continue;
            }
            $reviewId = (int)$row['review_id'];
            if ($reviewIds !== null && !isset($filter[$reviewId])) {
                continue;
            }
            $snapshots[$reviewId] = $row;
            if ($reviewIds !== null) {
                unset($filter[$reviewId]);
                if (!count($filter)) {
                    break;
                }
            }
        }

        fclose($handle);
        return $snapshots;
    }

    /**
     * Deletes all local files associated with a review batch.
     *
     * @param string $batchId Batch id.
     * @return void
     * @throws InvalidArgumentException If any existing batch file cannot be deleted.
     */
    public function deleteBatch($batchId) {
        $this->deleteFileIfExists($this->operationFile($batchId));
        $this->deleteFileIfExists($this->bodySnapshotIndexFile($batchId));
        $this->deleteFileIfExists($this->bodySnapshotFile($batchId));
        $this->deleteFileIfExists($this->currentStateFile($batchId));
        $this->deleteFileIfExists($this->jobsFile($batchId));
        $this->deleteFileIfExists($this->batchFile($batchId));
        $this->deleteFileIfExists($this->legacyBatchFile($batchId));
    }

    /**
     * Saves progress metadata for a long-running review operation.
     *
     * @param array $operation Operation metadata.
     * @return void
     * @throws InvalidArgumentException If operation metadata cannot be encoded or written.
     */
    public function saveOperation($operation) {
        $this->writeJsonFile($this->operationFile($operation['batch_id']), $operation);
    }

    /**
     * Starts a long-running review operation if no other one is processing.
     *
     * @param array $operation Operation metadata.
     * @return void
     * @throws InvalidArgumentException If another operation is active or operation metadata cannot be saved.
     */
    public function startOperation($operation) {
        $file = $this->operationFile($operation['batch_id']);
        $handle = fopen($file, 'c+');
        if (!$handle) {
            throw new InvalidArgumentException('Review batch operation could not be opened: ' . $file);
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new InvalidArgumentException('Review batch operation could not be locked: ' . $file);
            }
            rewind($handle);
            $contents = stream_get_contents($handle);
            $existing = $contents !== false && trim($contents) !== '' ? json_decode($contents, true) : false;
            if ($existing && isset($existing['status']) && $existing['status'] === 'processing') {
                $owner = isset($existing['owner_ip']) ? $existing['owner_ip'] : 'another session';
                throw new InvalidArgumentException('Review batch operation is already running by ' . $owner . '.');
            }

            $data = $this->encodeJson($operation);
            if (!ftruncate($handle, 0)) {
                throw new InvalidArgumentException('Review batch operation could not be truncated: ' . $file);
            }
            rewind($handle);
            $this->writeAll($handle, $data, $file);
            if (!fflush($handle)) {
                throw new InvalidArgumentException('Review batch operation could not be flushed: ' . $file);
            }
            flock($handle, LOCK_UN);
            fclose($handle);
        } catch (Exception $e) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Loads progress metadata for a long-running review operation.
     *
     * @param string $batchId Batch id.
     * @return array|false
     */
    public function loadOperation($batchId) {
        $file = $this->operationFile($batchId);
        if (!is_file($file)) {
            return false;
        }
        return $this->readJsonFile($file);
    }

    /**
     * Deletes progress metadata for a long-running review operation.
     *
     * @param string $batchId Batch id.
     * @return void
     * @throws InvalidArgumentException If operation metadata cannot be deleted.
     */
    public function deleteOperation($batchId) {
        $this->deleteFileIfExists($this->operationFile($batchId));
    }

    /**
     * Collapses the append-only audit manifest into the latest row for each reviewed job.
     *
     * Normal reads use the materialized current-state file; this only runs when that cache must be rebuilt.
     *
     * @param string $batchId Batch id.
     * @return array
     */
    private function readCollapsedJobs($batchId) {
        $file = $this->jobsFile($batchId);
        $jobs = array();
        if (!is_file($file)) {
            return $jobs;
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            return $jobs;
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $row = json_decode($line, true);
            if (!is_array($row)) {
                continue;
            }
            $key = $this->currentStateJobKey($row);
            if ($key === false) {
                continue;
            }
            if (!isset($jobs[$key])) {
                $jobs[$key] = array();
            }
            $jobs[$key] = array_merge($jobs[$key], $row);
        }
        fclose($handle);

        return $jobs;
    }

    /**
     * Loads materialized current job state, rebuilding it from audit JSONL when stale or missing.
     *
     * @param string $batchId Batch id.
     * @return array
     * @throws InvalidArgumentException If current state cannot be rebuilt or saved.
     */
    private function loadCurrentState($batchId) {
        $file = $this->currentStateFile($batchId);
        $auditSize = $this->getAuditSize($batchId);
        if (is_file($file)) {
            $current = $this->readJsonFile($file);
            if ($this->isValidCurrentState($current, $auditSize)) {
                return $current;
            }
        }
        return $this->rebuildCurrentState($batchId);
    }

    /**
     * Loads current state before appending an audit row.
     *
     * @param string $batchId Batch id.
     * @return array
     * @throws InvalidArgumentException If current state cannot be rebuilt or saved.
     */
    private function loadCurrentStateForAppend($batchId) {
        return $this->loadCurrentState($batchId);
    }

    /**
     * Rebuilds materialized current state from the append-only audit manifest.
     *
     * @param string $batchId Batch id.
     * @return array
     * @throws InvalidArgumentException If rebuilt state cannot be saved.
     */
    private function rebuildCurrentState($batchId) {
        $current = $this->emptyCurrentState($this->getAuditSize($batchId));
        $current['jobs'] = $this->readCollapsedJobs($batchId);
        $this->refreshCurrentStateCounts($current);
        $current['updated_at'] = date('c');
        $this->writeCurrentState($batchId, $current);
        return $current;
    }

    /**
     * Applies one audit row to materialized current state.
     *
     * @param array $current Current state, modified in place.
     * @param array $row Audit manifest row.
     * @return void
     */
    private function applyCurrentStateRow(&$current, $row) {
        $key = $this->currentStateJobKey($row);
        if ($key === false) {
            return;
        }

        $oldStatus = isset($current['jobs'][$key]['status']) ? $current['jobs'][$key]['status'] : null;
        $isNew = !isset($current['jobs'][$key]);
        if ($isNew) {
            $current['jobs'][$key] = array();
            $current['total'] = (int)$current['total'] + 1;
        }

        $current['jobs'][$key] = array_merge($current['jobs'][$key], $row);
        $newStatus = isset($current['jobs'][$key]['status']) ? $current['jobs'][$key]['status'] : null;
        if ($oldStatus === 'moved' && $newStatus !== 'moved') {
            $current['moved_count'] = max(0, (int)$current['moved_count'] - 1);
        } elseif ($oldStatus !== 'moved' && $newStatus === 'moved') {
            $current['moved_count'] = (int)$current['moved_count'] + 1;
        }
    }

    /**
     * Recomputes current state aggregate counts from jobs.
     *
     * @param array $current Current state, modified in place.
     * @return void
     */
    private function refreshCurrentStateCounts(&$current) {
        $current['total'] = count($current['jobs']);
        $current['moved_count'] = 0;
        foreach ($current['jobs'] as $job) {
            if (isset($job['status']) && $job['status'] === 'moved') {
                $current['moved_count'] = (int)$current['moved_count'] + 1;
            }
        }
    }

    /**
     * Builds an empty materialized current state document.
     *
     * @param int $auditSize Current audit manifest file size.
     * @return array
     */
    private function emptyCurrentState($auditSize) {
        return array(
            'version' => 1,
            'audit_size' => (int)$auditSize,
            'total' => 0,
            'moved_count' => 0,
            'jobs' => array(),
            'updated_at' => date('c'),
        );
    }

    /**
     * Checks whether a materialized current state document matches the audit file and its own counts.
     *
     * @param mixed $current Decoded current state.
     * @param int $auditSize Current audit manifest file size.
     * @return bool
     */
    private function isValidCurrentState($current, $auditSize) {
        if (!is_array($current)
                || !isset($current['version'], $current['audit_size'], $current['total'], $current['moved_count'], $current['jobs'])
                || (int)$current['version'] !== 1
                || (int)$current['audit_size'] !== (int)$auditSize
                || !is_array($current['jobs'])) {
            return false;
        }

        $movedCount = 0;
        foreach ($current['jobs'] as $job) {
            if (is_array($job) && isset($job['status']) && $job['status'] === 'moved') {
                $movedCount++;
            }
        }

        return (int)$current['total'] === count($current['jobs'])
                && (int)$current['moved_count'] === $movedCount;
    }

    /**
     * Writes materialized current state.
     *
     * @param string $batchId Batch id.
     * @param array $current Current state.
     * @return void
     * @throws InvalidArgumentException If current state cannot be written.
     */
    private function writeCurrentState($batchId, $current) {
        $this->writeJsonFile($this->currentStateFile($batchId), $current);
    }

    /**
     * Returns current audit manifest file size.
     *
     * @param string $batchId Batch id.
     * @return int
     */
    private function getAuditSize($batchId) {
        $file = $this->jobsFile($batchId);
        clearstatcache(true, $file);
        return is_file($file) ? (int)filesize($file) : 0;
    }

    /**
     * Builds the current-state key for one audit row.
     *
     * @param array $row Audit manifest row.
     * @return string|false
     */
    private function currentStateJobKey($row) {
        if (isset($row['original_id'])) {
            return 'o:' . $row['original_id'];
        }
        if (isset($row['review_id'])) {
            return 'r:' . $row['review_id'];
        }
        return false;
    }

    /**
     * Reads requested body snapshot rows by byte offset when the sidecar index is available.
     *
     * @param string $batchId Batch id.
     * @param array $filter Review-copy job ids keyed by id.
     * @return array|false
     */
    private function getBodySnapshotsByIndex($batchId, $filter) {
        $indexFile = $this->bodySnapshotIndexFile($batchId);
        $snapshotFile = $this->bodySnapshotFile($batchId);
        $index = array();
        $snapshots = array();

        if (!is_file($indexFile) || !is_file($snapshotFile)) {
            return false;
        }

        $indexHandle = fopen($indexFile, 'r');
        if (!$indexHandle) {
            return false;
        }
        while (($line = fgets($indexHandle)) !== false) {
            $row = json_decode($line, true);
            if (!is_array($row) || !isset($row['review_id'], $row['offset'], $row['length'])) {
                continue;
            }
            $reviewId = (int)$row['review_id'];
            if (isset($filter[$reviewId])) {
                $index[$reviewId] = array(
                    'review_id' => $reviewId,
                    'offset' => (int)$row['offset'],
                    'length' => (int)$row['length'],
                );
            }
        }
        fclose($indexHandle);

        if (count($index) !== count($filter)) {
            return false;
        }

        uasort($index, array($this, 'sortSnapshotOffsetsAscending'));
        $snapshotHandle = fopen($snapshotFile, 'rb');
        if (!$snapshotHandle) {
            return false;
        }

        foreach ($index as $entry) {
            if (fseek($snapshotHandle, $entry['offset']) !== 0) {
                fclose($snapshotHandle);
                return false;
            }
            $line = fgets($snapshotHandle, $entry['length'] + 1);
            if ($line === false) {
                fclose($snapshotHandle);
                return false;
            }
            $row = $this->decodeBodySnapshotLine($line);
            if (!is_array($row) || !isset($row['review_id'])) {
                fclose($snapshotHandle);
                return false;
            }
            $snapshots[(int)$row['review_id']] = $row;
        }

        fclose($snapshotHandle);
        return $snapshots;
    }

    /**
     * Decodes one body snapshot JSONL row and normalizes the body field.
     *
     * @param string $line JSONL row.
     * @return array|false
     */
    private function decodeBodySnapshotLine($line) {
        $row = json_decode($line, true);
        if (!is_array($row) || !isset($row['review_id'])) {
            return false;
        }
        $body = '';
        if (isset($row['body_encoding']) && $row['body_encoding'] === 'base64' && isset($row['body_base64'])) {
            $decoded = base64_decode($row['body_base64'], true);
            $body = $decoded === false ? '' : $decoded;
        } elseif (isset($row['body'])) {
            $body = $row['body'];
        }
        $row['body'] = $body;
        return $row;
    }

    /**
     * Writes one JSON document atomically enough for this file-based storage.
     *
     * @param string $file Destination file path.
     * @param mixed $data JSON-serializable data.
     * @return void
     * @throws InvalidArgumentException If the data cannot be encoded or written.
     */
    private function writeJsonFile($file, $data) {
        $this->writeFile($file, $this->encodeJson($data));
    }

    /**
     * Reads one JSON document from disk.
     *
     * @param string $file Source file path.
     * @return mixed
     */
    private function readJsonFile($file) {
        return json_decode(file_get_contents($file), true);
    }

    /**
     * Encodes JSON and fails loudly when data cannot be represented.
     *
     * @param mixed $data JSON-serializable data.
     * @return string
     * @throws InvalidArgumentException If JSON encoding fails.
     */
    private function encodeJson($data) {
        $json = json_encode($data);
        if ($json === false) {
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'JSON encode failed';
            throw new InvalidArgumentException($message);
        }
        return $json;
    }

    /**
     * Appends file contents and checks that all bytes were written.
     *
     * @param string $file Destination file path.
     * @param string $data Data to append.
     * @return void
     * @throws InvalidArgumentException If the append fails or writes only part of the data.
     */
    private function appendFile($file, $data) {
        $result = file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
        if ($result === false || $result !== strlen($data)) {
            throw new InvalidArgumentException('Could not append review batch file: ' . $file);
        }
    }

    /**
     * Writes file contents through a temporary file and rename.
     *
     * @param string $file Destination file path.
     * @param string $data Data to write.
     * @return void
     * @throws InvalidArgumentException If the temporary write or rename fails.
     */
    private function writeFile($file, $data) {
        $tmp = $file . '.tmp';
        $result = file_put_contents($tmp, $data, LOCK_EX);
        if ($result === false || $result !== strlen($data)) {
            throw new InvalidArgumentException('Could not write review batch file: ' . $tmp);
        }
        if (!rename($tmp, $file)) {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
            throw new InvalidArgumentException('Could not move review batch file into place: ' . $file);
        }
    }

    /**
     * Writes a full string to an open file handle.
     *
     * @param resource $handle Open file handle.
     * @param string $data Data to write.
     * @param string $file Destination file path for error reporting.
     * @return void
     * @throws InvalidArgumentException If the full string cannot be written.
     */
    private function writeAll($handle, $data, $file) {
        $written = 0;
        $length = strlen($data);
        while ($written < $length) {
            $result = fwrite($handle, substr($data, $written));
            if ($result === false || $result === 0) {
                throw new InvalidArgumentException('Could not write review batch file: ' . $file);
            }
            $written += $result;
        }
    }

    /**
     * Deletes one file when it exists.
     *
     * @param string $file File path.
     * @return void
     * @throws InvalidArgumentException If the file still exists after deletion is attempted.
     */
    private function deleteFileIfExists($file) {
        if (is_file($file) && !@unlink($file) && is_file($file)) {
            throw new InvalidArgumentException('Review batch file could not be deleted: ' . $file);
        }
    }

    /**
     * Builds the batch metadata path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function batchFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.batch.json';
    }

    /**
     * Builds the legacy batch metadata path used before batch files had a type suffix.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function legacyBatchFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.json';
    }

    /**
     * Builds the audit manifest path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function jobsFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.jobs.jsonl';
    }

    /**
     * Builds the materialized current-state path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function currentStateFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.current.json';
    }

    /**
     * Builds the operation metadata path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function operationFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.operation.json';
    }

    /**
     * Builds the body snapshot path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function bodySnapshotFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.body-snapshot.jsonl';
    }

    /**
     * Builds the body snapshot byte-offset index path for a batch id.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function bodySnapshotIndexFile($id) {
        return $this->path . DIRECTORY_SEPARATOR . $this->sanitizeId($id) . '.body-snapshot.idx.jsonl';
    }

    /**
     * Makes a batch id safe for use as a file name.
     *
     * @param string $id Batch id.
     * @return string
     */
    private function sanitizeId($id) {
        return preg_replace('/[^A-Za-z0-9_.-]/', '_', $id);
    }

    /**
     * Sort callback that presents newest review batches first.
     *
     * @param array $a First batch metadata row.
     * @param array $b Second batch metadata row.
     * @return int
     */
    private function sortBatchesNewestFirst($a, $b) {
        $aCreated = isset($a['created_at']) ? $a['created_at'] : '';
        $bCreated = isset($b['created_at']) ? $b['created_at'] : '';
        return strcmp($bCreated, $aCreated);
    }

    /**
     * Sort callback that reads snapshot rows in file order.
     *
     * @param array $a First index row.
     * @param array $b Second index row.
     * @return int
     */
    private function sortSnapshotOffsetsAscending($a, $b) {
        if ($a['offset'] === $b['offset']) {
            return 0;
        }
        return $a['offset'] < $b['offset'] ? -1 : 1;
    }
}
