<?php
$batch = $reviewBatch;
$operation = isset($reviewOperation) ? $reviewOperation : false;
$jobs = isset($reviewJobs) ? $reviewJobs : array();
$total = isset($reviewJobsTotal) ? (int)$reviewJobsTotal : 0;
$page = isset($reviewPage) ? (int)$reviewPage : 1;
$perPage = isset($reviewPerPage) ? (int)$reviewPerPage : 25;
$pages = $perPage > 0 ? (int)max(1, ceil($total / $perPage)) : 1;
$baseUrl = './?server=' . urlencode($server) . '&action=reviewBatchShow&batchId=' . urlencode($batch['id']) . '&perPage=' . $perPage;
$remainingMovedCount = isset($reviewRemainingMovedCount) ? (int)$reviewRemainingMovedCount : 0;
$pageSelectableCount = isset($reviewPageSelectableCount) ? (int)$reviewPageSelectableCount : 0;
$pageBodyCount = isset($reviewPageBodyCount) ? (int)$reviewPageBodyCount : $pageSelectableCount;
$jobPreviews = isset($reviewJobPreviews) ? $reviewJobPreviews : array();
$jobBodies = isset($reviewJobBodies) ? $reviewJobBodies : array();
$ownedByAnotherSession = $console->isReviewOwnedByAnotherSession($batch);
$takeOverBlockReason = $console->getReviewTakeOverBlockReason($batch, $operation);
$pageActionDisabled = $pageSelectableCount === 0 || $ownedByAnotherSession ? ' disabled="disabled"' : '';
$batchActionDisabled = $remainingMovedCount === 0 || $ownedByAnotherSession ? ' disabled="disabled"' : '';
?>
<h3>Review batch</h3>
<p>
    <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">&lt;&lt; review batches</a>
    |
    <a href="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($batch['source_tube']); ?>">&lt;&lt; source tube</a>
    |
    <a href="./?server=<?php echo urlencode($server); ?>">&lt;&lt; tubes</a>
    |
    <a class="text-danger"
       href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDelete&batchId=<?php echo urlencode($batch['id']); ?>"
       onclick="return confirm('Delete this review batch and any remaining review-copy jobs?');">delete review</a>
</p>

<?php if ($console->isReviewOwnedByAnotherSession($batch)): ?>
    <p class="alert alert-warning">
        Be careful, this review batch was prepared by another session (visiting from <?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?>).
        <?php if ($takeOverBlockReason === ''): ?>
            <a class="btn btn-xs btn-warning"
               href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchTakeOver&batchId=<?php echo urlencode($batch['id']); ?>"
               onclick="return confirm('Take over this review batch from session visiting from <?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?>?');">Take over this review batch</a>
        <?php else: ?>
            Cannot take over yet because <?php echo htmlspecialchars($takeOverBlockReason); ?>.
        <?php endif; ?>
    </p>
<?php endif; ?>
<?php if ($operation && isset($operation['status']) && $operation['status'] === 'processing' && $console->isReviewOwnedByAnotherSession($operation)): ?>
    <p class="alert alert-warning">
        A review operation is currently running from <?php echo htmlspecialchars($console->getReviewOwnerIp($operation)); ?>.
    </p>
<?php endif; ?>

<table class="table table-bordered table-condensed">
    <tbody>
        <tr>
            <th>Source</th>
            <td><?php echo htmlspecialchars($batch['source_tube']); ?> / <?php echo htmlspecialchars($batch['source_state']); ?></td>
        </tr>
        <tr>
            <th>Review tube</th>
            <td><?php echo htmlspecialchars($batch['review_tube']); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo htmlspecialchars($batch['status']); ?></td>
        </tr>
        <tr>
            <th>Prepared</th>
            <td><?php echo (int)$batch['processed']; ?> / <?php echo (int)$batch['target_count']; ?></td>
        </tr>
    </tbody>
</table>

<?php if (!empty($batch['error_message'])): ?>
    <p class="alert alert-danger">
        <strong>Review preparation stopped with an error.</strong>
        <?php echo htmlspecialchars($batch['error_message']); ?>
        Successfully moved jobs below can still be returned or deleted. Review copies from failed source cleanup can be inspected or deleted.
    </p>
<?php endif; ?>

<?php if ($batch['status'] === 'complete' && (int)$batch['processed'] === 0 && (int)$batch['target_count'] > 0): ?>
    <p class="alert alert-warning">
        This batch has no prepared jobs. It can be resumed with the current processor.
        <a class="btn btn-xs btn-warning" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchProgress&batchId=<?php echo urlencode($batch['id']); ?>">Resume preparation</a>
    </p>
<?php endif; ?>

<p>
    <a class="btn btn-sm btn-default" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=jsonl">Download audit log JSONL</a>
    <a class="btn btn-sm btn-default" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=csv">Download current summary CSV</a>
    <?php if (!empty($batch['include_body_snapshot'])): ?>
        <a class="btn btn-sm btn-default" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=body-snapshot">Download body snapshot JSONL</a>
    <?php endif; ?>
    <?php if ($pageBodyCount > 0): ?>
        <button type="button" class="btn btn-sm btn-default" id="reviewToggleBodies" data-expanded="0">Show full bodies on this page</button>
    <?php endif; ?>
</p>

<form id="reviewSingleDeleteForm" method="POST" action="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDeleteJobs&batchId=<?php echo urlencode($batch['id']); ?>">
    <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($batch['id']); ?>">
    <input type="hidden" name="returnPage" value="<?php echo (int)$page; ?>">
    <input type="hidden" name="perPage" value="<?php echo (int)$perPage; ?>">
</form>

<form method="POST">
    <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($batch['id']); ?>">
    <input type="hidden" name="returnPage" value="<?php echo (int)$page; ?>">
    <input type="hidden" name="perPage" value="<?php echo (int)$perPage; ?>">
    <input type="hidden" id="reviewRemainingMovedCount" value="<?php echo (int)$remainingMovedCount; ?>">

    <div class="form-inline" style="margin-bottom: 10px;">
        <label for="reviewReturnDelay">Return delay seconds</label>
        <input id="reviewReturnDelay" class="form-control input-sm" type="number" min="0" step="1" name="delay" value="0">
        <button type="submit" class="btn btn-sm btn-success"
                data-requires-selection="1"
                data-confirm="Return selected review jobs to the source tube?"
                formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchReturnJobs&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $pageActionDisabled; ?>>
            Return selected to source tube
        </button>
        <button type="submit" class="btn btn-sm btn-danger"
                data-requires-selection="1"
                data-confirm="Delete selected review-copy jobs?"
                formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDeleteJobs&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $pageActionDisabled; ?>>
            Delete selected review copies
        </button>
        <button type="submit" name="operation" value="return_all_moved" class="btn btn-sm btn-success"
                data-requires-moved="1"
                data-confirm="Return all remaining moved review jobs to the source tube?"
                formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchOperationStart&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $batchActionDisabled; ?>>
            Return all remaining moved jobs
        </button>
        <button type="submit" name="operation" value="delete_all_moved" class="btn btn-sm btn-danger"
                data-requires-moved="1"
                data-confirm="Delete all remaining moved review-copy jobs?"
                formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchOperationStart&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $batchActionDisabled; ?>>
            Delete all remaining review copies
        </button>
    </div>

    <table class="table table-striped table-bordered table-condensed" id="reviewJobsTable">
        <thead>
            <tr>
                <th>
                    <?php if ($pageSelectableCount > 0): ?>
                        <input type="checkbox" id="reviewSelectAll">
                        <br>
                        <button type="button" class="btn btn-xs btn-link" id="reviewSelectAllButton">all</button>
                        <button type="button" class="btn btn-xs btn-link" id="reviewSelectNoneButton">none</button>
                    <?php endif; ?>
                </th>
                <th>Original ID</th>
                <th>Review ID</th>
                <th>Status</th>
                <th>Pri</th>
                <th>TTR</th>
                <th>Age</th>
                <th>Reserves</th>
                <th>Buries</th>
                <th>Body preview</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
                <?php
                $reviewId = isset($job['review_id']) ? (int)$job['review_id'] : 0;
                $status = isset($job['status']) ? $job['status'] : '';
                $selectable = $status === 'moved';
                $viewable = $console->reviewJobHasInspectableCopy($job);
                $preview = isset($jobPreviews[$reviewId]) ? $jobPreviews[$reviewId] : '';
                $bodyInfo = isset($jobBodies[$reviewId]) ? $jobBodies[$reviewId] : array();
                $fullBody = isset($bodyInfo['body']) ? $bodyInfo['body'] : '';
                $contentType = isset($bodyInfo['content_type']) ? $bodyInfo['content_type'] : 'text';
                $bodySource = isset($bodyInfo['body_source']) ? $bodyInfo['body_source'] : '';
                $ageTitle = '';
                if (!empty($job['job_created_at'])) {
                    $ageTitle = 'Created at ' . $job['job_created_at'];
                } elseif (isset($job['event_at'], $job['age']) && $status === 'moved') {
                    $createdAt = strtotime($job['event_at']) - (int)$job['age'];
                    $ageTitle = 'Created at ' . date('c', $createdAt);
                }
                $rowClass = $console->isReviewJobCleanupStatus($status) ? ' class="warning"' : ($viewable ? '' : ' class="text-muted"');
                ?>
                <tr<?php echo $rowClass; ?>>
                    <td>
                        <?php if ($selectable && $reviewId): ?>
                            <input type="checkbox" class="reviewJobCheckbox" name="job[]" value="<?php echo $reviewId; ?>">
                        <?php endif; ?>
                    </td>
                    <td><?php echo isset($job['original_id']) ? (int)$job['original_id'] : ''; ?></td>
                    <td><?php echo $reviewId; ?></td>
                    <td><?php echo htmlspecialchars($status); ?></td>
                    <td><?php echo isset($job['pri']) ? (int)$job['pri'] : ''; ?></td>
                    <td><?php echo isset($job['ttr']) ? (int)$job['ttr'] : ''; ?></td>
                    <td<?php echo $ageTitle !== '' ? ' title="' . htmlspecialchars($ageTitle) . '"' : ''; ?>><?php echo isset($job['age']) ? $console->formatDuration($job['age']) : ''; ?></td>
                    <td><?php echo isset($job['reserves']) ? (int)$job['reserves'] : ''; ?></td>
                    <td><?php echo isset($job['buries']) ? (int)$job['buries'] : ''; ?></td>
                    <td>
                        <?php if ($viewable): ?>
                            <pre class="reviewJobBodyPreview"
                                 data-review-id="<?php echo $reviewId; ?>"
                                 data-preview="<?php echo htmlspecialchars($preview); ?>"
                                 style="max-height: 140px; overflow: auto; white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars($preview); ?></pre>
                            <pre class="reviewJobBodyFull"
                                 data-review-id="<?php echo $reviewId; ?>"
                                 data-content-type="<?php echo htmlspecialchars($contentType); ?>"
                                 data-body-source="<?php echo htmlspecialchars($bodySource); ?>"
                                 style="display: none;"><?php echo htmlspecialchars($fullBody); ?></pre>
                        <?php elseif ($status === 'returned'): ?>
                            Returned to source tube<?php echo isset($job['returned_id']) ? ' as job ' . (int)$job['returned_id'] : ''; ?>.
                        <?php elseif ($status === 'deleted'): ?>
                            Review copy deleted.
                        <?php elseif ($status === 'error'): ?>
                            <span class="text-danger"><?php echo isset($job['error_message']) ? htmlspecialchars($job['error_message']) : 'Review preparation error'; ?></span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($status); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($viewable && $reviewId): ?>
                            <button type="button"
                                    class="btn btn-xs btn-info reviewJobView"
                                    data-review-id="<?php echo $reviewId; ?>"
                                    data-original-id="<?php echo isset($job['original_id']) ? (int)$job['original_id'] : ''; ?>"
                                    data-status="<?php echo htmlspecialchars($status); ?>"
                                    data-pri="<?php echo isset($job['pri']) ? (int)$job['pri'] : ''; ?>"
                                    data-ttr="<?php echo isset($job['ttr']) ? (int)$job['ttr'] : ''; ?>"
                                    data-age="<?php echo isset($job['age']) ? (int)$job['age'] : ''; ?>"
                                    data-reserves="<?php echo isset($job['reserves']) ? (int)$job['reserves'] : ''; ?>"
                                    data-buries="<?php echo isset($job['buries']) ? (int)$job['buries'] : ''; ?>">
                                View
                            </button>
                            <?php if ($console->isReviewJobCleanupStatus($status)): ?>
                                <button type="submit"
                                        class="btn btn-xs btn-danger"
                                        form="reviewSingleDeleteForm"
                                        name="job[]"
                                        value="<?php echo $reviewId; ?>"
                                        data-confirm="Delete this review copy? The source job may already exist."<?php echo $ownedByAnotherSession ? ' disabled="disabled"' : ''; ?>>
                                    Delete review copy
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>

<ul class="pagination">
    <?php
    $paginationItems = array();
    for ($i = 1; $i <= $pages; $i++) {
        if ($i === 1 || $i === $pages || abs($i - $page) <= 2) {
            $paginationItems[] = $i;
        }
    }
    $previousItem = 0;
    foreach ($paginationItems as $paginationItem):
        if ($previousItem && $paginationItem > $previousItem + 1):
            ?>
            <li class="disabled"><span>...</span></li>
            <?php
        endif;
        $previousItem = $paginationItem;
        ?>
        <li class="<?php echo $paginationItem === $page ? 'active' : ''; ?>">
            <a href="<?php echo $baseUrl; ?>&page=<?php echo $paginationItem; ?>"><?php echo $paginationItem; ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="modal fade" id="reviewJobBodyModal" tabindex="-1" role="dialog" aria-labelledby="reviewJobBodyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="reviewJobBodyLabel">Review job body</h4>
            </div>
            <div class="modal-body">
                <table class="table table-condensed table-bordered">
                    <tbody id="reviewJobBodyStats"></tbody>
                </table>
                <pre style="max-height: 520px; overflow: auto; white-space: pre-wrap; word-break: break-word;"><code id="reviewJobBodyContent" style="white-space: pre-wrap; word-break: break-word;"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
