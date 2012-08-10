<?php

/**
 * smCore Test Suite - \smCore\Input
 *
 * @package smCore
 * @author smCore Dev Team
 * @license MPL 1.1
 * @version 1.0 Alpha
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 */

namespace smCore;

use PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase
{
	protected $_router;

	protected $_routes = array(
		'literal1' => array(
			'match' => 'one',
			'controller' => 'MyController',
		),
		'literal2' => array(
			'match' => array('two', '2'),
			'controller' => 'MyController',
		),
		'literal3' => array(
			'match' => 'three/three/three',
			'controller' => 'MyController',
		),
		'literal4' => array(
			'match' => 'four_with_underscore',
			'controller' => 'MyController',
		),
		'regex1' => array(
			'match' => 'user_(?<id>[0-9])',
			'controller' => 'MyController',
		),
		'regex2' => array(
			'match' => 'articles/([a-z0-9_-]+)',
			'controller' => 'MyController',
		),
		'regex3' => array(
			'match' => '[a-z][0-4]_[A-Z][5-9]',
			'controller' => 'MyController',
		),
		'regex4' => array(
			'match' => '\d{3}\-\d{4}',
			'controller' => 'MyController',
		),
		'slug1' => array(
			'match' => 'posts/:title',
			'controller' => 'MyController',
		),
		'slug2' => array(
			'match' => 'user/:name/profile',
			'controller' => 'MyController',
		),
		'slug3' => array(
			'match' => '/projects/:project/users/:user',
			'controller' => 'MyController',
		),
	);

	protected $_slugs = array(
	);

	public function __construct()
	{
		$this->_router = new Router;

		$this->_router->addRoutes($this->_routes, 'unit_test');
	}

	public function testLiteralRoutes()
	{
		$match = $this->_router->match('one');
		$this->assertEquals($match['method'], 'literal1');

		$match = $this->_router->match('/two/');
		$this->assertEquals($match['method'], 'literal2');

		$match = $this->_router->match('/three/three/three');
		$this->assertEquals($match['method'], 'literal3');

		$match = $this->_router->match('four_with_underscore/');
		$this->assertEquals($match['method'], 'literal4');
	}

	public function testRegexRoutes()
	{
		$match = $this->_router->match('/user_5/');
		$this->assertEquals($match['method'], 'regex1');

		$match = $this->_router->match('articles/best-diners-los-angeles');
		$this->assertEquals($match['method'], 'regex2');

		$match = $this->_router->match('b2_A9');
		$this->assertEquals($match['method'], 'regex3');

		$match = $this->_router->match('867-5309');
		$this->assertEquals($match['method'], 'regex4');
	}

	public function testSlugRoutes()
	{
		$match = $this->_router->match('/posts/my_first_post');
		$this->assertEquals($match['method'], 'slug1');
	}
}