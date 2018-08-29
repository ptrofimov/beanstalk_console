<div class="modal fade" id="clear-tubes" data-cookie="tubefilter" tabindex="-1" role="dialog" aria-labelledby="clear-tubes-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="clear-tubes-label">پاک کردن تعدادی از تونل ها</h4>
            </div>
            <div class="modal-body">
                <form>
                    <fieldset>
                        <div class="form-group">
                            <label for="focusedInput">نام تونل
                                <small class="text-muted">(استفاده کرد <a href="http://james.padolsey.com/javascript/regex-selector-for-jquery/" target="_blank">jQuery
                                        regexp</a> میتوان از قواعد)
                                </small>
                            </label>

                            <div class="input-group">
                                <input class="form-control focused" id="tubeSelector" type="text" placeholder="prefix*"
                                       value="<?php echo @$_COOKIE['tubeSelector']; ?>">

                                <div class="input-group-btn">
                                    <a href="#" class="btn btn-info" id="clearTubesSelect">Select</a>
                                </div>

                            </div>

                        </div>
                    </fieldset>
                    <div>
                        <strong>لیست تونل ها</strong>
                        <?php
                        foreach ((is_array($tubes) ? $tubes : array()) as $tube):
                            ?>
                            <div class="checkbox">
                                <label class="">
                                    <input type="checkbox" name="<?php echo $tube ?>" value="1">
                                    <b><?php echo $tube ?></b>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">بستن</button>
                <a href="#" class="btn btn-success" id="clearTubes">پاک کردن تونل های انتخاب شده</a>
                <br/><br/>

                <p class="text-muted text-right small">
                    * پاک کننده تونل با سر زدن به تونل ها و پاک کردن کار ها کار می کند
                </p>
            </div>
        </div>
    </div>
</div>