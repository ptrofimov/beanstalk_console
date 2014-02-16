<?php
$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
$sampleJobs = $console->getSampleJobs($tube);

if (!@empty($_COOKIE['tubePauseSeconds'])) {
    $tubePauseSeconds = intval($_COOKIE['tubePauseSeconds']);
} else {
    $tubePauseSeconds = 3600;
}
?>
<section id="summaryTable">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>name</th>
                <?php
                foreach ($fields as $key => $item):
                    $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
                    ?>
                    <th<?php echo $markHidden ?>  name="<?php echo $key ?>" title="<?php echo $item ?>"><?php echo $key ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array($tube) as $tubeItem): ?>
                <tr>
                    <td name="<?php echo $key ?>"><?php echo $tubeItem ?></td>
                    <?php $tubeStats = $console->getTubeStatValues($tubeItem) ?>
                    <?php
                    foreach ($fields as $key => $item):
                        $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
                        ?>
                        <td<?php echo $markHidden ?>><?php echo isset($tubeStats[$key]) ? $tubeStats[$key] : '' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>


<b>Actions:</b>&nbsp;
<a class="btn btn-small" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=kick&count=1"><i class="icon-forward"></i> Kick 1 job</a>
<a class="btn btn-small" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=kick&count=10" title="To kick more jobs, edit the `count` parameter"><i class="icon-fast-forward"></i> Kick 10 job</a>
<?php
if (empty($tubeStats['pause-time-left'])) {
    ?><a class="btn btn-small" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=pause&count=-1" title="Temporarily prevent jobs being reserved from the given tube. Pause for: <?php echo $tubePauseSeconds; ?> seconds"><i class="icon-pause"></i> Pause tube</a><?php
} else {
    ?><a class="btn btn-small" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=pause&count=0" title="<?php echo sprintf('Pause seconds left: %d', $tubeStats['pause-time-left']); ?>"><i class="icon-play"></i> Unpause tube</a><?php
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<div class="btn-group">
    <a data-toggle="modal" class="btn btn-success btn-small" href="#" id="addJob"><i class="icon-plus-sign icon-white"></i> Add job</a>
    <button class="btn btn-success btn-small dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">

        <?php
        if (is_array($sampleJobs) && count($sampleJobs)) {
            foreach ($sampleJobs as $key => $name) {
                ?>
                <li>
                    <a href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=loadSample&key=<?php echo $key; ?>"><?php echo htmlspecialchars($name); ?></a>
                </li>
            <?php }
            ?>
                <li class="divider"></li>
                <li><a href="?action=manageSamples">Manage samples</a></li>
                <?php 
        } else {
            ?>
            <li>
                <a href="#">There are no sample jobs</a>
            </li>
<?php } ?>
    </ul>
</div>

<?php foreach ($peek as $state => $job): ?>
    <hr>
    <div class="pull-left">
        <h3>Next job in "<?php echo $state ?>" state</h3>
    </div>
    <div class="clearfix"></div>
    <?php if ($job): ?>

        <div class="row show-grid">
            <div class="span3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Stats:</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php foreach ($job['stats'] as $key => $value): ?>
                            <tr>
                                <td><?php echo $key ?></td>
                                <td><?php echo $value ?></td>
                            </tr>
        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="span9">
                <div class="clearfix">
                    <div class="pull-left">
                        <b>Job data:</b>
                    </div>
        <?php if ($job): ?>
                        <div class="pull-right">
                            <div style="margin-bottom: 3px;">
                                <a class="btn btn-small btn-info addSample" data-state="<?php echo $state ?>" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&action=addSample"><i class="icon-plus icon-white"></i> Add to samples</a>
                                <a class="btn btn-small btn-danger" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&state=<?php echo $state ?>&action=deleteAll&count=1" onclick="return confirm('This process might hang a while on tubes with lots of jobs. Are you sure you want to continue?');"><i class="icon-trash icon-white"></i> Delete all <?php echo $state ?> jobs</a>
                                <a class="btn btn-small btn-danger" href="?server=<?php echo $server ?>&tube=<?php echo $tube ?>&state=<?php echo $state ?>&action=delete&count=1"><i class="icon-remove icon-white"></i> Delete</a>
                            </div>
                        </div>
        <?php endif; ?>

                </div>
                <pre><code><?php echo htmlspecialchars(trim(var_export($job['data'], true), "'"), ENT_COMPAT) ?></code></pre>
            </div>
        </div>
    <?php else: ?>
        <i>empty</i>
    <?php endif ?>
<?php endforeach ?>
