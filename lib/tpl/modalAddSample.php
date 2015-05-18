<div id="modalAddSample" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="settings-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="settings-label" class="modal-title">Add to samples</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="tube" value="<?php echo $tube; ?>"/>
                <fieldset>
                    <div class="alert alert-error hide" id="sampleSaveAlert">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <span><strong>Error!</strong> Required fields are marked *</span>
                    </div>
                    <input type="hidden" name="addsamplejobid" id="addsamplejobid">

                    <div class="form-group">
                        <label for="addsamplename"
                               title="You can highlight text inside the job, then hit the Add button, it will be automatically populated here."><b>Name *</b>
                            <i>(highlighted text is auto populated)</i></label>
                        <input class="form-control focused" id="addsamplename" name="addsamplename" type="text" value="" autocomplete="off">
                    </div>
                </fieldset>
                <div>
                    <label class="control-label" for="focusedInput"><b>Available on tubes *</label>
                    <?php
                    foreach ($tubes as $t):
                        $checked = '';
                        if ($t == $tube) {
                            $checked = 'checked="checked"';
                        }
                        ?>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" autocomplete="off" name="tubes[<?php echo $t ?>]" value="1" <?php echo $checked; ?>>
                                    <?php echo $t ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <a href="#" class="btn btn-success" id="sampleSave">Save</a>
            </div>
        </div>
    </div>
</div>