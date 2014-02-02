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

/**
 * Option
 * Super kick-ass class to manipulate array and object properties in a uniform manner
 */
class Option
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param array  $options
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function contains( &$options = array(), $key )
	{
		$_key = static::_cleanKey( $key );

		//	Check both the raw and cooked keys
		return
			( static::arrayLike( $options ) &&
				( array_key_exists( $key, $options ) || array_key_exists( $_key, $options ) || array_key_exists( ucfirst( $_key ), $options ) ) ) ||
			( is_object( $options ) && ( property_exists( $options, $key ) || property_exists( $options, $_key ) ) );
	}

	/**
	 * @param array $options
	 * @param array $keys
	 * @param mixed $defaultValue
	 *
	 * @return array
	 */
	public static function getMany( &$options = array(), $keys, $defaultValue = null )
	{
		$_results = array();
		$_keys = static::collapse( $keys, $defaultValue );

		foreach ( $_keys as $_key )
		{
			$_results[$_key] = static::get( $options, $_key, $defaultValue );
		}

		return $_results;
	}

	/**
	 * Standard way to see if an object has array access (i.e. $object['key'])
	 *
	 * @param mixed $object
	 *
	 * @return bool
	 */
	public static function arrayLike( $object )
	{
		return ( is_array( $object ) || $object instanceof \ArrayAccess );
	}

	/**
	 * Retrieves a a value from the given object.
	 * If $key is not set, the value is set to $defaultValue and returned.
	 * Optionally will unset option in array.
	 *
	 * @param object     $object
	 * @param string     $key
	 * @param mixed|null $defaultValue
	 * @param boolean    $unsetValue
	 *
	 * @return mixed
	 */
	public static function getFromObject( &$object, $key, $defaultValue = null, $unsetValue = false )
	{
		$_key = $key;
		$_cleanKey = static::_cleanKey( $key );
		$_newValue = $defaultValue;

		if ( $_key != $_cleanKey && !property_exists( $object, $_key ) )
		{
			if ( property_exists( $object, $_cleanKey ) )
			{
				$_key = $_cleanKey;
			}
			else if ( property_exists( $object, ucfirst( $_cleanKey ) ) )
			{
				$_key = ucfirst( $_cleanKey );
			}
		}

		if ( property_exists( $object, $_key ) )
		{
			$_newValue = $object->{$_key};

			if ( false !== $unsetValue )
			{
				unset( $object->{$_key} );
			}

			return $_newValue;
		}

		if ( method_exists( $object, 'get' . $key ) )
		{
			$_method = 'get' . $_key;
		}
		else if ( method_exists( $object, 'get_' . $_cleanKey ) )
		{
			$_method = 'get_' . $_cleanKey;
		}
		else
		{
			//	Nada...
			return $_newValue;
		}

		$_getter = $_method;
		$_setter = 's' . substr( $_method, 1 );
		$_newValue = $object->{$_getter}();

		if ( false !== $unsetValue && method_exists( $object, $_setter ) )
		{
			$object->{$_setter}( null );
		}

		return $_newValue;
	}

	/**
	 * Retrieves a a value from the given object.
	 * If $key is not set, the value is set to $defaultValue and returned.
	 * Optionally will unset option in array.
	 *
	 * @param object $object
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setObjectValue( &$object, $key, $value = null )
	{
		$_key = $key;
		$_cleanKey = static::_cleanKey( $key );

		if ( $_key != $_cleanKey && !property_exists( $object, $_key ) )
		{
			if ( property_exists( $object, $_cleanKey ) )
			{
				$_key = $_cleanKey;
			}
			else if ( property_exists( $object, ucfirst( $_cleanKey ) ) )
			{
				$_key = ucfirst( $_cleanKey );
			}
		}

		if ( property_exists( $object, $_key ) )
		{
			$object->{$_key} = $value;
		}
		else
		{
			if ( method_exists( $object, 'set' . $key ) )
			{
				$_method = 'set' . $_key;
			}
			else if ( method_exists( $object, 'set_' . $_cleanKey ) )
			{
				$_method = 'set_' . $_cleanKey;
			}
			else
			{
				//	Nada...
				return;
			}

			$_setter = $_method;
			$object->{$_setter}( $value );
		}
	}

	/**
	 * Retrieves an option from the given array. $defaultValue is set and returned if $_key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * @param array|\ArrayAccess $options
	 * @param string             $key
	 * @param mixed              $value
	 */
	public static function setArrayValue( &$options, $key, $value = null )
	{
		$_key = $key;
		$_cleanKey = static::_cleanKey( $key );

		//	Check for the original key too
		if ( $_key != $_cleanKey && !array_key_exists( $_key, $options ) )
		{
			if ( array_key_exists( $_cleanKey, $options ) )
			{
				$_key = $_cleanKey;
			}
			else if ( array_key_exists( ucfirst( $_cleanKey ), $options ) )
			{
				$_key = ucfirst( $_cleanKey );
			}
		}

		$options[$_key] = $value;
	}

	/**
	 * Retrieves an option from the given array. $defaultValue is set and returned if $_key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * @param array|\ArrayAccess|object $options
	 * @param string                    $key
	 * @param mixed|null                $defaultValue
	 * @param boolean                   $unsetValue
	 *
	 * @return mixed
	 */
	public static function getFromArray( &$options, $key, $defaultValue = null, $unsetValue = false )
	{
		$_key = $key;
		$_cleanKey = static::_cleanKey( $key );
		$_newValue = $defaultValue;

		//	Check for the original key too
		if ( $_key != $_cleanKey && !array_key_exists( $_key, $options ) )
		{
			if ( array_key_exists( $_cleanKey, $options ) )
			{
				$_key = $_cleanKey;
			}
			else if ( array_key_exists( ucfirst( $_cleanKey ), $options ) )
			{
				$_key = ucfirst( $_cleanKey );
			}
		}

		if ( array_key_exists( $_key, $options ) )
		{
			$_newValue = $options[$_key];

			if ( false !== $unsetValue )
			{
				unset( $options[$_key] );
			}
		}

		return $_newValue;
	}

	/**
	 * Retrieves an option from the given array. $defaultValue is set and returned if $_key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * @param array|\ArrayAccess|object $options
	 * @param string                    $key
	 * @param mixed|null                $defaultValue
	 * @param boolean                   $unsetValue
	 *
	 * @return mixed
	 */
	public static function get( &$options = array(), $key, $defaultValue = null, $unsetValue = false )
	{
		if ( is_array( $key ) )
		{
			return static::getMany( $options, $key, $defaultValue, $unsetValue );
		}

		//	Set the default value
		$_newValue = $defaultValue;

		//	Get object value if one
		if ( is_object( $options ) )
		{
			return static::getFromObject( $options, $key, $defaultValue, $unsetValue );
		}

		//	Get array value if it exists
		if ( static::arrayLike( $options ) )
		{
			return static::getFromArray( $options, $key, $defaultValue, $unsetValue );
		}

		//	Otherwise just return the default value
		return $_newValue;
	}

	/**
	 * Sets an value in the given array at key.
	 *
	 * @param array|\ArrayAccess|object $options
	 * @param string|array              $key Pass a single key or an array of KVPs
	 * @param mixed|null                $value
	 *
	 * @return array|string
	 */
	public static function set( &$options = array(), $key, $value = null )
	{
		$_options = static::collapse( $key, $value );

		foreach ( $_options as $_key => $_value )
		{
			if ( is_object( $options ) )
			{
				static::setObjectValue( $options, $_key, $_value );
				continue;
			}

			//	Set array value if it exists
			if ( static::arrayLike( $options ) )
			{
				static::setArrayValue( $options, $_key, $_value );
				continue;
			}
		}
	}

	/**
	 * @param array|\ArrayAccess|object $options
	 * @param string                    $key
	 * @param string                    $subKey
	 * @param mixed                     $defaultValue Only applies to target value
	 * @param boolean                   $unsetValue   Only applies to target value
	 *
	 * @return mixed
	 */
	public static function getDeep( &$options = array(), $key, $subKey, $defaultValue = null, $unsetValue = false )
	{
		$_deep = static::get( $options, $key, array() );

		return static::get( $_deep, $subKey, $defaultValue, $unsetValue );
	}

	/**
	 * Retrieves a boolean option from the given array. $defaultValue is set and returned if $_key is not 'set'.
	 * Optionally will unset option in array.
	 *
	 * Returns TRUE for "1", "true", "on", "yes" and "y". Returns FALSE otherwise.
	 *
	 * @param array|\ArrayAccess|object $options
	 * @param string                    $key
	 * @param boolean                   $defaultValue Defaults to false
	 * @param boolean                   $unsetValue
	 *
	 * @return mixed
	 */
	public static function getBool( &$options = array(), $key, $defaultValue = false, $unsetValue = false )
	{
		return Scalar::boolval( static::get( $options, $key, $defaultValue, $unsetValue ) );
	}

	/**
	 * Adds a value to a property array
	 *
	 * @param array  $source
	 * @param string $key
	 * @param string $subKey
	 * @param mixed  $value
	 *
	 * @return array The new array
	 */
	public static function addTo( &$source, $key, $subKey, $value = null )
	{
		$_target = static::clean( static::get( $source, $key, array() ) );
		static::set( $_target, $subKey, $value );
		static::set( $source, $key, $_target );

		return $_target;
	}

	/**
	 * Removes a value from a property array
	 *
	 * @param array  $source
	 * @param string $key
	 * @param string $subKey
	 *
	 * @return mixed The original value of the removed key
	 */
	public static function removeFrom( &$source, $key, $subKey )
	{
		$_target = static::clean( static::get( $source, $key, array() ) );
		$_result = static::remove( $_target, $subKey );
		static::set( $source, $key, $_target );

		return $_result;
	}

	/**
	 * Unsets an option in the given array
	 *
	 * @param array|\ArrayAccess|object $options
	 * @param string                    $key
	 *
	 * @return mixed
	 */
	public static function remove( &$options = array(), $key )
	{
		if ( static::arrayLike( $options ) )
		{
			return static::getFromArray( $options, $key, null, true );
		}

		if ( is_object( $options ) )
		{
			return static::getFromObject( $options, $key, null, true );
		}

		return null;
	}

	/**
	 * Ensures the argument passed in is actually an array with optional iteration callback
	 *
	 * @static
	 *
	 * @param array             $array
	 * @param callable|\Closure $callback
	 *
	 * @return array
	 */
	public static function clean( $array = null, $callback = null )
	{
		$_result = ( empty( $array ) ? array() : ( !is_array( $array ) ? array( $array ) : $array ) );

		if ( null === $callback || !is_callable( $callback ) )
		{
			return $_result;
		}

		$_response = array();

		foreach ( $_result as $_item )
		{
			$_response[] = call_user_func( $callback, $_item );
		}

		return $_response;
	}

	/**
	 * Converts $key and $value into array($key => $value) if $key is not already an array.
	 *
	 * @static
	 *
	 * @param string|array $key
	 * @param mixed        $value
	 *
	 * @return array
	 */
	public static function collapse( $key, $value = null )
	{
		return ( is_array( $key ) && null === $value )
			? $key
			: array(
				$key => $value
			);
	}

	/**
	 * Merge one or more arrays but ensures each is an array. Basically an idiot-proof array_merge
	 *
	 * @param array $target The destination array
	 *
	 * @return array The resulting array
	 * @return array
	 */
	public static function merge( $target )
	{
		$_arrays = static::clean( func_get_args() );
		$_target = static::clean( array_shift( $_arrays ) );

		foreach ( $_arrays as $_array )
		{
			$_target = array_merge(
				$_target,
				static::clean( $_array )
			);

			unset( $_array );
		}

		unset( $_arrays );

		return $_target;
	}

	/**
	 * Wrapper for a static::get on $_SERVER
	 *
	 * @param string $key
	 * @param string $defaultValue
	 * @param bool   $unsetValue
	 *
	 * @return mixed
	 */
	public static function server( $key, $defaultValue = null, $unsetValue = false )
	{
		return static::get( $_SERVER, $key, $defaultValue, $unsetValue );
	}

	/**
	 * Wrapper for a static::get on $_REQUEST
	 *
	 * @param string   $key
	 * @param string   $defaultValue
	 * @param bool|int $filter
	 *
	 * @return mixed
	 */
	public static function request( $key, $defaultValue = null, $filter = FILTER_SANITIZE_STRING )
	{
		$_value = FilterInput::request( $key, $defaultValue, $filter ? : FILTER_SANITIZE_STRING );

		return empty( $_value ) ? null : $_value;
	}

	/**
	 * Sets a value within an array only if the value is not set (SetIfNotSet=SINS).
	 * You can pass in an array of key value pairs and do many at once.
	 *
	 * @param \stdClass|array $options
	 * @param string          $key
	 * @param mixed           $value
	 */
	public static function sins( &$options = array(), $key, $value = null )
	{
		//	Accept an array as input or single KVP
		if ( !is_array( $key ) )
		{
			$key = array( $key => $value );
		}

		foreach ( $key as $_key => $_value )
		{
			if ( !static::contains( $options, $_key ) )
			{
				static::set( $options, $_key, $_value );
			}
		}
	}

	/**
	 * Converts key to a neutral format if not already...
	 *
	 * @param string $key
	 * @param bool   $opposite If true, the key is switched back to it's neutral or deneutral format
	 *
	 * @return string
	 */
	protected static function _cleanKey( $key, $opposite = true )
	{
		if ( $key == ( $_cleaned = Inflector::neutralize( $key ) ) )
		{
			if ( false !== $opposite )
			{
				return Inflector::deneutralize( $key, true );
			}
		}

		return $_cleaned;
	}
}
