<?php
$sampleJobs = $console->getSampleJobs();
if (!empty($sampleJobs)) {
    $_servers = $console->getServers();
    if (count($_servers) == 1) {
        $_server = current($_servers);
    }
    if (isset($_SESSION['info'])) {
        ?>
        <div class="alert alert-info" id="sampleSaveAlert">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <span><?php echo $_SESSION['info']; ?></span>
        </div>
        <script>
            window.setTimeout(function() {
                $(".alert").alert('close');
            }, 2000);
        </script>
        <?php
        unset($_SESSION['info']);
    }
    ?>
    <div class="clearfix">
        <div class="pull-right">
            <a href="?action=newSample" class="btn btn-small"><i class="icon-plus"></i> Add job to samples</a>
        </div>
    </div>
    <section id="summaryTable">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Kick job to tubes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleJobs as $key => $job): ?>
                    <tr>
                        <td name="<?php echo $key ?>"  style="line-height: 25px !important;"><a href="?action=editSample&key=<?php echo $key ?>"><?php echo htmlspecialchars($job['name']); ?></a></td>
                        <td>
                            <?php
                            if (is_array($job['tubes'])) {
                                foreach ($job['tubes'] as $tubename => $val) {
                                    if (isset($_server) && !empty($_server)) {
                                        ?>
                                        <a class="btn btn-small" href="?server=<?php echo $_server ?>&tube=<?php echo $tubename ?>&action=loadSample&key=<?php echo $key; ?>&redirect=<?php echo urlencode('index.php?action=manageSamples'); ?>"><i class="icon-forward"></i> <?php echo $tubename; ?></a>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="btn-group">
                                            <a class="btn btn-small" href="#" data-toggle="dropdown"><i class="icon-forward"></i> <?php echo $tubename; ?></a>
                                            <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php
                                                if (is_array($_servers)) {
                                                    foreach ($_servers as $server2) {
                                                        ?>
                                                        <li>
                                                            <a href="?server=<?php echo $server2 ?>&tube=<?php echo $tubename ?>&action=loadSample&key=<?php echo $key; ?>&redirect=<?php echo urlencode('index.php?action=manageSamples'); ?>"><?php echo $server2; ?></a>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <div class="pull-right">
                                <a class="btn btn-small" href="?action=editSample&key=<?php echo $key ?>"><i class="icon-pencil"></i> Edit</a>
                                <a class="btn btn-small" href="?action=deleteSample&key=<?php echo $key ?>"><i class="icon-trash"></i> Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </section>
<?php } else { ?>
    There are no saved jobs.
<?php } ?>
