<?php
/**
 * Service.php
 *
 * @copyright Copyright (c) 2012 Silverpop Systems, Inc.
 * @link      http://www.silverpop.com Silverpop Systems, Inc.
 * @author    Jerry Ablan <jablan@silverpop.com>
 *
 * @filesource
 */
namespace CIS\Services\Profiler;

require_once '/var/inc/cislib/GELFLogger.php';

/**
 * Service
 *
 * In order to stay light-weight, this class doesn't do very much at all.
 * Merely starts and stops a timer. Suitable for use in tight loops
 */
class Service extends \CIS\Services\BaseService implements \CIS\Interfaces\TimerEvents
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string The facility this service uses
	 */
	const GraylogFacility = 'cis.services.profiler.service';
	/**
	 * @var string The format for display times
	 */
	const LogTimeFormat = 'H:i:s';
	/**
	 * @var string Our connection string
	 */
	const MongoConnectionString = 'mongodb://cis_profiler:lXbwGasdFJyjGIHg@cismongo.atlis1/cis_profiler';
	/**
	 * @var string Our database name
	 */
	const MongoDatabase = 'cis_profiler';
	/**
	 * @var string The base name of the profiler collection
	 */
	const MongoCollection = 'profiles';

	//**************************************************************************
	//* Private Members
	//**************************************************************************

	/**
	 * @var \CIS\Components\Timer[]
	 */
	protected static $_timers = array();
	/**
	 * @var array
	 */
	protected static $_profiles = array();
	/**
	 * @var int
	 */
	protected $_pid = null;
	/**
	 * @var array
	 */
	protected $_additionalData = null;
	/**
	 * @var string
	 */
	protected $_streamName = null;
	/**
	 * @var int The level for profile info
	 */
	protected $_level = \CIS\Enums\GraylogLevel::Debug;
	/**
	 * @var \CIS\Services\Storage\MongoDb\Service Set to false to not have profiles stored
	 */
	protected $_storage = null;
	/**
	 * @var \MongoCollection
	 */
	protected $_collection = null;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * @param array $options
	 */
	public function __construct( $options = array() )
	{
		//	Default graylog to true unless specifically set to false in options
		$options['graylog'] = \CIS\Utility\Option::get( $options, 'graylog', true );
		$options['pid'] = getmypid();

		parent::__construct( $options );

		//	disable mongo storage if extension not loaded...
		if ( !extension_loaded( 'mongo' ) )
		{
			$this->_storage = false;
		}

		if ( null === $this->_storage )
		{
			$this->_storage = new \CIS\Services\Storage\MongoDb\Service( self::MongoConnectionString );
			$this->_collection = $this->_storage->getMongo()->selectCollection( self::MongoDatabase, self::MongoCollection );
		}

		//	Make sure logTimers is called before we die...
		register_shutdown_function( array( $this, 'logTimers' ) );
	}

	/**
	 * Registers a process to be profiled
	 *
	 * @param string $label
	 * @param bool   $autoStart If true, the timer will automatically start after constructed.
	 *
	 * @return \CIS\Components\Timer
	 */
	public function register( $label, $autoStart = false )
	{
		$_hash = md5( $this->_pid . $label );

		//	Already exists, return it
		if ( null !== ( $_timer = \CIS\Utility\Option::get( self::$_timers, $_hash ) ) )
		{
			return $_timer;
		}

		//	Add it to the array and return it
		self::$_timers[$_hash] = new \CIS\Components\Timer(
			array(
				'id'           => $_hash,
				'displayLabel' => $label,
				'label'        => $_hash . ':' . $label,
				'autoStart'    => $autoStart,
				'streamName'   => $this->_streamName,
				'owner'        => $this,
			)
		);

		return self::$_timers[$_hash];
	}

	/**
	 * Registers, runs and profiles a process. Returns whatever the profiled process returned
	 *
	 * @param string   $label
	 * @param callable $callable
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function profile( $label, $callable )
	{
		$_timer = $this->register( $label, false );
		$_timer->setStreamName( $this->_streamName );
		$_timer->start();

		try
		{
			$_returnValue = call_user_func( $callable );
		}
		catch ( \Exception $_ex )
		{
			//	If there is an exception, catch it, stop the timer, and re-throw
			$_timer->stop();
			throw $_ex;
		}

		$_timer->stop();

		return $_returnValue;
	}

	/**
	 * Call before the end of your process
	 * Dumps the timers out to graylog if wanted
	 */
	public function logTimers()
	{
		//	Stop any active timers...
		$this->stopAll();

		try
		{
			//	Nothing to do here...
			if ( false === $this->_graylog && empty( $this->_storage ) )
			{
				return;
			}

			if ( !empty( self::$_timers ) && is_array( self::$_timers ) )
			{
				foreach ( self::$_timers as $_hash => $_timer )
				{
					try
					{
						$this->logTimer( $_timer );
						unset( self::$_timers[$_hash] );
					}
					catch ( \Exception $_ex )
					{
						\CIS\CIS::log( 'Exception while dumping timers: ' . $_ex->getMessage() );
					}
				}
			}

			if ( !empty( self::$_profiles ) )
			{
				//	Log profiles at some point...
			}
		}
		catch ( \Exception $_ex )
		{
			\CIS\CIS::log( 'Exception while logging timers: ' . $_ex->getMessage() );
		}
	}

	/**
	 * Stops all active timers
	 */
	public function stopAll()
	{
		foreach ( self::$_timers as $_timer )
		{
			$_timer->stop( true );
		}
	}

	/**
	 * @param \CIS\Components\Timer $timer
	 * @param string                $message
	 * @param string                $streamName
	 * @param int                   $level
	 *
	 * @return mixed
	 */
	public function logTimer( $timer, $message = null, $streamName = null, $level = null )
	{
		if ( $this->_graylog )
		{
			$message = $message
				? :
				$timer->getLabel() . ' (start/stop/elapsed): ' .
				date( self::LogTimeFormat, $timer->getStart() ) . ' / ' .
				date( self::LogTimeFormat, $timer->getEnd() ) . ' / ' .
				$timer->elapsed( true );

			$timer->setStreamName( \CIS\Utility\Option::nvl( $timer->getStreamName(), $streamName, $this->_streamName ) );

			//	Merge in the timer stats...
			$this->_additionalData = array_merge(
				!is_array( $this->_additionalData ) ? array() : $this->_additionalData,
				$timer->asArray( '_' )
			);

			//	Get the additional data ready
			$_payload = array_merge(
				$this->_additionalData,
				array(
					'short_message' => $timer->getLabel() . ' : ' . $timer->average( true ),
					'full_message'  => $message,
					'level'         => $level ? : $this->_level,
					'facility'      => self::GraylogFacility,
				)
			);

//			\CIS\CIS::log( 'Gelf Payload: ' . print_r( $_payload, true ) );

			//	Shoot it off!
			\GELFLogger::logMessage( $_payload );
		}

		if ( null !== $this->_collection )
		{
			$this->_collection->insert( $timer->asArray() );
		}
	}

	/**
	 * @param array|string $key
	 * @param mixed        $value
	 *
	 * @return bool
	 */
	public function addAdditionalData( $key, $value = null )
	{
		if ( !is_array( $this->_additionalData ) )
		{
			$this->_additionalData = array();
		}

		if ( !is_array( $key ) )
		{
			$key = array( $key=> $value );
		}

		foreach ( $key as $_key => $_value )
		{
			$this->_additionalData[$_key] = $_value;
		}

		return $this->_additionalData;
	}

	//**************************************************************************
	//* Default Event Handlers
	//**************************************************************************

	/**
	 * @param \CIS\Services\ServiceEvent $event
	 *
	 * @return bool
	 */
	public function onTimerStart( $event )
	{
		/** @var $_timer \CIS\Components\Timer */
		$_timer = $event->getData();
		$_streamName = \CIS\Utility\Option::nvl( $_timer->getStreamName(), $this->_streamName );

		if ( !isset( self::$_profiles[$_streamName] ) || !is_array( self::$_profiles[$_streamName] ) )
		{
			self::$_profiles[$_streamName] = array();
		}

		if ( empty( self::$_profiles[$_streamName][$_timer->getId()] ) )
		{
			self::$_profiles[$_streamName][$_timer->getId()] = array();
		}

		return true;
	}

	/**
	 * @param \CIS\Services\ServiceEvent $event
	 *
	 * @return bool
	 */
	public function onTimerStop( $event )
	{
		/** @var $_timer \CIS\Components\Timer */
		$_timer = $event->getData();

		$_streamName = \CIS\Utility\Option::nvl( $_timer->getStreamName(), $this->_streamName );

		//	Add this timer to the end...
		self::$_profiles[$_streamName][$_timer->getId()][] = $_timer;

		return true;
	}

	/**
	 * @param \CIS\Services\ServiceEvent $event
	 *
	 * @return bool
	 */
	public function onTimerDestroyed( $event )
	{
		return true;
	}

	//**************************************************************************
	//* Properties
	//**************************************************************************

	/**
	 * @param array $additionalData
	 *
	 * @return array
	 */
	public function setAdditionalData( $additionalData )
	{
		$this->_additionalData = $additionalData;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAdditionalData()
	{
		return $this->_additionalData;
	}

	/**
	 * @param int $level
	 *
	 * @return int
	 */
	public function setLevel( $level )
	{
		$this->_level = $level;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLevel()
	{
		return $this->_level;
	}

	/**
	 * @param string $streamName
	 *
	 * @return string
	 */
	public function setStreamName( $streamName )
	{
		$this->_streamName = $streamName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStreamName()
	{
		return $this->_streamName;
	}

	/**
	 * @return \MongoCollection
	 */
	public function getCollection()
	{
		return $this->_collection;
	}

	/**
	 * @param \CIS\Services\Storage\MongoDb\Service $storage
	 *
	 * @return \CIS\Services\Storage\MongoDb\Service
	 */
	public function setStorage( $storage )
	{
		$this->_storage = $storage;
		return $this;
	}

	/**
	 * @return \CIS\Services\Storage\MongoDb\Service
	 */
	public function getStorage()
	{
		return $this->_storage;
	}

	/**
	 * @param \MongoCollection $collection
	 *
	 * @return \MongoCollection
	 */
	public function setCollection( $collection )
	{
		$this->_collection = $collection;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPid()
	{
		return $this->_pid;
	}

	/**
	 * @param string $label
	 *
	 * @return \CIS\Components\Timer
	 */
	public function getTimer( $label )
	{
		return \CIS\Utility\Option::get( self::$_timers, md5( $this->_pid . $label ) );
	}

	/**
	 * @return array|\CIS\Components\Timer[]
	 */
	public function getTimers()
	{
		return self::$_timers;
	}

}