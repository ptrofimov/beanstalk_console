<?php
$batch = $reviewBatch;
$operation = $reviewOperation;
$label = $operation['operation'] === 'return_all_moved' ? 'Returning jobs to source tube' : 'Deleting review copies';
$ownedByAnotherSession = $console->isReviewOwnedByAnotherSession($operation);
$takeOverBlockReason = $console->getReviewTakeOverBlockReason($batch, $operation);
?>
<h3><?php echo htmlspecialchars($label); ?></h3>
<p>
    <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchShow&batchId=<?php echo urlencode($batch['id']); ?>">&lt;&lt; review batch</a>
    |
    <a href="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($batch['source_tube']); ?>">&lt;&lt; source tube</a>
</p>

<?php if ($ownedByAnotherSession): ?>
    <p class="alert alert-warning">
        This review operation is running from <?php echo htmlspecialchars($console->getReviewOwnerIp($operation)); ?>.
        Cannot take over yet because <?php echo htmlspecialchars($takeOverBlockReason); ?>.
    </p>
<?php endif; ?>

<div id="reviewBatchOperationProgress"
     data-batch-id="<?php echo $ownedByAnotherSession ? '' : htmlspecialchars($batch['id']); ?>"
     data-show-url="./?server=<?php echo urlencode($server); ?>&action=reviewBatchShow&batchId=<?php echo urlencode($batch['id']); ?>&page=<?php echo isset($operation['return_page']) ? (int)$operation['return_page'] : 1; ?>&perPage=<?php echo isset($operation['per_page']) ? (int)$operation['per_page'] : 25; ?>">
    <table class="table table-bordered table-condensed">
        <tbody>
            <tr>
                <th>Source</th>
                <td><?php echo htmlspecialchars($batch['source_tube']); ?> / <?php echo htmlspecialchars($batch['source_state']); ?></td>
            </tr>
            <tr>
                <th>Operation</th>
                <td><?php echo htmlspecialchars($operation['operation']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td id="reviewBatchOperationStatus"><?php echo htmlspecialchars($operation['status']); ?></td>
            </tr>
            <tr>
                <th>Progress</th>
                <td><span id="reviewBatchOperationProcessed"><?php echo (int)$operation['processed']; ?></span> / <span id="reviewBatchOperationTarget"><?php echo (int)$operation['target_count']; ?></span></td>
            </tr>
            <tr>
                <th>Errors</th>
                <td id="reviewBatchOperationErrors"><?php echo (int)$operation['errors']; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="progress">
        <?php $pct = !empty($operation['target_count']) ? min(100, floor(((int)$operation['processed'] / (int)$operation['target_count']) * 100)) : 100; ?>
        <div id="reviewBatchOperationProgressBar" class="progress-bar" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
    </div>

    <p id="reviewBatchOperationMessage" class="text-muted"><?php echo $ownedByAnotherSession ? 'Operation is owned by another session.' : 'Processing automatically in chunks.'; ?></p>
</div>
