<?php
if (isset($action) && $action == 'search') {
    include_once('currentTubeSearchResults.php');
} else {
    include_once('currentTubeJobs.php');
}
