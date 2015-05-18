<?php

$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
$sampleJobs = $console->getSampleJobs($tube);

if (!@empty($_COOKIE['tubePauseSeconds'])) {
    $tubePauseSeconds = intval($_COOKIE['tubePauseSeconds']);
} else {
    $tubePauseSeconds = 3600;
}

include('currentTubeJobsSummaryTable.php');
include('currentTubeJobsActionsRow.php');
include('currentTubeJobsShowcase.php');
