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
<div class="container">
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
</div>

<script src="/assets/vendor/jquery/jquery.js"></script>
<script src="./js/jquery.color.js"></script>
<script src="./js/jquery.cookie.js"></script>
<script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="./highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<script src="./js/customer.js"></script>
</body>
</html>
