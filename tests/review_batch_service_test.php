<?php

require_once dirname(__FILE__) . '/../lib/Pheanstalk.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchService.php';

if (!class_exists('Pheanstalk_Response')) {
    class Pheanstalk_Response {
        const RESPONSE_NOT_FOUND = 'NOT_FOUND';
    }
}

class ReviewBatchServiceFakeJob {
    private $id;
    private $data;

    public function __construct($id, $data) {
        $this->id = $id;
        $this->data = $data;
    }

    public function getId() {
        return $this->id;
    }

    public function getData() {
        return $this->data;
    }
}

class ReviewBatchServiceFakeClient {
    public $jobs = array();
    public $readyJobs = array();
    public $delayedJobs = array();
    public $buriedJobs = array();
    public $stats = array();
    public $deleted = array();
    public $deleteExceptions = array();
    public $peekExceptions = array();
    public $events = array();
    public $usedTube = null;

    public function useTube($tube) {
        $this->usedTube = $tube;
        return $this;
    }

    public function peekReady() {
        return $this->shiftStateJob($this->readyJobs);
    }

    public function peekDelayed() {
        return $this->shiftStateJob($this->delayedJobs);
    }

    public function peekBuried() {
        return $this->shiftStateJob($this->buriedJobs);
    }

    public function peek($id) {
        $id = (int)$id;
        $this->events[] = 'peek:' . $id;
        if (isset($this->peekExceptions[$id])) {
            throw $this->peekExceptions[$id];
        }
        if (!isset($this->jobs[$id])) {
            throw new Exception(Pheanstalk_Response::RESPONSE_NOT_FOUND);
        }
        return $this->jobs[$id];
    }

    public function statsJob($job) {
        $id = (int)$job->getId();
        $this->events[] = 'stats:' . $id;
        return isset($this->stats[$id]) ? $this->stats[$id] : array();
    }

    public function delete($job) {
        $id = (int)$job->getId();
        $this->events[] = 'delete:' . $id;
        if (isset($this->deleteExceptions[$id])) {
            throw $this->deleteExceptions[$id];
        }
        $this->deleted[] = $id;
    }

    private function shiftStateJob(&$jobs) {
        if (!count($jobs)) {
            throw new Exception(Pheanstalk_Response::RESPONSE_NOT_FOUND);
        }
        $job = array_shift($jobs);
        $this->events[] = 'peek-state:' . $job->getId();
        return $job;
    }
}

class ReviewBatchServiceFakeInterface {
    public $_client;
    public $added = array();
    public $addExceptions = array();
    private $nextId = 900;

    public function __construct($client) {
        $this->_client = $client;
    }

    public function addJob($tubeName, $tubeData, $tubePriority = Pheanstalk::DEFAULT_PRIORITY, $tubeDelay = Pheanstalk::DEFAULT_DELAY, $tubeTtr = Pheanstalk::DEFAULT_TTR) {
        $this->_client->events[] = 'add:' . $tubeName;
        if (count($this->addExceptions)) {
            throw array_shift($this->addExceptions);
        }
        $this->nextId++;
        $this->added[] = array($tubeName, $tubeData, $tubePriority, $tubeDelay, $tubeTtr, $this->nextId);
        return $this->nextId;
    }
}

class ReviewBatchServiceFakeStorage {
    public $updates = array();
    public $appended = array();
    public $bodySnapshots = array();
    public $savedBatches = array();

    public function updateJob($batchId, $row) {
        $this->updates[] = array($batchId, $row);
    }

    public function appendJob($batchId, $row) {
        $this->appended[] = array($batchId, $row);
    }

    public function appendBodySnapshotRows($batchId, $rows) {
        $this->bodySnapshots[] = array($batchId, $rows);
    }

    public function saveBatch($batch) {
        $this->savedBatches[] = $batch;
    }
}

function assertServiceSame($expected, $actual, $message) {
    if ($expected !== $actual) {
        throw new Exception($message . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertServiceTrue($value, $message) {
    if (!$value) {
        throw new Exception($message);
    }
}

function makeReviewBatchService(&$client, &$storage, &$interface) {
    $client = new ReviewBatchServiceFakeClient();
    $storage = new ReviewBatchServiceFakeStorage();
    $interface = new ReviewBatchServiceFakeInterface($client);
    return new ReviewBatchService($interface, $storage);
}

try {
    $service = makeReviewBatchService($client, $storage, $interface);
    $client->jobs[101] = new ReviewBatchServiceFakeJob(101, 'payload');
    $batch = array('id' => 'batch-1', 'source_tube' => 'source');
    $manifestJob = array('original_id' => 1, 'review_id' => 101, 'pri' => 5, 'ttr' => 60);

    $result = $service->operateReviewJob($batch, $manifestJob, array('operation' => 'move_all_moved', 'target_tube' => 'source', 'delay' => 7));
    assertServiceSame(true, $result, 'Moving to source should succeed');
    assertServiceSame('returned', $storage->updates[0][1]['status'], 'Moving to source should be recorded as returned');
    assertServiceSame('source', $interface->added[0][0], 'Moving to source should add to source tube');
    assertServiceSame(5, $interface->added[0][2], 'Move should preserve priority');
    assertServiceSame(7, $interface->added[0][3], 'Move should use requested delay');
    assertServiceSame(60, $interface->added[0][4], 'Move should preserve TTR');
    assertServiceSame(array(101), $client->deleted, 'Move should delete the review copy');

    $client->jobs[102] = new ReviewBatchServiceFakeJob(102, 'payload2');
    $manifestJob = array('original_id' => 2, 'review_id' => 102, 'pri' => 6, 'ttr' => 120);
    $service->operateReviewJob($batch, $manifestJob, array('operation' => 'move_all_moved', 'target_tube' => 'other', 'delay' => 0));
    assertServiceSame('moved_to_tube', $storage->updates[1][1]['status'], 'Moving to another tube should be recorded as moved_to_tube');
    assertServiceSame('other', $storage->updates[1][1]['target_tube'], 'Moved rows should record target tube');

    $client->jobs[103] = new ReviewBatchServiceFakeJob(103, 'payload3');
    $manifestJob = array('original_id' => 3, 'review_id' => 103, 'pri' => 7, 'ttr' => 180);
    $service->operateReviewJob($batch, $manifestJob, array('operation' => 'duplicate_all_moved', 'target_tube' => 'copy', 'delay' => 0));
    assertServiceSame('duplicated', $storage->updates[2][1]['status'], 'Duplicate should be recorded as duplicated');
    assertServiceSame(array(101, 102), $client->deleted, 'Duplicate should leave the review copy in place');

    $client->jobs[104] = new ReviewBatchServiceFakeJob(104, 'payload4');
    $manifestJob = array('original_id' => 4, 'review_id' => 104);
    $service->operateReviewJob($batch, $manifestJob, array('operation' => 'delete_all_moved'));
    assertServiceSame('deleted', $storage->updates[3][1]['status'], 'Delete should be recorded as deleted');
    assertServiceSame(array(101, 102, 104), $client->deleted, 'Delete should remove the review copy');

    $manifestJob = array('original_id' => 5, 'review_id' => 105);
    $service->operateReviewJob($batch, $manifestJob, array('operation' => 'move_all_moved', 'target_tube' => 'source'));
    assertServiceSame('missing_review_job', $storage->updates[4][1]['status'], 'Missing review copy should block moves');

    $manifestJob = array('original_id' => 6, 'review_id' => 106);
    $service->operateReviewJob($batch, $manifestJob, array('operation' => 'delete_all_moved'));
    assertServiceSame('deleted', $storage->updates[5][1]['status'], 'Missing review copy should count as deleted for delete operations');

    $client->jobs[107] = new ReviewBatchServiceFakeJob(107, 'payload7');
    $client->deleteExceptions[107] = new Exception('delete failed');
    $manifestJob = array('original_id' => 7, 'review_id' => 107, 'pri' => 1, 'ttr' => 30);
    $result = $service->operateReviewJob($batch, $manifestJob, array('operation' => 'move_all_moved', 'target_tube' => 'elsewhere', 'delay' => 3));
    assertServiceSame(false, $result, 'Move should report failure when review cleanup fails');
    assertServiceSame('move_delete_error', $storage->updates[6][1]['status'], 'Move cleanup failure should keep review copy visible');
    assertServiceSame('elsewhere', $storage->updates[6][1]['target_tube'], 'Move cleanup failure should record destination tube');
    assertServiceTrue(isset($storage->updates[6][1]['target_id']), 'Move cleanup failure should record created destination id');

    $service = makeReviewBatchService($client, $storage, $interface);
    $sourceJob = new ReviewBatchServiceFakeJob(201, 'source payload');
    $client->readyJobs[] = $sourceJob;
    $client->stats[201] = array('pri' => 9, 'ttr' => 45, 'age' => 12);
    $batch = array(
        'id' => 'prep-1',
        'source_tube' => 'source',
        'source_state' => 'ready',
        'review_tube' => 'source.REVIEW.1',
        'target_count' => 1,
        'processed' => 0,
        'errors' => 0,
        'status' => 'processing',
        'include_body_snapshot' => 1,
    );
    $result = $service->processPreparationChunk($batch, 5);
    assertServiceSame('complete', $result['status'], 'Preparation should complete after target count is reached');
    assertServiceSame(1, $result['processed'], 'Preparation should increment processed count');
    assertServiceSame('source.REVIEW.1', $interface->added[0][0], 'Preparation should add to review tube');
    assertServiceSame(array('peek-state:201', 'stats:201', 'add:source.REVIEW.1', 'delete:201'), $client->events, 'Preparation should copy before deleting the source job');
    assertServiceSame('moved', $storage->appended[0][1]['status'], 'Preparation should append moved audit row');
    assertServiceSame('snapshot', $storage->bodySnapshots[0][1][0]['status'], 'Preparation should append body snapshot row when enabled');
    assertServiceSame(base64_encode('source payload'), $storage->bodySnapshots[0][1][0]['body_base64'], 'Body snapshot should store base64 body');

    $service = makeReviewBatchService($client, $storage, $interface);
    $sourceJob = new ReviewBatchServiceFakeJob(202, 'delete fails payload');
    $client->buriedJobs[] = $sourceJob;
    $client->stats[202] = array('pri' => 10, 'ttr' => 60);
    $client->deleteExceptions[202] = new Exception('source delete failed');
    $batch = array(
        'id' => 'prep-2',
        'source_tube' => 'source',
        'source_state' => 'buried',
        'review_tube' => 'source.REVIEW.2',
        'target_count' => 2,
        'processed' => 0,
        'errors' => 0,
        'status' => 'processing',
        'include_body_snapshot' => 1,
    );
    $result = $service->processPreparationChunk($batch, 5);
    assertServiceSame('error', $result['status'], 'Preparation should stop on source delete failure');
    assertServiceSame(1, $result['errors'], 'Preparation should increment error count');
    assertServiceSame(0, $result['processed'], 'Failed source cleanup should not count as processed');
    assertServiceSame('copy_delete_error', $storage->appended[0][1]['status'], 'Preparation should record copy_delete_error when review copy exists');
    assertServiceSame('source delete failed', $storage->appended[0][1]['error_message'], 'Preparation should record delete failure message');
    assertServiceSame(array('peek-state:202', 'stats:202', 'add:source.REVIEW.2', 'delete:202'), $client->events, 'Failed preparation should still copy before delete attempt');

    echo "ReviewBatchService tests passed.\n";
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
