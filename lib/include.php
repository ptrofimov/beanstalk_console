<?php

/**
 * @link https://github.com/ptrofimov/beanstalk_console
 * @link http://kr.github.com/beanstalkd/
 * @author Petr Trofimov, Sergey Lysenko
 */
function __autoload($class) {
    require_once str_replace('_', '/', $class) . '.php';
}

session_start();
require_once 'Pheanstalk/ClassLoader.php';
Pheanstalk_ClassLoader::register(dirname(__FILE__));

require_once 'BeanstalkInterface.class.php';
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../src/Storage.php';

$GLOBALS['server'] = !empty($_GET['server']) ? $_GET['server'] : '';
$GLOBALS['action'] = !empty($_GET['action']) ? $_GET['action'] : '';
$GLOBALS['state'] = !empty($_GET['state']) ? $_GET['state'] : '';
$GLOBALS['count'] = !empty($_GET['count']) ? $_GET['count'] : '';
$GLOBALS['tube'] = !empty($_GET['tube']) ? $_GET['tube'] : '';
$GLOBALS['tplMain'] = !empty($_GET['tplMain']) ? $_GET['tplMain'] : '';
$GLOBALS['tplBlock'] = !empty($_GET['tplBlock']) ? $_GET['tplBlock'] : '';

class Console {

    public $interface;
    protected $_tplVars = array();
    protected $_globalVar = array();
    protected $_errors = array();
    private $serversConfig = array();
    private $serversEnv = array();
    private $serversCookie = array();
    private $searchResults = array();
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
        if (null !== getenv('BEANSTALK_SERVERS')) {
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
            do {
                switch ($state) {
                    case 'ready':
                        $job = $this->interface->_client->useTube($tube)->peekReady();
                        break;
                    case 'delayed':
                        try {
                            $ready = $this->interface->_client->useTube($tube)->peekReady();
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
                            $bury = $this->interface->_client->useTube($tube)->peekBuried();
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
                        $job = $this->interface->_client->useTube($tube)->peekDelayed();
                        if ($job) {
                            //when we found job with Delayed, kick all messages, to be ready, so that we can Delete them.
                            $this->interface->kick($tube, 100000000);
                            $this->deleteAllFromTube('ready', $tube);
                            return;
                        }
                        break;
                    case 'buried':
                        $job = $this->interface->_client->useTube($tube)->peekBuried();
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

            $this->_tplVars['tubes'] = $this->interface->getTubes();

            $stats = $this->interface->getTubesStats();

            $this->_tplVars['tubesStats'] = $stats;
            $this->_tplVars['peek'] = $this->interface->peekAll($this->_globalVar['tube']);
            $this->_tplVars['contentType'] = $this->interface->getContentType();
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
                sprintf('Location: ./?server=%s&tube=%s', $this->_globalVar['server'], urlencode($this->_globalVar['tube'])));
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
        if ($this->_globalVar['count'] == -1) {
            if (!@empty($_COOKIE['tubePauseSeconds'])) {
                $this->_globalVar['count'] = $_COOKIE['tubePauseSeconds'];
            } else {
                $this->_globalVar['count'] = 3600;
            }
        }
        $this->interface->pauseTube($this->_globalVar['tube'], $this->_globalVar['count']);
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
            $jobList['total']+=count($jobList[$state]);
        }

        $this->searchResults = $jobList;
    }

    private function findJobsByState($tube, $state, $searchStr, $limit = 25) {
        $jobList = array();
        $job = null;
        $total = $this->interface->getTubeStats($tube);
        $totalJobs = 0;

        try {
            switch ($state) {
                case 'ready':
                    $job = $this->interface->_client->useTube($tube)->peekReady();
                    $totalJobs = $total[2]['value'];
                    break;
                case 'delayed':
                    $job = $this->interface->_client->useTube($tube)->peekDelayed();
                    $totalJobs = $total[4]['value'];
                    break;
                case 'buried':
                    $job = $this->interface->_client->useTube($tube)->peekBuried();
                    $totalJobs = $total[5]['value'];
                    break;
            }
        } catch (Exception $e) {
            
        }

        if ($job === null)
            return $jobList;

        $jobList = array();
        $lastId = $job->getId() + $totalJobs;

        $added = 0;
        for ($id = $job->getId(); $id < $lastId; $id++) {
            try {
                $job = $this->interface->_client->peek($id);
                if (strpos($job->getData(), $searchStr) !== false) {
                    $jobList[$id] = $job;
                    $added++;
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

}
