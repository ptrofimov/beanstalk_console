<?php
$batch = $reviewBatch;
$operation = isset($reviewOperation) ? $reviewOperation : false;
$ownedByAnotherSession = $console->isReviewOwnedByAnotherSession($batch);
$takeOverBlockReason = $console->getReviewTakeOverBlockReason($batch, $operation);
?>
<h3>Preparing review batch</h3>
<p>
    <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">&lt;&lt; review batches</a>
    |
    <a href="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($batch['source_tube']); ?>">&lt;&lt; source tube</a>
</p>

<?php if ($ownedByAnotherSession): ?>
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

<div id="reviewBatchProgress"
     data-batch-id="<?php echo $ownedByAnotherSession ? '' : htmlspecialchars($batch['id']); ?>"
     data-show-url="./?server=<?php echo urlencode($server); ?>&action=reviewBatchShow&batchId=<?php echo urlencode($batch['id']); ?>">
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
                <td id="reviewBatchStatus"><?php echo htmlspecialchars($batch['status']); ?></td>
            </tr>
            <tr>
                <th>Progress</th>
                <td><span id="reviewBatchProcessed"><?php echo (int)$batch['processed']; ?></span> / <span id="reviewBatchTarget"><?php echo (int)$batch['target_count']; ?></span></td>
            </tr>
        </tbody>
    </table>

    <div class="progress">
        <?php $pct = !empty($batch['target_count']) ? min(100, floor(((int)$batch['processed'] / (int)$batch['target_count']) * 100)) : 100; ?>
        <div id="reviewBatchProgressBar" class="progress-bar" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
    </div>

    <p id="reviewBatchMessage" class="text-muted"><?php echo $ownedByAnotherSession ? 'Preparation is owned by another session.' : 'Processing automatically. The batch is limited to the job count recorded when it started.'; ?></p>
    <p><a class="btn btn-default" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchShow&batchId=<?php echo urlencode($batch['id']); ?>">Open batch</a></p>
</div>
