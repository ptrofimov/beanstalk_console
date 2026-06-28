<?php
$batches = isset($reviewBatches) ? $reviewBatches : array();
$sourceTube = isset($reviewSourceTube) ? $reviewSourceTube : null;
?>
<div class="modern-review-header">
    <div>
        <div class="modern-review-breadcrumbs">
            <a href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">Review Batches</a>
            <?php if ($sourceTube): ?>
                <span class="text-muted">/</span>
                <span class="text-muted"><?php echo htmlspecialchars($sourceTube); ?></span>
            <?php endif; ?>
        </div>
        <h2 class="modern-review-title">Review Batches</h2>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <?php if ($sourceTube): ?>
            <a class="btn btn-sm btn-default" style="border-radius: 6px; font-weight: 500; padding: 5px 10px; font-size: 12px;" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches">Show All Batches</a>
        <?php endif; ?>
        <?php if (count($batches) > 0): ?>
            <?php
            $deleteAllUrl = './?server=' . urlencode($server) . '&action=reviewBatchDeleteAll';
            if ($sourceTube) {
                $deleteAllUrl .= '&sourceTube=' . urlencode($sourceTube);
            }
            $confirmMsg = $sourceTube ? 'Delete all review batches for this tube?' : 'Delete all review batches?';
            ?>
            <a class="btn btn-sm btn-danger"
               style="border-radius: 6px; font-weight: 500; padding: 5px 10px; font-size: 12px;"
               href="<?php echo $deleteAllUrl; ?>"
               onclick="return confirm('<?php echo $confirmMsg; ?> This will delete the batches and any remaining review-copy jobs.');">
                <i class="glyphicon glyphicon-trash"></i> Delete All Batches
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!count($batches)): ?>
    <p>No review batches found.</p>
<?php else: ?>
    <table class="table table-hover modern-batches-table">
        <thead>
            <tr>
                <th>Created</th>
                <th>Source tube</th>
                <th>State</th>
                <th>Review tube</th>
                <th>Status</th>
                <th>Progress</th>
                <th style="width: 120px; text-align: right;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($batches as $batch): ?>
                <?php
                $isInfo = $sourceTube && isset($batch['source_tube']) && $batch['source_tube'] === $sourceTube;
                $rowClass = 'clickable-row' . ($isInfo ? ' info-row' : '');
                $showUrl = './?server=' . urlencode($server) . '&action=reviewBatchShow&batchId=' . urlencode($batch['id']);
                ?>
                <tr class="<?php echo $rowClass; ?>" onclick="window.location.href='<?php echo $showUrl; ?>'">
                    <td><?php echo htmlspecialchars($batch['created_at']); ?></td>
                    <td><strong><?php echo htmlspecialchars($batch['source_tube']); ?></strong></td>
                    <td><?php echo htmlspecialchars($batch['source_state']); ?></td>
                    <td><?php echo htmlspecialchars($batch['review_tube']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($batch['status']); ?>
                        <?php if ($console->isReviewOwnedByAnotherSession($batch)): ?>
                            <br><small class="text-warning">Prepared by another session (<?php echo htmlspecialchars($console->getReviewOwnerIp($batch)); ?>).</small>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo (int)$batch['processed']; ?></strong> / <?php echo (int)$batch['target_count']; ?></td>
                    <td style="text-align: right;">
                        <a class="btn btn-xs btn-default" style="border-radius: 4px; font-weight: 500;" href="<?php echo $showUrl; ?>" onclick="event.stopPropagation();">Open</a>
                        <a class="btn btn-xs btn-danger" style="border-radius: 4px; font-weight: 500;"
                           href="./?server=<?php echo urlencode($server); ?>&action=reviewBatchDelete&batchId=<?php echo urlencode($batch['id']); ?>"
                           onclick="event.stopPropagation(); return confirm('Delete this review batch and any remaining review-copy jobs?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
