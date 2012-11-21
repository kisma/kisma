<?php
/**
 * Scalar.php
 */
namespace Kisma\Core\Utility;
/**
 * Scalar
 * Scalar utility class
 */
class Scalar implements \Kisma\Core\Interfaces\UtilityLike
{
	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * Ensures the end of a string has only one of something
	 *
	 * @param string $search
	 * @param string $oneWhat
	 *
	 * @return string
	 */
	public static function trimSingle( $search, $oneWhat = ' ' )
	{
		return trim( $oneWhat . $search . $oneWhat, $oneWhat );
	}

	/**
	 * Ensures the end of a string has only one of something
	 *
	 * @param string $search
	 * @param string $oneWhat
	 *
	 * @return string
	 */
	public static function rtrimSingle( $search, $oneWhat = ' ' )
	{
		return rtrim( $search . $oneWhat, $oneWhat );
	}

	/**
	 * Ensures the front of a string has only one of something
	 *
	 * @param string $search
	 * @param string $oneWhat
	 *
	 * @return string
	 */
	public static function ltrimSingle( $search, $oneWhat = ' ' )
	{
		return ltrim( $oneWhat . $search, $oneWhat );
	}

	/**
	 * Multi-argument is_array helper
	 *
	 * Usage: is_array( $array1[, $array2][, ...])
	 *
	 * @param mixed      $possibleArray
	 * @param mixed|null $_ [optional]
	 *
	 * @return bool
	 */
	public static function is_array( $possibleArray, $_ = null )
	{
		foreach ( func_get_args() as $_argument )
		{
			if ( !is_array( $_argument ) )
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Prepend an array
	 *
	 * @param array  $array
	 * @param string $string
	 * @param bool   $deep
	 *
	 * @return array
	 */
	public static function array_prepend( $array, $string, $deep = false )
	{
		if ( empty( $array ) || empty( $string ) )
		{
			return $array;
		}

		foreach ( $array as $key => $element )
		{
			if ( is_array( $element ) )
			{
				if ( $deep )
				{
					$array[$key] = self::array_prepend( $element, $string, $deep );
				}
				else
				{
					trigger_error( 'array_prepend: array element', E_USER_WARNING );
				}
			}
			else
			{
				$array[$key] = $string . $element;
			}
		}

		return $array;
	}

	/**
	 * Takes a list of things and returns them in an array as the values. Keys are maintained.
	 *
	 * @param ...
	 *
	 * @return array
	 */
	public static function argsToArray()
	{
		$_array = array();

		foreach ( func_get_args() as $_key => $_argument )
		{
			$_array[$_key] = $_argument;
		}

		//	Return the fresh array...
		return $_array;
	}

	/**
	 * Returns the first non-empty argument or null if none found.
	 * Allows for multiple nvl chains. Example:
	 *
	 *<code>
	 *    if ( null !== Option::nvl( $x, $y, $z ) ) {
	 *        //    none are null
	 *    } else {
	 *        //    One of them is null
	 *    }
	 *
	 * IMPORTANT NOTE!
	 * Since PHP evaluates the arguments before calling a function, this is NOT a short-circuit method.
	 *
	 * @return mixed
	 */
	public static function nvl()
	{
		$_default = null;
		$_args = func_num_args();
		$_haystack = func_get_args();

		for ( $_i = 0; $_i < $_args; $_i++ )
		{
			if ( null !== ( $_default = Option::get( $_haystack, $_i ) ) )
			{
				break;
			}
		}

		return $_default;
	}

	/**
	 * Convenience "in_array" method. Takes variable args.
	 *
	 * The first argument is the needle, the rest are considered in the haystack. For example:
	 *
	 * Option::in( 'x', 'x', 'y', 'z' ) returns true
	 * Option::in( 'a', 'x', 'y', 'z' ) returns false
	 *
	 * @internal param mixed $needle
	 * @internal param mixed $haystack
	 *
	 * @return bool
	 */
	public static function in()
	{
		$_haystack = func_get_args();

		if ( !empty( $_haystack ) && count( $_haystack ) > 1 )
		{
			$_needle = array_shift( $_haystack );

			return in_array( $_needle, $_haystack );
		}

		return false;
	}

	/**
	 * Shortcut for str(i)pos
	 *
	 * @static
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @param bool   $caseSensitive
	 * @param int    $offset
	 *
	 * @return bool
	 */
	public static function within( $haystack, $needle, $offset = 0, $caseSensitive = false )
	{
		if ( false === $caseSensitive )
		{
			//	Case-insensitive
			return false !== stripos( $haystack, $needle, $offset );
		}

		//	Case-sensitive
		return false !== strpos( $haystack, $needle, $offset );
	}

	/**
	 * Takes the arguments and concatenates them with $separator in between.
	 *
	 * @param string $separator
	 *
	 * @return string
	 */
	public static function glue( $separator )
	{
		return implode( $separator, func_get_args() );
	}

}
