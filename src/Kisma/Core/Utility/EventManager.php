<?php
/**
 * This file is part of Kisma(tm).
 *
 * Kisma(tm) <https://github.com/kisma/kisma>
 * Copyright 2009-2014 Jerry Ablan <jerryablan@gmail.com>
 *
 * Kisma(tm) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kisma(tm) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kisma(tm).  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Kisma\Core\Utility;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\IO\IOInterface;
use Composer\Script;
use Composer\Script\CommandEvent;
use Composer\Script\PackageEvent;
use Composer\Util\ProcessExecutor;
use Kisma\Core\Exceptions\InvalidEventHandlerException;
use Kisma\Core\Interfaces\SubscriberLike;
use Kisma\Core\SeedUtility;

/**
 * The manager of events
 *
 * @author Jerry Ablan <jerryablan@gmail.com>
 */

/**
 * EventManager class
 * Utility class that provides event management
 */
class EventManager extends SeedUtility
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string The default event handler signature
	 */
	const DEFAULT_HANDLER_SIGNATURE = '/^_?on(.*)$/';

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var array The event map for the application
	 */
	protected static $_eventMap = array();
	/**
	 * @var int A counter of fired events for the run of the app
	 */
	protected static $_lastEventId = 0;
	/**
	 * @var bool If true, the manager will auto-discover events based on a signature
	 */
	protected static $_autoDiscover = false;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Wires up any event handlers automatically
	 *
	 * @param SubscriberLike $subscriber
	 * @param array|null     $listeners Array of 'event.name' => callback/closure pairs
	 * @param string         $signature
	 *
	 * @internal param \Kisma\Core\Interfaces\SubscriberLike $subscriber
	 * @return void
	 */
	public static function subscribe( SubscriberLike $subscriber, $listeners = null, $signature = self::DEFAULT_HANDLER_SIGNATURE )
	{
		//	Allow for passed in listeners
		$_listeners = $listeners ? : static::discover( $subscriber, $signature );

		//	Nothing to do? Bail
		if ( empty( $_listeners ) )
		{
			return;
		}

		//	And wire them up...
		foreach ( $_listeners as $_eventName => $_callback )
		{
			$_tag = Inflector::neutralize( $_eventName );

			static::on(
				  $subscriber,
				  $_tag,
				  $_callback
			);

			unset( $_callback, $_eventName, $_tag );
		}

		unset( $_listeners );
	}

	/**
	 * Builds a hash of events and handlers that are present in this object based on the event handler signature.
	 * This merely builds the hash, nothing is done with it.
	 *
	 * @param SubscriberLike $subscriber
	 * @param string         $signature
	 *
	 * @return array
	 */
	public static function discover( SubscriberLike $subscriber, $signature = self::DEFAULT_HANDLER_SIGNATURE )
	{
		static $_discovered = array();

		//	No auto-discover? Nothing to do...
		if ( !static::$_autoDiscover )
		{
			return $_discovered;
		}

		$_objectId = $subscriber->getId();

		if ( !( $subscriber instanceof SubscriberLike ) )
		{
			//	Not a subscriber, beat it...
			$_discovered[$_objectId] = true;

			return false;
		}

		Log::debug( 'START event discovery' );

		$_listeners = array();

		if ( !isset( $_discovered[$_objectId] ) )
		{
			$_mirror = new \ReflectionClass( $subscriber );
			$_methods = $_mirror->getMethods( \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED );

			//	Check each method for the event handler signature
			foreach ( $_methods as $_method )
			{
				$_name = $_method->name;

				//	Event handler?
				if ( 0 == preg_match( $signature, $_name, $_matches ) )
				{
					continue;
				}

				//	Add to the end of the array...
				$_eventName = Inflector::tag( $_matches[1] );

				if ( null === ( $_eventTag = $_mirror->getConstant( $_eventName ) ) )
				{
					$_eventTag = Inflector::tag( $_matches[1], true );
				}

				self::on(
					$subscriber,
					$_eventTag,
						function ( $event ) use ( $subscriber, $_name )
						{
							return call_user_func( array( $subscriber, $_name ), $event );
						}
				);

				unset( $_eventTag, $_matches, $_method );
			}

			unset( $_methods, $_mirror );

			$_discovered[spl_object_hash( $subscriber )] = true;
		}

		Log::debug( 'END event discovery' );

		//	Return the current map
		return $_listeners;
	}

	/**
	 * @param SubscriberLike $subscriber
	 * @param string         $tag
	 * @param callable|null  $listener
	 */
	public static function on( $subscriber, $tag, $listener = null )
	{
		if ( null === $listener )
		{
			self::unsubscribe( $subscriber, $tag );

			return;
		}

		$_tag = Inflector::tag( $tag, true );
		$_objectId = $subscriber->getId();

		if ( !isset( self::$_eventMap[$_tag] ) )
		{
			self::$_eventMap[$_tag] = array();
		}

		if ( !isset( self::$_eventMap[$_tag][$_objectId] ) )
		{
			$_listeners[$_tag][$_objectId] = array();
		}

		self::$_eventMap[$_tag][$_objectId][] = $listener;
	}

	/**
	 * @param SubscriberLike $subscriber
	 * @param string         $eventName
	 */
	public static function unsubscribe( $subscriber, $eventName = null )
	{
		$_objectId = $subscriber->getId();
		$_tag = Inflector::tag( $eventName, true );

		foreach ( self::$_eventMap as $_eventTag => $_subscribers )
		{
			\Kisma\Core\Utility\Log::debug( '---- Unsub "' . $_objectId . '" from "' . $_eventTag . '"' );

			foreach ( $_subscribers as $_subscriberId => $_closures )
			{
				if ( $_objectId == $_subscriberId )
				{
					foreach ( Option::clean( $_closures ) as $_index => $_closure )
					{
						if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) )
						{
							/** @noinspection PhpUndefinedMethodInspection */
							$_closure->bindTo( null );
						}

						//	Remove and reindex the map
						unset( self::$_eventMap[$_eventTag][$_subscriberId][$_index], $_closure );
						self::$_eventMap[$_eventTag][$_subscriberId] = array_values( self::$_eventMap[$_eventTag][$_subscriberId] );
					}
				}
			}

			if ( $_eventTag == $_tag )
			{
				break;
			}
		}

		foreach ( self::$_eventMap as $_eventTag => $_subscriberId )
		{
			if ( $_objectId == $_subscriberId )
			{
				unset( self::$_eventMap[$_eventTag][$_objectId] );

				Log::debug(
				   '-- "' . $subscriber->getTag() . '" unsubscribed from "' . $_eventTag . '"',
				   array(
					   'tag' => $_subscriberId,
				   )
				);
			}
		}
	}

	/**
	 * Publishes an event to all subscribers
	 *
	 * @static
	 *
	 * @param null|SubscriberLike $publisher
	 * @param string              $eventName
	 * @param mixed               $eventData
	 *
	 * @throws \Kisma\Core\Exceptions\InvalidEventHandlerException
	 * @return bool|int
	 */
	public static function publish( $publisher, $eventName, $eventData = null )
	{
		//	Make sure object is cool
		if ( !self::canPublish( $publisher ) )
		{
			//	Not a publisher. Bail
			return false;
		}

		//	Ensure this is a valid event
		$_eventTag = Inflector::tag( $eventName, true );

		if ( !isset( self::$_eventMap[$_eventTag] ) || empty( self::$_eventMap[$_eventTag] ) )
		{
			//	No registered listeners, bail
			return false;
		}

		//	Make a new event if one wasn't provided
		$_event = ( $eventData instanceof \Kisma\Core\Events\SeedEvent ) ? $eventData : new \Kisma\Core\Events\SeedEvent( $publisher, $eventData );

		$_event->setEventTag( $_eventTag );

		//	Call each handler in order
		if ( isset( self::$_eventMap[$_eventTag] ) && !empty( self::$_eventMap[$_eventTag] ) )
		{
			$_publisherId = $publisher->getId();

			Log::debug( '---- Publish "' . $_eventTag . '" from "' . $_publisherId . '"' );

			foreach ( self::$_eventMap[$_eventTag] as $_listenerIndex => $_listeners )
			{
				foreach ( $_listeners as $_subscriberId => $_closures )
				{
					/** @var $_closures \Closure[] */
					foreach ( Option::clean( $_closures ) as $_closure )
					{
						//	Stop further handling if the event has been kilt...
						if ( $_event->wasKilled() )
						{
							return true;
						}

						//	Generate an id...
						$_event->setEventId( self::generateEventId( $_event ) );

						//	Call the handler
						if ( is_string( $_closure ) || is_callable( $_closure ) )
						{
							//	Call the method
							$_result = call_user_func( $_closure, $_event );
//							Log::debug(
//								'-- "' . $publisher->getTag() . '" handler for "' . $_event->getEventTag() . '" called',
//								array(
//									'tag'     => $_subscriberId,
//									'result'  => print_r( $_result, true ),
//									'eventId' => $_event->getEventId(),
//								)
//							);
						}
						elseif ( is_array( $_closure ) && 1 == count( $_closure ) && $_closure[0] instanceof \Closure )
						{
							//	Call the closure...
							if ( false === $_closure[0]( $_event ) )
							{
								return false;
							}
						}
						else
						{
							//	Oops!
							throw new InvalidEventHandlerException( 'Event "' .
																	( is_object( $_closure[0] ) ? get_class( $_closure[0] ) : '<unknownClass>' ) .
																	'.' .
																	$_eventTag .
																	' has an invalid listener subscribed to it.' );
						}

						unset( $_closure );
					}

					unset( $_closures );
				}

				unset( $_listeners );
			}
		}

		return true;
	}

	/**
	 * @param object $subscriber
	 *
	 * @return bool|string
	 */
	public static function canPublish( $subscriber )
	{
		//	Publisher with an event manager?
		return ( $subscriber instanceof \Kisma\Core\Interfaces\Events\PublisherLike );
	}

	/**
	 * @param object $subscriber
	 *
	 * @return bool|string
	 */
	public static function isSubscriber( $subscriber )
	{
		//	A subscriber?
		return ( $subscriber instanceof SubscriberLike );
	}

	/**
	 * @param \Kisma\Core\Events\SeedEvent $event
	 *
	 * @return string
	 */
	public static function generateEventId( $event )
	{
		return hash( 'sha256', $event->getSource()->getId() . getmypid() . microtime( true ) ) . '.' . self::$_lastEventId++;
	}

	/**
	 * @return array The map of events to listeners
	 */
	public static function getEventMap()
	{
		return self::$_eventMap;
	}
}

/**
 * The Event Dispatcher.
 *
 * Example in command:
 *     $dispatcher = new EventDispatcher($this->getComposer(), $this->getApplication()->getIO());
 *     // ...
 *     $dispatcher->dispatch(ScriptEvents::POST_INSTALL_CMD);
 *     // ...
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Nils Adermann <naderman@naderman.de>
 */
class EventDispatcher
{
	protected $composer;
	protected $io;
	protected $loader;
	protected $process;

	/**
	 * Constructor.
	 *
	 * @param Composer        $composer The composer instance
	 * @param IOInterface     $io       The IOInterface instance
	 * @param ProcessExecutor $process
	 */
	public function __construct( Composer $composer, IOInterface $io, ProcessExecutor $process = null )
	{
		$this->composer = $composer;
		$this->io = $io;
		$this->process = $process ? : new ProcessExecutor( $io );
	}

	/**
	 * Dispatch an event
	 *
	 * @param string $eventName An event name
	 * @param Event  $event
	 */
	public function dispatch( $eventName, Event $event = null )
	{
		if ( null == $event )
		{
			$event = new Event( $eventName );
		}

		$this->doDispatch( $event );
	}

	/**
	 * Dispatch a script event.
	 *
	 * @param string       $eventName The constant in ScriptEvents
	 * @param Script\Event $event
	 */
	public function dispatchScript( $eventName, Script\Event $event = null )
	{
		if ( null == $event )
		{
			$event = new Script\Event( $eventName, $this->composer, $this->io );
		}

		$this->doDispatch( $event );
	}

	/**
	 * Dispatch a package event.
	 *
	 * @param string             $eventName The constant in ScriptEvents
	 * @param boolean            $devMode   Whether or not we are in dev mode
	 * @param OperationInterface $operation The package being installed/updated/removed
	 */
	public function dispatchPackageEvent( $eventName, $devMode, OperationInterface $operation )
	{
		$this->doDispatch( new PackageEvent( $eventName, $this->composer, $this->io, $devMode, $operation ) );
	}

	/**
	 * Dispatch a command event.
	 *
	 * @param string  $eventName The constant in ScriptEvents
	 * @param boolean $devMode   Whether or not we are in dev mode
	 */
	public function dispatchCommandEvent( $eventName, $devMode )
	{
		$this->doDispatch( new CommandEvent( $eventName, $this->composer, $this->io, $devMode ) );
	}

	/**
	 * Triggers the listeners of an event.
	 *
	 * @param  Event $event The event object to pass to the event handlers/listeners.
	 *
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	protected function doDispatch( Event $event )
	{
		$listeners = $this->getListeners( $event );

		foreach ( $listeners as $callable )
		{
			if ( !is_string( $callable ) && is_callable( $callable ) )
			{
				call_user_func( $callable, $event );
			}
			elseif ( $this->isPhpScript( $callable ) )
			{
				$className = substr( $callable, 0, strpos( $callable, '::' ) );
				$methodName = substr( $callable, strpos( $callable, '::' ) + 2 );

				if ( !class_exists( $className ) )
				{
					$this->io->write( '<warning>Class ' . $className . ' is not autoloadable, can not call ' . $event->getName() . ' script</warning>' );
					continue;
				}
				if ( !is_callable( $callable ) )
				{
					$this->io->write( '<warning>Method ' . $callable . ' is not callable, can not call ' . $event->getName() . ' script</warning>' );
					continue;
				}

				try
				{
					$this->executeEventPhpScript( $className, $methodName, $event );
				}
				catch ( \Exception $e )
				{
					$message = "Script %s handling the %s event terminated with an exception";
					$this->io->write( '<error>' . sprintf( $message, $callable, $event->getName() ) . '</error>' );
					throw $e;
				}
			}
			else
			{
				if ( 0 !== ( $exitCode = $this->process->execute( $callable ) ) )
				{
					$event->getIO()->write( sprintf( '<error>Script %s handling the %s event returned with an error</error>', $callable, $event->getName() ) );

					throw new \RuntimeException( 'Error Output: ' . $this->process->getErrorOutput(), $exitCode );
				}
			}

			if ( $event->isPropagationStopped() )
			{
				break;
			}
		}
	}

	/**
	 * @param string $className
	 * @param string $methodName
	 * @param Event  $event Event invoking the PHP callable
	 */
	protected function executeEventPhpScript( $className, $methodName, Event $event )
	{
		$className::$methodName( $event );
	}

	/**
	 * Add a listener for a particular event
	 *
	 * @param string   $eventName The event name - typically a constant
	 * @param Callable $listener  A callable expecting an event argument
	 * @param integer  $priority  A higher value represents a higher priority
	 */
	protected function addListener( $eventName, $listener, $priority = 0 )
	{
		$this->listeners[$eventName][$priority][] = $listener;
	}

	/**
	 * Adds object methods as listeners for the events in getSubscribedEvents
	 *
	 * @see EventSubscriberInterface
	 *
	 * @param EventSubscriberInterface $subscriber
	 */
	public function addSubscriber( EventSubscriberInterface $subscriber )
	{
		foreach ( $subscriber->getSubscribedEvents() as $eventName => $params )
		{
			if ( is_string( $params ) )
			{
				$this->addListener( $eventName, array( $subscriber, $params ) );
			}
			elseif ( is_string( $params[0] ) )
			{
				$this->addListener( $eventName, array( $subscriber, $params[0] ), isset( $params[1] ) ? $params[1] : 0 );
			}
			else
			{
				foreach ( $params as $listener )
				{
					$this->addListener( $eventName, array( $subscriber, $listener[0] ), isset( $listener[1] ) ? $listener[1] : 0 );
				}
			}
		}
	}

	/**
	 * Retrieves all listeners for a given event
	 *
	 * @param  Event $event
	 *
	 * @return array All listeners: callables and scripts
	 */
	protected function getListeners( Event $event )
	{
		$scriptListeners = $this->getScriptListeners( $event );

		if ( !isset( $this->listeners[$event->getName()][0] ) )
		{
			$this->listeners[$event->getName()][0] = array();
		}
		krsort( $this->listeners[$event->getName()] );

		$listeners = $this->listeners;
		$listeners[$event->getName()][0] = array_merge( $listeners[$event->getName()][0], $scriptListeners );

		return call_user_func_array( 'array_merge', $listeners[$event->getName()] );
	}

	/**
	 * Finds all listeners defined as scripts in the package
	 *
	 * @param  Event $event Event object
	 *
	 * @return array Listeners
	 */
	protected function getScriptListeners( Event $event )
	{
		$package = $this->composer->getPackage();
		$scripts = $package->getScripts();

		if ( empty( $scripts[$event->getName()] ) )
		{
			return array();
		}

		if ( $this->loader )
		{
			$this->loader->unregister();
		}

		$generator = $this->composer->getAutoloadGenerator();
		$packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
		$packageMap = $generator->buildPackageMap( $this->composer->getInstallationManager(), $package, $packages );
		$map = $generator->parseAutoloads( $packageMap, $package );
		$this->loader = $generator->createLoader( $map );
		$this->loader->register();

		return $scripts[$event->getName()];
	}

	/**
	 * Checks if string given references a class path and method
	 *
	 * @param  string $callable
	 *
	 * @return boolean
	 */
	protected function isPhpScript( $callable )
	{
		return false === strpos( $callable, ' ' ) && false !== strpos( $callable, '::' );
	}
}