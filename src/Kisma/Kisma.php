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
namespace Kisma;

use Kisma\Core\Enums\CoreSettings;
use Kisma\Core\Enums\Events\LifeEvents;
use Kisma\Core\Interfaces\PublisherLike;
use Kisma\Core\Utility\Detector;
use Kisma\Core\Utility\Option;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Kisma
 * Contains a few core functions implemented statically to be lightweight and single instance.
 */
class Kisma implements PublisherLike, EventSubscriberInterface
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string The current version
	 */
	const VERSION = '0.3.x-dev';
	/**
	 * @const string The tag for saving options
	 */
	const OPTIONS = 'kisma.options';

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var array The library configuration options
	 *            Container
	 */
	protected static $_options
		= array(
			CoreSettings::BASE_PATH          => __DIR__,
			CoreSettings::AUTOLOADER         => null,
			CoreSettings::INITIALIZED        => false,
			CoreSettings::VERSION            => self::VERSION,
			CoreSettings::DETECTED_FRAMEWORK => null,
			CoreSettings::EVENT_DISPATCHER   => null,
		);

	//**************************************************************************
	//* Methods
	//**************************************************************************

	/**
	 * Plant the seed of life into Kisma!
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public static function conceive( $options = array() )
	{
		//	Set any passed in options...
		if ( is_callable( $options ) )
		{
			$options = call_user_func( $options );
		}

		//	Load from session...
		static::__wakeup();

		//	Set any application-level options passed in
		static::$_options = Option::merge( static::$_options, $options );

		//	Register our faux-destructor
		if ( false === ( $_conceived = static::$_options[CoreSettings::INITIALIZED] ) )
		{
			\register_shutdown_function(
				function ( $type = LifeEvents::DEATH )
				{
					Kisma::__sleep();
					Kisma::getDispatcher()->dispatch( $type );
				}
			);

			//	Try and detect the framework being used...
			static::$_options[CoreSettings::DETECTED_FRAMEWORK] = Detector::framework();

			//	We done baby! Set this now to escape autoloader recursion hell
			static::$_options[CoreSettings::INITIALIZED] = $_conceived = true;

			//	If the composer autoloader has been started, get it...
			if ( !isset( static::$_options[CoreSettings::AUTOLOADER] ) && class_exists( '\\ComposerAutoloaderInit', false ) )
			{
				static::$_options[CoreSettings::AUTOLOADER] = \ComposerAutoloaderInit::getLoader();
			}

			//	And let the world know we're alive
			static::getDispatcher()->dispatch( LifeEvents::BIRTH );
		}

		return $_conceived;
	}

	/**
	 * Serialize
	 */
	public static function __sleep()
	{
		//	Save options out to session...
		if ( isset( $_SESSION ) )
		{
			$_SESSION[static::OPTIONS] = static::$_options;
		}
	}

	/**
	 * Deserialize
	 */
	public static function __wakeup()
	{
		//	Load options from session...
		if ( isset( $_SESSION, $_SESSION[static::OPTIONS] ) )
		{
			//	Merge them into the fold
			static::$_options = array_merge(
				$_SESSION[static::OPTIONS],
				static::$_options
			);
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function set( $key, $value = null )
	{
		Option::set( static::$_options, $key, $value );
	}

	/**
	 * @param string $key
	 * @param string $subKey
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function addTo( $key, $subKey, $value = null )
	{
		Option::addTo( static::$_options, $key, $subKey, $value );
	}

	/**
	 * @param string $key
	 * @param string $subKey
	 *
	 * @return mixed
	 */
	public static function removeFrom( $key, $subKey )
	{
		Option::removeFrom( static::$_options, $key, $subKey );
	}

	/**
	 * @param string $key If you pass in a null, you'll get the entire array of options
	 * @param mixed  $defaultValue
	 * @param bool   $removeIfFound
	 *
	 * @return mixed|array
	 */
	public static function get( $key, $defaultValue = null, $removeIfFound = false )
	{
		if ( null === $key )
		{
			return static::$_options;
		}

		return Option::get( static::$_options, $key, $defaultValue, $removeIfFound );
	}

	/**
	 * @param \Kisma\SeedEvent $event
	 *
	 * @return bool
	 */
	public static function onBirth( SeedEvent $event )
	{
		static::__wakeup();

		return true;
	}

	/**
	 * @param \Kisma\Core\Events\SeedEvent $event
	 *
	 * @return bool
	 */
	public static function onDeath( $event )
	{
		static::__sleep();

		return true;
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
	 */
	public static function setDispatcher( $dispatcher )
	{
		static::set( CoreSettings::EVENT_DISPATCHER, $dispatcher );
	}

	/**
	 * @return \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	public static function getDispatcher()
	{
		if ( null === ( $_dispatcher = static::get( CoreSettings::EVENT_DISPATCHER ) ) )
		{
			static::setDispatcher( $_dispatcher = new EventDispatcher() );
		}

		return $_dispatcher;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents()
	{
		return array(
			LifeEvents::BIRTH => 'onBirth',
			LifeEvents::DEATH => 'onDeath'
		);
	}
}
