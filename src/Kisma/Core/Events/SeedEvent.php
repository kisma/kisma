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
namespace Kisma\Core\Events;

use Kisma\Core\Interfaces\PublisherLike;
use Kisma\Core\Interfaces\SeedLike;
use Kisma\Core\Interfaces\SubscriberLike;
use Kisma\Core\Utility\Inflector;
use Symfony\Component\EventDispatcher\Event;

/**
 * The base event class for Kisma
 *
 * It encapsulates the parameters associated with an event.
 * This object is modeled after jQuery's event object.
 *
 * If an event handler calls an event's stopPropagation() method, no further
 * listeners will be called.
 *
 * @author Jerry Ablan <jerryablan@gmail.com>
 */
class SeedEvent extends Event implements SeedLike
{
	//**************************************************************************
	//* Members
	//**************************************************************************

	/**
	 * @var string The type of event, or event name
	 */
	protected $_type = null;
	/**
	 * @var mixed An optional object of data passed to an event handler
	 */
	protected $_data = null;
	/**
	 * @var bool Set to true to stop the default action from being performed
	 */
	protected $_defaultPrevented = false;
	/**
	 * @var bool Set to true to stop the bubbling of events at any point
	 */
	protected $_propagationStopped = false;
	/**
	 * @var mixed The last value returned by an event handler that was triggered by this event, unless the value was null.
	 */
	protected $_result = null;
	/**
	 * @var int The time of the event. Unix timestamp returned from time()
	 */
	protected $_timestamp = null;
	/**
	 * @var PublisherLike The object that published this event (jQuery's event.target)
	 */
	protected $_publisher = null;
	/**
	 * @var SubscriberLike The current subscriber in the listener chain (jQuery's event.currentTarget)
	 */
	protected $_currentSubscriber = null;
	/**
	 * @var string A user-defined event ID
	 */
	protected $_id = null;

	//**************************************************************************
	//* Methods
	//**************************************************************************

	/**
	 * Constructor.
	 *
	 * @param \Kisma\Core\Interfaces\PublisherLike $publisher
	 * @param string                               $type
	 * @param mixed                                $data
	 *
	 * @internal param string $type
	 * @internal param \Kisma\Core\Interfaces\SeedLike $source
	 */
	public function __construct( PublisherLike $publisher, $type, $data = null )
	{
		$this->_id = spl_object_hash( $this );
		$this->_publisher = $publisher;
		$this->_data = $data;
		$this->_timestamp = time();
	}

	/**
	 * Tells the event manager to prevent the default action from being performed
	 */
	public function preventDefault()
	{
		$this->_defaultPrevented = true;
	}

	/**
	 * @return bool
	 */
	public function isDefaultPrevented()
	{
		return $this->_defaultPrevented;
	}

	/**
	 * @param \Kisma\Core\Interfaces\SubscriberLike $currentSubscriber
	 *
	 * @return SeedEvent
	 */
	public function setCurrentSubscriber( $currentSubscriber )
	{
		$this->_currentSubscriber = $currentSubscriber;

		return $this;
	}

	/**
	 * @return \Kisma\Core\Interfaces\SubscriberLike
	 */
	public function getCurrentSubscriber()
	{
		return $this->_currentSubscriber;
	}

	/**
	 * @param mixed $data
	 *
	 * @return SeedEvent
	 */
	public function setData( $data )
	{
		$this->_data = $data;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param string $id
	 *
	 * @return SeedEvent
	 */
	public function setId( $id )
	{
		$this->_id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return \Kisma\Core\Interfaces\PublisherLike
	 */
	public function getPublisher()
	{
		return $this->_publisher;
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->_result;
	}

	/**
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return Inflector::neutralize( $this->_type );
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_type;
	}
}
