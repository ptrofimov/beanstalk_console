	
	<table class="table table-bordered table-striped styled">
		<?php $tubeStats = $console->interface->getTubeStats($tube);?>
		<tr>
			<?php foreach($tubeStats as $item):?>
			<th title="<?=$item['descr']?>"><?=$item['key']?></th>
			<?php endforeach;?>
		</tr>
		<tr>
			<?php foreach($tubeStats as $item):?>
				<td><?=$item['value']?></td>
			<?php endforeach;?>
		</tr>
	</table>
	
	<p>
		<b>Actions:</b>&nbsp;
		<a class="btn btn-small" href="?server=<?=$server?>&tube=<?=$tube?>&action=kick&count=1"><i class="icon-play"></i> Kick 1 job</a>
		<a class="btn btn-small" href="?server=<?=$server?>&tube=<?=$tube?>&action=kick&count=10"><i class="icon-forward"></i> Kick 10 jobs</a>
		<a class="btn btn-danger btn-small" href="?server=<?=$server?>&tube=<?=$tube?>&action=delete&count=1"><i class="icon-trash icon-white"></i> Delete next ready job</a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  data-toggle="modal" class="btn btn-success btn-small" href="#" id="addJob"><i class="icon-plus-sign icon-white"></i> Add job</a>
	</p>

	<?php foreach($peek as $state=>$job):?>
		<hr />
		<h3>Next job in "<?=$state?>" state</h3>
		<?php if($job):?>
		
		<div class="row show-grid">
		    <div class="span3">
		    	<table class="table">
				 <thead>
					<tr>
						<th>Stats:</th>
						<th>&nbsp;</th>
					</tr>
				 </thead>
				 <tbody>
					<?php foreach($job['stats'] as $key=>$value):?>
					<tr>
						<td><?=$key?></td>
						<td><?=$value?></td>
					</tr>
					<?php endforeach?>
				</tbody>
				</table>
		    </div>
			<div class="span9">
				<b>Job data:</b><br />
				<pre><code><?=trim(var_export($job['data'],true), "'");?></code></pre>			
			</div>
		</div>		
		<?php else:?>
			<i>empty</i>
		<?php endif?>
	<?php endforeach?>