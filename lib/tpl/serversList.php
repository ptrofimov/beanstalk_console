<?php
$servers = $console->getServers();
?>

<?php if(!empty($servers)):?>
    <ul class="breadcrumb lead">
        <li class="active">Beanstalkd console</li>
    </ul>
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
<?php else:?>
    <div class="page-header">
        <h1 class="text-info">Hello!</h1>
    </div>
    <p>
        This is Beanstalk console, web-interface for
        <a href="http://kr.github.io/beanstalkd/" target="_blank">simple and fast work queue</a>
    </p>
    <p>
        Your servers' list is empty. You could fix it in two ways:
        <ol>
            <li>Click the button below to add server just for you and save it in cookies</li>
            <li>Edit <b>config.php</b> file and add server for everybody</li>
        </ol>
    </p>
<?php endif?>

<br /><a href="#servers-add" role="button" class="btn btn-info" data-toggle="modal">Add server</a>

<div id="servers-add" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="servers-add-label" class="text-info">Add server</h3>
    </div>
    <div class="modal-body">
        <form class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="host">Host</label>
                <div class="controls">
                    <input type="text" id="host" value="localhost">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="port">Port</label>
                <div class="controls">
                    <input type="text" id="port" value="<?php echo Pheanstalk::DEFAULT_PORT?>">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-info">Add server</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>
</div>
