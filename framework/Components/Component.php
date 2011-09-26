<?php
/**
 * Kisma(tm) : PHP Nanoframework (http://github.com/Pogostick/kisma/)
 * Copyright 2011, Pogostick, LLC. (http://www.pogostick.com/)
 *
 * Dual licensed under the MIT License and the GNU General Public License (GPL) Version 2.
 * See {@link http://github.com/Pogostick/kisma/licensing/} for complete information.
 *
 * @copyright		Copyright 2011, Pogostick, LLC. (http://www.pogostick.com/)
 * @link			http://github.com/Pogostick/kisma/ Kisma(tm)
 * @license			http://github.com/Pogostick/kisma/licensing/
 * @author			Jerry Ablan <kisma@pogostick.com>
 * @category		Kisma_Components
 * @package			kisma.components
 * @namespace		\Kisma\Components
 * @since			v1.0.0
 * @filesource
 */

//*************************************************************************
//* Namespace Declarations
//*************************************************************************

/**
 * @namespace Kisma\Components Kisma components
 */
namespace Kisma\Components;

/**
 * Convenience alias for the Kisma helpers
 * @see \Kisma\Kisma
 */
use \Kisma\Kisma as K;

/**
 * Component
 * The womb within...
 *
 * @property array $options
 * @property Event[] $events
 * @property Aspect[] $aspects
 * @property \Exception[] $errors
 * @property int $index
 * @property-read int $count
 * @property bool $skipNext
 * @property bool $readOnly
 * @property bool $logging
 * @property string $eventHandlerSignature
 */
class Component implements \Kisma\IKisma, \Kisma\IAspectable, \Kisma\IOptions, \Countable, \Iterator
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/***
	 * @var array This object's options
	 */
	protected $_options = array();
	/**
	 * @var \Kisma\Events\Event[] This component's events
	 */
	protected $_events = array();
	/**
	 * @var \Kisma\Aspects\Aspect[] This object's aspects
	 */
	protected $_aspects = array();
	/**
	 * @var \Exception[]
	 */
	protected $_errors = array();
	/**
	 * @var integer Iteration index
	 */
	protected $_index = 0;
	/**
	 * @var integer Holds the number of settings we have
	 */
	protected $_count = 0;
	/**
	 * @var boolean Used when un-setting values during iteration to ensure we do not skip the next element
	 */
	protected $_skipNext = false;
	/**
	 * @var boolean If true, configuration settings cannot be changed once loaded
	 */
	protected $_readOnly = true;
	/**
	 * @var bool|int The logging flags for this object
	 */
	protected $_logging = true;
	/**
	 * @var string The prefix of a method indicating it is an event handler
	 */
	protected $_eventHandlerSignature = 'on';

	//*************************************************************************
	//* Default/Magic Methods
	//*************************************************************************

	/**
	 * The base component constructor
	 * @param array $options
	 * @return \Kisma\Components\Component
	 */
	public function __construct( $options = array() )
	{
		//	Configure our properties
		$this->_loadConfiguration( $options, true );

		//	Auto-bind events, remove from $options
		if ( false !== $this->getOption( 'auto_bind', true ) )
		{
			$this->autoBindEvents( $this->getOption( 'auto_bind_options', array(), true ) );
		}

		//	Set our count...
		$this->_count = count( $this->_options );

		//	Trigger our afterConstruct event
		$this->trigger( 'after_constructor' );
	}

	/**
	 * Allow calling Aspect methods from the object
	 * @throws \BadMethodCallException
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call( $method, $arguments )
	{
		//	See if any of our aspects have this method
		foreach ( $this->_aspects as $_aspect )
		{
			//	Call aspect methods if they exist
			if ( method_exists( $_aspect, $method ) )
			{
				return call_user_func_array( array( $_aspect, $method ), $arguments );
			}
		}

		//	Guess not...
		throw new \BadMethodCallException( __CLASS__ . '::' . $method . ' is undefined.' );
	}

	//*************************************************************************
	//* Event Handling Methods
	//*************************************************************************

	/**
	 * Looks for event handler signatures and auto-binds the events.
	 * Event handler signatures start with the word 'on'.
	 *
	 * @param array $options
	 * @return bool
	 */
	public function autoBindEvents( $options = array() )
	{
		$_mirror = new \ReflectionClass( $this );

		//	See if there are any events that should be ignored
		$_ignores = K::o( $options, 'ignore_events', array() );

		//	Clean up the ignore list
		array_walk( $_ignores, function( &$ignore ) {
			$ignore = K::standardizeName( $ignore );
		});

		//	Check each method for the event handler signature
		foreach ( $_mirror->getMethods() as $_method )
		{
			/** @noinspection PhpUndefinedFieldInspection */
			$_realMethodName = $_method->name;
			$_stub = strtolower( substr( $_realMethodName, 0, strlen( $this->_eventHandlerSignature ) ) );

			if ( $_stub == $this->_eventHandlerSignature )
			{
				//	Standardize the event name and, if not ignored, bind it
				$_eventId = K::standardizeName( substr( $_realMethodName, strlen( $this->_eventHandlerSignature ) ) );

				if ( !in_array( $_eventId, $_ignores ) )
				{
					//	Bind it like a binder!
					$this->bind(
						$_eventId,
						array(
							$this,
							$_realMethodName,
						)
					);
				}
			}
		}
	}

	/**
	 * @param string $name
	 * @param callback $callback
	 * @return \Kisma\Events\Event
	 */
	public function bind( $name, $callback )
	{
		$name = K::standardizeName( $name );

		if ( !isset( $this->_events[$name] ) )
		{
			//	Create a new event
			$this->_events[$name] = new \Kisma\Events\Event(
				array(
					'source' => $this,
				)
			);
		}

		return $this->_events[$name]->addHandler( $callback );
	}

	/**
	 * @param string $name
	 * @param callback $callback
	 * @return \Kisma\Components\Component
	 */
	public function unbind( $name, $callback )
	{
		$name = K::standardizeName( $name );

		foreach ( $this->_events as $_eventId => $_handler )
		{
			if ( $name == $_eventId )
			{
				return $this->_events[$name]->removeHandler( $callback );
			}
		}

		return false;
	}

	/**
	 * @param string $eventId
	 * @param mixed $data
	 * @param callback $callback
	 * @return bool
	 */
	public function trigger( $eventId, $data = null, $callback = null )
	{
		//	Now, see what's what
		if ( !is_string( $eventId ) )
		{
			throw new EventException( 'Event name specified is not a string, and therefore does not exist.' );
		}
		
		$_eventId = K::standardizeName( $eventId );
		
		if ( isset( $this->_events, $this->_events[$_eventId] ) )
		{
			$_callback = ( $callback ?: null );
			$_data = ( $data ?: null );
			
			//	Do we have a callback?
			if ( null === $_callback )
			{
				if ( null !== $data )
				{
					if ( is_callable( $_data ) )
					{
						//	This is a callback...
						$_callback = $_data;
						$_data = null;
					}
					else
					{
						//	Data is data, no callback...
						$_callback = null;
					}
				}
			}
			
			//	Return the result of the event propagation
			return $this->_events[$_eventId]->fireEvent( $_data, $_callback );
		}

		//	We don't have that event, return false.
		return false;
	}

	//*************************************************************************
	//* Aspect Handling Methods
	//*************************************************************************

	/**
	 * Link an aspect to this component
	 * @param string $aspectClass
	 * @param array $options
	 * @return \Kisma\Aspects\Aspect
	 */
	public function linkAspect( $aspectClass, $options = array() )
	{
		$_aspectClass = K::standardizeName( $aspectClass );

		/** @var $_aspect \Kisma\Aspects\Aspect */
		if ( null !== ( $this->_aspects[$_aspectClass] = K::createComponent( $_aspectClass, $options ) ) )
		{
			$this->_aspects[$_aspectClass]->link( $this );
		}
	}

	/**
	 * Links multiple aspects to this component. Pass array of aspect options
	 * indexed by aspect class name.
	 *
	 * @param array $aspects
	 * @return \Kisma\Components\Component
	 */
	public function linkAspects( $aspects = array() )
	{
		foreach ( $aspects as $_aspectClass => $_options )
		{
			$this->linkAspect( $_aspectClass, $_options );
		}

		return $this;
	}

	/**
	 * Unlinks all aspects from this component.
	 * @return \Kisma\Components\Component
	 */
	public function unlinkAspects()
	{
		foreach ( $this->_aspects as $_aspectClass => $_aspect )
		{
			$this->unlinkAspect( $_aspectClass );
			unset( $this->_aspects[$_aspectClass] );
		}

		//	Make a fresh array
		$this->_aspects = array();
		return $this;
	}

	/**
	 * Unlinks an aspect from this component
	 * @param string $aspectClass
	 * @return bool
	 * @see Aspect
	 */
	public function unlinkAspect( $aspectClass )
	{
		$aspectClass = K::standardizeName( $aspectClass );

		if ( isset( $this->_aspects[$aspectClass] ) )
		{
			$this->_aspects[$aspectClass]->unlink( $this );
			unset( $this->_aspects[$aspectClass] );
			return true;
		}

		return false;
	}

	//*************************************************************************
	//* Private Methods 
	//*************************************************************************

	/**
	 * Loads an array into properties if they exist.
	 * @param array $options
	 * @param bool $noMerge If true, this object's options will be cleared first
	 * @return $this
	 */
	protected function _loadConfiguration( $options = array(), $noMerge = false )
	{
		$_options = array();

		//	Catch null input...
		if ( null === $options || !is_array( $options ) || empty( $options ) )
		{
			$options = array();
		}

		//	Loop through, set...
		foreach ( $options as $_key => $_value )
		{
			try
			{
				K::__property( $this, $_key, \Kisma\AccessorMode::Set, $_value );
			}
			catch ( \Kisma\UndefinedPropertyException $_ex )
			{
				//	Undefined, add to options...
				$_options[$_key] = $_value;
			}
		}

		if ( $noMerge )
		{
			//	Overwrite the options...
			$this->_options = $_options;
		}
		else
		{
			//	Merge the options...
			$this->_options = array_merge(
				$this->_options,
				$_options
			);
		}
	}

	//*************************************************************************
	//* Interface Methods
	//*************************************************************************

	/**
	 * Required by Countable interface
	 * @return int
	 */
	public function count()
	{
		return $this->_count;
	}

	/**
	 * Required by Iterator interface
	 * @return mixed
	 */
	public function current()
	{
		$this->_skipNext = false;
		return current( $this->_options );
	}

	/**
	 * Required by Iterator interface
	 * @return mixed
	 */
	public function key()
	{
		return key( $this->_options );
	}

	/**
	 * Required by Iterator interface
	 */
	public function next()
	{
		if ( $this->_skipNext )
		{
			$this->_skipNext = false;
			return;
		}

		next( $this->_options );

		$this->_index++;
	}

	/**
	 * Required by Iterator interface
	 */
	public function rewind()
	{
		$this->_skipNext = false;
		reset( $this->_options );
		$this->_index = 0;
	}

	/**
	 * Required by Iterator interface
	 * @return boolean
	 */
	public function valid()
	{
		return ( $this->_index < $this->_count );
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param array $aspects
	 * @return \Kisma\Components\Component
	 */
	public function setAspects( $aspects = array() )
	{
		$this->_aspects = $aspects;
		return $this;
	}

	/**
	 * @return \Kisma\Aspects\Aspect[]
	 */
	public function getAspects()
	{
		return $this->_aspects;
	}

	/**
	 * @param array $errors
	 * @return \Kisma\Components\Component
	 */
	public function setErrors( $errors = array() )
	{
		$this->_errors = $errors;
		return $this;
	}

	/**
	 * @return \Kisma\Components\Exception[]
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * @param \Kisma\Events\Event[] $events
	 * @return \Kisma\Components\Component
	 */
	public function setEvents( $events = array() )
	{
		$this->_events = $events;
		return $this;
	}

	/**
	 * @param string|null $eventId
	 * @return \Kisma\Components\Event[]
	 */
	public function getEvents( $eventId = null )
	{
		if ( null !== $eventId )
		{
			$_filteredEvents = array();
			$_eventId = K::standardizeName( $eventId );

			foreach ( $this->_events as $_name => $_handler )
			{
				if ( $_name == $_eventId )
				{
					$_filteredEvents[$_name][] = $_handler;
				}
			}

			return $_filteredEvents;
		}

		return $this->_events;
	}

	/**
	 * @param int $index
	 * @return \Kisma\Components\Component $this
	 */
	public function setIndex( $index )
	{
		$this->_index = $index;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getIndex()
	{
		return $this->_index;
	}

	/**
	 * @param bool $logging
	 * @return \Kisma\Components\Component
	 */
	public function setLogging( $logging = false )
	{
		$this->_logging = $logging;
		return $this;
	}

	/**
	 * @return bool|int
	 */
	public function getLogging()
	{
		return $this->_logging;
	}

	/**
	 * @param int $count
	 * @return \Kisma\Components\Component $this
	 */
	protected function _setCount( $count )
	{
		$this->_count = $count;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->_count;
	}

	/**
	 * sets all options at once
	 * @param array $options
	 * @return \Kisma\Components\Component $this
	 */
	public function setOptions( $options = array() )
	{
		//	Bulk set all options
		foreach ( $options as $_key => $_value )
		{
			$this->_options[$_key] = $_value;
		}

		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed|null $value
	 * @return mixed
	 */
	public function setOption( $name, $value = null )
	{
		$this->_options[$name] = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * @param string $name
	 * @param mixed|null $defaultValue
	 * @param bool $deleteAfter If true, key is removed from the option list after it is read.
	 * @return mixed
	 */
	public function getOption( $name, $defaultValue = null, $deleteAfter = false )
	{
		return K::o( $this->_options, $name, $defaultValue, $deleteAfter );
	}

	/**
	 * @param boolean $readOnly
	 * @return \Kisma\Components\Component $this
	 */
	public function setReadOnly( $readOnly = true )
	{
		$this->_readOnly = $readOnly;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getReadOnly()
	{
		return $this->_readOnly;
	}

	/**
	 * @param boolean $skipNext
	 * @return \Kisma\Components\Component $this
	 */
	public function setSkipNext( $skipNext )
	{
		$this->_skipNext = $skipNext;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSkipNext()
	{
		return $this->_skipNext;
	}

	/**
	 * @param string $eventHandlerSignature
	 * @return \Kisma\Components\Component $this
	 */
	public function setEventHandlerSignature( $eventHandlerSignature = 'on' )
	{
		$this->_eventHandlerSignature = $eventHandlerSignature;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEventHandlerSignature()
	{
		return $this->_eventHandlerSignature;
	}

}