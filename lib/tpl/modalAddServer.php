<div id="servers-add" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true" dir="rtl">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button style="float: left;" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="servers-add-labal">ایجاد سرور</h4>

            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="host" style="float: right;">هاست</label>

                        <div class="col-sm-10">
                            <input type="text" id="host" value="localhost" class="form-control" style="float: left;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="port" style="float: right;">پورت</label>

                        <div class="col-sm-10">
                            <input type="text" id="port" value="<?php echo Pheanstalk::DEFAULT_PORT ?>" class="form-control" style="float: left;">
                        </div>
                    </div>


                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" style="float: left;">ایجاد سرور</button>
                <button class="btn" data-dismiss="modal" aria-hidden="true" style="float: left;">انصراف</button>
            </div>
        </div>
    </div>

</div>