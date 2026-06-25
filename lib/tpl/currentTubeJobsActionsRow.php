<?php
$sampleJobs = $console->getSampleJobs($tube);
$buriedJobsCount = isset($allStats['current-jobs-buried']) ? $allStats['current-jobs-buried'] : 0;
$reviewEnabled = $console->isReviewEnabled();
$reviewBatchCount = 0;
$reviewError = null;
$reviewSafety = array();
$reviewSafetyError = null;
$reviewOwnerIps = array();
if ($reviewEnabled) {
    try {
        $reviewBatchesForTube = $console->getReviewBatches($tube);
        $reviewBatchCount = count($reviewBatchesForTube);
        foreach ($reviewBatchesForTube as $reviewBatchForTube) {
            if ($console->isReviewOwnedByAnotherSession($reviewBatchForTube)) {
                $reviewOwnerIps[$console->getReviewOwnerIp($reviewBatchForTube)] = true;
            }
        }
    } catch (Exception $e) {
        $reviewError = $e->getMessage();
    }
    try {
        foreach (array('buried', 'delayed', 'ready') as $reviewState) {
            $reviewSafety[$reviewState] = $console->getReviewSafety($tube, $reviewState);
        }
    } catch (Exception $e) {
        $reviewSafetyError = $e->getMessage();
    }
}
$readyJobsCount = isset($allStats['current-jobs-ready']) ? (int)$allStats['current-jobs-ready'] : 0;
$delayedJobsCount = isset($allStats['current-jobs-delayed']) ? (int)$allStats['current-jobs-delayed'] : 0;
$bodySnapshotDisabled = !empty($config['review']['neverIncludeBodySnapshot']);

$tubePauseSeconds = $settings->getTubePauseSeconds();
if ($tubePauseSeconds === -1) {
    $tubePauseSeconds = 3600;
}
?>
<section id="actionsRow">
    <b>Actions:</b>&nbsp;
    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=1"><i class="glyphicon glyphicon-forward"></i> Kick 1 job</a>

    <form method="GET" style="display:inline">
        <div class="btn-group" role="group">
            <button type="submit" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-fast-forward"></i> Kick more </button>
            <input type="hidden" name="server" value="<?php echo $server ?>">
            <input type="hidden" name="tube" value="<?php echo urlencode($tube) ?>">
            <input type="hidden" name="action" value="kick">
            <input id="kick_tube_no_<?php echo md5($tube); ?>" type="number" value="10" name="count" min="0" step="1" size="4" class="btn btn-default btn-sm kick_jobs_no">
        </div>
    </form>

    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=<?= $buriedJobsCount ?>"><i class="glyphicon glyphicon-forward"></i> Kick all jobs</a>

    <?php
    if (empty($tubeStats['pause-time-left'])) {
    ?><a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=pause&count=-1"
            title="Temporarily prevent jobs being reserved from the given tube. Pause for: <?php echo $tubePauseSeconds; ?> seconds"><i class="glyphicon glyphicon-pause"></i>
            Pause tube</a><?php
                        } else {
                            ?><a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=pause&count=0"
            title="<?php echo sprintf('Pause seconds left: %d', isset($tubeStats['pause-time-left']) ? $tubeStats['pause-time-left'] : 0); ?>"><i class="glyphicon glyphicon-play"></i> Unpause tube</a><?php
                                                                                                                                                            }
                                                                                                                                                                ?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <div class="btn-group">
        <a data-toggle="modal" class="btn btn-success btn-sm" href="#" id="addJob"><i class="glyphicon glyphicon-plus-sign glyphicon-white"></i> Add job</a>
        <button class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">

            <?php
            if (is_array($sampleJobs) && count($sampleJobs)) {
                foreach ($sampleJobs as $key => $name) {
            ?>
                    <li>
                        <a href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=loadSample&key=<?php echo urlencode($key); ?>"><?php echo htmlspecialchars($name); ?></a>
                    </li>
                <?php
                }
                ?>
                <li class="divider"></li>
                <li><a href="./?action=manageSamples">Manage samples</a></li>
            <?php
            } else {
            ?>
                <li>
                    <a href="#">There are no sample jobs</a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php if ($reviewEnabled && $reviewBatchCount > 0): ?>
        <a class="btn btn-warning btn-sm" href="./?server=<?php echo urlencode($server); ?>&action=reviewBatches&sourceTube=<?php echo urlencode($tube); ?>"><i class="glyphicon glyphicon-list-alt glyphicon-white"></i> Reviews for this tube (<?php echo (int)$reviewBatchCount; ?>)</a>
    <?php endif; ?>
    <?php if ($reviewEnabled): ?>
        <a data-toggle="modal" class="btn btn-info btn-sm" href="#reviewBatchStart"><i class="glyphicon glyphicon-eye-open glyphicon-white"></i> Prepare review batch</a>
    <?php endif; ?>
    <?php if ($reviewError): ?>
        <span class="text-danger">Review batches unavailable: <?php echo htmlspecialchars($reviewError); ?></span>
    <?php endif; ?>
</section>

<?php if ($reviewEnabled): ?>
<div class="modal fade" id="reviewBatchStart" tabindex="-1" role="dialog" aria-labelledby="reviewBatchStartLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="./?server=<?php echo urlencode($server); ?>&tube=<?php echo urlencode($tube); ?>&action=reviewBatchStart">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="reviewBatchStartLabel">Prepare jobs for review</h4>
                </div>
                <div class="modal-body">
                    <?php if (count($reviewOwnerIps)): ?>
                        <p class="alert alert-warning">
                            Existing review batches for this tube were prepared from <?php echo htmlspecialchars(implode(', ', array_keys($reviewOwnerIps))); ?>.
                        </p>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="reviewState">State</label>
                        <select id="reviewState" name="state" class="form-control">
                            <option value="buried" data-count="<?php echo (int)$buriedJobsCount; ?>">Buried (<?php echo (int)$buriedJobsCount; ?>)</option>
                            <option value="delayed" data-count="<?php echo (int)$delayedJobsCount; ?>">Delayed (<?php echo (int)$delayedJobsCount; ?>)</option>
                            <option value="ready" data-count="<?php echo (int)$readyJobsCount; ?>">Ready (<?php echo (int)$readyJobsCount; ?>)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reviewTube">Review tube</label>
                        <input id="reviewTube" name="reviewTube" class="form-control" value="<?php echo htmlspecialchars(ReviewBatchNaming::defaultReviewTube($tube)); ?>">
                    </div>
                    <p class="help-block">The batch records the current job count for the selected state and processes up to that number of jobs. Jobs added later are left for a later batch when queue order allows.</p>
                    <?php if (!$bodySnapshotDisabled): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="includeBodySnapshot" value="1" checked="checked">
                                Write body snapshot JSONL during preparation
                            </label>
                            <p class="help-block">Stores each job body in a local body-snapshot file as it is reviewed. This preserves payloads after review copies are returned or deleted, but can create a large sensitive file and affect review-page/body-load performance.</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($reviewSafetyError): ?>
                        <p class="text-danger">Review safety checks unavailable: <?php echo htmlspecialchars($reviewSafetyError); ?></p>
                    <?php else: ?>
                        <ul class="list-unstyled">
                            <?php foreach ($reviewSafety as $reviewState => $reviewStateSafety): ?>
                                <li><strong><?php echo ucfirst($reviewState); ?>:</strong> <?php echo htmlspecialchars($reviewStateSafety['message']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($console->isUnsafeReviewOverrideEnabled('ready') || $console->isUnsafeReviewOverrideEnabled('delayed')): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="forceUnsafe" value="1">
                                Use configured unsafe override for ready/delayed jobs
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="reviewBatchStartSubmit">Start review batch</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
