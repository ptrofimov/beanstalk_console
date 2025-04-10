<?php

$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
$sampleJobs = $console->getSampleJobs($tube);
$allStats = $console->getTubeStatValues($tube);

$tubePauseSeconds = $settings->getTubePauseSeconds();
if ($tubePauseSeconds === -1) {
    $tubePauseSeconds = 3600;
}

include('currentTubeJobsSummaryTable.php');
include('currentTubeJobsActionsRow.php');
include('currentTubeJobsShowcase.php');
