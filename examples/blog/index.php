<?php
/**
 * @file
 * Basic index.php file for a Kisma application
 *
 * Kisma(tm) : PHP Nanoframework (http://github.com/Pogostick/kisma/)
 * Copyright 2009-2011, Jerry Ablan/Pogostick, LLC., All Rights Reserved
 *
 * @copyright Copyright (c) 2009-2011 Jerry Ablan/Pogostick, LLC.
 * @license http://github.com/Pogostick/Kisma/blob/master/LICENSE
 *
 * @author Jerry Ablan <kisma@pogostick.com>
 * @category Examples
 * @package kisma.examples
 * @since 1.0.0
 *
 * @ingroup examples
 */

//	Include the Kisma bootstrap
require_once __DIR__ . '/../../src/Kisma.php';

//	Initialize Kisma
$_app = new \Kisma\Kisma(
	array(
		'app.config.app_root' => __DIR__,
		'app.config.app_namespace' => 'ExampleBlog',
	)
);

//	A little debug joy
$_app['debug'] = true;

//	Start!
$_app->run();