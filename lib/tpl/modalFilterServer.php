<div id="filterServer" data-cookie="filter" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="filter-label" class="text-info">Filter columns</h3>
            </div>
            <div class="modal-body">
                <form class="form-group">
                    <div class="tabbable">
                        <ul class="nav nav-tabs">
                            <?php
                            $i = 0;
                            foreach ($console->getServerStatsGroups() as $groupName => $fields): $i++;
                                ?>
                                <li <?php if ($i == 1) echo 'class="active"' ?>><a href="#<?php echo $groupName ?>" data-toggle="tab"><?php echo $groupName ?></a></li>
                            <?php endforeach ?>
                        </ul>
                        <div class="tab-content">
                            <?php
                            $i = 0;
                            foreach ($console->getServerStatsGroups() as $groupName => $fields): $i++;
                                ?>
                                <div class="tab-pane <?php if ($i == 1) echo 'active' ?>" id="<?php echo $groupName ?>">
                                    <?php foreach ($fields as $key => $description): ?>
                                        <div class="control-group">
                                            <div class="controls">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="<?php echo $key ?>" <?php if (in_array($key, $visible)) echo 'checked="checked"' ?>>
                                                        <b><?php echo $key ?></b>
                                                        <br/><?php echo $description ?>
                                                    </label>
                                                </div>
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
    </div>
</div>