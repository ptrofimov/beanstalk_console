<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Beanstalk console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./css/bootstrap.css" rel="stylesheet">
    
    <style type="text/css">
      body {
        padding: 80px 10px 40px 10px; 
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    
    <script>
    	var url = "./?server=<?=$server?>";
    	var contentType = "<?=isset($contentType)?$contentType:''?>";
    </script>
    
    <link href="./css/bootstrap-responsive.css" rel="stylesheet">
    <link href="./css/customer.css" rel="stylesheet">
	<link href="./highlight/styles/magula.css" rel="stylesheet">
	
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body data-spy="scroll" data-target=".subnav" data-offset="50">
  
<!-- Header Line -->  
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
					<li><a href="./?server=<?=$item?>"><?=$item?></a></li>
				<?php endforeach?>              
	            </ul>
          	</div>
          </div>         
          
          <div class="nav-collapse">
            <ul class="nav">
            	<?php if(!empty($server)):?><li class="active"><a href="?server=<?=$server?>"><?=$server?></a></li><?php endif;?>
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
<!-- End Header Line -->

<?php if(!empty($errors)): ?>
	<h2>Errors</h2>
	<?php foreach ($errors as $item):?>		
		<p><?=$item?></p>
	<?php endforeach;?>	
	<a href="./"><< back</a>
<?php else:?>     
	<?php if(!$tube):?>
	
	<!-- Table All Tube -->
	<div id="idAllTubes">	
		<?php require_once '../lib/tpl/allTubes.php';?>
	</div>
	<div id="idAllTubesCopy" style="display:none"></div>
	<!-- End Table All Tube -->
	
	<?php elseif(!in_array($tube,$tubes)):?>
	
	<!-- Tube not found -->
		<?=sprintf('Tube "%s" not found or it is empty',$tube)?>
		<br><br><a href="./?server=<?=$server?>"> << back </a>
	<!-- End Tube not found -->
	 
	<?php else:?>
	
	<!-- Table current Tube -->
		<?php require_once '../lib/tpl/currentTube.php';?>
	<!-- End Table current Tube -->
	
	<?php endif;?>	
	
	
	<!-- Modal window add job -->
	<?php require_once '../lib/tpl/modalAddJob.php';?>
	<!-- End Modal window add job -->
<?php endif;?>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="./js/jquery.js"></script>
    <script src="./js/jquery.color.js"></script>
    <script src="./js/jquery.cookie.js"></script>
    <script src="./js/bootstrap-transition.js"></script>
    <script src="./js/bootstrap-alert.js"></script>
    <script src="./js/bootstrap-modal.js"></script>
    <script src="./js/bootstrap-dropdown.js"></script>
    <script src="./js/bootstrap-scrollspy.js"></script>
    <script src="./js/bootstrap-tab.js"></script>
    <script src="./js/bootstrap-tooltip.js"></script>
    <script src="./js/bootstrap-popover.js"></script>
    <script src="./js/bootstrap-button.js"></script>
    <script src="./js/bootstrap-collapse.js"></script>
    <script src="./js/bootstrap-carousel.js"></script>
    <script src="./js/bootstrap-typeahead.js"></script>
    
    <script src="./highlight/highlight.pack.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
    
    <script src="./js/customer.js"></script>
  </body>
 </html>