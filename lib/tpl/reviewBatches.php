<?php
$batches = isset($reviewBatches) ? $reviewBatches : array();
$sourceTube = isset($reviewSourceTube) ? $reviewSourceTube : null;
?>
<h3>Review batches<?php echo $sourceTube ? ' for ' . htmlspecialchars($sourceTube) : ''; ?></h3>
<p>
    <a href="./?server=<?php echo urlencode($server); ?>">&lt;&lt; back to tubes</a>
    <?php if ($sourceTube): ?>
        |
        <a href="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($sourceTube); ?>">&lt;&lt; back to tube</a>
        |
        <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">show all review batches</a>
    <?php endif; ?>
</p>

<?php if (!count($batches)): ?>
    <p>No review batches found.</p>
<?php else: ?>
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Created</th>
                <th>Source tube</th>
                <th>State</th>
                <th>Review tube</th>
                <th>Status</th>
                <th>Progress</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($batches as $batch): ?>
                <tr<?php echo $sourceTube && isset($batch['source_tube']) && $batch['source_tube'] === $sourceTube ? ' class="info"' : ''; ?>>
                    <td><?php echo htmlspecialchars($batch['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($batch['source_tube']); ?></td>
                    <td><?php echo htmlspecialchars($batch['source_state']); ?></td>
                    <td><?php echo htmlspecialchars($batch['review_tube']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($batch['status']); ?>
                        <?php if ($console->isReviewOwnedByAnotherSession($batch)): ?>
                            <br><small class="text-warning">Be careful, this review batch was prepared by another session (visiting from <?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?>).</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int)$batch['processed']; ?> / <?php echo (int)$batch['target_count']; ?></td>
                    <td>
                        <a class="btn btn-xs btn-default" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchShow&batchId=<?php echo urlencode($batch['id']); ?>">Open</a>
                        <a class="btn btn-xs btn-danger"
                           href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDelete&batchId=<?php echo urlencode($batch['id']); ?>"
                           onclick="return confirm('Delete this review batch and any remaining review-copy jobs?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
