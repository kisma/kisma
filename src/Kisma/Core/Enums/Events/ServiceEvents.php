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
 * ServiceLike
 * Defines the event interface for all services
 */
class ServiceEvents extends SeedEnum
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string Fired before service call
	 */
	const PRE_PROCESS = 'kisma.core.service.pre_process';
	/**
	 * @var string Fired when the service call did not fail
	 */
	const SUCCESS = 'kisma.core.service.success';
	/**
	 * @var string Fired if the service call fails
	 */
	const FAILURE = 'kisma.core.service.failure';
	/**
	 * @var string Fired when the service call is complete, regardless of failure
	 */
	const COMPLETE = 'kisma.core.service.complete';
	/**
	 * @var string Fired after service call
	 */
	const POST_PROCESS = 'kisma.core.service.post_process';
}
