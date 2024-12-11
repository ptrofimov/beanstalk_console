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
                    <?php foreach ((is_array($tubes) ? $tubes : array()) as $tubeItem): ?>
                        <?php
                        $arr_tubeStats = $tplVars['tubesStats'][$tubeItem] ?? array();
                        if(empty($arr_tubeStats) && isset($_SESSION['oldTubeStats'][$tubeItem])) {
                            $arr_tubeStats = $_SESSION['oldTubeStats'][$tubeItem];
                        } else if (!empty($arr_tubeStats)) {
                            if(!isset($_SESSION['oldTubesStats'])) {
                                $_SESSION['oldTubesStats'] = array();
                            }
                            $_SESSION['oldTubeStats'][$tubeItem] = $arr_tubeStats;
                        }
                        $tubeStats = array();
                        foreach ($arr_tubeStats as $key => $arr) {
                            $tubeStats[$key] = $arr['value'];
                        }
                        ?>
                        <tr class="<?php echo (isset($tubeStats['pause-time-left']) && $tubeStats['pause-time-left'] > '0') ? 'tr-tube-paused' : ''; ?>"
                            title="<?php echo (isset($tubeStats['pause-time-left']) && $tubeStats['pause-time-left'] > '0') ? 'Pause seconds left: ' . $tubeStats['pause-time-left'] : ''; ?>"
                            >
                            <td id="<?php echo 'tube-' . htmlspecialchars($tubeItem) ?>"><a href="./?server=<?php echo urlencode($server) ?>&tube=<?php echo urlencode($tubeItem) ?>"><?php echo htmlspecialchars($tubeItem) ?></a>
                            </td>
                            <?php
                            foreach ($fields as $key => $item):
                                $classes = array("td-$key");
                                if (!in_array($key, $visible)) {
                                    $classes[] = 'hide';
                                }
                                if (isset($tubeStats[$key]) && $tubeStats[$key] != '0') {
                                    $classes[] = 'hasValue';
                                }
                                $cssClass = '';
                                if (count($classes) > 0) {
                                    $cssClass = ' class = "' . join(' ', $classes) . '"';
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
