<?php
class BeanstalkInterface
{
	protected $_contentType;
	protected $_client;
	
	public function __construct( $server )
	{
		$list = explode( ':', $server );
		$this->_client = new Pheanstalk( $list[ 0 ], isset( $list[ 1 ] ) ? $list[ 1 ] : '' );
	}
	
	public function getTubes()
	{
		$tubes = $this->_client->listTubes();
		sort( $tubes );
		return $tubes;
	}
	
	public function getTubesStats()
	{
		$stats = array();
		foreach ( $this->getTubes() as $tube )
		{
			$stats[] = $this->getTubeStats( $tube );
		}
		return $stats;
	}
	
	public function getTubeStats( $tube )
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
			'pause' => 'the number of seconds the tube has been paused for', 
			'cmd-pause-tube' => 'the cumulative number of pause-tube commands for this tube', 
			'pause-time-left' => 'the number of seconds until the tube is un-paused' );
		
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
			'cmd-pause-tube' => 'Pause(cmd)', 
			'pause' => 'Pause(sec)', 
			'pause-time-left' => 'Pause(left)' );
		
		foreach ( $this->_client->statsTube( $tube ) as $key => $value )
		{
			if (! array_key_exists($key, $nameTube)) {
				continue;
			}

			$stats[] = array(
				'key' => $nameTube[ $key ],
				'value' => $value,
				'descr' => isset( $descr[ $key ] ) ? $descr[ $key ] : '' );
		}
		return $stats;
	}
	
	public function peekReady( $tube )
	{
		return $this->_peek( $tube, 'peekReady' );
	}
	
	public function peekDelayed( $tube )
	{
		return $this->_peek( $tube, 'peekDelayed' );
	}
	
	public function peekBuried( $tube )
	{
		return $this->_peek( $tube, 'peekBuried' );
	}
	
	public function peekAll( $tube )
	{
		return array( 
			'ready' => $this->peekReady( $tube ), 
			'delayed' => $this->peekDelayed( $tube ), 
			'buried' => $this->peekBuried( $tube ) );
	}
	
	public function kick( $tube, $limit )
	{
		$this->_client->useTube( $tube )->kick( $limit );
	}
	
	public function deleteReady( $tube )
	{
		$job = $this->_client->useTube( $tube )->peekReady();
		$this->_client->delete( $job );
	
	}
	
	public function addJob( $tubeName, $tubeData, $tubePriority = null, $tubeDelay = null, $tubeTtr = null )
	{
		$this->_client->useTube( $tubeName );
		$result = $this->_client->useTube( $tubeName )->put( $tubeData, $tubePriority, $tubeDelay, $tubeTtr );
		
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
	
	private function _peek( $tube, $method )
	{
		try
		{
			$job = $this->_client->useTube( $tube )->{$method}();
			$peek = array( 
				'id' => $job->getId(), 
				'data' => $job->getData(), 
				'stats' => $this->_client->statsJob( $job ) );
		}
		catch ( Exception $ex )
		{
			$peek = array();
		}
		if ( $peek )
		{
			$peek[ 'data' ] = $this->_decodeDate( $peek[ 'data' ] );
		}
		return $peek;
	}
	
	private function _decodeDate( $pData )
	{
		$this->_contentType = false;
		$out = $pData;
		$data = @unserialize( $pData );
		if ( $data )
		{
			$this->_contentType = 'php';
			$out = $data;
		}
		else
		{
			$data = @json_decode( $pData, true );
			if ( $data )
			{
				$this->_contentType = 'json';
				//$out = $data;
			}
		}
		return $out;
	}
}