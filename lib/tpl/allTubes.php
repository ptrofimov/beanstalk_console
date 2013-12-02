<?php
$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
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
            <?php foreach ($tubes as $tubeItem): ?>
                <tr>
                    <td name="<?php echo $key ?>"><a href="index.php?server=<?php echo $server ?>&tube=<?php echo $tubeItem ?>" ><?php echo $tubeItem ?></a></td>
                    <?php $values = $console->getTubeStatValues($tubeItem) ?>
                    <?php
                    foreach ($fields as $key => $item):
                        $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
                        ?>
                        <td<?php echo $markHidden ?>><?php echo isset($values[$key]) ? $values[$key] : '' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>

<div id="filter" data-cookie="tubefilter" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filter-label" class="text-info">Filter columns</h3>
    </div>
    <div class="modal-body">
        <form>
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <?php
                    $i = 0;
                    foreach ($groups as $groupName => $items): $i++;
                        ?>
                        <li <?php if ($i == 1) echo 'class="active"' ?>><a href="#<?php echo $groupName ?>" data-toggle="tab"><?php echo $groupName ?></a></li>
                    <?php endforeach ?>
                </ul>
                <div class="tab-content">
                    <?php
                    $i = 0;
                    foreach ($groups as $groupName => $items): $i++;
                        ?>
                        <div class="tab-pane <?php if ($i == 1) echo 'active' ?>" id="<?php echo $groupName ?>">
                            <?php
                            foreach ($items as $key):
                                $description = isset($fields[$key]) ? $fields[$key] : '';
                                ?>
                                <div class="control-group">
                                    <div class="controls">
                                        <label class="checkbox">
                                            <input type="checkbox" name="<?php echo $key ?>" <?php if (in_array($key, $visible)) echo 'checked="checked"' ?>>
                                            <b><?php echo $key ?></b>
                                            <br /><?php echo $description ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>
<div id="clear-tubes" data-cookie="tubefilter" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filter-label" class="text-info">Clear multiple tubes</h3>
    </div>
    <div class="modal-body">
        <form>
            <fieldset>  		
                <div class="control-group">
                    <label class="control-label" for="focusedInput">Tube name (supports <a href="http://james.padolsey.com/javascript/regex-selector-for-jquery/" target="_blank">jQuery regexp</a> syntax)</label>
                    <div class="controls">
                        <input class="input-xlarge focused" id="tubeSelector" type="text" placeholder="prefix*" value="<?php echo @$_COOKIE['tubeSelector'];?>">
                    </div>
                    <a href="#" class="btn" id="clearTubesSelect">Select</a>
                </div>
            </fieldset>
            <div>
                <?php
                foreach ($tubes as $tube):
                    ?>
                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input type="checkbox" name="<?php echo $tube ?>" value="1">
                                <b><?php echo $tube ?></b>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <p class="muted text-left">
        * Tube clear works by peeking to all jobs and deleting them in a loop.
        </p>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <a href="#" class="btn btn-success" id="clearTubes">Clear selected tubes</a>
        
    </div>
</div>