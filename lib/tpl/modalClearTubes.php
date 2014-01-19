<div id="clear-tubes" data-cookie="tubefilter" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filter-label" class="text-info">Clear multiple tubes</h3>
    </div>
    <div class="modal-body">
        <form>
            <fieldset>  		
                <div class="control-group">
                    <label class="control-label" for="focusedInput">Tube name (supports <a href="http://james.padolsey.com/javascript/regex-selector-for-jquery/" target="_blank">jQuery regexp</a> syntax)</label>
                    <div class="controls">
                        <input class="input-xlarge focused" id="tubeSelector" type="text" placeholder="prefix*" value="<?php echo @$_COOKIE['tubeSelector']; ?>">
                    </div>
                    <a href="#" class="btn" id="clearTubesSelect">Select</a>
                </div>
            </fieldset>
            <div>
                <?php
                foreach ($tubes as $tube):
                    ?>
                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input type="checkbox" name="<?php echo $tube ?>" value="1">
                                <b><?php echo $tube ?></b>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <p class="muted text-left">
            * Tube clear works by peeking to all jobs and deleting them in a loop.
        </p>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <a href="#" class="btn btn-success" id="clearTubes">Clear selected tubes</a>

    </div>
</div>