<?php
$servers = $console->getServers();
?><!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            <?php if ($tube) echo $tube . ' - ' ?>
            <?php echo $server ? $server : 'تمامی سرور ها' ?> - 
            داشبور
        </title>

        <!-- Bootstrap core CSS -->
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/customer.css" rel="stylesheet">
        <link href="highlight/styles/magula.css" rel="stylesheet">
        <link rel="shortcut icon" href="assets/favicon.ico">
        <script>
            var url = "./?server=<?php echo $server ?>";
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
                        <a class="navbar-brand" href="./?">مدیریت سرور</a>
                    </div>
                    <div class="collapse navbar-collapse">
                        <ul class="nav navbar-nav">

                            <?php if ($server): ?>
                                <!-- Server dropdown: current, then All, then remaining -->
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <?php
                                        $serverKey = array_search($server, $servers);
                                        $serverLabel = is_numeric($serverKey) || empty($serverKey) ? $server : $serverKey;
                                        ?>
                                        <?php echo $serverLabel ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="./?">تمامی سرور ها</a></li>
                                        <?php foreach (array_diff($servers, array($server)) as $key => $serverItem): ?>
                                            <li><a href="./?server=<?php echo $serverItem ?>"><?php echo empty($key) || is_numeric($key) ? $serverItem : $key ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <!-- Server dropdown: All, then remaining -->
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        تمامی سرور ها <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($servers as $key => $serverItem): ?>
                                            <li><a href="./?server=<?php echo $serverItem ?>"><?php echo empty($key) || is_numeric($key) ? $serverItem : $key ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php endif ?>

                            <?php if ($tube): ?>
                                <!-- Tube dropdown: current, then All, then remaining -->
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <?php echo $tube ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="./?server=<?php echo $server ?>">تمامی تونل ها</a></li>
                                        <?php foreach (array_diff($tubes, array($tube)) as $tubeItem): ?>
                                            <li><a href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tubeItem) ?>"><?php echo $tubeItem ?></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php elseif ($tubes): ?>
                                <!-- Tube dropdown: All, then remaining -->
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        تمامی تونل ها <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($tubes as $tubeItem): ?>
                                            <li><a href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tubeItem) ?>"><?php echo $tubeItem ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif ?>
                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="padding:4px !important;"><img src="assets/hamburger.png" width="32px" height="32px"></a>
                                <ul class="dropdown-menu" role="menu" dir="rtl">
                                    <?php if (!isset($_tplPage) && !$server) { ?>
                                        <li><a href="#filterServer" role="button" data-toggle="modal" >فیلتر کردن ستون ها</a></li>
                                        <?php
                                    } elseif (!isset($_tplPage) && $server) {
                                        ?>
                                        <li><a href="#filter" role="button" data-toggle="modal" >فیلتر کردن ستون ها</a></li>
                                        <?php
                                    }
                                    if ($server && !$tube) {
                                        ?>
                                        <li><a href="#clear-tubes" role="button" data-toggle="modal" >پاک کردن تعدادی از تونل ها</a></li>
                                    <?php } ?>
                                    <li><a href="./?action=manageSamples" role="button" >مدیریت نمونه ها</a></li>
                                    <li class="divider"></li>
                                    <li><a href="https://github.com/kr/beanstalkd" >سایت ‌Beanstalkd در گیت هاب</a></li>
                                    <li><a href="https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt" >مشخصات پروتکل</a></li>
                                    <li><a href="https://github.com/snip77/Persian_beanstalk_console" >Beanstalkd فارسی در گیت هاب</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#settings" role="button" data-toggle="modal">تغییر تنظیمات</a></li>
                                </ul>
                            </li>
                            <?php if (@$config['auth']['enabled']) { ?>
                                <li class="dropdown">
                                    <a target="_blank" href="./?logout=true">خروج<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a>
                                </li>
                            <?php } ?>
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
                        <?php if (isset($server, $tube) && $server && $tube) { ?>
                            <form  class="navbar-form navbar-right" style="margin-top:5px;margin-bottom:0px;" role="search" action="" method="get">
                                <input type="hidden" name="server" value="<?php echo $server; ?>"/>
                                <input type="hidden" name="tube" value="<?php echo urlencode($tube); ?>"/>
                                <input type="hidden" name="state" value="<?php echo $state; ?>"/>
                                <input type="hidden" name="action" value="search"/>
                                <input type="hidden" name="limit" value="<?php echo empty($_COOKIE['searchResultLimit']) ? 25 : $_COOKIE['searchResultLimit']; ?>"/>
                                <div class="form-group">
                                    <input type="text" class="form-control input-sm search-query" name="searchStr" placeholder="Search this tube">
                                </div>
                            </form>
                        <?php } ?>
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
                        require_once dirname(__FILE__) . '/modalAddServer.php';
                        require_once dirname(__FILE__) . '/modalFilterServer.php';
                    }
                    ?>
                <?php elseif (!$tube):
                    ?>
                    <div id="idAllTubes">
                        <?php require_once dirname(__FILE__) . '/allTubes.php'; ?>
                        <?php require_once dirname(__FILE__) . '/modalClearTubes.php'; ?>
                    </div>
                    <div id='idAllTubesCopy' style="display:none"></div>
                <?php elseif (!in_array($tube, $tubes)):
                    ?>
                    <?php echo sprintf('Tube "%s" not found or it is empty', $tube) ?>
                    <br><br><a href="./?server=<?php echo $server ?>"> << back </a>
                <?php else:
                    ?>
                    <?php require_once dirname(__FILE__) . '/currentTube.php'; ?>
                    <?php require_once dirname(__FILE__) . '/modalAddJob.php'; ?>
                    <?php require_once dirname(__FILE__) . '/modalAddSample.php'; ?>
                <?php endif; ?>
                <?php if (!isset($_tplPage)) { ?>
                    <?php require_once dirname(__FILE__) . '/modalFilterColumns.php'; ?>
                <?php } ?>
                <?php require_once dirname(__FILE__) . '/modalSettings.php'; ?>
            <?php endif; ?>
        </div>

        <script src='assets/vendor/jquery/jquery.js'></script>
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
