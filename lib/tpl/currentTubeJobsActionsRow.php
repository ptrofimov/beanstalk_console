<?php
$sampleJobs = $console->getSampleJobs($tube);

if (!@empty($_COOKIE['tubePauseSeconds'])) {
    $tubePauseSeconds = intval($_COOKIE['tubePauseSeconds']);
} else {
    $tubePauseSeconds = 3600;
}
?>
<section id="actionsRow">
    <b>Actions:</b>&nbsp;
    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=1"><i class="glyphicon glyphicon-forward"></i> Kick 1 job</a>
    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=kick&count=10"
       title="To kick more jobs, edit the `count` parameter"><i class="glyphicon glyphicon-fast-forward"></i> Kick 10 job</a>
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
