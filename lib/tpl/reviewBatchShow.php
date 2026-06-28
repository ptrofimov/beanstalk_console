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
$targetTubeOptions = isset($tubes) && is_array($tubes) ? $tubes : array();
if (!in_array($batch['source_tube'], $targetTubeOptions, true)) {
    $targetTubeOptions[] = $batch['source_tube'];
    sort($targetTubeOptions);
}
?>

<div class="modern-review-container">
    <!-- Header & Breadcrumbs -->
    <div class="modern-review-header">
        <div>
            <div class="modern-review-breadcrumbs">
                <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">Review Batches</a>
                <span class="text-muted">/</span>
                <a href="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($batch['source_tube']); ?>"><?php echo htmlspecialchars($batch['source_tube']); ?></a>
                <span class="text-muted">/</span>
                <span class="text-muted">Batch Show</span>
            </div>
            <h2 class="modern-review-title">Review Batch Details</h2>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <!-- Download Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 6px; font-weight: 500; padding: 5px 10px; font-size: 12px;">
                    <i class="glyphicon glyphicon-download"></i> Download <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 5px;">
                    <li><a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=jsonl"><i class="glyphicon glyphicon-list-alt" style="margin-right: 8px;"></i> Audit Log JSONL</a></li>
                    <li><a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=csv"><i class="glyphicon glyphicon-file" style="margin-right: 8px;"></i> Summary CSV</a></li>
                    <?php if (!empty($batch['include_body_snapshot'])): ?>
                        <li><a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDownloadManifest&batchId=<?php echo urlencode($batch['id']); ?>&format=body-snapshot"><i class="glyphicon glyphicon-camera" style="margin-right: 8px;"></i> Body Snapshot JSONL</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- Delete Review Button -->
            <a class="btn btn-sm btn-danger"
               style="border-radius: 6px; font-weight: 500; padding: 5px 10px; font-size: 12px;"
               href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDelete&batchId=<?php echo urlencode($batch['id']); ?>"
               onclick="return confirm('Delete this review batch and any remaining review-copy jobs?');">
                <i class="glyphicon glyphicon-trash"></i> Delete Review
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if ($console->isReviewOwnedByAnotherSession($batch)): ?>
        <p class="alert alert-warning" style="border-radius: 6px; padding: 10px; font-size: 13px;">
            <i class="glyphicon glyphicon-warning-sign"></i> This review batch was prepared by another session (visiting from <strong><?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?></strong>).
            <?php if ($takeOverBlockReason === ''): ?>
                <a class="btn btn-xs btn-warning"
                   style="margin-left: 10px; border-radius: 4px;"
                   href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchTakeOver&batchId=<?php echo urlencode($batch['id']); ?>"
                   onclick="return confirm('Take over this review batch from session visiting from <?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?>?');">Take over batch</a>
            <?php else: ?>
                <span class="text-muted" style="margin-left: 10px;">Cannot take over yet: <?php echo htmlspecialchars($takeOverBlockReason); ?></span>
            <?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($operation && isset($operation['status']) && $operation['status'] === 'processing' && $console->isReviewOwnedByAnotherSession($operation)): ?>
        <p class="alert alert-warning" style="border-radius: 6px; padding: 10px; font-size: 13px;">
            <i class="glyphicon glyphicon-refresh"></i> A review operation is currently running from <strong><?php echo htmlspecialchars($console->getReviewOwnerIp($operation)); ?></strong>.
        </p>
    <?php endif; ?>
    <?php if (!empty($batch['error_message'])): ?>
        <p class="alert alert-danger" style="border-radius: 6px; padding: 10px; font-size: 13px;">
            <i class="glyphicon glyphicon-remove-sign"></i> <strong>Review preparation stopped with an error:</strong>
            <?php echo htmlspecialchars($batch['error_message']); ?>
            <br><span style="font-size: 11px; opacity: 0.9;">Successfully moved jobs below can still be returned or deleted. Review copies from failed source cleanup can be inspected or deleted.</span>
        </p>
    <?php endif; ?>
    <?php if ($batch['status'] === 'complete' && (int)$batch['processed'] === 0 && (int)$batch['target_count'] > 0): ?>
        <p class="alert alert-warning" style="border-radius: 6px; padding: 10px; font-size: 13px;">
            <i class="glyphicon glyphicon-info-sign"></i> This batch has no prepared jobs. It can be resumed with the current processor.
            <a class="btn btn-xs btn-warning" style="margin-left: 10px; border-radius: 4px;" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchProgress&batchId=<?php echo urlencode($batch['id']); ?>">Resume preparation</a>
        </p>
    <?php endif; ?>

    <!-- Two-Column Split Layout -->
    <div class="modern-split-container">
        <!-- Left Column: Source Info & Downloads -->
        <div class="modern-info-card">
            <div class="modern-info-title">Source Details</div>
            <ul class="modern-info-list">
                <li class="modern-info-item"><strong>Source:</strong> <?php echo htmlspecialchars($batch['source_tube']); ?> <span class="text-muted">(<?php echo htmlspecialchars($batch['source_state']); ?>)</span></li>
                <li class="modern-info-item"><strong>Review Tube:</strong> <?php echo htmlspecialchars($batch['review_tube']); ?></li>
                <li class="modern-info-item"><strong>Prepared:</strong> <?php echo (int)$batch['processed']; ?> / <?php echo (int)$batch['target_count']; ?></li>
            </ul>
            <?php if ($pageBodyCount > 0): ?>
                <div style="margin-top: 4px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                    <button type="button" class="btn btn-sm btn-default btn-block" id="reviewToggleBodies" data-expanded="0" style="border-radius: 6px; font-size: 11px; padding: 5px 8px; font-weight: 500; text-align: left;">
                        <i class="glyphicon glyphicon-align-left"></i> Show full bodies on page
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Operations Panel -->
        <div class="modern-ops-card">
            <div class="modern-ops-title">Operations</div>
            <form method="POST" style="margin: 0; padding: 0;">
                <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($batch['id']); ?>">
                <input type="hidden" name="returnPage" value="<?php echo (int)$page; ?>">
                <input type="hidden" name="perPage" value="<?php echo (int)$perPage; ?>">
                <input type="hidden" id="reviewRemainingMovedCount" value="<?php echo (int)$remainingMovedCount; ?>">

                <!-- Destination Inputs Row -->
                <div class="modern-ops-row">
                    <div class="modern-ops-group">
                        <label for="reviewTargetTube">Destination Tube</label>
                        <div class="form-inline">
                            <input id="reviewTargetTube" class="form-control input-sm" style="width: 220px; display: inline-block;" type="text" name="targetTube" value="<?php echo htmlspecialchars($batch['source_tube']); ?>" placeholder="choose tube">
                            <select class="form-control input-sm" style="width: 180px; display: inline-block; margin-left: 5px;" onchange="document.getElementById('reviewTargetTube').value = this.value;">
                                <option value="" selected="selected">-- choose tube --</option>
                                <option value="<?php echo htmlspecialchars($batch['source_tube']); ?>">Source tube (<?php echo htmlspecialchars($batch['source_tube']); ?>)</option>
                                <?php foreach ($targetTubeOptions as $targetTubeOption): ?>
                                    <?php if ($targetTubeOption === $batch['source_tube']) continue; ?>
                                    <option value="<?php echo htmlspecialchars($targetTubeOption); ?>"><?php echo htmlspecialchars($targetTubeOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modern-ops-group">
                        <label for="reviewReturnDelay">Delay (seconds)</label>
                        <div class="form-inline">
                            <input id="reviewReturnDelay" class="form-control input-sm" style="width: 75px; display: inline-block;" type="number" min="0" step="1" name="delay" value="0">
                            <div class="btn-group" style="margin-left: 4px; display: inline-block; vertical-align: middle;">
                                <button type="button" class="btn btn-xs btn-default" onclick="document.getElementById('reviewReturnDelay').value = 60;" style="padding: 3px 6px;">60s</button>
                                <button type="button" class="btn btn-xs btn-default" onclick="document.getElementById('reviewReturnDelay').value = 180;" style="padding: 3px 6px;">180s</button>
                                <button type="button" class="btn btn-xs btn-default" onclick="document.getElementById('reviewReturnDelay').value = 300;" style="padding: 3px 6px;">300s</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Split Row -->
                <div class="modern-ops-actions-row">
                    <div class="modern-ops-action-group">
                        <div class="modern-ops-action-title">Selected Jobs</div>
                        <div class="modern-ops-btn-group">
                            <button type="submit" class="btn btn-sm btn-primary"
                                    data-requires-selection="1"
                                    data-requires-target="1"
                                    data-confirm="Move selected review jobs to the destination tube?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchMoveJobs&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $pageActionDisabled; ?>>
                                Move
                            </button>
                            <button type="submit" class="btn btn-sm btn-default"
                                    data-requires-selection="1"
                                    data-requires-target="1"
                                    data-confirm="Duplicate selected review jobs to the destination tube?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDuplicateJobs&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $pageActionDisabled; ?>>
                                Duplicate
                            </button>
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-requires-selection="1"
                                    data-confirm="Delete selected review-copy jobs?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDeleteJobs&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $pageActionDisabled; ?>>
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="modern-ops-action-group">
                        <div class="modern-ops-action-title">All Undecided Jobs</div>
                        <div class="modern-ops-btn-group">
                            <button type="submit" name="operation" value="move_all_moved" class="btn btn-sm btn-primary"
                                    data-requires-moved="1"
                                    data-requires-target="1"
                                    data-confirm="Move all undecided review jobs to the destination tube?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchOperationStart&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $batchActionDisabled; ?>>
                                Move All
                            </button>
                            <button type="submit" name="operation" value="duplicate_all_moved" class="btn btn-sm btn-default"
                                    data-requires-moved="1"
                                    data-requires-target="1"
                                    data-confirm="Duplicate all undecided review jobs to the destination tube?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchOperationStart&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $batchActionDisabled; ?>>
                                Duplicate All
                            </button>
                            <button type="submit" name="operation" value="delete_all_moved" class="btn btn-sm btn-danger"
                                    data-requires-moved="1"
                                    data-confirm="Delete all undecided review-copy jobs?"
                                    formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchOperationStart&batchId=<?php echo urlencode($batch['id']); ?>"<?php echo $batchActionDisabled; ?>>
                                Delete All
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Single Delete Form (Hidden) -->
    <form id="reviewSingleDeleteForm" method="POST" action="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDeleteJobs&batchId=<?php echo urlencode($batch['id']); ?>">
        <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($batch['id']); ?>">
        <input type="hidden" name="returnPage" value="<?php echo (int)$page; ?>">
        <input type="hidden" name="perPage" value="<?php echo (int)$perPage; ?>">
    </form>

    <!-- Jobs Table Card -->
    <div class="modern-table-card">
        <table class="table table-striped table-hover modern-table" id="reviewJobsTable">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">
                        <?php if ($pageSelectableCount > 0): ?>
                            <input type="checkbox" id="reviewSelectAll" style="margin-bottom: 4px;">
                            <div style="font-size: 9px; font-weight: normal; margin-top: 2px;">
                                <button type="button" class="btn btn-xs btn-link" id="reviewSelectAllButton" style="padding: 0; font-size: 9px; line-height: 1;">all</button>
                                <span style="color: #cbd5e1;">|</span>
                                <button type="button" class="btn btn-xs btn-link" id="reviewSelectNoneButton" style="padding: 0; font-size: 9px; line-height: 1;">none</button>
                            </div>
                        <?php endif; ?>
                    </th>
                    <th>Original ID</th>
                    <th>Stats</th>
                    <th>Age</th>
                    <th>Body preview</th>
                    <th style="width: 140px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <?php
                    $reviewId = isset($job['review_id']) ? (int)$job['review_id'] : 0;
                    $status = isset($job['status']) ? $job['status'] : '';
                    $selectable = in_array($status, array('moved', 'duplicated'), true);
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
                    $rowClassNames = array();
                    if ($console->isReviewJobCleanupStatus($status)) {
                        $rowClassNames[] = 'warning';
                    }
                    if (!$viewable) {
                        $rowClassNames[] = 'text-muted';
                    } else {
                        $rowClassNames[] = 'clickable-row';
                    }
                    $rowClass = count($rowClassNames) > 0 ? ' class="' . implode(' ', $rowClassNames) . '"' : '';
                    ?>
                    <tr<?php echo $rowClass; ?>>
                        <td style="text-align: center;">
                            <?php if ($selectable && $reviewId): ?>
                                <input type="checkbox" class="reviewJobCheckbox" name="job[]" value="<?php echo $reviewId; ?>">
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo isset($job['original_id']) ? (int)$job['original_id'] : '-'; ?></strong></td>
                        <td>
                            pri: <?php echo isset($job['pri']) ? (int)$job['pri'] : 0; ?><br>
                            ttr: <?php echo isset($job['ttr']) ? (int)$job['ttr'] : 0; ?><br>
                            reserves: <?php echo isset($job['reserves']) ? (int)$job['reserves'] : 0; ?><br>
                            buries: <?php echo isset($job['buries']) ? (int)$job['buries'] : 0; ?>
                        </td>
                        <td<?php echo $ageTitle !== '' ? ' title="' . htmlspecialchars($ageTitle) . '"' : ''; ?>><?php echo isset($job['age']) ? $console->formatDuration($job['age']) : ''; ?></td>
                        <td>
                            <?php if ($viewable): ?>
                                <pre class="reviewJobBodyPreview"
                                     data-review-id="<?php echo $reviewId; ?>"
                                     data-preview="<?php echo htmlspecialchars($preview); ?>"
                                     style="max-height: 120px; overflow: auto; white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars($preview); ?></pre>
                                <pre class="reviewJobBodyFull"
                                     data-review-id="<?php echo $reviewId; ?>"
                                     data-content-type="<?php echo htmlspecialchars($contentType); ?>"
                                     data-body-source="<?php echo htmlspecialchars($bodySource); ?>"
                                     style="display: none;"><?php echo htmlspecialchars($fullBody); ?></pre>
                            <?php elseif ($status === 'returned'): ?>
                                <span class="text-success"><i class="glyphicon glyphicon-ok-sign"></i> Returned</span> to source tube<?php echo isset($job['returned_id']) ? ' as job ' . (int)$job['returned_id'] : ''; ?>.
                            <?php elseif ($status === 'deleted'): ?>
                                <span class="text-danger"><i class="glyphicon glyphicon-minus-sign"></i> Deleted</span> review copy.
                            <?php elseif ($status === 'moved_to_tube'): ?>
                                <span class="text-primary"><i class="glyphicon glyphicon-share-alt"></i> Moved</span> to <?php echo isset($job['target_tube']) ? htmlspecialchars($job['target_tube']) : 'destination tube'; ?><?php echo isset($job['target_id']) ? ' as job ' . (int)$job['target_id'] : ''; ?>.
                            <?php elseif ($status === 'duplicated'): ?>
                                <span class="text-info"><i class="glyphicon glyphicon-duplicate"></i> Duplicated</span> to <?php echo isset($job['target_tube']) ? htmlspecialchars($job['target_tube']) : 'destination tube'; ?><?php echo isset($job['target_id']) ? ' as job ' . (int)$job['target_id'] : ''; ?>.
                            <?php elseif ($status === 'error'): ?>
                                <span class="text-danger"><i class="glyphicon glyphicon-remove-sign"></i> <?php echo isset($job['error_message']) ? htmlspecialchars($job['error_message']) : 'Review preparation error'; ?></span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($status); ?>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                <?php if ($viewable && $reviewId): ?>
                                    <button type="button"
                                            class="btn btn-xs btn-info reviewJobView"
                                            style="border-radius: 4px; font-weight: 500;"
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
                                    <?php if ($status === 'duplicated'): ?>
                                        <button type="submit"
                                                class="btn btn-xs btn-default"
                                                style="border-radius: 4px; font-weight: 500;"
                                                name="job[]"
                                                value="<?php echo $reviewId; ?>"
                                                data-requires-target="1"
                                                data-confirm="Duplicate this review job to the destination tube again?"<?php echo $ownedByAnotherSession ? ' disabled="disabled"' : ''; ?>
                                                formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDuplicateJobs&batchId=<?php echo urlencode($batch['id']); ?>">
                                            Duplicate
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($console->isReviewJobCleanupStatus($status)): ?>
                                        <button type="submit"
                                                class="btn btn-xs btn-danger"
                                                style="border-radius: 4px; font-weight: 500;"
                                                form="reviewSingleDeleteForm"
                                                name="job[]"
                                                value="<?php echo $reviewId; ?>"
                                                data-confirm="Delete this review copy? This only removes the copy in the review tube."<?php echo $ownedByAnotherSession ? ' disabled="disabled"' : ''; ?>>
                                            Delete Copy
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="modern-pagination-container">
        <ul class="pagination modern-pagination">
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
    </div>
</div>

<!-- Modal View -->
<div class="modal fade" id="reviewJobBodyModal" tabindex="-1" role="dialog" aria-labelledby="reviewJobBodyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="reviewJobBodyLabel" style="font-weight: normal; color: #0f172a;">Review Job Details</h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <table class="table table-condensed table-bordered" style="border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
                    <tbody id="reviewJobBodyStats"></tbody>
                </table>
                <pre style="max-height: 480px; overflow: auto; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; box-shadow: inset 0 1px 2px 0 rgba(0,0,0,0.05);"><code id="reviewJobBodyContent" style="white-space: pre-wrap; word-break: break-word; color: #334155; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px;"></code></pre>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px; align-items: center;">
                <form method="POST" id="reviewModalJobForm" data-owned="<?php echo $ownedByAnotherSession ? '1' : '0'; ?>" style="margin: 0; display: flex; gap: 6px;">
                    <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($batch['id']); ?>">
                    <input type="hidden" name="returnPage" value="<?php echo (int)$page; ?>">
                    <input type="hidden" name="perPage" value="<?php echo (int)$perPage; ?>">
                    <input type="hidden" name="job[]" id="reviewModalJobId" value="">
                    <input type="hidden" name="targetTube" id="reviewModalTargetTube" value="">
                    <input type="hidden" name="delay" id="reviewModalDelay" value="">
                    
                    <button type="submit" id="reviewModalMoveBtn" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 500;"
                            data-confirm="Move this job to the destination tube?"
                            formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchMoveJobs&batchId=<?php echo urlencode($batch['id']); ?>">
                        Move Job
                    </button>
                    <button type="submit" id="reviewModalDuplicateBtn" class="btn btn-sm btn-default" style="border-radius: 6px; font-weight: 500;"
                            data-confirm="Duplicate this job to the destination tube?"
                            formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDuplicateJobs&batchId=<?php echo urlencode($batch['id']); ?>">
                        Duplicate Job
                    </button>
                    <button type="submit" id="reviewModalDeleteBtn" class="btn btn-sm btn-danger" style="border-radius: 6px; font-weight: 500;"
                            data-confirm="Delete this job?"
                            formaction="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDeleteJobs&batchId=<?php echo urlencode($batch['id']); ?>">
                        Delete Job
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-default" style="border-radius: 6px; font-weight: 500;" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
