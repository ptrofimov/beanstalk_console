<?php
$servers = $console->getServers();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Beanstalk console</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="assets/vendor/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
        <link href="css/customer.css" rel="stylesheet">
        <link href="highlight/styles/magula.css" rel="stylesheet">
        <script>
            var url = "./index.php?server=<?php echo $server ?>";
            var contentType = "<?php echo isset($contentType) ? $contentType : '' ?>";
        </script>
    </head>
    <body>
        <div class="container">

            <?php if (!empty($servers)): ?>
                <div class="navbar">
                    <div class="navbar-inner">
                        <div class="container">
                            <a class="btn btn-navbar" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </a>
                            <a class="brand" href="index.php">Beanstalk console</a>
                            <div class="nav-collapse collapse navbar-responsive-collapse">
                                <ul class="nav">
                                    <?php if ($server && $tube): ?>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                                <?php echo $server ?> <b class="caret"></b>
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
                                                <?php echo $tube ?> <b class="caret"></b>
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
                                                <?php echo $server ?> <b class="caret"></b>
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
                                                All tubes <b class="caret"></b>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($tubes as $tubeItem): ?>
                                                    <li><a href="index.php?server=<?php echo $server ?>&tube=<?php echo $tubeItem ?>"><?php echo $tubeItem ?></a></li>
                                                <?php endforeach ?>
                                            </ul>
                                        </li>
                                    <?php else: ?>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                                All servers <b class="caret"></b>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($servers as $serverItem): ?>
                                                    <li><a href="index.php?server=<?php echo $serverItem ?>"><?php echo $serverItem ?></a></li>
                                                <?php endforeach ?>
                                            </ul>
                                        </li>
                                    <?php endif ?>
                                    <!--<li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="#">Action</a></li>
                                            <li><a href="#">Another action</a></li>
                                            <li><a href="#">Something                                 <li><a href="#">Something                                 <li><a href="#">Something                                 <li><a href="#">Something else here</a></li>
                                            <li class="divider"></li>
                                            <li class="nav-header">Nav header</li>
                                            <li><a href="#">Separated link</a></li>
                                            <li><a href="#">One more separated link</a></li>
                                        </ul>
                                    </li>-->
                                </ul>
                                <!--<form class="navbar-search pull-left" action="">
                                    <input type="text" class="search-query span2" placeholder="Search">
                                </form>-->
                                <ul class="nav pull-right">
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Toolbox <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#filter" role="button" data-toggle="modal">Filter columns</a>
                                                <?php if ($server && !$tube) { ?>
                                                    <a href="#clear-tubes" role="button" data-toggle="modal">Clear multiple tubes</a>
                                                <?php } ?> 
                                            </li>
                                        </ul>
                                    </li>


                                    <!--<li><a href="#">Link</a></li>
                                    <li class="divider-vertical"></li>-->
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Links <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="https://github.com/kr/beanstalkd">Beanstalk (github)</a></li>
                                            <li><a href="https://github.com/kr/beanstalkd/blob/master/doc/protocol.md">Protocol Specification</a></li>
                                            <li class="divider"></li>
                                            <li><a href="https://github.com/ptrofimov/beanstalk_console">Beanstalk console (github)</a></li>
                                        </ul>
                                    </li>
                                    <a class="btn btn-small" href="#" id="autoRefresh"><i class="icon-refresh"></i></a>
                                </ul>
                            </div><!-- /.nav-collapse -->
                        </div>
                    </div><!-- /navbar-inner -->
                </div>
            <?php endif ?>
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $item): ?>
                    <p class="alert alert-error"><span class="label label-important">Error</span> <?php echo $item ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!$server): ?>
                    <?php include(dirname(__FILE__) . '/serversList.php') ?>
                <?php elseif (!$tube): ?>
                    <div id="idAllTubes">
                        <?php require_once '../lib/tpl/allTubes.php'; ?>
                    </div>
                    <div id="idAllTubesCopy" style="display:none"></div>
                <?php elseif (!in_array($tube, $tubes)): ?>
                    <?php echo sprintf('Tube "%s" not found or it is empty', $tube) ?>
                    <br><br><a href="./?server=<?php echo $server ?>"> << back </a>
                <?php else: ?>
                    <?php require_once '../lib/tpl/currentTube.php'; ?>
                <?php endif; ?>

                <?php require_once '../lib/tpl/modalAddJob.php'; ?>
            <?php endif; ?>
        </div>

        <script src="assets/vendor/jquery/jquery.js"></script>
        <script src="js/jquery.color.js"></script>
        <script src="js/jquery.cookie.js"></script>
        <script src="js/jquery.regexp.js"></script>
        <script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="highlight/highlight.pack.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>
        <script src="js/customer.js"></script>
    </body>
</html>
