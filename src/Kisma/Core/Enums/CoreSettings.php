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
 * Default kernel-level settings defined by Kisma in ENUM format
 */
class CoreSettings extends SeedEnum
{
	/**
	 * @var string Set to non-empty to enable debug logging
	 */
	const VERBOSITY = 'app.verbosity';
	/**
	 * @var string The base path of the Kisma library
	 */
	const BASE_PATH = 'app.base_path';
	/**
	 * @var string The Composer autoloader object
	 */
	const AUTOLOADER = 'app.autoloader';
	/**
	 * @var string Set to TRUE once Kisma is initialized
	 */
	const INITIALIZED = 'app.initialized';
	/**
	 * @var string The version of Kisma
	 */
	const VERSION = 'app.version';
	/**
	 * @var string The detected framework, if any
	 */
	const DETECTED_FRAMEWORK = 'app.detected_framework';
	/**
	 * @var string The event dispatcher
	 */
	const EVENT_DISPATCHER = 'app.event_dispatcher';
}
