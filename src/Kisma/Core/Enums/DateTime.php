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
 * DateTime
 * Various date and time constants
 */
class DateTime extends SeedEnum
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const __default = self::SECONDS_PER_MINUTE;
	/**
	 * @var int circa 01/01/1980 (Ahh... my TRS-80... good times)
	 */
	const THE_BEGINNING = 315550800;
	/**
	 * @var int
	 */
	const MICROSECONDS_PER_SECOND = 1000000;
	/**
	 * @var int
	 */
	const MILLISECONDS_PER_SECOND = 1000;
	/**
	 * @var int
	 */
	const SECONDS_PER_MINUTE = 60;
	/**
	 * @var int A 24th of a day
	 */
	const SECONDS_PER_HOUR = 3600;
	/**
	 * @var int An 8th of a day
	 */
	const SECONDS_PER_EIGHTH_DAY = 10800;
	/**
	 * @var int A 4th of a day
	 */
	const SECONDS_PER_QUARTER_DAY = 21600;
	/**
	 * @var int A half of a day
	 */
	const SECONDS_PER_HALF_DAY = 43200;
	/**
	 * @var int A full day
	 */
	const SECONDS_PER_DAY = 86400;
	/**
	 * @var int
	 */
	const SECONDS_PER_WEEK = 604800;
	/**
	 * @var int circa 01/01/2038 (despite the Mayan calendar or John Titor...)
	 */
	const THE_END = 2145934800;
}
