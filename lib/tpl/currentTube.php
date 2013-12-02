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
            <?php foreach($fields as $key=>$item):
            $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
            ?>
            <th<?php echo $markHidden?>  name="<?php echo $key?>" title="<?php echo $item?>"><?php echo $key?></th>
            <?php endforeach;?>
        </tr>
        </thead>
        <tbody>
        <?php foreach(array($tube) as $tubeItem):?>
        <tr>
            <td name="<?php echo $key?>"><?php echo $tubeItem?></td>
            <?php $values=$console->getTubeStatValues($tubeItem)?>
            <?php foreach($fields as $key=>$item):
            $markHidden = !in_array($key, $visible) ? ' class="hide"' : '';
            ?>
            <td<?php echo $markHidden?>><?php echo isset($values[$key])?$values[$key]:''?></td>
            <?php endforeach;?>
        </tr>
            <?php endforeach?>
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
                    <?php $i=0; foreach($groups as $groupName => $items): $i++;?>
                    <li <?php if($i==1) echo 'class="active"'?>><a href="#<?php echo $groupName?>" data-toggle="tab"><?php echo $groupName?></a></li>
                    <?php endforeach?>
                </ul>
                <div class="tab-content">
                    <?php $i=0; foreach($groups as $groupName => $items): $i++;?>
                    <div class="tab-pane <?php if($i==1) echo 'active'?>" id="<?php echo $groupName?>">
                        <?php foreach($items as $key):
                        $description = isset($fields[$key]) ? $fields[$key] : '';
                        ?>
                        <div class="control-group">
                            <div class="controls">
                                <label class="checkbox">
                                    <input type="checkbox" name="<?php echo $key?>" <?php if(in_array($key, $visible)) echo 'checked="checked"'?>>
                                    <b><?php echo $key?></b>
                                    <br /><?php echo $description?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach?>
                    </div>
                    <?php endforeach?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<p>
    <b>Actions:</b>&nbsp;
    <a class="btn btn-small" href="?server=<?php echo $server?>&tube=<?php echo $tube?>&action=kick&count=1"><i class="icon-play"></i> Kick 1 job</a>
    <a class="btn btn-small" href="?server=<?php echo $server?>&tube=<?php echo $tube?>&action=kick&count=10"><i class="icon-forward"></i> Kick 10 jobs</a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  data-toggle="modal" class="btn btn-success btn-small" href="#" id="addJob"><i class="icon-plus-sign icon-white"></i> Add job</a>
</p>

<?php foreach($peek as $state=>$job):?>
    <hr>
    <div class="pull-left">
        <h3>Next job in "<?php echo $state?>" state</h3>
    </div>
    <div class="pull-right">
        <a class="btn btn-danger btn-small" href="?server=<?php echo $server?>&tube=<?php echo $tube?>&state=<?php echo $state ?>&action=delete&count=1"><i class="icon-trash icon-white"></i> Delete next <?php echo $state ?> job</a>
        <a class="btn btn-danger btn-small" href="?server=<?php echo $server?>&tube=<?php echo $tube?>&state=<?php echo $state ?>&action=deleteAll&count=1" onclick="return confirm('This process might hang a while on tubes with lots of jobs. Are you sure you want to continue?');"><i class="icon-trash icon-white"></i> Delete all <?php echo $state ?> jobs</a>
    </div>
    <div class="clearfix"></div>
    <?php if($job):?>

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
                <?php foreach($job['stats'] as $key=>$value):?>
                <tr>
                    <td><?php echo $key?></td>
                    <td><?php echo $value?></td>
                </tr>
                <?php endforeach?>
            </tbody>
            </table>
        </div>
        <div class="span9">
            <b>Job data:</b><br />
            <pre><code><?php echo htmlspecialchars(trim(var_export($job['data'],true), "'"), ENT_COMPAT)?></code></pre>
        </div>
    </div>
    <?php else:?>
        <i>empty</i>
    <?php endif?>
<?php endforeach?>
