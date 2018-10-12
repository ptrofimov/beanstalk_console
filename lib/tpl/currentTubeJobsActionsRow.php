<?php
$sampleJobs = $console->getSampleJobs($tube);
$buriedJobsCount = $allStats['current-jobs-buried'];

if (!@empty($_COOKIE['tubePauseSeconds'])) {
    $tubePauseSeconds = intval($_COOKIE['tubePauseSeconds']);
} else {
    $tubePauseSeconds = 3600;
}
?>
<section id="actionsRow">
    <b>Actions:</b>&nbsp;
    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=1"><i class="glyphicon glyphicon-forward"></i> Kick 1 job</a>

    <form method="GET">
        <div class="btn-group" role="group">
            <button type="submit" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-fast-forward"></i> Kick more </button>
            <input type="hidden" name="server" value="<?php echo $server ?>">
            <input type="hidden" name="tube" value="<?php echo urlencode($tube) ?>">
            <input type="hidden" name="action" value="kick">
            <input id="kick_tube_no_<?php echo md5($tube);?>" type="number" value="10" name="count" min="0" step="1" size="4" class="btn btn-default btn-sm kick_jobs_no">
        </div>
    </form>

    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=<?=$buriedJobsCount?>"><i class="glyphicon glyphicon-forward"></i> Kick all jobs</a>

    <?php
    if (empty($tubeStats['pause-time-left'])) {
        ?><a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=pause&count=-1"
           title="Temporarily prevent jobs being reserved from the given tube. Pause for: <?php echo $tubePauseSeconds; ?> seconds"><i class="glyphicon glyphicon-pause"></i>
            Pause tube</a><?php
    } else {
        ?><a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=pause&count=0"
           title="<?php echo sprintf('Pause seconds left: %d', $tubeStats['pause-time-left']); ?>"><i class="glyphicon glyphicon-play"></i> Unpause tube</a><?php
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
</section>
