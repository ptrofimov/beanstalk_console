<?php
if (isset($isNewRecord) && $isNewRecord) {
    $action = '?action=newSample';
} else {
    $action = '?action=editSample&key=' . $_GET['key'];
}
?>
<form name="sampleJobsEdit" action="<?php echo $action; ?>" method="POST">
    <div class="clearfix form-group">
        <div class="pull-left">
            <?php
            if (isset($isNewRecord) && $isNewRecord) {
                ?>
                <h4 class="text-info">New sample job</h4>
                <?php
            } else {
                ?>
                <h4 class="text-info">Edit: <?php echo htmlspecialchars($job['name']); ?></h4>
            <?php } ?>
        </div>
        <div class="pull-right">
            <a href="./?action=manageSamples" class="btn btn-default btn-small"><i class="glyphicon glyphicon-list"></i> Manage samples</a>
        </div>
    </div>
    <div class=" form-group">
        <fieldset>
            <?php
            if (isset($error)) {
                ?>
                <div class="alert alert-error">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <span> <?php echo $error; ?></span>
                </div>
            <?php } ?>
            <div class="control-group">
                <label class="control-label" for="addsamplename"><b>Name *</b></label>

                <div class="controls form-group">
                    <input class="input-xlarge focused" id="addsamplename" name="name" type="text" value="<?php echo @htmlspecialchars($job['name']); ?>"
                           autocomplete="off">
                </div>
            </div>
        </fieldset>
        <div class="clearfix">
            <label class="control-label" for="focusedInput"><b>Available on tubes *</b></label>
            <br/>
            <?php
            if (isset($job) && is_array($job['tubes'])) {
                ?>
                <div class="pull-left" style="padding-right: 35px;">
                    Saved to:
                    <blockquote>
                        <?php
                        foreach ($job['tubes'] as $t => $val) {
                            $checked = '';
                            if (@array_key_exists($t, $job['tubes'])) {
                                $checked = 'checked="checked"';
                            }
                            ?>
                            <div class="control-group">
                                <div class="controls">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" autocomplete="off" name="tubes[<?php echo $t ?>]" value="1" <?php echo $checked; ?>>
                                        <?php echo $t ?>
                                    </label>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </blockquote>
                </div>
                <?php
            }
            if (is_array($serverTubes)) {
                foreach ($serverTubes as $server => $tubes) {
                    if (is_array($tubes)) {
                        ?>
                        <div class="pull-left" style="padding-right: 35px;">
                            <?php
                            echo $server;
                            ?>
                            <blockquote>
                                <?php
                                foreach ($tubes as $t) {
                                    $checked = '';
                                    if (@array_key_exists($t, $job['tubes'])) {
                                        $checked = 'checked="checked"';
                                    }
                                    ?>
                                    <div class="control-group">
                                        <div class="controls">
                                            <label class="checkbox-inline">
                                                <input type="checkbox" autocomplete="off" name="tubes[<?php echo $t ?>]" value="1" <?php echo $checked; ?>>
                                                <?php echo $t ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </blockquote>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div>
            <label class="control-label" for="jobdata"><b>Job data *</b></label>
            <textarea name="jobdata" id="jobdata" style="width:100%" rows="3"><?php echo @htmlspecialchars($job['data']); ?></textarea>
        </div>
    </div>
    <div>
        <input type="submit" class="btn btn-success" value="Save"/>
    </div>
</form>
