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
namespace Kisma\Core\Enums;

use Kisma\Core\Utility\Inflector;
use Kisma\Core\Utility\Option;

/**
 * SeedEnum
 * This is the non-SplEnum version
 */
abstract class SeedEnum
{
	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var array The cache for quick lookups
	 */
	protected static $_constants = null;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Returns the default value for this enum if called as a function: $_x = SeedEnum()
	 */
	public function __invoke()
	{
		return static::defines( '__default', true );
	}

	/**
	 * Returns all my constants as an array of Name => Value pairs
	 *
	 * @param bool $includeDefault Whether to include __default property.
	 * @param bool $flipped        If true, array is flipped to Value => Name pairs before return
	 * @param bool $overwrite
	 *
	 * @return array
	 */
	public static function getConstList( $includeDefault = true, $flipped = false, $overwrite = false )
	{
		$_key = static::_cacheKey( $_class = get_called_class() );

		if ( $overwrite || null === ( $_constants = Option::get( static::$_constants, $_key ) ) )
		{
			$_mirror = new \ReflectionClass( $_class );
			$_constants = $_mirror->getConstants();
			unset( $_mirror );

			if ( false === $includeDefault && isset( $_constants['__default'] ) )
			{
				unset( $_constants['__default'] );
			}

			static::$_constants[$_key] = $_constants;
		}

		return $flipped ? array_flip( $_constants ) : $_constants;
	}

	/**
	 * Gets a guaranteed cache key
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected static function _cacheKey( $class = null )
	{
		static $_key = null;

		return $_key ? : Inflector::neutralize( $class ? : \get_called_class(), true );
	}

	/**
	 * Returns a hash of the called class's constants ( CONSTANT_NAME => value ). Caches for speed
	 * (class cache hash, say that ten times fast!).
	 *
	 * @param bool   $flipped If true, the array is flipped before return ( value => CONSTANT_NAME )
	 * @param string $class   Used internally to cache constants
	 * @param bool   $pretty  If true, the constant names themselves are cleaned up for display purposes.
	 *
	 * @return array
	 */
	public static function getDefinedConstants( $flipped = false, $class = null, $pretty = false )
	{
		$_constants = static::getConstList( !$pretty, !$pretty ? $flipped : false );

		if ( !$pretty )
		{
			return $_constants;
		}

		$_work = $flipped ? $_constants : array_flip( $_constants );

		array_walk(
			$_work,
			function ( &$value )
			{
				$value = Inflector::display( $value );
			}
		);

		return $flipped ? $_constants : array_flip( $_constants );
	}

	/**
	 * Returns true or false if this class contains a specific constant VALUE (not the name).
	 *
	 * Use for validity checks:
	 *
	 *    if ( false === VeryCoolShit::contains( $evenCoolerShit ) ) {
	 *        throw new \InvalidArgumentException( 'Sorry, your selection of "' . $evenCoolerShit . '" is invalid.' );
	 *    }
	 *
	 * @param mixed $value      The constant's value
	 * @param bool  $returnName If true, returns the name of the constant if found, but throws an exception if not
	 *
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public static function contains( $value, $returnName = false )
	{
		$_constants = static::getConstList( true, true );
		$_has = isset( $_constants[$value] );

		if ( false === $_has && false !== $returnName )
		{
			throw new \InvalidArgumentException( 'The constant value "' . $value . '" is not defined.' );
		}

		return $returnName ? $_constants[$value] : $_has;
	}

	/**
	 * Returns true or false if this class defines a specific constant NAME.
	 * Optionally returns the value of the constant, but throws an
	 * exception if not found.
	 *
	 * Use for validity checks:
	 *
	 *    if ( false === VeryCoolShit::defines( $evenCoolerShit ) ) {
	 *        throw new \InvalidArgumentException( 'Sorry, your selection of "' . $evenCoolerShit . '" is invalid.' );
	 *    }
	 *
	 * @param string $constant    The constant name
	 * @param bool   $returnValue If true, returns the value of the constant if found, but throws an exception if not
	 *
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public static function defines( $constant, $returnValue = false )
	{
		$_constants = static::getConstList();
		$_has = isset( $_constants[$constant] );

		if ( false === $_has && false !== $returnValue )
		{
			throw new \InvalidArgumentException( 'The constant "' . $constant . '" is not defined.' );
		}

		return $returnValue ? $_constants[$constant] : $_has;
	}

	/**
	 * Given a constant VALUE, return the constant NAME as a string
	 *
	 * @param string $constant
	 * @param bool   $flipped If false, $constant should contain the constant name and the value will be returned
	 * @param bool   $pretty  If true, returned value is prettified (acme.before_event becomes "Acme Before Event")
	 *
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public static function nameOf( $constant, $flipped = true, $pretty = false )
	{
		$_name = $flipped ? static::contains( $constant, true ) : static::defines( $constant, true );

		return $pretty ? Inflector::display( $_name ) : $_name;
	}

	/**
	 * @param mixed $constant
	 * @param bool  $flipped
	 *
	 * @return string
	 */
	public static function prettyNameOf( $constant, $flipped = true )
	{
		return static::nameOf( $constant, $flipped, true );
	}

}
