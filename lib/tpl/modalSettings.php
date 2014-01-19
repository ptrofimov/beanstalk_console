<div id="settings" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="settings-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="settings-label" class="text-info">Settings</h3>
    </div>
    <div class="modal-body">
        <fieldset>  		
            <div class="control-group">
                <label class="control-label" for="tubePauseSeconds"><b>Tube pause seconds</b> (<i>-1</i> means the default: <i>3600</i>, <i>0</i> is reserved for un-pause)</label>
                <div class="controls">
                    <input class="input-xlarge focused" id="tubePauseSeconds" type="text" value="<?php
                    if (@empty($_COOKIE['tubePauseSeconds']))
                        echo -1;
                    else
                        echo @intval($_COOKIE['tubePauseSeconds']);
                    ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="focusedInput"><b>Auto-refresh interval in milliseconds</b> (Default: <i>500</i>)</label>
                <div class="controls">
                    <input class="input-xlarge focused" id="autoRefreshTimeoutMs" type="text" value="<?php
                    if (@empty($_COOKIE['autoRefreshTimeoutMs']))
                        echo 500;
                    else
                        echo @intval($_COOKIE['autoRefreshTimeoutMs']);
                    ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="focusedInput"><b>Preferred way to deal with job data</b></label>
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="isDisabledUnserialization" value="1" <?php if (@$_COOKIE['isDisabledUnserialization'] != 1) { ?>checked="checked"<?php } ?>>
                        before display: unserialize()
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" id="isDisabledJsonDecode" value="1" <?php if (@$_COOKIE['isDisabledJsonDecode'] != 1) { ?>checked="checked"<?php } ?>>
                        before display: json_decode()
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" id="isDisabledJobDataHighlight" value="1" <?php if (@$_COOKIE['isDisabledJobDataHighlight'] != 1) { ?>checked="checked"<?php } ?>>
                        after display: enable highlight
                    </label>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>