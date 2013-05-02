<div class="modal hide" id="modalAddJob">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Add new job</h3>
  </div>
  <div class="modal-body">
	<form class="form-horizontal">
  		<fieldset>  		
  			<div class="alert alert-error hide" id="tubeSaveAlert">
		        <button type="button" class="close" data-dismiss="alert">×</button>
		        <strong>Error!</strong> Required fields are marked *
		    </div>
  				
		    <div class="control-group">
		   		<label class="control-label" for="focusedInput">*Tube name</label>
		        <div class="controls">
		        	<input class="input-xlarge focused" id="tubeName" type="text" value="<?php echo $tube?>">
		        </div>
		    </div>
		    <div class="control-group">
		    	<label class="control-label" for="textarea">*Data</label>
		    	<div class="controls">
		        	<textarea class="input-xlarge" id="tubeData" rows="3"></textarea>
		    	</div>
		    </div>
		    <div class="control-group">
		   		<label class="control-label" for="focusedInput">Priority</label>
		        <div class="controls">
		        	<input class="input-xlarge focused" id="tubePriority" type="text" value="">
		        </div>
		    </div>
		    <div class="control-group">
		   		<label class="control-label" for="focusedInput">Delay</label>
		        <div class="controls">
		        	<input class="input-xlarge focused" id="tubeDelay" type="text" value="">
		        </div>
		    </div>
		    <div class="control-group">
		   		<label class="control-label" for="focusedInput">Ttr</label>
		        <div class="controls">
		        	<input class="input-xlarge focused" id="tubeTtr" type="text" value="">
		        </div>
		    </div>
		  </div>
		  <div class="modal-footer">
		    <a href="#" class="btn" data-dismiss="modal">Close</a>
		    <a href="#" class="btn btn-success" id="tubeSave">Save changes</a>
		  </div>
		</fieldset>
	</form>
</div>