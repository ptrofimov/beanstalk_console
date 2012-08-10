
	<section id="summaryTable">
	  <h2>Server's tubes statistics</h2>	  
	  <br>	 
	  <table class="table table-bordered table-striped">
	    <thead>
	      	<tr>
			<?php foreach(reset($tubesStats) as $item):?>
				<th title="<?=$item['descr']?>"><?=$item['key']?></th>
			<?php endforeach;?>
			</tr>
	    </thead>
	    <tbody>			
		<?php foreach($tubesStats as $row):?>
			<tr id="tube_<?=$row[0]['value']?>">
			<?php foreach($row as $item):?>
				<?php if($item['key']=='name'):?>
					<td><a href="?server=<?=$server?>&tube=<?=$item['value']?>"><?=$item['value']?></a></td>
				<?php else:?>
					<td><?=$item['value']?></td>
				<?php endif?>
			<?php endforeach;?>
			</tr>
		<?php endforeach?>
	    </tbody>
	  </table>
	</section>
