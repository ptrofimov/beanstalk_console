<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Beanstalk console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/assets/vendor/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
    <link href="./css/customer.css" rel="stylesheet">
    <link href="./highlight/styles/magula.css" rel="stylesheet">
    <script>
        var url = "./index.php?server=<?php echo $server?>";
        var contentType = "<?php echo isset($contentType)?$contentType:''?>";
    </script>
</head>
<body>
<?php /*?>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="./">Beanstalk console</a>

            <div class="btn-toolbar pull-right" style="margin: 0px; padding: 0px; height: 40px">
                <?php if(empty($tube)):?>
                <div class="btn-group" style="margin: 0px 21px 0px 0px;">
                    <a class="btn btn-small" href="#" id="autoRefresh"><i class="icon-refresh"></i></a>
                </div>
                <?php endif;?>
                <div class="btn-group">
                    <a class="btn btn-info" id="addServer" href="#"><i class="icon-plus-sign icon-white"></i> Add server</a>
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="icon-leaf"></i> Server<span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" id="listServers">
                        <?php foreach($config['servers'] as $item):?>
                        <li><a href="./?server=<?php echo $item?>"><?php echo $item?></a></li>
                        <?php endforeach?>
                    </ul>
                </div>
            </div>

            <div class="nav-collapse">
                <ul class="nav">
                    <?php if(!empty($server)):?><li class="active"><a href="?server=<?php echo $server?>"><?php echo $server?></a></li><?php endif;?>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>
<div id="subnavServer" class="subnav subnav-fixed" style="display:none;">
    <div class="pull-right form-inline" style="padding: 4px 25px 0px 0px">
        <input class="input-xlarge focused" id="server" name="server" type="text" placeholder="server"> :
        <input class="input-small focused" id="port" name="port" type="text" placeholder="port">
        <button type="submit" id="saveServer" class="btn btn-primary">Add</button>
    </div>
</div>
<?php */?>

<?php if(!empty($errors)): ?>
    <h2>Errors</h2>
    <?php foreach ($errors as $item):?>
        <p><?php echo $item?></p>
    <?php endforeach;?>
    <a href="./"><< back</a>
<?php else:?>
    <?php if(!$server):?>
        <?php include(dirname(__FILE__) . '/serversList.php')?>
    <?php elseif(!$tube):?>
        <div id="idAllTubes">
            <?php require_once '../lib/tpl/allTubes.php';?>
        </div>
        <div id="idAllTubesCopy" style="display:none"></div>
    <?php elseif(!in_array($tube,$tubes)):?>
        <?php echo sprintf('Tube "%s" not found or it is empty',$tube)?>
        <br><br><a href="./?server=<?php echo $server?>"> << back </a>
    <?php else:?>
        <?php require_once '../lib/tpl/currentTube.php';?>
    <?php endif;?>

    <?php require_once '../lib/tpl/modalAddJob.php';?>
<?php endif;?>

<script src="/assets/vendor/jquery/jquery.js"></script>
<script src="./js/jquery.color.js"></script>
<script src="./js/jquery.cookie.js"></script>
<script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="./highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<script src="./js/customer.js"></script>
</body>
</html>
