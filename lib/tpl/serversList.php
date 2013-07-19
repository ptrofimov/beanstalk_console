<?php
$servers = $console->getServers();
?>

<ul class="breadcrumb lead">
    <li class="active">Home</li>
</ul>

<?php if(!empty($servers)):?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Server</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($servers as $server):?>
                <tr>
                    <td><a href="?server=<?php echo $server?>"><?php echo $server?></a></td>
                    <td><a class="btn" href="?action=serversRemove&removeServer=<?php echo $server?>">Remove</a></td>
                </tr>
            <?php endforeach?>
        </tbody>
    </table>
<?php endif?>

<a href="#servers-add" role="button" class="btn" data-toggle="modal">Add server</a>

<div id="servers-add" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="servers-add-label">Add server</h3>
    </div>
    <div class="modal-body">
        <form class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="host">Host</label>
                <div class="controls">
                    <input type="text" id="host" placeholder="localhost" value="localhost">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="port">Port</label>
                <div class="controls">
                    <input type="text" id="port" placeholder="11300" value="11300">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary">Add server</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>
</div>
