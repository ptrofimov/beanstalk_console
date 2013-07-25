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
                    <li class="active"><a href="index.php">Servers</a></li>
                    <!--<li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
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
                    <li>
                        <a href="#filter" role="button" data-toggle="modal">Filter columns</a>
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
                </ul>
            </div><!-- /.nav-collapse -->
        </div>
    </div><!-- /navbar-inner -->
</div>

<?php if(!empty($servers)):?>
    <!--<div class="text-right">
        <a href="#filter" role="button" class="btn btn-info" data-toggle="modal">Filter columns</a>
    </div>-->
    <table class="table table-striped table-hover" id="servers-index">
        <thead>
            <tr>
                <th>name</th>
                <?php foreach($console->getServerStats(reset($servers)) as $key => $item):?>
                    <th class="<?php if(!in_array($key, $visible)) echo 'hide'?>" name="<?php echo $key?>" title="<?php echo $item['description']?>"><?php echo $key?></th>
                <?php endforeach?>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($servers as $server):
                    $stats = $console->getServerStats($server);
            ?>
                <tr>
                    <?php if(empty($stats)):?>
                    <td><?php echo $server?></td>
                    <?php else:?>
                    <td><a href="?server=<?php echo $server?>"><?php echo $server?></a></td>
                    <?php endif?>
                    <?php foreach($stats as $key => $item):?>
                        <td class="<?php if(!in_array($key, $visible)) echo 'hide'?>" name="<?php echo $key?>"><?php echo htmlspecialchars($item['value'])?></td>
                    <?php endforeach?>
                    <?php if(empty($stats)):?>
                        <td colspan="<?php echo count($visible)?>" class="row-full">connection error</td>
                        <?/*php foreach(BeanstalkInterface::getServerStatsFields() as $key => $item):?>
                            <td class="<?php if(!in_array($key, $visible)) echo 'hide'?>" name="<?php echo $key?>">ERROR</td>
                        <?php endforeach*/?>
                    <?php endif?>
                    <td><a class="btn btn-small" title="Remove from list" href="?action=serversRemove&removeServer=<?php echo $server?>"><span class="icon-minus"></span></a></td>
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

<div id="filter" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="servers-add-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filter-label" class="text-info">Filter columns</h3>
    </div>
    <div class="modal-body">
        <form>
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <?php $i=0; foreach($console->getServerStatsGroups() as $groupName => $fields): $i++;?>
                        <li <?php if($i==1) echo 'class="active"'?>><a href="#<?php echo $groupName?>" data-toggle="tab"><?php echo $groupName?></a></li>
                    <?php endforeach?>
                </ul>
                <div class="tab-content">
                    <?php $i=0; foreach($console->getServerStatsGroups() as $groupName => $fields): $i++;?>
                    <div class="tab-pane <?php if($i==1) echo 'active'?>" id="<?php echo $groupName?>">
                        <?php foreach($fields as $key => $description):?>
                            <div class="control-group">
                                <div class="controls">
                                    <label class="checkbox">
                                        <input type="checkbox" name="<?php echo $key?>" <?php if(in_array($key, $visible)) echo 'checked="checked"'?>>
                                        <b><?php echo $key?></b>
                                        <br /><?php echo $description?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach?>
                    </div>
                    <?php endforeach?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>
