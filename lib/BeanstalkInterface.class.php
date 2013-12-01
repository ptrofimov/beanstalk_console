<?php
class BeanstalkInterface
{
    protected $_contentType;
    public $_client;

    public function __construct($server)
    {
        $list = explode(':', $server);
        $this->_client = new Pheanstalk($list[0], isset($list[1]) ? $list[1] : '');
    }

    public function getTubes()
    {
        $tubes = $this->_client->listTubes();
        sort($tubes);
        return $tubes;
    }

    public static function getServerStatsFields()
    {
        return array(
            'binlog-current-index' => 'the index of the current binlog file being written to. If binlog is not active this value will be 0',
            'binlog-max-size' => 'the maximum size in bytes a binlog file is allowed to get before a new binlog file is opened',
            'binlog-oldest-index' => 'the index of the oldest binlog file needed to store the current jobs',
            'binlog-records-migrated' => 'the cumulative number of records written as part of compaction',
            'binlog-records-written' => 'the cumulative number of records written to the binlog',
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
        );
    }

    public function getServerStats()
    {
        $fields = $this->getServerStatsFields();
        $stats = array();
        $object = $this->_client->stats();
        foreach ($fields as $key => $description) {
            if (isset($object[$key])) {
                $stats[$key] = array(
                    'key' => $key,
                    'description' => $description,
                    'value' => $object[$key],
                );
            }
        }
        return $stats;
    }

    public function getTubesStats()
    {
        $stats = array();
        foreach ($this->getTubes() as $tube) {
            $stats[] = $this->getTubeStats($tube);
        }
        return $stats;
    }

    public function getTubeStats($tube)
    {
        $stats = array();
        $descr = array(
            'name' => 'the tube\'s name',
            'current-jobs-urgent' => 'the number of ready jobs with priority < 1024 in this tube',
            'current-jobs-ready' => 'the number of jobs in the ready queue in this tube',
            'current-jobs-reserved' => 'the number of jobs reserved by all clients in this tube',
            'current-jobs-delayed' => 'the number of delayed jobs in this tube',
            'current-jobs-buried' => 'the number of buried jobs in this tube',
            'total-jobs' => 'the cumulative count of jobs created in this tube',
            'current-waiting' => 'the number of open connections that have issued a reserve command while watching this tube but not yet received a response',
            'cmd-delete' => 'the cumulative number of delete commands for this tube',
            'pause' => 'the number of seconds the tube has been paused for',
            'cmd-pause-tube' => 'the cumulative number of pause-tube commands for this tube',
            'pause-time-left' => 'the number of seconds until the tube is un-paused');

        $nameTube = array(
            'name' => 'name',
            'current-jobs-urgent' => 'Urgent',
            'current-jobs-ready' => 'Ready',
            'current-jobs-reserved' => 'Reserved',
            'current-jobs-delayed' => 'Delayed',
            'current-jobs-buried' => 'Buried',
            'total-jobs' => 'Total',
            'current-using' => 'Using',
            'current-watching' => 'Watching',
            'current-waiting' => 'Waiting',
            'cmd-delete' => 'Delete(cmd)',
            'cmd-pause-tube' => 'Pause(cmd)',
            'pause' => 'Pause(sec)',
            'pause-time-left' => 'Pause(left)');
        foreach ($this->_client->statsTube($tube) as $key => $value) {
            if (!array_key_exists($key, $nameTube)) {
                continue;
            }

            $stats[] = array(
                'key' => $nameTube[$key],
                'value' => $value,
                'descr' => isset($descr[$key]) ? $descr[$key] : '');
        }
        return $stats;
    }

    public function peekReady($tube)
    {
        return $this->_peek($tube, 'peekReady');
    }

    public function peekDelayed($tube)
    {
        return $this->_peek($tube, 'peekDelayed');
    }

    public function peekBuried($tube)
    {
        return $this->_peek($tube, 'peekBuried');
    }

    public function peekAll($tube)
    {
        return array(
            'ready' => $this->peekReady($tube),
            'delayed' => $this->peekDelayed($tube),
            'buried' => $this->peekBuried($tube));
    }

    public function kick($tube, $limit)
    {
        $this->_client->useTube($tube)->kick($limit);
    }

    public function deleteReady($tube)
    {
        $job = $this->_client->useTube($tube)->peekReady();
        $this->_client->delete($job);

    }
	
	public function deleteBuried($tube)
    {
        $job = $this->_client->useTube($tube)->peekBuried();
        $this->_client->delete($job);
    }

	public function deleteDelayed($tube)
    {
        $job = $this->_client->useTube($tube)->peekDelayed();
        $this->_client->delete($job);
    }
	
    public function addJob($tubeName, $tubeData, $tubePriority = null, $tubeDelay = null, $tubeTtr = null)
    {
        $this->_client->useTube($tubeName);
        $result = $this->_client->useTube($tubeName)->put($tubeData, $tubePriority, $tubeDelay, $tubeTtr);

        return $result;
    }

    public function getContentType()
    {
        return $this->_contentType;
    }

    /* INTERNAL */

    /**
     * Pheanstalk class instance
     *
     * @var Pheanstalk
     */

    private function _peek($tube, $method)
    {
        try {
            $job = $this->_client->useTube($tube)->{$method}();
            $peek = array(
                'id' => $job->getId(),
                'data' => $job->getData(),
                'stats' => $this->_client->statsJob($job));
        } catch (Exception $ex) {
            $peek = array();
        }
        if ($peek) {
            $peek['data'] = $this->_decodeDate($peek['data']);
        }
        return $peek;
    }

    private function _decodeDate($pData)
    {
        $this->_contentType = false;
        $out = $pData;
        $data = @unserialize($pData);
        if ($data) {
            $this->_contentType = 'php';
            $out = $data;
        } else {
            $data = @json_decode($pData, true);
            if ($data) {
                $this->_contentType = 'json';
                //$out = $data;
            }
        }
        return $out;
    }
}