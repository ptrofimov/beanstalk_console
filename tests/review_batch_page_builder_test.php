<?php

if (!class_exists('Pheanstalk_Response')) {
    class Pheanstalk_Response {
        const RESPONSE_NOT_FOUND = 'NOT_FOUND';
    }
}

require_once dirname(__FILE__) . '/../src/Settings.php';
require_once dirname(__FILE__) . '/../src/TubeBodyDisplaySettings.php';
require_once dirname(__FILE__) . '/../src/JobBodyFormatter.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchPageBuilder.php';

$GLOBALS['config'] = array(
    'settings' => array(
        'enableJsonDecode' => false,
        'enableUnserialization' => false,
        'enableBase64Decode' => false,
    ),
);

function assertPageSame($expected, $actual, $message) {
    if ($expected !== $actual) {
        throw new Exception($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertPageTrue($value, $message) {
    if (!$value) {
        throw new Exception($message);
    }
}

class ReviewBatchPageBuilderFakeJob {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }
}

class ReviewBatchPageBuilderFakeClient {
    public $peekCalls = 0;
    private $responses;

    public function __construct($responses) {
        $this->responses = $responses;
    }

    public function peek($id) {
        $this->peekCalls++;
        if (!array_key_exists($id, $this->responses)) {
            return false;
        }
        if ($this->responses[$id] instanceof Exception) {
            throw $this->responses[$id];
        }
        return new ReviewBatchPageBuilderFakeJob($this->responses[$id]);
    }
}

class ReviewBatchPageBuilderFakeInterface {
    public $_client;

    public function __construct($client) {
        $this->_client = $client;
    }
}

class ReviewBatchPageBuilderFakeStorage {
    public $updates = array();
    public $snapshotCalls = 0;
    private $jobs;
    private $snapshots;

    public function __construct($jobs, $snapshots) {
        $this->jobs = $jobs;
        $this->snapshots = $snapshots;
    }

    public function loadOperation($batchId) {
        return false;
    }

    public function getBatchSummary($batchId, $offset = 0, $limit = null) {
        $jobs = array_values($this->jobs);
        $pageJobs = $limit === null ? array_slice($jobs, $offset) : array_slice($jobs, $offset, $limit);
        $movedCount = 0;
        $pageSelectableCount = 0;
        foreach ($jobs as $job) {
            if (isset($job['status']) && $job['status'] === 'moved') {
                $movedCount++;
            }
        }
        foreach ($pageJobs as $job) {
            if (isset($job['status'], $job['review_id']) && $job['status'] === 'moved' && (int)$job['review_id'] > 0) {
                $pageSelectableCount++;
            }
        }
        return array(
            'jobs' => $pageJobs,
            'total' => count($jobs),
            'moved_count' => $movedCount,
            'page_selectable_count' => $pageSelectableCount,
        );
    }

    public function getBodySnapshots($batchId, $reviewIds = null) {
        $this->snapshotCalls++;
        if ($reviewIds === null) {
            return $this->snapshots;
        }
        $result = array();
        foreach ($reviewIds as $reviewId) {
            if (isset($this->snapshots[$reviewId])) {
                $result[$reviewId] = $this->snapshots[$reviewId];
            }
        }
        return $result;
    }

    public function updateJob($batchId, $row) {
        $this->updates[] = $row;
        foreach ($this->jobs as $index => $job) {
            if (isset($job['review_id'], $row['review_id']) && (int)$job['review_id'] === (int)$row['review_id']) {
                $this->jobs[$index] = array_merge($job, $row);
                return;
            }
        }
        $this->jobs[] = $row;
    }
}

function makeReviewPageBuilder($jobs, $snapshots, $responses, &$storage, &$client) {
    $storage = new ReviewBatchPageBuilderFakeStorage($jobs, $snapshots);
    $client = new ReviewBatchPageBuilderFakeClient($responses);
    return new ReviewBatchPageBuilder(new ReviewBatchPageBuilderFakeInterface($client), $storage);
}

$batch = array('id' => 'batch-1', 'include_body_snapshot' => true);

try {
    $builder = makeReviewPageBuilder(
        array(array('original_id' => 1, 'review_id' => 101, 'status' => 'moved')),
        array(101 => array('body' => 'snapshot body abc')),
        array(101 => 'live body should not load'),
        $storage,
        $client
    );
    $page = $builder->buildShowPage($batch, 1, 25, 8);
    assertPageSame('snapshot', $page['reviewJobBodies'][101]['body_source'], 'Snapshot body should be preferred');
    assertPageSame('snapshot...', $page['reviewJobPreviews'][101], 'Snapshot preview should be truncated');
    assertPageSame(0, $client->peekCalls, 'Snapshot body should not peek the live review job');

    $builder = makeReviewPageBuilder(
        array(array('original_id' => 5, 'review_id' => 105, 'status' => 'moved')),
        array(105 => array('body' => 'disabled snapshot should not load')),
        array(105 => 'live body when snapshots disabled'),
        $storage,
        $client
    );
    $page = $builder->buildShowPage(array('id' => 'batch-1', 'include_body_snapshot' => false), 1, 25, 50);
    assertPageSame('live', $page['reviewJobBodies'][105]['body_source'], 'Disabled snapshots should use the live review job');
    assertPageSame(0, $storage->snapshotCalls, 'Disabled snapshots should not read body snapshot storage');
    assertPageSame(1, $client->peekCalls, 'Disabled snapshots should peek the live review job');

    $builder = makeReviewPageBuilder(
        array(array('original_id' => 2, 'review_id' => 102, 'status' => 'moved')),
        array(),
        array(102 => 'live body abc'),
        $storage,
        $client
    );
    $page = $builder->buildShowPage(array('id' => 'batch-1'), 1, 25, 50);
    assertPageSame('live', $page['reviewJobBodies'][102]['body_source'], 'Live body should be used when no snapshot exists');
    assertPageSame('live body abc', $page['reviewJobBodies'][102]['body'], 'Live body should be rendered in full');
    assertPageSame(1, $client->peekCalls, 'Live fallback should peek once');

    $builder = makeReviewPageBuilder(
        array(array('original_id' => 3, 'review_id' => 103, 'status' => 'moved')),
        array(),
        array(103 => new Exception('temporary failure')),
        $storage,
        $client
    );
    $page = $builder->buildShowPage(array('id' => 'batch-1'), 1, 25, 100);
    assertPageSame('error', $page['reviewJobBodies'][103]['body_source'], 'Generic body load errors should render an error body');
    assertPageTrue(strpos($page['reviewJobBodies'][103]['body'], 'Unable to load review body: temporary failure') === 0, 'Generic error message should be visible');

    $builder = makeReviewPageBuilder(
        array(array('original_id' => 4, 'review_id' => 104, 'status' => 'moved')),
        array(),
        array(104 => new Exception(Pheanstalk_Response::RESPONSE_NOT_FOUND)),
        $storage,
        $client
    );
    $page = $builder->buildShowPage(array('id' => 'batch-1'), 1, 25, 100);
    assertPageSame('missing_review_job', $page['reviewJobs'][0]['status'], 'Missing review jobs should be reconciled into page rows');
    assertPageSame(1, count($storage->updates), 'Missing review jobs should append one manifest update');
    assertPageSame(0, $page['reviewRemainingMovedCount'], 'Missing review jobs should refresh moved counts');
    assertPageSame(0, $page['reviewPageSelectableCount'], 'Missing review jobs should refresh selectable counts');

    assertPageTrue($builder->reviewJobHasInspectableCopy(array('status' => 'return_delete_error', 'review_id' => 105)), 'Cleanup rows should be inspectable');
    assertPageTrue($builder->reviewJobHasInspectableCopy(array('status' => 'duplicated', 'review_id' => 107)), 'Duplicated rows should keep their review copy inspectable');
    assertPageTrue(!$builder->reviewJobHasInspectableCopy(array('status' => 'returned', 'review_id' => 106)), 'Returned rows should not be inspectable');
    assertPageSame('abc', $builder->truncateReviewBody('abc', 3), 'Exact-length previews should not be truncated');
    assertPageSame('abc...', $builder->truncateReviewBody('abcdef', 3), 'Long previews should be truncated');
    assertPageSame('minutes: 1<br>seconds: 5', $builder->formatDuration(65), 'Duration helper should match existing display format');

    echo "ReviewBatchPageBuilder tests passed.\n";
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
