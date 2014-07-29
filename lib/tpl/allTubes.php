<?php
$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
?>

<section id="summaryTable">
    <div class="row">
        <div class="col-sm-12">
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
                    <?php foreach ($tubes as $tubeItem): ?>
                        <tr>
                            <td name="<?php echo $key ?>"><a href="index.php?server=<?php echo $server ?>&tube=<?php echo $tubeItem ?>"><?php echo $tubeItem ?></a>
                            </td>
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
        </div>
    </div>
</section>