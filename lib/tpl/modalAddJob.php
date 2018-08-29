<div class="modal fade" id="modalAddJob" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">اضافه کردن کار جدید</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="alert alert-error hide" id="tubeSaveAlert">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>اخطار!</strong> فیلد های لازم علامت گذاری شده اند توسط : *
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-3" for="focusedInput">*نام تونل</label>

                            <div class="col-xs-9">
                                <input class="form-control focused" id="tubeName" type="text" value="<?php echo $tube ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-3" for="textarea">*اطلاعات</label>

                            <div class="col-xs-9">
                                <textarea id="tubeData" rows="3" class="form-control ">
                                </textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-3" for="focusedInput">اولویت</label>

                            <div class="col-xs-9">
                                <input class="form-control focused" id="tubePriority" type="text" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-3" for="focusedInput">تاخیر</label>

                            <div class="col-xs-9">
                                <input class="form-control focused" id="tubeDelay" type="text" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-3" for="focusedInput">Ttr</label>

                            <div class="col-xs-9">
                                <input class="form-control focused" id="tubeTtr" type="text" value="">
                            </div>
                        </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">بستن</a>
                <a href="#" class="btn btn-success" id="tubeSave">اعمال تغییرات</a>
            </div>
            </fieldset>
            </form>
        </div>
    </div>
</div>