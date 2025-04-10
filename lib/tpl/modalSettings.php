<?php
// Get default values directly from config for labels (provide fallbacks just in case)
$configSettings = $GLOBALS['config']['settings'] ?? [];
$defaultAutoRefreshTimeoutMs = $configSettings['autoRefreshTimeoutMs'] ?? 500;
$defaultSearchResultLimit = $configSettings['searchResultLimit'] ?? 25;

?>
<div id="settings" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="settings-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="settings-label">Settings</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="form-group">
                        <label for="tubePauseSeconds"><b>Tube pause seconds</b> (<i>-1</i> means the default: <i>3600</i>, <i>0</i> is reserved for
                            un-pause)</label>
                        <input class="form-control focused" id="tubePauseSeconds" name="tubePauseSeconds" type="text" value="<?php echo htmlspecialchars($settings->getTubePauseSeconds()); ?>">
                    </div>
                    <div class="form-group">
                        <label for="autoRefreshTimeoutMs"><b>Auto-refresh interval in milliseconds</b> (Default: <i><?php echo htmlspecialchars($defaultAutoRefreshTimeoutMs); ?></i>)</label>
                        <input class="form-control focused" id="autoRefreshTimeoutMs" name="autoRefreshTimeoutMs" type="text" value="<?php echo htmlspecialchars($settings->getAutoRefreshTimeoutMs()); ?>">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="enableAutoRefreshLoad" name="enableAutoRefreshLoad" value="1" <?php if ($settings->isAutoRefreshLoadEnabled()) echo 'checked="checked"'; ?>>
                                auto-refresh on load
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="searchResultLimit"><b>Search result limits</b> (Default: <i><?php echo htmlspecialchars($defaultSearchResultLimit); ?></i>)</label>
                        <input class="form-control focused" id="searchResultLimit" name="searchResultLimit" type="text" value="<?php echo htmlspecialchars($settings->getSearchResultLimit()); ?>">
                    </div>
                    <div class="form-group">
                        <label><b>Preferred way to deal with job data</b></label>

                        <div class="checkbox">
                            <label>
                                <!-- ID/Name now matches config/cookie key -->
                                <input type="checkbox" id="enableJsonDecode" name="enableJsonDecode" value="1" <?php if ($settings->isJsonDecodeEnabled()) echo 'checked="checked"'; ?>>
                                before display: json_decode()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="enableUnserialization" name="enableUnserialization" value="1" <?php if ($settings->isUnserializationEnabled()) echo 'checked="checked"'; ?>>
                                before display: unserialize()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="enableBase64Decode" name="enableBase64Decode" value="1" <?php if ($settings->isBase64DecodeEnabled()) echo 'checked="checked"'; ?>>
                                before display: base64_decode()
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="enableJobDataHighlight" name="enableJobDataHighlight" value="1" <?php if ($settings->isJobDataHighlightEnabled()) echo 'checked="checked"'; ?>>
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
    </div>
</div>