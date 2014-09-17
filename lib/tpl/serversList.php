<?php
$servers = $console->getServers();
$cookieServers = $console->getServersCookie();

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
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-striped table-hover" id="servers-index">
                <thead>
                    <tr>
                        <th>name</th>
                        <?php foreach ($console->getServerStats(reset($servers)) as $key => $item): ?>
                            <th class="<?php if (!in_array($key, $visible)) echo 'hide' ?>" name="<?php echo $key ?>"
                                title="<?php echo $item['description'] ?>"><?php echo $key ?></th>
                            <?php endforeach ?>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach (array_unique($servers) as $server):
                        $stats = $console->getServerStats($server);
                        ?>
                        <tr>
                            <?php if (empty($stats)): ?>
                                <td><?php echo $server ?></td>
                            <?php else: ?>
                                <td><a href="?server=<?php echo $server ?>"><?php echo $server ?></a></td>
                            <?php endif ?>
                            <?php foreach ($stats as $key => $item): ?>
                                <td class="<?php if (!in_array($key, $visible)) echo 'hide' ?>"
                                    name="<?php echo $key ?>"><?php echo htmlspecialchars($item['value']) ?></td>
                                <?php endforeach ?>
                                <?php if (empty($stats)): ?>
                                <td colspan="<?php echo count($visible) ?>" class="row-full">&nbsp;</td>
                            <?php endif ?>
                            <td><?php if (array_intersect(array($server), $cookieServers)): ?>
                                    <a class="btn btn-xs btn-danger" title="Remove from list" href="?action=serversRemove&removeServer=<?php echo $server ?>"><span
                                            class="glyphicon glyphicon-minus"></span></a>
                                    <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <a href="#servers-add" role="button" class="btn btn-info" id="addServer">Add server</a>
        </div>
    </div>
<?php else: ?>
    <div class="site-wrapper">
        <div class="site-wrapper-inner">
            <div class="col-sm-8 col-sm-push-2 text-center">
                <h1>Hello!</h1>

                <p class="lead">
                    This is Beanstalk console,<br/>web-interface for
                    <a href="http://kr.github.io/beanstalkd/" target="_blank">simple and fast work queue</a>
                </p>

                <p>
                    Your servers' list is empty. You could fix it in two ways:
                <ol class="inside">
                    <li>Click the button below to add server just for you and save it in cookies</li>
                    <li>Edit <b>config.php</b> file and add server for everybody</li>
                </ol>
                </p>
                <p>
                    <br/><a href="#servers-add" role="button" class="btn btn-lg btn-success" data-toggle="modal">Add server</a>
                </p>
            </div>
        </div>
    </div>



<?php
endif;
if ($tplVars['_tplMain'] != 'ajax') {
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
        if (version_compare($version, $config['version']) > 0) {
            ?>
            <br/>
            <div class="alert alert-info" style="position: relative;top:50px;">
                <span>A new version is available: <b><?php echo $version; ?></b> Get it from <b><a href="https://github.com/ptrofimov/beanstalk_console"
                                                                                                   target="_blank">Github</a></b></span>
            </div>
            <?php
        }
    }
}
?>