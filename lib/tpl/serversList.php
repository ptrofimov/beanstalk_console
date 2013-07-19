
<ul class="breadcrumb lead">
    <li class="active">Home</li>
</ul>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Server</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($console->getServers() as $server):?>
            <tr>
                 <td><a href="?server=<?php echo $server?>"><?php echo $server?></a></td>
            </tr>
        <?php endforeach?>
    </tbody>
</table>
