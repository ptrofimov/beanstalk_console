<?php
$tubeBodyDisplay = isset($tubeBodyDisplay) && is_array($tubeBodyDisplay) ? $tubeBodyDisplay : array();
$tubeBodyDisplayOverride = isset($tubeBodyDisplayOverride) && is_array($tubeBodyDisplayOverride) ? $tubeBodyDisplayOverride : null;
$hasTubeBodyDisplayOverride = $tubeBodyDisplayOverride !== null;
$bodyDisplayValues = $hasTubeBodyDisplayOverride ? $tubeBodyDisplayOverride : $tubeBodyDisplay;
$bodyDisplayTargetTube = isset($tubeBodyDisplayTargetTube) && $tubeBodyDisplayTargetTube !== '' ? $tubeBodyDisplayTargetTube : $tube;
$bodyDisplayBatchId = isset($tubeBodyDisplayBatchId) ? $tubeBodyDisplayBatchId : '';
?>
<div id="tube-body-display-settings" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tube-body-display-settings-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="./?server=<?php echo urlencode($server); ?>&action=tubeBodyDisplaySave">
                <input type="hidden" name="tube" value="<?php echo htmlspecialchars($bodyDisplayTargetTube); ?>">
                <?php if ($bodyDisplayBatchId !== ''): ?>
                    <input type="hidden" name="batchId" value="<?php echo htmlspecialchars($bodyDisplayBatchId); ?>">
                <?php endif; ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="tube-body-display-settings-label">Body display for <?php echo htmlspecialchars($bodyDisplayTargetTube); ?></h4>
                </div>
                <div class="modal-body">
                    <p class="help-block">
                        These settings apply to this tube on this server for everyone using this console.
                        Review batches prepared from this source tube use the same settings.
                    </p>

                    <div class="radio">
                        <label>
                            <input type="radio" name="mode" value="global" <?php if (!$hasTubeBodyDisplayOverride) echo 'checked="checked"'; ?>>
                            Use global settings
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="mode" value="custom" <?php if ($hasTubeBodyDisplayOverride) echo 'checked="checked"'; ?>>
                            Custom for this tube
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label><b>Apply before display</b></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="enableBase64Decode" value="1" <?php if (!empty($bodyDisplayValues['enableBase64Decode'])) echo 'checked="checked"'; ?>>
                                base64_decode()
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="enableUnserialization" value="1" <?php if (!empty($bodyDisplayValues['enableUnserialization'])) echo 'checked="checked"'; ?>>
                                unserialize()
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="enableJsonDecode" value="1" <?php if (!empty($bodyDisplayValues['enableJsonDecode'])) echo 'checked="checked"'; ?>>
                                json_decode()
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true" type="button">Close</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
