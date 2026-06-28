<?php

/**
 * @link https://github.com/ptrofimov/beanstalk_console
 * @link http://kr.github.com/beanstalkd/
 * @author Petr Trofimov, Sergey Lysenko
 */
function autoload_class($class) {
    $file = str_replace('_', '/', $class) . '.php';
    if (stream_resolve_include_path($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoload_class');

session_name('beanstalkconsole');
session_start();
require_once 'Pheanstalk/ClassLoader.php';
Pheanstalk_ClassLoader::register(dirname(__FILE__));

require_once 'BeanstalkInterface.class.php';
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../src/Storage.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchNaming.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchStorage.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchService.php';
require_once dirname(__FILE__) . '/../src/Settings.php';
require_once dirname(__FILE__) . '/../src/ReviewBatchPageBuilder.php';

$GLOBALS['server'] = !empty($_GET['server']) ? filter_input(INPUT_GET, 'server', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['action'] = !empty($_GET['action']) ? filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['state'] = !empty($_GET['state']) ? filter_input(INPUT_GET, 'state', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['count'] = !empty($_GET['count']) ? filter_input(INPUT_GET, 'count', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['tube'] = !empty($_GET['tube']) ? filter_input(INPUT_GET, 'tube', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['tplMain'] = !empty($_GET['tplMain']) ? filter_input(INPUT_GET, 'tplMain', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$GLOBALS['tplBlock'] = !empty($_GET['tplBlock']) ? filter_input(INPUT_GET, 'tplBlock', FILTER_SANITIZE_SPECIAL_CHARS) : '';

class Console {

    /**
     * @var BeanstalkInterface
     */
    public $interface;
    protected $_tplVars = array();
    protected $_globalVar = array();
    protected $_errors = array();
    private $serversConfig = array();
    private $serversEnv = array();
    private $serversCookie = array();
    private $searchResults = array();
    private $reviewBatchPageBuilder = null;
    private $actionTimeStart = 0;

    public function __construct() {
        $this->__init();
        $this->_main();
    }

    /** @return array */
    public function getServers() {
        return array_merge($this->serversConfig, $this->serversEnv, $this->serversCookie);
    }

    /** @return array */
    public function getServersConfig() {
        return $this->serversConfig;
    }

    /** @return array */
    public function getServersEnv() {
        return $this->serversEnv;
    }

    /** @return array */
    public function getServersCookie() {
        return $this->serversCookie;
    }

    public function getServerStats($server) {
        if (empty($server) || !is_string($server)) {
            return array();
        }
        try {
            $interface = new BeanstalkInterface($server);
            $stats = $interface->getServerStats();
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $stats = array();
        }

        return $stats;
    }

    public function getServerStatsGroups() {
        return array(
            'binlog' => array(
                'binlog-current-index' => 'the index of the current binlog file being written to. If binlog is not active this value will be 0',
                'binlog-max-size' => 'the maximum size in bytes a binlog file is allowed to get before a new binlog file is opened',
                'binlog-oldest-index' => 'the index of the oldest binlog file needed to store the current jobs',
                'binlog-records-migrated' => 'the cumulative number of records written as part of compaction',
                'binlog-records-written' => 'the cumulative number of records written to the binlog',
            ),
            'cmd' => array(
                'cmd-bury' => 'the cumulative number of bury commands',
                'cmd-delete' => 'the cumulative number of delete commands',
                'cmd-ignore' => 'the cumulative number of ignore commands',
                'cmd-kick' => 'the cumulative number of kick commands',
                'cmd-list-tube-used' => 'the cumulative number of list-tube-used commands',
                'cmd-list-tubes' => 'the cumulative number of list-tubes commands',
                'cmd-list-tubes-watched' => 'the cumulative number of list-tubes-watched commands',
                'cmd-pause-tube' => 'the cumulative number of pause-tube commands',
                'cmd-peek' => 'the cumulative number of peek commands',
                'cmd-peek-buried' => 'the cumulative number of peek-buried commands',
                'cmd-peek-delayed' => 'the cumulative number of peek-delayed commands',
                'cmd-peek-ready' => 'the cumulative number of peek-ready commands',
                'cmd-put' => 'the cumulative number of put commands',
                'cmd-release' => 'the cumulative number of release commands',
                'cmd-reserve' => 'the cumulative number of reserve commands',
                'cmd-stats' => 'the cumulative number of stats commands',
                'cmd-stats-job' => 'the cumulative number of stats-job commands',
                'cmd-stats-tube' => 'the cumulative number of stats-tube commands',
                'cmd-use' => 'the cumulative number of use commands',
                'cmd-watch' => 'the cumulative number of watch commands',
            ),
            'current' => array(
                'current-connections' => 'the number of currently open connections',
                'current-jobs-buried' => 'the number of buried jobs',
                'current-jobs-delayed' => 'the number of delayed jobs',
                'current-jobs-ready' => 'the number of jobs in the ready queue',
                'current-jobs-reserved' => 'the number of jobs reserved by all clients',
                'current-jobs-urgent' => 'the number of ready jobs with priority < 1024',
                'current-producers' => 'the number of open connections that have each issued at least one put command',
                'current-tubes' => 'the number of currently-existing tubes',
                'current-waiting' => 'the number of open connections that have issued a reserve command but not yet received a response',
                'current-workers' => 'the number of open connections that have each issued at least one reserve command',
            ),
            'other' => array(
                'hostname' => 'the hostname of the machine as determined by uname',
                'id' => 'a random id string for this server process, generated when each beanstalkd process starts',
                'job-timeouts' => 'the cumulative count of times a job has timed out',
                'max-job-size' => 'the maximum number of bytes in a job',
                'pid' => 'the process id of the server',
                'rusage-stime' => 'the cumulative system CPU time of this process in seconds and microseconds',
                'rusage-utime' => 'the cumulative user CPU time of this process in seconds and microseconds',
                'total-connections' => 'the cumulative count of connections',
                'total-jobs' => 'the cumulative count of jobs created',
                'uptime' => 'the number of seconds since this server process started running',
                'version' => 'the version string of the server',
            ),
        );
    }

    public function getTubeStatFields() {
        return array(
            'current-jobs-urgent' => 'number of ready jobs with priority < 1024 in this tube',
            'current-jobs-ready' => 'number of jobs in the ready queue in this tube',
            'current-jobs-reserved' => 'number of jobs reserved by all clients in this tube',
            'current-jobs-delayed' => 'number of delayed jobs in this tube',
            'current-jobs-buried' => 'number of buried jobs in this tube',
            'total-jobs' => 'cumulative count of jobs created in this tube in the current beanstalkd process',
            'current-using' => 'number of open connections that are currently using this tube',
            'current-waiting' => 'number of open connections that have issued a reserve command while watching this tube but not yet received a response',
            'current-watching' => 'number of open connections that are currently watching this tube',
            'pause' => 'number of seconds the tube has been paused for',
            'cmd-delete' => 'cumulative number of delete commands for this tube',
            'cmd-pause-tube' => 'cumulative number of pause-tube commands for this tube',
            'pause-time-left' => 'number of seconds until the tube is un-paused',
        );
    }

    public function getTubeStatGroups() {
        return array(
            'current' => array(
                'current-jobs-buried',
                'current-jobs-delayed',
                'current-jobs-ready',
                'current-jobs-reserved',
                'current-jobs-urgent',
                'current-using',
                'current-waiting',
                'current-watching',
            ),
            'other' => array(
                'cmd-delete',
                'cmd-pause-tube',
                'pause',
                'pause-time-left',
                'total-jobs',
            ),
        );
    }

    public function getTubeStatVisible() {
        if (!empty($_COOKIE['tubefilter'])) {
            return explode(',', $_COOKIE['tubefilter']);
        } else {
            return array(
                'current-jobs-buried',
                'current-jobs-delayed',
                'current-jobs-ready',
                'current-jobs-reserved',
                'current-jobs-urgent',
                'total-jobs',
            );
        }
    }

    /**
     * Returns stats-tube values, treating a vanished tube as empty.
     *
     * @param string $tube Tube name.
     * @return array
     * @throws Pheanstalk_Exception_ServerException If stats-tube fails for a reason other than NOT_FOUND.
     */
    public function getTubeStatValues($tube) {
        // make sure, that rapid tube disappearance (eg: anonymous tubes, don't kill the interface, as they might be missing)
        try {
            return $this->interface->_client->statsTube($tube);
        } catch (Pheanstalk_Exception_ServerException $ex) {
            if (strpos($ex->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) !== false) {
                return array();
            } else {
                throw $ex;
            }
        }
    }

    public function getSearchResult() {
        return $this->searchResults;
    }

    /**
     * Returns whether review batch functionality is enabled by configuration.
     *
     * @return bool
     */
    public function isReviewEnabled() {
        if (!isset($this->_globalVar['config']['review']['enabled'])) {
            return false;
        }
        return (bool)$this->_globalVar['config']['review']['enabled'];
    }

    protected function __init() {
        $this->_globalVar = array(
            'server' => $GLOBALS['server'],
            'action' => $GLOBALS['action'],
            'state' => $GLOBALS['state'],
            'count' => $GLOBALS['count'],
            'tube' => $GLOBALS['tube'],
            '_tplMain' => $GLOBALS['tplMain'],
            '_tplBlock' => $GLOBALS['tplBlock'],
            'config' => $GLOBALS['config']);
        $this->_tplVars = $this->_globalVar;
        if (!in_array($this->_tplVars['_tplBlock'], array('allTubes', 'serversList'))) {
            unset($this->_tplVars['_tplBlock']);
        }
        if (!in_array($this->_tplVars['_tplMain'], array('main', 'ajax'))) {
            unset($this->_tplVars['_tplMain']);
        }
        if (empty($this->_tplVars['_tplMain'])) {
            $this->_tplVars['_tplMain'] = 'main';
        }

        foreach ($GLOBALS['config']['servers'] as $key => $server) {
            $this->serversConfig[$key] = $server;
        }
        if (false !== getenv('BEANSTALK_SERVERS')) {
            foreach (explode(',', getenv('BEANSTALK_SERVERS')) as $key => $server) {
                $this->serversEnv[$key] = $server;
            }
        }
        if (isset($_COOKIE['beansServers'])) {
            foreach (explode(';', $_COOKIE['beansServers']) as $key => $server) {
                $this->serversCookie[$key] = $server;
            }
        }
        try {
            $storage = new Storage($GLOBALS['config']['storage']);
        } catch (Exception $ex) {
            $this->_errors[] = $ex->getMessage();
        }
        if ($this->isReviewEnabled()) {
            try {
                $reviewStorage = new ReviewBatchStorage($GLOBALS['config']);
            } catch (Exception $ex) {
                $this->_errors[] = $ex->getMessage();
            }
        }
        if (isset($_SESSION['error'])) {
            $this->_errors[] = $_SESSION['error'];
            unset($_SESSION['error']);
        }
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function getTplVars($var = null) {
        if (!empty($var)) {
            $result = !empty($this->_tplVars[$var]) ? $this->_tplVars[$var] : null;
        } else {
            $result = $this->_tplVars;
        }

        return $result;
    }

    protected function deleteAllFromTube($state, $tube) {
        try {
            $this->interface->_client->useTube($tube);
            do {
                switch ($state) {
                    case 'ready':
                        $job = $this->interface->_client->peekReady();
                        break;
                    case 'delayed':
                        try {
                            $ready = $this->interface->_client->peekReady();
                            if ($ready) {
                                $this->_errors[] = 'Cannot delete Delayed until there are Ready messages on this tube';
                                return;
                            }
                        } catch (Exception $e) {
                            // there might be no jobs to peek at, and peekReady raises exception in this situation
                            if (strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) === false) {
                                throw $e;
                            }
                        }
                        try {
                            $bury = $this->interface->_client->peekBuried();
                            if ($bury) {
                                $this->_errors[] = 'Cannot delete Delayed until there are Bury messages on this tube';
                                return;
                            }
                        } catch (Exception $e) {
                            // there might be no jobs to peek at, and peekReady raises exception in this situation
                            if (strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) === false) {
                                throw $e;
                            }
                        }
                        $job = $this->interface->_client->peekDelayed();
                        if ($job) {
                            //when we found job with Delayed, kick all messages, to be ready, so that we can Delete them.
                            $this->interface->kick($tube, 100000000);
                            $this->deleteAllFromTube('ready', $tube);
                            return;
                        }
                        break;
                    case 'buried':
                        $job = $this->interface->_client->peekBuried();
                        break;
                }

                if ($job) {
                    $this->interface->_client->delete($job);
                    set_time_limit(5);
                }
            } while (!empty($job));
        } catch (Exception $e) {
            // there might be no jobs to peek at, and peekReady raises exception in this situation
            // skip not found exception
            if (strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) === false) {
                $this->_errors[] = $e->getMessage();
            }
        }
    }

    protected function _main() {


        if (!isset($_GET['server'])) {
            // execute methods without a server
            // Refactor target: this dispatch block can eventually share dispatchAction().
            if (isset($_GET['action']) && in_array($_GET['action'], array('serversRemove', 'manageSamples', 'deleteSample', 'editSample', 'newSample'))) {
                $funcName = "_action" . ucfirst($this->_globalVar['action']);
                if (method_exists($this, $funcName)) {
                    $this->$funcName();
                }
                return;
            }
            return;
        }

        try {
            $this->interface = new BeanstalkInterface($this->_globalVar['server']);

            if (!empty($_GET['action']) && $this->shouldDispatchBeforeTubeStats($this->_globalVar['action'])) {
                if ($this->isReviewJsonAction($this->_globalVar['action'])) {
                    $this->dispatchJsonAction($this->_globalVar['action']);
                    return;
                }
                if ($this->dispatchAction($this->_globalVar['action'])) {
                    return;
                }
            }

            $this->_tplVars['tubes'] = $this->interface->getTubes();

            $stats = $this->interface->getTubesStats();

            $this->_tplVars['tubesStats'] = $stats;
            // Only the default tube page renders the peek showcase; actions load their own data.
            $this->_tplVars['peek'] = array();
            if (empty($_GET['action']) && !empty($this->_globalVar['tube'])) {
                $this->_tplVars['peek'] = $this->interface->peekAll($this->_globalVar['tube']);
            }
            $this->_tplVars['contentType'] = $this->interface->getContentType();
            // Refactor target: this dispatch block can eventually share dispatchAction().
            if (!empty($_GET['action'])) {
                $funcName = "_action" . ucfirst($this->_globalVar['action']);
                if (method_exists($this, $funcName)) {
                    $this->$funcName();
                }
                return;
            }
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $this->_errors[] = 'The server is unavailable';
        } catch (Pheanstalk_Exception_ServerException $e) {
            // if we get response not found, we just skip it (as the peekAll reached a tube which no longer existed)
            if (strpos($e->getMessage(), Pheanstalk_Response::RESPONSE_NOT_FOUND) === false) {
                $this->_errors[] = $e->getMessage();
            }
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
    }

    /**
     * Dispatches a normal page action when the target method exists.
     *
     * @param string $action Request action name.
     * @return bool True when an action was handled.
     * @throws Exception Propagates exceptions thrown by the dispatched action.
     */
    private function dispatchAction($action) {
        $funcName = "_action" . ucfirst($action);
        if (!method_exists($this, $funcName)) {
            return false;
        }
        if (strpos($action, 'review') === 0 && !$this->isReviewEnabled()) {
            $this->_errors[] = 'Review batches are disabled by config.';
            return true;
        }
        $this->$funcName();
        return true;
    }

    /**
     * Dispatches a JSON action and converts uncaught exceptions to HTTP 500 JSON.
     *
     * @param string $action Request action name.
     * @return void
     */
    private function dispatchJsonAction($action) {
        try {
            $funcName = "_action" . ucfirst($action);
            if (!method_exists($this, $funcName)) {
                $this->jsonResponse(array('result' => false, 'error' => 'Unsupported action'), 404);
            }
            if (strpos($action, 'review') === 0 && !$this->isReviewEnabled()) {
                $this->jsonResponse(array('result' => false, 'error' => 'Review batches are disabled by config.'), 403);
            }
            $this->$funcName();
        } catch (Exception $e) {
            $this->jsonResponse(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    /**
     * Returns whether an action should run before normal tube stats are loaded.
     *
     * @param string $action Request action name.
     * @return bool
     */
    private function shouldDispatchBeforeTubeStats($action) {
        return in_array($action, array(
            'reviewBatchStart',
            'reviewBatchAddJobs',
            'reviewBatchProcess',
            'reviewBatchReturnJobs',
            'reviewBatchDeleteJobs',
            'reviewBatchMoveJobs',
            'reviewBatchDuplicateJobs',
            'reviewBatchDownloadManifest',
            'reviewBatchDelete',
            'reviewBatchDeleteAll',
            'reviewBatchTakeOver',
            'reviewBatchOperationStart',
            'reviewBatchOperationProcess',
        ), true);
    }

    /**
     * Returns whether a review action should be dispatched through JSON error handling.
     *
     * @param string $action Request action name.
     * @return bool
     */
    private function isReviewJsonAction($action) {
        return in_array($action, array(
            'reviewBatchProcess',
            'reviewBatchOperationProcess',
        ), true);
    }

    protected function _actionKick() {
        $this->interface->kick($this->_globalVar['tube'], $this->_globalVar['count']);
        header(
                sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'])));
        exit();
    }

    protected function _actionKickJob() {
        $job = $this->interface->_client->peek(intval($_GET['jobid']));
        if ($job) {
            $this->interface->_client->kickJob($job);
        }
        header(
                sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'])));
        exit();
    }

    protected function _actionDelete() {
        switch ($this->_globalVar['state']) {
            case 'ready':
                $this->interface->deleteReady($this->_globalVar['tube']);
                break;
            case 'delayed':
                $this->interface->deleteDelayed($this->_globalVar['tube']);
                break;
            case 'buried':
                $this->interface->deleteBuried($this->_globalVar['tube']);
                break;
        }

        $this->_postDelete();
    }

    protected function _actionDeleteJob() {
        $job = $this->interface->_client->peek(intval($_GET['jobid']));
        if ($job) {
            $this->interface->_client->delete($job);
        }
        $this->_postDelete();
    }

    protected function _postDelete() {
        $arr = $this->getTubeStatValues($this->_globalVar['tube']);
        $availableJobs = $arr['current-jobs-urgent'] + $arr['current-jobs-ready'] + $arr['current-jobs-reserved'] + $arr['current-jobs-delayed'] + $arr['current-jobs-buried'];
        if (empty($availableJobs)) {
            // make sure we redirect to all tubes, as this tube no longer exists
            $this->_globalVar['tube'] = null;
        }
        header(
                sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'] ?? '')));
        exit();
    }

    protected function _actionDeleteAll($tube = null) {
        if (empty($tube)) {
            $tube = $this->_globalVar['tube'];
        }
        $this->deleteAllFromTube($this->_globalVar['state'], $tube);
        if (empty($this->_errors)) {
            $this->_postDelete();
        }
    }

    protected function _actionServersRemove() {
        $server = $_GET['removeServer'];
        $cookie_servers = array_diff($this->getServersCookie(), array($server));
        if (count($cookie_servers)) {
            setcookie('beansServers', implode(';', $cookie_servers), time() + 86400 * 365);
        } else {
            // no servers, clear cookie
            setcookie('beansServers', '', time() - 86400 * 365);
        }
        header('Location: ./?');
        exit();
    }

    protected function _actionAddjob() {
        $result = array('result' => false);

        $tubeName = !empty($_POST['tubeName']) ? $_POST['tubeName'] : '';
        $tubeData = !empty($_POST['tubeData']) ? stripcslashes($_POST['tubeData']) : '';
        $tubePriority = !empty($_POST['tubePriority']) ? $_POST['tubePriority'] : '';
        $tubeDelay = !empty($_POST['tubeDelay']) ? $_POST['tubeDelay'] : '';
        $tubeTtr = !empty($_POST['tubeTtr']) ? $_POST['tubeTtr'] : '';

        $id = $this->interface->addJob($tubeName, $tubeData, $tubePriority, $tubeDelay, $tubeTtr);

        if (!empty($id)) {
            $result = array('result' => true, 'id' => $id);
        }

        echo json_encode($result);
        exit();
    }

    protected function _actionReloader() {
        $this->_tplVars['_tplMain'] = 'ajax';
        $this->_tplVars['_tplBlock'] = 'allTubes';
    }

    protected function _actionClearTubes() {
        if (is_array($_POST)) {
            foreach ($_POST as $tube => $v) {
                $states = array('ready', 'delayed', 'buried');
                foreach ($states as $state) {
                    $this->deleteAllFromTube($state, $tube);
                }
            }
        }
        echo json_encode(array('result' => true));
        exit();
    }

    protected function _actionPause() {
        $delayParam = $this->_globalVar['count']; // Get delay from URL parameter ('count')

        if ($delayParam === '-1') {
            // If URL param is -1, get the effective setting (Cookie or Config default)
            $settings = new Settings();
            $settingValue = $settings->getTubePauseSeconds();

            // If the setting itself is -1 (meaning use beanstalkd default), set delay to 3600
            if ($settingValue == -1) {
                $delay = 3600; // Beanstalkd default pause time (1 hour)
            } else {
                // Otherwise, use the non-negative value from settings (cookie or config)
                $delay = max(0, $settingValue); // Ensure it's not negative if config/cookie somehow has < 0
            }
        } else {
            // Use the value directly from the URL parameter, ensuring it's a non-negative integer
            $delay = max(0, intval($delayParam));
        }
        $this->interface->pauseTube($this->_globalVar['tube'], $delay);
        header(
                sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'])));
        exit();
    }

    protected function _actionAddSample() {
        $success = false;
        $error = '';
        $response = array('result' => &$success, 'error' => &$error);
        if (isset($_POST['addsamplejobid']) && isset($_POST['addsamplename']) && isset($_POST['tube']) && isset($_POST['tubes'])) {
            try {
                $job = $this->interface->_client->peek(intval($_POST['addsamplejobid']));
                if ($job) {
                    $res = $this->_storeSampleJob($_POST, $job->getData());
                    if ($res === true) {
                        $success = true;
                    } else {
                        $error = $res;
                    }
                } else {
                    $error = 'Invalid state option';
                }
            } catch (Exception $e) {
                // there might be no jobs to peek at, and peekReady raises exception in this situation
                $error = $e->getMessage();
            }
        } else {
            $error = 'Required fields are not set';
        }
        echo json_encode($response);
        exit();
    }

    protected function _actionLoadSample() {
        $key = $_GET['key'];
        if (!empty($key)) {
            $storage = new Storage($this->_globalVar['config']['storage']);
            $job = $storage->load($key);
            if ($job) {
                $this->interface->addJob($this->_globalVar['tube'], $job['data']);
            }
        }
        if (isset($_GET['redirect'])) {
            $_SESSION['info'] = 'Job placed on tube';
            header(sprintf('Location: %s', $_GET['redirect']));
        } else {
            header(sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'])));
        }
        exit();
    }

    protected function _actionManageSamples() {
        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'sampleJobsManage';
    }

    protected function _actionEditSample() {
        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'sampleJobsEdit';
        $key = $_GET['key'];
        if (!empty($key)) {
            $storage = new Storage($this->_globalVar['config']['storage']);
            $job = $storage->load($key);
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_POST['jobdata']) && isset($_POST['name']) && isset($_POST['tubes'])) {
                    $oldjob = $job;
                    $storage->delete($key);
                    $job['name'] = $_POST['name'];
                    $job['tubes'] = $_POST['tubes'];
                    $job['data'] = htmlspecialchars_decode($_POST['jobdata']);
                    if ($storage->saveJob($job)) {
                        header('Location: ./?action=manageSamples');
                    } else {
                        $storage->saveJob($oldjob);
                        $this->_tplVars['error'] = $storage->getError();
                    }
                } else {
                    $job['name'] = @$_POST['name'];
                    $job['data'] = @$_POST['jobdata'];
                    $job['tubes'] = @$_POST['tubes'];
                    $this->_tplVars['error'] = 'Required fields are not set';
                }
            }
            if ($job) {
                $this->_tplVars['job'] = $job;
            } else {
                $this->_errors[] = 'Cannot locate job';
                return;
            }
        } else {
            $this->_errors[] = 'The requested key is invalid';
            return;
        }
        $serverTubes = array();
        if (is_array($this->getServers())) {
            foreach ($this->getServers() as $server) {
                try {
                    $interface = new BeanstalkInterface($server);
                    $tubes = $interface->getTubes();
                    if (is_array($tubes)) {
                        $serverTubes[$server] = $tubes;
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
        if (empty($serverTubes)) {
            $this->_errors[] = 'No tubes were found, please connect a server.';
            return;
        }
        $this->_tplVars['serverTubes'] = $serverTubes;
    }

    protected function _actionNewSample() {
        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'sampleJobsEdit';
        $this->_tplVars['isNewRecord'] = true;
        $storage = new Storage($this->_globalVar['config']['storage']);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['jobdata']) && isset($_POST['name']) && isset($_POST['tubes'])) {
                $job['name'] = $_POST['name'];
                $job['tubes'] = $_POST['tubes'];
                $job['data'] = htmlspecialchars_decode($_POST['jobdata']);
                if ($storage->saveJob($job)) {
                    header('Location: ./?action=manageSamples');
                } else {
                    $this->_tplVars['error'] = $storage->getError();
                }
            } else {
                $job['name'] = @$_POST['name'];
                $job['data'] = @$_POST['jobdata'];
                $job['tubes'] = @$_POST['tubes'];
                $this->_tplVars['error'] = 'Required fields are not set';
            }
        }

        $serverTubes = array();
        if (is_array($this->getServers())) {
            foreach ($this->getServers() as $server) {
                try {
                    $interface = new BeanstalkInterface($server);
                    $tubes = $interface->getTubes();
                    if (is_array($tubes)) {
                        $serverTubes[$server] = $tubes;
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
        if (empty($serverTubes)) {
            $this->_errors[] = 'No tubes were found, please connect a server.';
            return;
        }
        $this->_tplVars['serverTubes'] = $serverTubes;
    }

    protected function _actionDeleteSample() {
        $key = $_GET['key'];
        if (!empty($key)) {
            $storage = new Storage($this->_globalVar['config']['storage']);
            $job = $storage->load($key);
            if ($job) {
                $storage->delete($key);
            }
        }
        header('Location: ./?action=manageSamples');
        exit();
    }

    /**
     * Shows the list of review batches, optionally filtered to one source tube.
     *
     * @return void
     */
    protected function _actionReviewBatches() {
        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'reviewBatches';
        $sourceTube = isset($_GET['sourceTube']) ? $_GET['sourceTube'] : null;
        $this->_tplVars['reviewSourceTube'] = $sourceTube;
        $this->_tplVars['reviewBatches'] = $this->getReviewBatches($sourceTube);
    }

    /**
     * Creates a new review batch and redirects to the chunked preparation progress page.
     *
     * @return void
     * @throws InvalidArgumentException If review storage cannot be created or initialized.
     * @throws Pheanstalk_Exception_ServerException If tube safety stats fail unexpectedly.
     */
    protected function _actionReviewBatchStart() {
        $state = isset($_POST['state']) ? $_POST['state'] : 'buried';
        $tube = $this->_globalVar['tube'];
        $forceUnsafe = !empty($_POST['forceUnsafe']);
        if (!empty($_POST['pauseAndProceed'])) {
            $this->interface->pauseTube($tube, $this->getReviewPauseSeconds());
        }
        $safety = $this->canPrepareReviewState($tube, $state, $forceUnsafe);

        if (!$safety['allowed']) {
            $_SESSION['error'] = $safety['message'];
            header(sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($tube)));
            exit();
        }

        $tubeStats = $this->getTubeStatValues($tube);
        $stateCount = $this->getStateCountFromStats($tubeStats, $state);
        if ($stateCount <= 0) {
            $_SESSION['error'] = 'The selected state has no jobs to review.';
            header(sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($tube)));
            exit();
        }
        $targetCount = isset($_POST['reviewLimit']) ? max(1, (int)$_POST['reviewLimit']) : $stateCount;
        $targetCount = min($targetCount, $stateCount);
        $createdAt = date('c');
        $id = 'review-' . date('Ymd-His') . '-' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $tube) . '-' . mt_rand(1000, 9999);
        $reviewTube = isset($_POST['reviewTube']) && $_POST['reviewTube'] !== ''
                ? $_POST['reviewTube']
                : ReviewBatchNaming::defaultReviewTube($tube);

        $batch = array(
            'id' => $id,
            'source_server' => $this->_globalVar['server'],
            'source_tube' => $tube,
            'source_state' => $state,
            'review_tube' => $reviewTube,
            'created_at' => $createdAt,
            'owner_session_id' => $this->getReviewSessionId(),
            'owner_ip' => $this->getRequestIp(),
            'status' => 'processing',
            'target_count' => $targetCount,
            'source_state_count' => $stateCount,
            'processed' => 0,
            'errors' => 0,
            'force_unsafe' => $forceUnsafe ? 1 : 0,
            'include_body_snapshot' => $this->shouldIncludeBodySnapshot() && !empty($_POST['includeBodySnapshot']) ? 1 : 0,
            'safety_message' => $safety['message'],
        );

        $storage = $this->getReviewBatchStorage();
        $storage->createBatch($batch);

        header(sprintf('Location: ./?server=%s&action=reviewBatchProgress&batchId=%s', $this->_globalVar['server'], urlencode($id)));
        exit();
    }

    /**
     * Appends additional jobs from the source tube to an existing review batch.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded.
     */
    protected function _actionReviewBatchAddJobs() {
        $batchId = isset($_POST['batchId']) ? $_POST['batchId'] : (isset($_GET['batchId']) ? $_GET['batchId'] : null);
        if (!$batchId) {
            throw new InvalidArgumentException('Batch ID is required');
        }
        $storage = $this->getReviewBatchStorage();
        $batch = $storage->loadBatch($batchId);
        if (!$batch) {
            throw new InvalidArgumentException('Review batch not found');
        }
        $this->assertReviewBatchOwner($batch);

        $tube = $batch['source_tube'];
        $state = $batch['source_state'];
        $tubeStats = $this->getTubeStatValues($tube);
        $stateCount = $this->getStateCountFromStats($tubeStats, $state);

        if ($stateCount <= 0) {
            $_SESSION['error'] = 'No new jobs to review in the source tube.';
            header(sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($tube)));
            exit();
        }

        $batch['target_count'] = (int)$batch['target_count'] + $stateCount;
        $batch['status'] = 'processing';
        $storage->saveBatch($batch);

        header(sprintf('Location: ./?server=%s&action=reviewBatchProgress&batchId=%s', $this->_globalVar['server'], urlencode($batchId)));
        exit();
    }

    /**
     * Shows progress while jobs are copied to the review tube and removed from the source state.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded.
     */
    protected function _actionReviewBatchProgress() {
        $batch = $this->loadReviewBatchFromRequest();
        $storage = $this->getReviewBatchStorage();
        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'reviewBatchProgress';
        $this->_tplVars['reviewBatch'] = $batch;
        $this->_tplVars['reviewOperation'] = $storage->loadOperation($batch['id']);
    }

    /**
     * Processes one preparation chunk for a review batch.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or storage cannot record progress.
     * @throws Pheanstalk_Exception_ServerException If tube safety stats fail unexpectedly.
     */
    protected function _actionReviewBatchProcess() {
        $batch = $this->loadReviewBatchFromRequest();
        $this->assertReviewBatchOwner($batch);
        $storage = $this->getReviewBatchStorage();

        if ($batch['status'] === 'complete' && (int)$batch['processed'] === 0 && (int)$batch['target_count'] > 0) {
            $batch['status'] = 'processing';
        }

        if ($batch['status'] !== 'processing') {
            $this->jsonResponse(array('result' => true, 'batch' => $batch));
        }

        $safety = $this->canPrepareReviewState($batch['source_tube'], $batch['source_state'], !empty($batch['force_unsafe']));
        if (!$safety['allowed']) {
            $batch['status'] = 'paused_safety_check_failed';
            $batch['safety_message'] = $safety['message'];
            $storage->saveBatch($batch);
            $this->jsonResponse(array('result' => false, 'batch' => $batch, 'error' => $safety['message']));
        }

        $chunkSize = $this->getReviewConfigInt('chunkSize', 50);
        if ($chunkSize < 1) {
            $chunkSize = 50;
        }

        $service = new ReviewBatchService($this->interface, $storage);
        $batch = $service->processPreparationChunk($batch, $chunkSize);
        $this->jsonResponse(array('result' => $batch['status'] !== 'error', 'batch' => $batch));
    }

    /**
     * Displays one review batch with paginated collapsed manifest rows.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded.
     */
    protected function _actionReviewBatchShow() {
        $batch = $this->loadReviewBatchFromRequest();
        $batch = $this->syncReviewBatchFromQueue($batch);
        $storage = $this->getReviewBatchStorage();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['perPage']) ? max(1, min(100, (int)$_GET['perPage'])) : 25;
        $previewLength = $this->getReviewConfigInt('bodyPreviewLength', 100);
        if ($previewLength < 1) {
            $previewLength = 100;
        }

        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'reviewBatchShow';
        $pageBuilder = new ReviewBatchPageBuilder($this->interface, $storage);
        $this->_tplVars = array_merge($this->_tplVars, $pageBuilder->buildShowPage($batch, $page, $perPage, $previewLength));
    }

    /**
     * Returns selected review-copy jobs to the source tube inline.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or the operation cannot be recorded.
     */
    protected function _actionReviewBatchReturnJobs() {
        $this->reviewBatchOperateJobs('return');
    }

    /**
     * Deletes selected review-copy jobs inline.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or the operation cannot be recorded.
     */
    protected function _actionReviewBatchDeleteJobs() {
        $this->reviewBatchOperateJobs('delete');
    }

    /**
     * Moves selected review-copy jobs to the destination tube inline.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or the operation cannot be recorded.
     */
    protected function _actionReviewBatchMoveJobs() {
        $this->reviewBatchOperateJobs('move');
    }

    /**
     * Duplicates selected review-copy jobs to the destination tube inline.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or the operation cannot be recorded.
     */
    protected function _actionReviewBatchDuplicateJobs() {
        $this->reviewBatchOperateJobs('duplicate');
    }

    /**
     * Downloads audit, summary, or body-snapshot export files for a review batch.
     *
     * @return void
     * @throws InvalidArgumentException If the batch or requested export file cannot be read.
     */
    protected function _actionReviewBatchDownloadManifest() {
        $batch = $this->loadReviewBatchFromRequest();
        $format = isset($_GET['format']) ? $_GET['format'] : 'jsonl';
        $storage = $this->getReviewBatchStorage();

        if ($format === 'csv') {
            $this->downloadReviewBatchCsv($batch, $storage);
            return;
        }
        if ($format === 'body-snapshot') {
            $this->downloadReviewBatchBodySnapshot($batch, $storage);
            return;
        }

        $file = $storage->getJobsFile($batch['id']);
        if (!is_file($file) || !is_readable($file)) {
            throw new InvalidArgumentException('Review batch manifest not found');
        }

        header('Content-Type: application/x-ndjson');
        header('Content-Disposition: attachment; filename="' . $batch['id'] . '.audit.jsonl"');
        readfile($file);
        exit();
    }

    /**
     * Deletes a review batch, including remaining review-copy jobs and local files.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or local files cannot be deleted.
     */
    protected function _actionReviewBatchDelete() {
        $batch = $this->loadReviewBatchFromRequest();
        $this->assertReviewBatchOwner($batch);
        $storage = $this->getReviewBatchStorage();
        $this->assertNoActiveReviewOperation($batch['id'], $storage);
        $summary = $storage->getBatchSummary($batch['id']);
        $jobs = $summary['jobs'];

        foreach ($jobs as $manifestJob) {
            $status = isset($manifestJob['status']) ? $manifestJob['status'] : '';
            if (in_array($status, array('returned', 'deleted', 'moved_to_tube'), true)) {
                continue;
            }
            if (empty($manifestJob['review_id'])) {
                continue;
            }
            try {
                $reviewJob = $this->interface->_client->peek((int)$manifestJob['review_id']);
                if ($reviewJob) {
                    $this->interface->_client->delete($reviewJob);
                }
            } catch (Exception $e) {
                if (!$this->isBeanstalkNotFound($e)) {
                    $_SESSION['error'] = 'Review batch was not deleted: ' . $e->getMessage();
                    header('Location: ' . $this->reviewBatchShowUrl($batch['id']));
                    exit();
                }
            }
        }

        $storage->deleteBatch($batch['id']);
        header(sprintf('Location: ./?server=%s&action=reviewBatches', $this->_globalVar['server']));
        exit();
    }

    /**
     * Deletes all review batches (or all batches for a specific source tube),
     * including their remaining review-copy jobs and local files.
     */
    protected function _actionReviewBatchDeleteAll() {
        $sourceTube = isset($_GET['sourceTube']) && $_GET['sourceTube'] !== '' ? $_GET['sourceTube'] : null;
        $storage = $this->getReviewBatchStorage();
        $batches = $storage->listBatches();

        foreach ($batches as $batch) {
            if ($sourceTube !== null && (!isset($batch['source_tube']) || $batch['source_tube'] !== $sourceTube)) {
                continue;
            }
            try {
                $summary = $storage->getBatchSummary($batch['id']);
                $jobs = $summary['jobs'];

                foreach ($jobs as $manifestJob) {
                    $status = isset($manifestJob['status']) ? $manifestJob['status'] : '';
                    if (in_array($status, array('returned', 'deleted', 'moved_to_tube'), true)) {
                        continue;
                    }
                    if (empty($manifestJob['review_id'])) {
                        continue;
                    }
                    try {
                        $reviewJob = $this->interface->_client->peek((int)$manifestJob['review_id']);
                        if ($reviewJob) {
                            $this->interface->_client->delete($reviewJob);
                        }
                    } catch (Exception $e) {
                        // ignore if job is not found
                    }
                }
                $storage->deleteBatch($batch['id']);
            } catch (Exception $e) {
                // ignore and continue
            }
        }

        $redirectUrl = './?server=' . urlencode($this->_globalVar['server']) . '&action=reviewBatches';
        if ($sourceTube !== null) {
            $redirectUrl .= '&sourceTube=' . urlencode($sourceTube);
        }
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Starts a chunked all-remaining review operation for a review batch.
     *
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded, operation is invalid, or operation metadata cannot be saved.
     */
    protected function _actionReviewBatchOperationStart() {
        $batch = $this->loadReviewBatchFromRequest();
        $this->assertReviewBatchOwner($batch);
        $storage = $this->getReviewBatchStorage();
        $operationType = isset($_POST['operation']) ? $_POST['operation'] : '';
        $delay = isset($_POST['delay']) ? max(0, (int)$_POST['delay']) : 0;

        if (!in_array($operationType, array('move_all_moved', 'duplicate_all_moved', 'delete_all_moved'), true)) {
            throw new InvalidArgumentException('Unsupported review operation');
        }
        $targetTube = isset($_POST['targetTube']) ? trim($_POST['targetTube']) : '';
        if (in_array($operationType, array('move_all_moved', 'duplicate_all_moved'), true) && $targetTube === '') {
            throw new InvalidArgumentException('Destination tube is required for move and duplicate operations');
        }

        $summary = $storage->getBatchSummary($batch['id'], 0, 0);
        $targetCount = $summary['moved_count'];
        $now = date('c');
        $operation = array(
            'id' => 'op-' . date('Ymd-His') . '-' . mt_rand(1000, 9999),
            'batch_id' => $batch['id'],
            'operation' => $operationType,
            'delay' => $delay,
            'target_tube' => $targetTube,
            'return_page' => isset($_POST['returnPage']) ? max(1, (int)$_POST['returnPage']) : 1,
            'per_page' => isset($_POST['perPage']) ? max(1, min(100, (int)$_POST['perPage'])) : 25,
            'mode' => 'all_moved',
            'status' => 'processing',
            'processed' => 0,
            'target_count' => $targetCount,
            'errors' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'owner_session_id' => $this->getReviewSessionId(),
            'owner_ip' => $this->getRequestIp(),
        );
        $storage->startOperation($operation);

        header(sprintf('Location: ./?server=%s&action=reviewBatchOperationProgress&batchId=%s', $this->_globalVar['server'], urlencode($batch['id'])));
        exit();
    }

    /**
     * Shows progress for a chunked all-remaining return/delete operation.
     *
     * @return void
     * @throws InvalidArgumentException If the batch or operation metadata cannot be loaded.
     */
    protected function _actionReviewBatchOperationProgress() {
        $batch = $this->loadReviewBatchFromRequest();
        $operation = $this->loadReviewBatchOperation($batch['id']);

        $this->_tplVars['_tplMain'] = 'main';
        $this->_tplVars['_tplPage'] = 'reviewBatchOperationProgress';
        $this->_tplVars['reviewBatch'] = $batch;
        $this->_tplVars['reviewOperation'] = $operation;
    }

    /**
     * Processes one chunk of a long-running review return/delete operation.
     *
     * @return void
     * @throws InvalidArgumentException If the batch or operation cannot be loaded or progress cannot be recorded.
     */
    protected function _actionReviewBatchOperationProcess() {
        $batch = $this->loadReviewBatchFromRequest();
        $storage = $this->getReviewBatchStorage();
        $operation = $this->loadReviewBatchOperation($batch['id']);
        $this->assertReviewOperationOwner($operation);

        if ($operation['status'] !== 'processing') {
            $this->jsonResponse(array('result' => true, 'operation' => $operation));
        }

        $chunkSize = $this->getReviewConfigInt('chunkSize', 50);
        if ($chunkSize < 1) {
            $chunkSize = 50;
        }

        $jobs = $storage->getMovedJobs($batch['id'], $chunkSize);
        if (!count($jobs)) {
            $operation['status'] = 'complete';
            $operation['target_count'] = (int)$operation['processed'];
            $operation['updated_at'] = date('c');
            $storage->saveOperation($operation);
            $this->jsonResponse(array('result' => true, 'operation' => $operation));
        }

        $service = new ReviewBatchService($this->interface, $storage);
        foreach ($jobs as $manifestJob) {
            $result = $service->operateReviewJob($batch, $manifestJob, $operation);
            $operation['processed'] = (int)$operation['processed'] + 1;
            if (!$result) {
                $operation['errors'] = (int)$operation['errors'] + 1;
            }
        }

        $remaining = max(0, (int)$operation['target_count'] - (int)$operation['processed']);
        if ($remaining === 0) {
            $operation['status'] = 'complete';
        }
        $operation['updated_at'] = date('c');
        $storage->saveOperation($operation);

        $this->jsonResponse(array('result' => true, 'operation' => $operation, 'remaining' => $remaining));
    }

    /**
     * Takes ownership of a stable review batch for the current session.
     *
     * @return void
     * @throws InvalidArgumentException If the batch is processing or an operation is active.
     */
    protected function _actionReviewBatchTakeOver() {
        $batch = $this->loadReviewBatchFromRequest();
        $storage = $this->getReviewBatchStorage();
        $operation = $storage->loadOperation($batch['id']);
        $blockReason = $this->getReviewTakeOverBlockReason($batch, $operation);
        if ($blockReason !== '') {
            throw new InvalidArgumentException('Cannot take over yet because ' . $blockReason . '.');
        }

        $storage->takeOverBatch($batch['id'], $this->getReviewSessionId(), $this->getRequestIp());
        header('Location: ' . $this->reviewBatchShowUrl($batch['id']));
        exit();
    }

    /**
     * Returns stored review batches for the current server, optionally filtered by source tube.
     *
     * @param string|null $sourceTube Source tube filter.
     * @return array
     * @throws InvalidArgumentException If review storage is unavailable.
     */
    public function getReviewBatches($sourceTube = null) {
        if (!$this->isReviewEnabled()) {
            return array();
        }
        $result = array();
        $batches = $this->getReviewBatchStorage()->listBatches();
        foreach ($batches as $batch) {
            if (isset($batch['source_server']) && $batch['source_server'] === $this->_globalVar['server']) {
                if ($sourceTube !== null && (!isset($batch['source_tube']) || $batch['source_tube'] !== $sourceTube)) {
                    continue;
                }
                $result[] = $batch;
            }
        }
        return $result;
    }

    /**
     * Counts review batches associated with one source tube.
     *
     * @param string $tube Source tube name.
     * @return int
     * @throws InvalidArgumentException If review storage is unavailable.
     */
    /**
     * Finds a review batch where the given tube is used as the review tube.
     *
     * @param string $tube Tube name.
     * @return array|false The batch array, or false if not found.
     */
    public function getReviewBatchForReviewTube($tube) {
        if (!$this->isReviewEnabled()) {
            return false;
        }
        try {
            $batches = $this->getReviewBatchStorage()->listBatches();
            foreach ($batches as $batch) {
                if (isset($batch['review_tube']) && $batch['review_tube'] === $tube) {
                    return $batch;
                }
            }
        } catch (Exception $e) {
            // ignore
        }
        return false;
    }

    /**
     * Scans the review tube for any jobs not currently recorded in the batch manifest,
     * and appends them to the manifest automatically.
     *
     * @param array $batch The review batch metadata.
     * @return array The updated review batch metadata.
     */
    public function syncReviewBatchFromQueue($batch) {
        if (!$this->isReviewEnabled()) {
            return $batch;
        }

        try {
            $reviewTube = $batch['review_tube'];
            $tubeStats = $this->interface->getTubeStats($reviewTube);
            if (!$tubeStats) {
                return $batch;
            }

            $ready = isset($tubeStats['current-jobs-ready']) ? (int)$tubeStats['current-jobs-ready'] : 0;
            $delayed = isset($tubeStats['current-jobs-delayed']) ? (int)$tubeStats['current-jobs-delayed'] : 0;
            $buried = isset($tubeStats['current-jobs-buried']) ? (int)$tubeStats['current-jobs-buried'] : 0;
            $totalInQueue = $ready + $delayed + $buried;

            if ($totalInQueue <= 0) {
                return $batch;
            }

            $storage = $this->getReviewBatchStorage();
            $summary = $storage->getBatchSummary($batch['id'], 0, null);
            $manifestJobs = $summary['jobs'];

            // Find which jobs in the manifest are still active in the review tube
            $manifestActiveIds = array();
            $maxReviewId = 0;
            foreach ($manifestJobs as $mj) {
                $rid = isset($mj['review_id']) ? (int)$mj['review_id'] : 0;
                if ($rid > 0) {
                    $maxReviewId = max($maxReviewId, $rid);
                    $status = isset($mj['status']) ? $mj['status'] : '';
                    if (in_array($status, array('moved', 'duplicated'), true)) {
                        $manifestActiveIds[$rid] = true;
                    }
                }
            }

            $extraCount = $totalInQueue - count($manifestActiveIds);
            if ($extraCount <= 0) {
                return $batch;
            }

            // We need to scan beanstalkd job IDs starting from $maxReviewId + 1
            // to find the $extraCount jobs belonging to $reviewTube.
            $foundCount = 0;
            $currentId = $maxReviewId + 1;
            $maxScanLookahead = 10000;
            $scanned = 0;

            $newJobs = array();
            $bodySnapshots = array();

            while ($foundCount < $extraCount && $scanned < $maxScanLookahead) {
                $scanned++;
                try {
                    $jobStats = $this->interface->_client->statsJob($currentId);
                    if ($jobStats && isset($jobStats['tube']) && $jobStats['tube'] === $reviewTube) {
                        // Found a job belonging to our review tube!
                        $job = $this->interface->_client->peek($currentId);
                        if ($job) {
                            $priority = isset($jobStats['pri']) ? (int)$jobStats['pri'] : Pheanstalk::DEFAULT_PRIORITY;
                            $ttr = isset($jobStats['ttr']) ? (int)$jobStats['ttr'] : Pheanstalk::DEFAULT_TTR;
                            
                            $row = array(
                                'original_id' => null,
                                'review_id' => $currentId,
                                'status' => 'moved',
                                'pri' => $priority,
                                'delay' => isset($jobStats['delay']) ? (int)$jobStats['delay'] : 0,
                                'ttr' => $ttr,
                                'job_created_at' => date('c', time() - (isset($jobStats['age']) ? (int)$jobStats['age'] : 0)),
                            );
                            $newJobs[] = $row;
                            
                            if (!empty($batch['include_body_snapshot'])) {
                                $bodySnapshots[] = array(
                                    'original_id' => null,
                                    'review_id' => $currentId,
                                    'status' => 'snapshot',
                                    'body_encoding' => 'base64',
                                    'body_base64' => base64_encode($job->getData()),
                                );
                            }
                            $foundCount++;
                        }
                    }
                } catch (Exception $e) {
                    // ignore NotFound or other errors
                }
                $currentId++;
            }

            if (count($newJobs) > 0) {
                foreach ($newJobs as $row) {
                    $storage->appendJob($batch['id'], $row);
                }
                if (count($bodySnapshots) > 0) {
                    $storage->appendBodySnapshotRows($batch['id'], $bodySnapshots);
                }
                $batch['target_count'] = (int)$batch['target_count'] + count($newJobs);
                $batch['processed'] = (int)$batch['processed'] + count($newJobs);
                $storage->saveBatch($batch);
            }

        } catch (Exception $e) {
            // ignore outer errors
        }

        return $batch;
    }

    public function countReviewBatchesForTube($tube) {
        if (!$this->isReviewEnabled()) {
            return 0;
        }
        return count($this->getReviewBatches($tube));
    }

    /**
     * Returns whether the collapsed manifest row should still have a review-copy job to inspect.
     *
     * @param array $job Collapsed manifest row.
     * @return bool
     */
    public function reviewJobHasInspectableCopy($job) {
        return $this->getReviewBatchPageBuilder()->reviewJobHasInspectableCopy($job);
    }

    /**
     * Returns whether the manifest status represents a leftover review copy needing cleanup.
     *
     * @param string $status Manifest status.
     * @return bool
     */
    public function isReviewJobCleanupStatus($status) {
        return $this->getReviewBatchPageBuilder()->isReviewJobCleanupStatus($status);
    }

    /**
     * Formats a seconds value in the same days/hours/minutes/seconds style as job stats.
     *
     * @param int $value Duration in seconds.
     * @return string
     */
    public function formatDuration($value) {
        return $this->getReviewBatchPageBuilder()->formatDuration($value);
    }

    /**
     * Returns whether a state can be prepared for review under current safety rules.
     *
     * @param string $tube Tube name.
     * @param string $state Job state.
     * @return array
     * @throws Pheanstalk_Exception_ServerException If tube safety stats fail unexpectedly.
     */
    public function getReviewSafety($tube, $state) {
        return $this->canPrepareReviewState($tube, $state, false);
    }

    /**
     * Returns whether the configured unsafe override is available for a ready/delayed state.
     *
     * @param string $state Job state.
     * @return bool
     */
    public function isUnsafeReviewOverrideEnabled($state) {
        if ($state === 'ready') {
            return $this->getReviewConfigBool('allowUnsafeReadyOverride', false);
        }
        if ($state === 'delayed') {
            return $this->getReviewConfigBool('allowUnsafeDelayedOverride', false);
        }
        return false;
    }

    protected function _actionMoveJobsTo() {
        $destServer = (isset($_GET['server'])) ? $_GET['server'] : null;
        $destTube = (isset($_GET['destTube'])) ? $_GET['destTube'] : null;
        $destState = (isset($_GET['destState'])) ? $_GET['destState'] : null;
        if (!empty($destTube) && in_array($GLOBALS['state'], array('ready', 'delayed', 'buried'))) {
            $this->moveJobsFromTo($destServer, $GLOBALS['tube'], $GLOBALS['state'], $destTube);
        }
        if (!empty($destState)) {
            $this->moveJobsToState($destServer, $GLOBALS['tube'], $GLOBALS['state'], $destState);
        }
    }

    protected function _actionSearch() {
        $this->actionTimeStart = microtime(true);
        $timelimit_in_seconds = 15;
        $searchStr = (isset($_GET['searchStr'])) ? $_GET['searchStr'] : null;
        $states = array('ready', 'delayed', 'buried');
        $jobList = array();
        $limit = null;

        if ($searchStr === null or $searchStr === '')
            return false;

        if (isset($_GET['limit'])) {
            $limit = intval($_GET['limit']);
        }

        foreach ($states as $state) {
            $jobList[$state] = $this->findJobsByState($GLOBALS['tube'], $state, $searchStr, $limit);
            $jobList['total'] += count($jobList[$state]);
        }

        $this->searchResults = $jobList;
    }

    private function findJobsByState($tube, $state, $searchStr, $limit = 25) {
        $jobList = array();
        $job = null;

        try {
            $stats = $this->interface->getServerStats();
        } catch (Exception $e) {
            return $jobList;
        }

        $ready = $stats['current-jobs-ready']['value'];
        $reserved = $stats['current-jobs-reserved']['value'];
        $delayed = $stats['current-jobs-delayed']['value'];
        $buried = $stats['current-jobs-buried']['value'];
        $deleted = $stats['cmd-delete']['value'];

        try {
            switch ($state) {
                case 'ready':
                    $job = $this->interface->_client->useTube($tube)->peekReady();
                    break;
                case 'delayed':
                    $job = $this->interface->_client->useTube($tube)->peekDelayed();
                    break;
                case 'buried':
                    $job = $this->interface->_client->useTube($tube)->peekBuried();
                    break;
            }
        } catch (Exception $e) {
            
        }

        if ($job === null)
            return $jobList;

        $jobList = array();
        $lastId = $ready + $reserved + $delayed + $buried + $deleted;

        $added = 0;
        for ($id = $job->getId(); $id <= $lastId; $id++) {
            try {
                /** @var Pheanstalk_Job $job */
                $job = $this->interface->_client->peek($id);
                if ($job) {
                    $jobStats = $this->interface->_client->statsJob($job);
                    if ($jobStats->tube === $tube &&
                            $jobStats->state === $state &&
                            strpos($job->getData(), $searchStr) !== false
                    ) {
                        $jobList[$id] = $job;
                        $added++;
                    }
                }
            } catch (Pheanstalk_Exception_ServerException $e) {

            }
            if ($added >= $limit || (microtime(true) - $this->actionTimeStart) > $limit) {
                break;
            }
        }

        return $jobList;
    }

    private function _storeSampleJob($post, $jobData) {
        $storage = new Storage($this->_globalVar['config']['storage']);
        $job_array = array();
        $job_array['name'] = trim($post['addsamplename']);
        $job_array['tubes'] = $post['tubes'];
        $job_array['data'] = $jobData;
        if ($storage->saveJob($job_array)) {
            return true;
        } else {
            return $storage->getError();
        }
    }

    public function getSampleJobs($tube = null) {
        $storage = new Storage($this->_globalVar['config']['storage']);
        if ($tube) {
            return $storage->getJobsForTube($tube);
        } else {
            return $storage->getJobs();
        }
    }

    private function moveJobsFromTo($server, $tube, $state, $destTube) {
        try {
            do {
                $this->interface->_client->useTube($tube);
                switch ($state) {
                    case 'ready':
                        $job = $this->interface->_client->peekReady();
                        break;
                    case 'delayed':
                        $job = $this->interface->_client->peekDelayed();
                        break;
                    case 'buried':
                        $job = $this->interface->_client->peekBuried();
                        break;
                }

                if ($job) {
                    $this->interface->addJob($destTube, $job->getData());
                    $this->interface->_client->delete($job);
                    set_time_limit(5);
                }
            } while (!empty($job));
        } catch (Exception $e) {
            // there might be no jobs to peek at, and peekReady raises exception in this situation
        }
        header(sprintf('Location: ./?server=%s&tube=%s', $server, urlencode($destTube)));
    }

    private function moveJobsToState($server, $tube, $state, $destState) {
        try {
            do {
                $job = null;
                switch ($state) {
                    case 'ready':
                        $job = $this->interface->_client->watch($tube)->reserve(0);
                        break;
                    default:
                        return;
                }

                if ($job) {
                    switch ($destState) {
                        case 'buried':
                            $this->interface->_client->bury($job);
                            break;
                        default:
                            return;
                    }
                    set_time_limit(5);
                }
            } while (!empty($job));
        } catch (Exception $e) {
            // there might be no jobs to peek at, and peekReady raises exception in this situation
        }
        header(sprintf('Location: ./?server=%s&tube=%s', $server, urlencode($tube)));
    }

    /**
     * Runs the selected-row review action for small manual selections.
     *
     * @param string $operation Operation name: move, duplicate, or delete.
     * @return void
     * @throws InvalidArgumentException If the batch cannot be loaded or the operation cannot be recorded.
     */
    private function reviewBatchOperateJobs($operation) {
        $batch = $this->loadReviewBatchFromRequest();
        $this->assertReviewBatchOwner($batch);
        $storage = $this->getReviewBatchStorage();
        $service = new ReviewBatchService($this->interface, $storage);
        $this->assertNoActiveReviewOperation($batch['id'], $storage);
        $selected = isset($_POST['job']) && is_array($_POST['job']) ? $_POST['job'] : array();
        $selectedJobs = $storage->getJobsByReviewIds($batch['id'], $selected);
        $delay = isset($_POST['delay']) ? max(0, (int)$_POST['delay']) : 0;
        $targetTube = isset($_POST['targetTube']) ? trim($_POST['targetTube']) : '';
        if ($operation === 'return' && $targetTube === '') {
            $targetTube = $batch['source_tube'];
        }
        if (in_array($operation, array('move', 'duplicate'), true) && $targetTube === '') {
            throw new InvalidArgumentException('Destination tube is required for move and duplicate operations');
        }
        $operationTypes = array(
            'return' => 'move_all_moved',
            'move' => 'move_all_moved',
            'duplicate' => 'duplicate_all_moved',
            'delete' => 'delete_all_moved',
        );
        if (!isset($operationTypes[$operation])) {
            throw new InvalidArgumentException('Unsupported review operation');
        }

        foreach ($selected as $reviewId) {
            $reviewId = (int)$reviewId;
            $manifestJob = isset($selectedJobs[$reviewId]) ? $selectedJobs[$reviewId] : false;
            if (!$manifestJob || !isset($manifestJob['review_id'])) {
                continue;
            }
            $status = isset($manifestJob['status']) ? $manifestJob['status'] : '';
            $canMoveOrDuplicate = in_array($status, array('moved', 'duplicated'), true);
            $canDelete = $status === 'moved' || $this->isReviewJobCleanupStatus($status);
            if (($operation === 'delete' && !$canDelete)
                    || (in_array($operation, array('return', 'move', 'duplicate'), true) && !$canMoveOrDuplicate)) {
                continue;
            }

            $operationMeta = array(
                'operation' => $operationTypes[$operation],
                'delay' => $delay,
                'target_tube' => $targetTube,
            );
            $service->operateReviewJob($batch, $manifestJob, $operationMeta);
        }

        header('Location: ' . $this->reviewBatchShowUrl($batch['id']));
        exit();
    }

    /**
     * Loads the operation metadata for an in-progress chunked review operation.
     *
     * @param string $batchId Batch id.
     * @return array
     * @throws InvalidArgumentException If the operation metadata is missing or review storage is unavailable.
     */
    private function loadReviewBatchOperation($batchId) {
        $operation = $this->getReviewBatchStorage()->loadOperation($batchId);
        if (!$operation) {
            throw new InvalidArgumentException('Review batch operation not found');
        }
        return $operation;
    }

    /**
     * Blocks manual/batch actions while a long-running operation is active.
     *
     * @param string $batchId Batch id.
     * @param ReviewBatchStorage $storage Review batch storage.
     * @return void
     * @throws InvalidArgumentException If an operation is already processing.
     */
    private function assertNoActiveReviewOperation($batchId, $storage) {
        $operation = $storage->loadOperation($batchId);
        if ($operation && isset($operation['status']) && $operation['status'] === 'processing') {
            $owner = isset($operation['owner_ip']) ? $operation['owner_ip'] : 'another session';
            throw new InvalidArgumentException('Review batch operation is already running by ' . $owner . '.');
        }
    }

    /**
     * Ensures only the session that prepared a batch can mutate it.
     *
     * @param array $batch Review batch metadata.
     * @return void
     * @throws InvalidArgumentException If another session owns the batch.
     */
    private function assertReviewBatchOwner($batch) {
        if ($this->isReviewOwnedByAnotherSession($batch)) {
            throw new InvalidArgumentException('Review batch was prepared by ' . $this->getReviewOwnerIp($batch) . '.');
        }
    }

    /**
     * Ensures only the session that started an operation can continue polling it.
     *
     * @param array $operation Operation metadata.
     * @return void
     * @throws InvalidArgumentException If another session owns the active operation.
     */
    private function assertReviewOperationOwner($operation) {
        if (isset($operation['status']) && $operation['status'] === 'processing'
                && isset($operation['owner_session_id']) && $operation['owner_session_id'] !== $this->getReviewSessionId()) {
            $owner = isset($operation['owner_ip']) ? $operation['owner_ip'] : 'another session';
            throw new InvalidArgumentException('Review batch operation is already running by ' . $owner . '.');
        }
    }

    /**
     * Returns a stable id for the current PHP session.
     *
     * @return string
     */
    public function getReviewSessionId() {
        return session_id();
    }

    /**
     * Returns the request IP used in review ownership messages.
     *
     * @return string
     */
    public function getRequestIp() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown IP';
    }

    /**
     * Returns whether the current session differs from stored review ownership.
     *
     * @param array $record Batch or operation metadata.
     * @return bool
     */
    public function isReviewOwnedByAnotherSession($record) {
        return isset($record['owner_session_id']) && $record['owner_session_id'] !== '' && $record['owner_session_id'] !== $this->getReviewSessionId();
    }

    /**
     * Returns a displayable owner IP for review ownership warnings.
     *
     * @param array $record Batch or operation metadata.
     * @return string
     */
    public function getReviewOwnerIp($record) {
        return isset($record['owner_ip']) && $record['owner_ip'] !== '' ? $record['owner_ip'] : 'unknown IP';
    }

    /**
     * Returns why ownership cannot be taken over, or an empty string when takeover is allowed.
     *
     * @param array $batch Review batch metadata.
     * @param array|false $operation Active operation metadata, if any.
     * @return string
     */
    public function getReviewTakeOverBlockReason($batch, $operation = false) {
        if (isset($batch['status']) && $batch['status'] === 'processing') {
            return 'preparation is in progress';
        }
        if ($operation && isset($operation['status']) && $operation['status'] === 'processing') {
            return $this->getReviewOperationLabel($operation) . ' is in progress from ' . $this->getReviewOwnerIp($operation);
        }
        return '';
    }

    /**
     * Returns a human-readable operation label.
     *
     * @param array $operation Operation metadata.
     * @return string
     */
    public function getReviewOperationLabel($operation) {
        $operationType = isset($operation['operation']) ? $operation['operation'] : '';
        if ($operationType === 'move_all_moved') {
            if (isset($operation['target_tube']) && $operation['target_tube'] !== '') {
                return 'moving jobs to ' . $operation['target_tube'];
            }
            return 'returning jobs to the source tube';
        }
        if ($operationType === 'delete_all_moved') {
            return 'deleting review copies';
        }
        if ($operationType === 'duplicate_all_moved') {
            if (isset($operation['target_tube']) && $operation['target_tube'] !== '') {
                return 'duplicating jobs to ' . $operation['target_tube'];
            }
            return 'duplicating jobs to another tube';
        }
        return $operationType !== '' ? $operationType : 'a review operation';
    }

    /**
     * Detects beanstalkd NOT_FOUND errors so missing review copies can be reconciled.
     *
     * @param Exception $e Queue exception.
     * @return bool
     */
    private function isBeanstalkNotFound($e) {
        return $this->getReviewBatchPageBuilder()->isBeanstalkNotFound($e);
    }

    /**
     * Builds a review-batch URL that preserves pagination context.
     *
     * @param string $batchId Batch id.
     * @param int|null $page Current page, or null to read from request context.
     * @param int|null $perPage Current page size, or null to read from request context.
     * @return string
     */
    private function reviewBatchShowUrl($batchId, $page = null, $perPage = null) {
        if ($page === null && isset($_REQUEST['returnPage'])) {
            $page = max(1, (int)$_REQUEST['returnPage']);
        }
        if ($perPage === null && isset($_REQUEST['perPage'])) {
            $perPage = max(1, min(100, (int)$_REQUEST['perPage']));
        }

        $url = sprintf('./?server=%s&action=reviewBatchShow&batchId=%s', urlencode($this->_globalVar['server']), urlencode($batchId));
        if ($page !== null) {
            $url .= '&page=' . (int)$page;
        }
        if ($perPage !== null) {
            $url .= '&perPage=' . (int)$perPage;
        }
        return $url;
    }

    /**
     * Streams the current collapsed manifest summary as CSV.
     *
     * @param array $batch Review batch metadata.
     * @param ReviewBatchStorage $storage Review batch storage.
     * @return void
     */
    private function downloadReviewBatchCsv($batch, $storage) {
        $summary = $storage->getBatchSummary($batch['id']);
        $jobs = $summary['jobs'];
        $fields = array(
            'original_id',
            'review_id',
            'returned_id',
            'target_tube',
            'target_id',
            'status',
            'pri',
            'age',
            'job_created_at',
            'delay',
            'ttr',
            'time-left',
            'file',
            'reserves',
            'timeouts',
            'releases',
            'buries',
            'kicks',
            'return_delay',
            'target_delay',
            'error_message',
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $batch['id'] . '.current-summary.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $fields);
        foreach ($jobs as $job) {
            $row = array();
            foreach ($fields as $field) {
                $row[] = isset($job[$field]) ? $job[$field] : '';
            }
            fputcsv($out, $row);
        }
        fclose($out);
        exit();
    }

    /**
     * Streams the body snapshot captured during review preparation.
     *
     * @param array $batch Review batch metadata.
     * @param ReviewBatchStorage $storage Review batch storage.
     * @return void
     * @throws InvalidArgumentException If the body snapshot cannot be read.
     */
    private function downloadReviewBatchBodySnapshot($batch, $storage) {
        $file = $storage->getBodySnapshotFile($batch['id']);
        if (empty($batch['include_body_snapshot']) || !is_file($file) || !is_readable($file)) {
            throw new InvalidArgumentException('Review batch body snapshot not found');
        }

        header('Content-Type: application/x-ndjson');
        header('Content-Disposition: attachment; filename="' . $batch['id'] . '.body-snapshot.jsonl"');
        readfile($file);
        exit();
    }

    /**
     * Creates review storage after enforcing the feature enabled flag.
     *
     * @return ReviewBatchStorage
     * @throws InvalidArgumentException If review is disabled or storage is unavailable.
     */
    private function getReviewBatchStorage() {
        if (!$this->isReviewEnabled()) {
            throw new InvalidArgumentException('Review batches are disabled by config.');
        }
        return new ReviewBatchStorage($this->_globalVar['config']);
    }

    /**
     * Returns the review page/display helper.
     *
     * @return ReviewBatchPageBuilder
     * @throws InvalidArgumentException If review storage is unavailable.
     */
    private function getReviewBatchPageBuilder() {
        if ($this->reviewBatchPageBuilder === null) {
            $this->reviewBatchPageBuilder = new ReviewBatchPageBuilder($this->interface, $this->getReviewBatchStorage());
        }
        return $this->reviewBatchPageBuilder;
    }

    /**
     * Loads and validates a review batch referenced by the current request.
     *
     * @return array
     * @throws InvalidArgumentException If the batch is missing, review storage is unavailable, or the batch belongs to another server.
     */
    private function loadReviewBatchFromRequest() {
        $id = isset($_REQUEST['batchId']) ? $_REQUEST['batchId'] : '';
        $batch = $this->getReviewBatchStorage()->loadBatch($id);
        if (!$batch) {
            throw new InvalidArgumentException('Review batch not found');
        }
        if (isset($batch['source_server']) && $batch['source_server'] !== $this->_globalVar['server']) {
            throw new InvalidArgumentException('Review batch belongs to a different server');
        }
        return $batch;
    }

    /**
     * Sends a JSON response and ends the request.
     *
     * @param array $response Response payload.
     * @param int $statusCode HTTP status code.
     * @return void
     */
    private function jsonResponse($response, $statusCode = 200) {
        if ($statusCode !== 200) {
            http_response_code($statusCode);
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Reads a boolean review config value with a fallback.
     *
     * @param string $key Review config key.
     * @param bool $default Default value.
     * @return bool
     */
    private function getReviewConfigBool($key, $default) {
        if (isset($this->_globalVar['config']['review'][$key])) {
            return (bool)$this->_globalVar['config']['review'][$key];
        }
        return $default;
    }

    /**
     * Reads an integer review config value with a fallback.
     *
     * @param string $key Review config key.
     * @param int $default Default value.
     * @return int
     */
    private function getReviewConfigInt($key, $default) {
        if (isset($this->_globalVar['config']['review'][$key])) {
            return (int)$this->_globalVar['config']['review'][$key];
        }
        return $default;
    }

    /**
     * Returns whether body snapshot capture is permitted by configuration.
     *
     * @return bool
     */
    private function shouldIncludeBodySnapshot() {
        return empty($this->_globalVar['config']['review']['neverIncludeBodySnapshot']);
    }

    /**
     * Returns the configured pause duration used by the review pause-and-proceed path.
     *
     * @return int Pause duration in seconds.
     */
    private function getReviewPauseSeconds() {
        $settings = new Settings();
        $settingValue = $settings->getTubePauseSeconds();
        if ($settingValue == -1) {
            return 3600;
        }
        return max(0, (int)$settingValue);
    }

    /**
     * Evaluates whether preparing the requested state is safe or explicitly allowed.
     *
     * @param string $tube Tube name.
     * @param string $state Job state.
     * @param bool $forceUnsafe Whether the user requested the unsafe override.
     * @return array
     * @throws Pheanstalk_Exception_ServerException If tube safety stats fail unexpectedly.
     */
    private function canPrepareReviewState($tube, $state, $forceUnsafe) {
        if ($state === 'buried') {
            return array('allowed' => true, 'message' => 'Buried review can proceed because buried jobs are not reservable by workers.');
        }

        if ($state !== 'ready' && $state !== 'delayed') {
            return array('allowed' => false, 'message' => 'Unsupported review state.');
        }

        $stats = $this->getTubeStatValues($tube);
        $watching = isset($stats['current-watching']) ? (int)$stats['current-watching'] : 0;
        $waiting = isset($stats['current-waiting']) ? (int)$stats['current-waiting'] : 0;
        $pauseTimeLeft = isset($stats['pause-time-left']) ? (int)$stats['pause-time-left'] : 0;
        $safeBecausePaused = $pauseTimeLeft > 0;

        if ($state === 'ready') {
            if ($safeBecausePaused) {
                return array('allowed' => true, 'message' => 'Ready review is allowed because the tube is paused for ' . $pauseTimeLeft . ' more seconds.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
            }
            if ($watching === 0 && $waiting === 0 && $this->getReviewConfigBool('allowReadyWhenUnwatched', false)) {
                return array('allowed' => true, 'message' => 'Ready review is allowed because there are no watchers or waiters.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
            }
            if ($forceUnsafe && $this->getReviewConfigBool('allowUnsafeReadyOverride', false)) {
                return array('allowed' => true, 'message' => 'Unsafe ready review override is enabled.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
            }
            return array('allowed' => false, 'message' => 'Ready jobs can only be reviewed when no clients are watching/waiting or the tube is paused, unless unsafe ready override is enabled.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
        }

        if ($safeBecausePaused) {
            return array('allowed' => true, 'message' => 'Delayed review is allowed because the tube is paused for ' . $pauseTimeLeft . ' more seconds.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
        }
        if ($watching === 0 && $waiting === 0 && $this->getReviewConfigBool('allowDelayedWhenUnwatched', false)) {
            return array('allowed' => true, 'message' => 'Delayed review is allowed because there are no watchers or waiters.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
        }
        if ($forceUnsafe && $this->getReviewConfigBool('allowUnsafeDelayedOverride', false)) {
            return array('allowed' => true, 'message' => 'Unsafe delayed review override is enabled.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
        }
        return array('allowed' => false, 'message' => 'Delayed jobs can only be reviewed when no clients are watching/waiting or the tube is paused, unless unsafe delayed override is enabled.', 'watching' => $watching, 'waiting' => $waiting, 'pause_time_left' => $pauseTimeLeft);
    }

    /**
     * Extracts the current job count for a state from stats-tube output.
     *
     * @param array $stats stats-tube response.
     * @param string $state Job state.
     * @return int
     */
    private function getStateCountFromStats($stats, $state) {
        $key = 'current-jobs-' . $state;
        return isset($stats[$key]) ? (int)$stats[$key] : 0;
    }

}
