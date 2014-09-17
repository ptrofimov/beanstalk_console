<?php
$servers = $console->getServers();
?><!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Beanstalk console</title>

        <!-- Bootstrap core CSS -->
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/customer.css" rel="stylesheet">
        <link href="highlight/styles/magula.css" rel="stylesheet">
        <script>
            var url = "./index.php?server=<?php echo $server ?>";
            var contentType = "<?php echo isset($contentType) ? $contentType : '' ?>";
        </script>

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <?php if (!empty($servers)): ?>
        <body>
        <?php else: ?>
        <body class="no-nav">
        <?php endif ?>

        <?php if (!empty($servers)): ?>
            <div class="navbar navbar-fixed-top navbar-default" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="index.php">Beanstalk console</a>
                    </div>
                    <div class="collapse navbar-collapse">
                        <ul class="nav navbar-nav">

                            <?php if ($server && $tube): ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <?php echo $server ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="index.php">All servers</a></li>
                                        <?php foreach (array_diff($servers, array($server)) as $serverItem): ?>
                                            <li><a href="index.php?server=<?php echo $serverItem ?>"><?php echo $serverItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <?php echo $tube ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="index.php?server=<?php echo $server ?>">All tubes</a></li>
                                        <?php foreach (array_diff($tubes, array($tube)) as $tubeItem): ?>
                                            <li><a href="index.php?server=<?php echo $server ?>&tube=<?php echo $tubeItem ?>"><?php echo $tubeItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php elseif ($server): ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <?php echo $server ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="index.php">All servers</a></li>
                                        <?php foreach (array_diff($servers, array($server)) as $serverItem): ?>
                                            <li><a href="index.php?server=<?php echo $serverItem ?>"><?php echo $serverItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        All tubes <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($tubes as $tubeItem): ?>
                                            <li><a href="index.php?server=<?php echo $server ?>&tube=<?php echo $tubeItem ?>"><?php echo $tubeItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php else:
                                ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        All servers <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($servers as $serverItem): ?>
                                            <li><a href="index.php?server=<?php echo $serverItem ?>"><?php echo $serverItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php endif ?>

                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Toolbox <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <?php if (!isset($_tplPage) && !$server) { ?>
                                        <li><a href="#filterServer" role="button" data-toggle="modal">Filter columns</a></li>
                                        <?php
                                    } elseif (!isset($_tplPage) && $server) {
                                        ?>
                                        <li><a href="#filter" role="button" data-toggle="modal">Filter columns</a></li>
                                        <?php
                                    }
                                    if ($server && !$tube) {
                                        ?>
                                        <li><a href="#clear-tubes" role="button" data-toggle="modal">Clear multiple tubes</a></li>
                                    <?php } ?>
                                    <li><a href="index.php?action=manageSamples" role="button">Manage samples</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#settings" role="button" data-toggle="modal">Edit settings</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Links <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="https://github.com/kr/beanstalkd">Beanstalk (github)</a></li>
                                    <li><a href="https://github.com/kr/beanstalkd/blob/master/doc/protocol.md">Protocol Specification</a></li>
                                    <li class="divider"></li>
                                    <li><a href="https://github.com/ptrofimov/beanstalk_console">Beanstalk console (github)</a></li>
                                </ul>
                            </li>
                            <?php if ($server && !$tube) { ?>
                                <li>
                                    <button type="button" id="autoRefresh" class="btn btn-default btn-small">
                                        <span class="glyphicon glyphicon-refresh"></span>
                                    </button>
                                </li>
                            <?php } else if (!$tube) { ?>
                                <li>
                                    <button type="button" id="autoRefreshSummary" class="btn btn-default btn-small">
                                        <span class="glyphicon glyphicon-refresh"></span>
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>

                    </div>
                    <!--/.nav-collapse -->
                </div>
            </div>

            <div class="container">
            <?php endif ?>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $item): ?>
                    <p class="alert alert-error"><span class="label label-important">Error</span> <?php echo $item ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (isset($_tplPage)): ?>
                    <?php include(dirname(__FILE__) . '/' . $_tplPage . '.php') ?>
                <?php elseif (!$server): ?>
                    <div id="idServers">
                        <?php
                        include(dirname(__FILE__) . '/serversList.php');
                        ?>
                    </div>
                    <div id="idServersCopy" style="display:none"></div>
                    <?php
                    if ($tplVars['_tplMain'] != 'ajax') {
                        require_once '../lib/tpl/modalAddServer.php';
                        require_once '../lib/tpl/modalFilterServer.php';
                    }
                    ?>
                <?php elseif (!$tube):
                    ?>
                    <div id="idAllTubes">
                        <?php require_once '../lib/tpl/allTubes.php'; ?>
                        <?php require_once '../lib/tpl/modalClearTubes.php'; ?>
                    </div>
                    <div id="idAllTubesCopy" style="display:none"></div>
                <?php elseif (!in_array($tube, $tubes)):
                    ?>
                    <?php echo sprintf('Tube "%s" not found or it is empty', $tube) ?>
                    <br><br><a href="./?server=<?php echo $server ?>"> << back </a>
                <?php else:
                    ?>
                    <?php require_once '../lib/tpl/currentTube.php'; ?>
                    <?php require_once '../lib/tpl/modalAddJob.php'; ?>
                    <?php require_once '../lib/tpl/modalAddSample.php'; ?>
                <?php endif; ?>
                <?php if (!isset($_tplPage)) { ?>
                    <?php require_once '../lib/tpl/modalFilterColumns.php'; ?>
                <?php } ?>
                <?php require_once '../lib/tpl/modalSettings.php'; ?>
            <?php endif; ?>
        </div>

        <script src="assets/vendor/jquery/jquery.js"></script>
        <script src="js/jquery.color.js"></script>
        <script src="js/jquery.cookie.js"></script>
        <script src="js/jquery.regexp.js"></script>
        <script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
        <?php if (isset($_COOKIE['isDisabledJobDataHighlight']) and $_COOKIE['isDisabledJobDataHighlight'] != 1) { ?>
            <script src="highlight/highlight.pack.js"></script>
            <script>hljs.initHighlightingOnLoad();</script><?php } ?>
        <script src="js/customer.js"></script>
    </body>
</html>
