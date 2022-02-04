<?php
$fields = $console->getTubeStatFields();
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
                        foreach ($fields as $key => $item) {
                            $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
                            if (in_array($key, array('current-jobs-buried', 'current-jobs-delayed', 'current-jobs-ready'))) {
                                ?>
                                <th<?php echo $markHidden ?>  name="<?php echo $key ?>" title="<?php echo $item ?>"><a class="a-unstyled" href="#" onclick="document.getElementById('<?php echo $key; ?>').scrollIntoView(true);return false;"><?php echo $key ?><b class="caret"></b></a></th>
                                    <?php } else { ?>
                                <th<?php echo $markHidden ?>  name="<?php echo $key ?>" title="<?php echo $item ?>"><?php echo $key ?></th>
                                <?php
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array($tube) as $tubeItem): ?>
                        <?php $tubeStats = $console->getTubeStatValues($tubeItem) ?>
                        <tr class="<?php echo ($tubeStats['pause-time-left'] > '0')? 'tr-tube-paused': ''; ?>"
                            title="<?php echo ($tubeStats['pause-time-left'] > '0')? 'Pause seconds left: ' . $tubeStats['pause-time-left'] : ''; ?>"
                            >
                            <td id="<?php echo 'tube-' . $tubeItem ?>"><?php echo $tubeItem ?></td>
                            <?php
                            foreach ($fields as $key => $item):
                                $classes = array("td-$key");
                                if (!in_array($key, $visible)) {
                                    $classes[] = 'hide' ;
                                }
                                if (isset($tubeStats[$key]) && $tubeStats[$key] != '0') {
                                    $classes[] = 'hasValue';
                                }
                                $cssClass = '' ;
                                if (count($classes) > 0) {
                                    $cssClass = ' class = "' . join(' ', $classes) . '"' ;
                                }
                                ?>
                                <td<?php echo $cssClass ?>><?php echo isset($tubeStats[$key]) ? $tubeStats[$key] : '' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

</section>