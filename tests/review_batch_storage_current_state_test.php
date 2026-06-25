<?php

require_once dirname(__FILE__) . '/../src/ReviewBatchStorage.php';

function assertSameValue($expected, $actual, $message) {
    if ($expected !== $actual) {
        throw new Exception($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertTrueValue($value, $message) {
    if (!$value) {
        throw new Exception($message);
    }
}

function removeTree($path) {
    if (!is_dir($path)) {
        return;
    }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $child = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($child)) {
            removeTree($child);
        } else {
            unlink($child);
        }
    }
    rmdir($path);
}

$base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'review-storage-test-' . uniqid('', true);
$storagePath = $base . DIRECTORY_SEPARATOR . 'review-batches';
$batchId = 'test-batch';

try {
    mkdir($base, 0777, true);
    $storage = new ReviewBatchStorage(array(
        'storage' => $base . DIRECTORY_SEPARATOR . 'storage.json',
        'review' => array('storagePath' => $storagePath),
    ));

    $batch = array(
        'id' => $batchId,
        'source_server' => 'test',
        'source_tube' => 'tube',
        'source_state' => 'buried',
        'review_tube' => 'review.tube',
        'created_at' => date('c'),
        'status' => 'complete',
        'target_count' => 2,
        'processed' => 2,
        'errors' => 0,
    );
    $storage->createBatch($batch);

    $jobsFile = $storagePath . DIRECTORY_SEPARATOR . $batchId . '.jobs.jsonl';
    $currentFile = $storagePath . DIRECTORY_SEPARATOR . $batchId . '.current.json';
    assertTrueValue(is_file($currentFile), 'Current-state file should be created with the batch');

    $storage->appendJob($batchId, array('original_id' => 1, 'review_id' => 101, 'status' => 'moved', 'pri' => 1, 'job_created_at' => '2026-06-19T00:00:00+00:00'));
    $storage->appendJob($batchId, array('original_id' => 2, 'review_id' => 102, 'status' => 'moved', 'pri' => 2));
    $storage->updateJob($batchId, array('original_id' => 1, 'review_id' => 101, 'status' => 'returned', 'returned_id' => 201));

    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(2, $summary['total'], 'Current summary total should match collapsed jobs');
    assertSameValue(1, $summary['moved_count'], 'Current summary moved count should track latest statuses');
    assertSameValue(1, $summary['page_selectable_count'], 'Selectable count should use current page moved rows');
    assertSameValue('returned', $summary['jobs'][0]['status'], 'First job should be returned after update');
    assertSameValue('2026-06-19T00:00:00+00:00', $summary['jobs'][0]['job_created_at'], 'Original creation time should survive status updates');
    assertSameValue('moved', $summary['jobs'][1]['status'], 'Second job should remain moved');

    $moved = $storage->getMovedJobs($batchId, 10);
    assertSameValue(1, count($moved), 'Moved-job lookup should return only current moved rows');
    assertSameValue(102, (int)$moved[0]['review_id'], 'Moved-job lookup should return the remaining moved review id');

    $selected = $storage->getJobsByReviewIds($batchId, array(101, 102, 999));
    assertSameValue(2, count($selected), 'Selected lookup should return matching review ids');
    assertSameValue('returned', $selected[101]['status'], 'Selected lookup should include returned row');
    assertSameValue('moved', $selected[102]['status'], 'Selected lookup should include moved row');

    $current = json_decode(file_get_contents($currentFile), true);
    assertSameValue(filesize($jobsFile), (int)$current['audit_size'], 'Current-state audit size should match jobs JSONL size');
    assertSameValue(2, (int)$current['total'], 'Current-state total should be stored');
    assertSameValue(1, (int)$current['moved_count'], 'Current-state moved count should be stored');

    file_put_contents($currentFile, json_encode(array(
        'version' => 1,
        'audit_size' => 0,
        'total' => 0,
        'moved_count' => 0,
        'jobs' => array(),
    )));
    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(2, $summary['total'], 'Stale current-state file should rebuild from audit JSONL');
    $current = json_decode(file_get_contents($currentFile), true);
    assertSameValue(filesize($jobsFile), (int)$current['audit_size'], 'Rebuilt current state should store current audit size');

    file_put_contents($currentFile, '{bad json');
    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(2, $summary['total'], 'Malformed current-state file should rebuild from audit JSONL');

    $current = json_decode(file_get_contents($currentFile), true);
    $current['total'] = 99;
    $current['audit_size'] = filesize($jobsFile);
    file_put_contents($currentFile, json_encode($current));
    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(2, $summary['total'], 'Current-state count mismatch should rebuild from audit JSONL');

    file_put_contents($currentFile, 'not json');
    $storage->appendJob($batchId, array('review_id' => 103, 'status' => 'moved', 'pri' => 3));
    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(3, $summary['total'], 'Append should recover malformed current state before applying the new row');
    assertSameValue(2, $summary['moved_count'], 'Append should preserve rebuilt jobs and count the new moved row');

    $storage->appendJob($batchId, array('status' => 'ignored'));
    $summary = $storage->getBatchSummary($batchId, 0, 10);
    assertSameValue(3, $summary['total'], 'Rows without a job id should be audited but not added to current jobs');
    $current = json_decode(file_get_contents($currentFile), true);
    assertSameValue(filesize($jobsFile), (int)$current['audit_size'], 'Ignored audit rows should still advance current audit size');

    unlink($currentFile);
    $moved = $storage->getMovedJobs($batchId, 10);
    assertSameValue(2, count($moved), 'Missing current-state file should rebuild for moved lookup');
    assertTrueValue(is_file($currentFile), 'Current-state file should be recreated after rebuild');

    $batches = $storage->listBatches();
    assertSameValue(1, count($batches), 'Batch listing should ignore current-state JSON files');

    $storage->deleteBatch($batchId);
    assertTrueValue(!is_file($currentFile), 'Batch deletion should remove current-state file');
    assertTrueValue(!is_file($jobsFile), 'Batch deletion should remove audit file');

    removeTree($base);
    echo "ReviewBatchStorage current-state tests passed.\n";
} catch (Exception $e) {
    removeTree($base);
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
