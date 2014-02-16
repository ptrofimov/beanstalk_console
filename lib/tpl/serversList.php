<?php
$servers = $console->getServers();
if (!empty($_COOKIE['filter'])) {
    $visible = explode(',', $_COOKIE['filter']);
} else {
    $visible = array(
        'current-jobs-urgent',
        'current-jobs-ready',
        'current-jobs-reserved',
        'current-jobs-delayed',
        'current-jobs-buried',
        'current-tubes',
        'current-connections',
    );
}
?>
<?php if (!empty($servers)): ?>
    <table class="table table-striped table-hover" id="servers-index">
        <thead>
            <tr>
                <th>name</th>
                <?php foreach ($console->getServerStats(reset($servers)) as $key => $item): ?>
                    <th class="<?php if (!in_array($key, $visible)) echo 'hide' ?>" name="<?php echo $key ?>" title="<?php echo $item['description'] ?>"><?php echo $key ?></th>
                <?php endforeach ?>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($servers as $server):
                $stats = $console->getServerStats($server);
                ?>
                <tr>
                    <?php if (empty($stats)): ?>
                        <td><?php echo $server ?></td>
                    <?php else: ?>
                        <td><a href="?server=<?php echo $server ?>"><?php echo $server ?></a></td>
                    <?php endif ?>
                    <?php foreach ($stats as $key => $item): ?>
                        <td class="<?php if (!in_array($key, $visible)) echo 'hide' ?>" name="<?php echo $key ?>"><?php echo htmlspecialchars($item['value']) ?></td>
                    <?php endforeach ?>
                    <?php if (empty($stats)): ?>
                        <td colspan="<?php echo count($visible) ?>" class="row-full">&nbsp;</td>
                    <?php endif ?>
                    <td><a class="btn btn-small" title="Remove from list" href="?action=serversRemove&removeServer=<?php echo $server ?>"><span class="icon-minus"></span></a></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else: ?>
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
<?php endif ?>

<br /><a href="#servers-add" role="button" class="btn btn-info" data-toggle="modal">Add server</a>
<?php
$url = 'https://api.github.com/repos/ptrofimov/beanstalk_console/tags';
$ctx = stream_context_create(
        array('http' => array(
                'timeout' => 2,
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" . // check function.stream-context-create on php.net
                "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" .
                "Accept: application/vnd.github.v3+json\r\n",
            )
        ));

$json = @file_get_contents($url, false, $ctx);
if ($json) {
    $document = json_decode($json, true);
    $latest = current($document);
    $version = @$latest['name'];
    if (version_compare($version, $config['version'])>0) {
        ?>
        <br/>
        <div class="alert alert-info" style="position: relative;top:50px;">
            <span>A new version is available: <b><?php echo $version; ?></b> Get it from <b><a href="https://github.com/ptrofimov/beanstalk_console" target="_blank">Github</a></b></span>
        </div>
        <?php
    }
}
?>
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
                    <input type="text" id="port" value="<?php echo Pheanstalk::DEFAULT_PORT ?>">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-info">Add server</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>
</div>

<div id="filter" data-cookie="filter" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filter-label" class="text-info">Filter columns</h3>
    </div>
    <div class="modal-body">
        <form>
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <?php
                    $i = 0;
                    foreach ($console->getServerStatsGroups() as $groupName => $fields): $i++;
                        ?>
                        <li <?php if ($i == 1) echo 'class="active"' ?>><a href="#<?php echo $groupName ?>" data-toggle="tab"><?php echo $groupName ?></a></li>
                    <?php endforeach ?>
                </ul>
                <div class="tab-content">
                    <?php
                    $i = 0;
                    foreach ($console->getServerStatsGroups() as $groupName => $fields): $i++;
                        ?>
                        <div class="tab-pane <?php if ($i == 1) echo 'active' ?>" id="<?php echo $groupName ?>">
                            <?php foreach ($fields as $key => $description): ?>
                                <div class="control-group">
                                    <div class="controls">
                                        <label class="checkbox">
                                            <input type="checkbox" name="<?php echo $key ?>" <?php if (in_array($key, $visible)) echo 'checked="checked"' ?>>
                                            <b><?php echo $key ?></b>
                                            <br /><?php echo $description ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>
