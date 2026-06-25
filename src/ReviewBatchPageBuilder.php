<?php

/**
 * Builds review batch page data and owns review-specific display helpers.
 */
class ReviewBatchPageBuilder {

    private $interface;
    private $storage;

    /**
     * Stores queue and review storage dependencies.
     *
     * @param BeanstalkInterface $interface Queue interface wrapper.
     * @param ReviewBatchStorage $storage Review batch storage.
     */
    public function __construct($interface, $storage) {
        $this->interface = $interface;
        $this->storage = $storage;
    }

    /**
     * Builds template variables for the paginated review batch page.
     *
     * @param array $batch Review batch metadata.
     * @param int $page One-based page number.
     * @param int $perPage Page size.
     * @param int $previewLength Maximum preview length.
     * @return array Template variables for reviewBatchShow.
     * @throws InvalidArgumentException If review storage cannot read or update the batch.
     */
    public function buildShowPage($batch, $page, $perPage, $previewLength) {
        $activeOperation = $this->storage->loadOperation($batch['id']);
        $offset = ($page - 1) * $perPage;
        $summary = $this->storage->getBatchSummary($batch['id'], $offset, $perPage);
        $jobs = $summary['jobs'];
        $previews = array();
        $bodies = array();
        $previewReviewIds = array();
        $reviewJobIndexes = array();
        $manifestUpdated = false;

        // First identify visible rows that should still have a review-copy body to inspect.
        foreach ($jobs as $jobIndex => $job) {
            if ($this->reviewJobHasInspectableCopy($job)) {
                $reviewId = (int)$job['review_id'];
                $previewReviewIds[] = $reviewId;
                $reviewJobIndexes[$reviewId] = $jobIndex;
            }
        }

        // Body snapshots are preferred because they show the exact payload captured during preparation.
        $bodySnapshots = !empty($batch['include_body_snapshot'])
                ? $this->storage->getBodySnapshots($batch['id'], $previewReviewIds)
                : array();
        foreach ($previewReviewIds as $reviewId) {
            if (isset($bodySnapshots[$reviewId])) {
                $display = $this->formatJobBodyForDisplay($bodySnapshots[$reviewId]['body'], false);
                $bodies[$reviewId] = array(
                    'body' => $display['body'],
                    'content_type' => $display['content_type'],
                    'body_source' => 'snapshot',
                );
                $previews[$reviewId] = $this->truncateReviewBody($display['body'], $previewLength);
                continue;
            }

            try {
                $display = $this->getLiveReviewJobBodyDisplay($reviewId);
                $bodies[$reviewId] = array(
                    'body' => $display['body'],
                    'content_type' => $display['content_type'],
                    'body_source' => 'live',
                );
                $previews[$reviewId] = $this->truncateReviewBody($display['body'], $previewLength);
            } catch (Exception $e) {
                if ($this->isBeanstalkNotFound($e)) {
                    // Missing review copies are reflected back into the manifest before rendering.
                    $jobIndex = isset($reviewJobIndexes[$reviewId]) ? $reviewJobIndexes[$reviewId] : null;
                    $this->storage->updateJob($batch['id'], array(
                        'original_id' => $jobIndex !== null && isset($jobs[$jobIndex]['original_id']) ? $jobs[$jobIndex]['original_id'] : null,
                        'review_id' => $reviewId,
                        'status' => 'missing_review_job',
                        'error_message' => $e->getMessage(),
                    ));
                    if ($jobIndex !== null) {
                        $jobs[$jobIndex]['status'] = 'missing_review_job';
                        $jobs[$jobIndex]['error_message'] = $e->getMessage();
                    }
                    $manifestUpdated = true;
                    $previews[$reviewId] = '';
                    continue;
                }

                $message = 'Unable to load review body: ' . $e->getMessage();
                $bodies[$reviewId] = array(
                    'body' => $message,
                    'content_type' => 'text',
                    'body_source' => 'error',
                );
                $previews[$reviewId] = $this->truncateReviewBody($message, $previewLength);
            }
        }

        if ($manifestUpdated) {
            $summary = $this->storage->getBatchSummary($batch['id'], $offset, $perPage);
            $jobs = $summary['jobs'];
        }

        return array(
            'reviewBatch' => $batch,
            'reviewOperation' => $activeOperation,
            'reviewJobs' => $jobs,
            'reviewJobsTotal' => $summary['total'],
            'reviewPage' => $page,
            'reviewPerPage' => $perPage,
            'reviewRemainingMovedCount' => $summary['moved_count'],
            'reviewPageSelectableCount' => $summary['page_selectable_count'],
            'reviewPageBodyCount' => count($bodies),
            'reviewJobPreviews' => $previews,
            'reviewJobBodies' => $bodies,
        );
    }

    /**
     * Returns a formatted display body for a live review-copy job.
     *
     * @param int $reviewId Review-copy job id.
     * @return array
     * @throws Exception If the live review-copy job cannot be peeked.
     */
    public function getLiveReviewJobBodyDisplay($reviewId) {
        $job = $this->interface->_client->peek((int)$reviewId);
        if (!$job) {
            throw new Exception('Review job not found');
        }
        return $this->formatJobBodyForDisplay($job->getData(), false);
    }

    /**
     * Returns whether the collapsed manifest row should still have a review-copy job to inspect.
     *
     * @param array $job Collapsed manifest row.
     * @return bool
     */
    public function reviewJobHasInspectableCopy($job) {
        if (!isset($job['status'], $job['review_id']) || (int)$job['review_id'] <= 0) {
            return false;
        }
        return $job['status'] === 'moved' || $this->isReviewJobCleanupStatus($job['status']);
    }

    /**
     * Returns whether the manifest status represents a leftover review copy needing cleanup.
     *
     * @param string $status Manifest status.
     * @return bool
     */
    public function isReviewJobCleanupStatus($status) {
        return in_array($status, array('copy_delete_error', 'return_delete_error'), true);
    }

    /**
     * Truncates a formatted body for table preview display.
     *
     * @param string $body Formatted job body.
     * @param int $length Maximum preview length.
     * @return string
     */
    public function truncateReviewBody($body, $length = 100) {
        if (strlen($body) > $length) {
            return substr($body, 0, $length) . '...';
        }
        return $body;
    }

    /**
     * Formats a seconds value in the same days/hours/minutes/seconds style as job stats.
     *
     * @param int $value Duration in seconds.
     * @return string
     */
    public function formatDuration($value) {
        $value = (int)$value;
        $days = floor($value / 86400);
        $hours = floor($value / 3600) % 24;
        $minutes = floor($value / 60) % 60;
        $seconds = floor($value % 60);
        $parts = array();

        if ($days > 0) {
            $parts[] = 'days: ' . $days;
        }
        if ($hours > 0) {
            $parts[] = 'hours: ' . $hours;
        }
        if ($minutes > 0) {
            $parts[] = 'minutes: ' . $minutes;
        }
        if ($seconds > 0 || !count($parts)) {
            $parts[] = 'seconds: ' . $seconds;
        }

        return implode('<br>', $parts);
    }

    /**
     * Applies configured body decoders and formats a job body for display/export.
     *
     * @param string $body Raw job body.
     * @param bool $html Whether to HTML-escape the formatted body.
     * @return array
     */
    public function formatJobBodyForDisplay($body, $html) {
        $settings = new Settings();
        $contentType = 'text';
        $display = $body;

        if ($settings->isBase64DecodeEnabled()) {
            $decoded = base64_decode($display, true);
            if ($decoded !== false) {
                $display = $decoded;
                $contentType = 'base64';
            }
        }

        if ($settings->isUnserializationEnabled()) {
            $unserialized = @unserialize($display);
            if ($unserialized !== false || $display === serialize(false)) {
                $display = print_r($unserialized, true);
                $contentType = 'php';
            }
        }

        if ($settings->isJsonDecodeEnabled()) {
            $decodedJson = json_decode($display, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $display = json_encode($decodedJson, JSON_PRETTY_PRINT);
                $contentType = 'json';
            }
        }

        if (!is_string($display)) {
            $display = print_r($display, true);
        }
        if (!preg_match('//u', $display)) {
            $display = base64_encode($display);
            $contentType = 'base64';
        }

        return array(
            'body' => $html ? htmlspecialchars($display) : $display,
            'content_type' => $contentType,
        );
    }

    /**
     * Detects beanstalkd NOT_FOUND errors so missing review copies can be reconciled.
     *
     * @param Exception $e Queue exception.
     * @return bool
     */
    public function isBeanstalkNotFound($e) {
        return strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) !== false;
    }
}
