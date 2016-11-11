<?php
$searchResults = $console->getSearchResult();
include('currentTubeJobsSummaryTable.php');
?>
<section id="actionsRow">
    <a class="btn btn-default btn-sm" href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>"><i class="glyphicon glyphicon-backward"></i>  &nbsp;Back to tube</a>
</section>
<?php
if ($searchResults['total'] > 0) {
    unset($searchResults['total']);
    ?>
    <section id="searchResult">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped table-hover" style="table-layout:fixed;">
                    <thead>
                        <tr>
                            <th class="col-md-1">id</th>
                            <th class="col-md-1">state</th>
                            <th>data</th>
                            <th class="col-md-1">action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $state => $jobList): ?>
                            <?php foreach ($jobList as $job): ?>
                                <tr>
                                    <td><?php echo $job->getId(); ?></td>
                                    <td><?php echo $state; ?></td>
                                    <td class="ellipsize"><?php echo htmlspecialchars($job->getData()); ?></td>
                                    <td>
                                        <div class="dropdown btn-group-xs">
                                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                                                Actions
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                                <li role="presentation"><a role="menuitem" class="addSample" data-jobid="<?php echo $job->getId(); ?>"
                                                                           href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=addSample">
                                                        <i class="glyphicon glyphicon-plus glyphicon-white"></i>
                                                        Add to samples</a>
                                                </li>
                                                <li role="presentation"><a role="menuitem"
                                                                           href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&state=<?php echo $state ?>&action=deleteJob&jobid=<?php echo $job->getId(); ?>"><i
                                                            class="glyphicon glyphicon-remove glyphicon-white"></i>
                                                        Delete</a>
                                                </li>
                                                <li role="presentation"><a role="menuitem"
                                                                           href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&state=<?php echo $state ?>&action=kickJob&jobid=<?php echo $job->getId(); ?>"><i
                                                            class="glyphicon glyphicon-forward glyphicon-white"></i>
                                                        Kick</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
        First <?php echo $_GET['limit']; ?> rows are displayed for each state.
        <br/>
        <br/>
    </section>

    <?php
} else {
    ?>
    <br/>
    No results found for <b><?php echo htmlspecialchars($_GET['searchStr']); ?></b> in tube: <b><?php echo $tube; ?></b>
    <?php
}
