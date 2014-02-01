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
namespace Kisma\Core\Enums\Events;

use Kisma\Core\Enums\SeedEnum;

/**
 * RouterEvents
 * The core events raised by service routers
 */
class RouterEvents extends SeedEnum
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const REQUEST_RECEIVED = 'kisma.core.router.request_received';
	/**
	 * @var string
	 */
	const PRE_PROCESS = 'kisma.core.router.pre_process';
	/**
	 * @var string
	 */
	const POST_PROCESS = 'kisma.core.router.post_process';
	/**
	 * @var string
	 */
	const REQUEST_COMPLETE = 'kisma.core.router.request_complete';
}
