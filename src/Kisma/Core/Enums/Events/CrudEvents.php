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
use Kisma\Core\Interfaces\EventTypeLike;

/**
 * CrudEvents
 * Defines an interface for CRUD service events
 */
class CrudEvents extends SeedEnum implements EventTypeLike
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const BEFORE_CREATE = 'kisma.core.crud.before_create';
	/**
	 * @var string
	 */
	const AFTER_CREATE = 'kisma.core.crud.after_create';
	/**
	 * @var string
	 */
	const BEFORE_READ = 'kisma.core.crud.before_read';
	/**
	 * @var string
	 */
	const AFTER_READ = 'kisma.core.crud.after_read';
	/**
	 * @var string
	 */
	const BEFORE_UPDATE = 'kisma.core.crud.before_update';
	/**
	 * @var string
	 */
	const AFTER_UPDATE = 'kisma.core.crud.after_update';
	/**
	 * @var string
	 */
	const BEFORE_DELETE = 'kisma.core.crud.before_delete';
	/**
	 * @var string
	 */
	const AFTER_DELETE = 'kisma.core.crud.after_delete';
}