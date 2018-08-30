<div id="settings" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="settings-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="settings-label">Settings</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="form-group" dir="rtl">
                        <label for="tubePauseSeconds"><b>زمان توقف تونل</b> (<i>-1</i> یعنی پیش فرض: <i>3600</i>, <i>0</i> برای توقف نکردن)</label>

                        <input class="form-control focused" id="tubePauseSeconds" type="text" value="<?php
                        if (@empty($_COOKIE['tubePauseSeconds']))
                            echo -1;
                        else
                            echo @intval($_COOKIE['tubePauseSeconds']);
                        ?>">
                    </div>
                    <div class="form-group" dir="rtl">
                        <label for="focusedInput"><b>به روز رسانی خودکار در میلی ثانیه</b> (پیش فرض: <i>500</i>)</label>
                        <input class="form-control focused" id="autoRefreshTimeoutMs" type="text" value="<?php
                        if (@empty($_COOKIE['autoRefreshTimeoutMs']))
                            echo 500;
                        else
                            echo @intval($_COOKIE['autoRefreshTimeoutMs']);
                        ?>">
                        <div class="checkbox" dir="ltr">
                            <label>
                                <input type="checkbox" id="isEnabledAutoRefreshLoad" value="0"
                                       <?php if (@$_COOKIE['isEnabledAutoRefreshLoad'] == 1) { ?>checked="checked"<?php } ?>>
                                به روز رسانی به محض بارگزاری
                            </label>
                        </div>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label for="focusedInput"><b>محدود کردن جواب های جستجو </b> (پیش فرض: <i>25</i>)</label>
                        <input class="form-control focused" id="searchResultLimit" type="text" value="<?php
                        if (@empty($_COOKIE['searchResultLimit']))
                            echo 25;
                        else
                            echo @intval($_COOKIE['searchResultLimit']);
                        ?>">
                    </div>
                    <div class="form-group">
                        <label for="focusedInput"><b>Preferred way to deal with job data</b></label>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="isDisabledJsonDecode" value="1"
                                       <?php if (@$_COOKIE['isDisabledJsonDecode'] != 1) { ?>checked="checked"<?php } ?>>
                                قبل نمایش: json_decode()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="isDisabledUnserialization" value="1"
                                       <?php if (@$_COOKIE['isDisabledUnserialization'] != 1) { ?>checked="checked"<?php } ?>>
                                قبل نمایش: unserialize()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="isEnabledBase64Decode" value="1"
                                       <?php if (@$_COOKIE['isEnabledBase64Decode'] == 1) { ?>checked="checked"<?php } ?>>
                                قبل نمایش: base64_decode()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="isDisabledJobDataHighlight" value="1"
                                       <?php if (@$_COOKIE['isDisabledJobDataHighlight'] != 1) { ?>checked="checked"<?php } ?>>
                                بعد نمایش: enable highlight
                            </label>
                        </div>

                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">بستن</button>
            </div>

        </div>
    </div>
</div>