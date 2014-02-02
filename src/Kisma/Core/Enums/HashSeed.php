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

/**
 * HashSeed
 * The various supported hash types for the Hash utility class
 */
class HashSeed extends SeedEnum
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int Default value
	 */
	const __default = self::ALL;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALL = 0;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA_LOWER = 1;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA_UPPER = 2;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA = 3;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA_NUMERIC = 4;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA_LOWER_NUMERIC = 5;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const NUMERIC = 6;
	/**
	 * @const int The various supported hash types for Utility\Hash
	 */
	const ALPHA_LOWER_NUMERIC_IDIOT_PROOF = 7;
	/**
	 * @var int The maximum default length for unique hash generations
	 */
	const MAX_HASH_LENGTH = 128;

	//*************************************************************************
	//	Members
	//*************************************************************************

	/**
	 * @var array The various hash seeds used by this class
	 */
	protected static $_hashSeeds
		= array(
			self::ALL                             => array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			self::ALPHA_LOWER                     => array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z'
			),
			HashSeed::ALPHA_UPPER                 => array(
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z'
			),
			self::ALPHA                           => array(
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z'
			),
			self::ALPHA_NUMERIC                   => array(
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			self::ALPHA_LOWER_NUMERIC             => array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			self::NUMERIC                         => array(
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			self::ALPHA_LOWER_NUMERIC_IDIOT_PROOF => array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'j',
				'k',
				'm',
				'n',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
		);

	//*************************************************************************
	//	Methods
	//*************************************************************************

	/**
	 * @param int $type
	 *
	 * @return array
	 */
	public static function getSeed( $type )
	{
		return static::$_hashSeeds[$type];
	}

}
