<?php

use JasonLewis\EnhancedRouter\Router;

class RoutingTest extends PHPUnit_Framework_TestCase {


	public function testNestedGroupsInheritAttributes()
	{
		$router = new Router;
		$router->group(array('before' => 'foo'), function() use ($router)
		{
			$router->get('first', function() {});

			$router->group(array('before' => 'bar'), function() use ($router)
			{
				$router->get('second', function() {});

				$router->group(array('before' => 'baz'), function() use ($router)
				{
					$router->get('third', function() {});
				});
			});
		});
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$this->assertEquals(array('foo', 'bar', 'baz'), $routes[0]->getOption('_before'));
		$this->assertEquals(array('foo', 'bar'), $routes[1]->getOption('_before'));
		$this->assertEquals(array('foo'), $routes[2]->getOption('_before'));
	}


	public function testFiltersAreAppliedToGroups()
	{
		$router = new Router;
		$router->group(array('prefix' => 'foo'), function() use ($router)
		{
			$router->get('bar', function() {});
		})->before('qux');
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$this->assertEquals(array('qux'), $routes[0]->getOption('_before'));
	}


	public function testOuterFiltersOnGroupsAreAppliedFirst()
	{
		$router = new Router;
		$router->group(array('prefix' => 'foo'), function() use ($router)
		{
			$router->group(array('prefix' => 'bar'), function() use ($router)
			{
				$router->get('qux', function() {});
			})->before('inner');
		})->before('outer');
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$this->assertEquals(array('outer', 'inner'), $routes[0]->getOption('_before'));
	}


	public function testRequirementsAreSetOnGroups()
	{
		$router = new Router;
		$router->group(array('prefix' => '{foo}'), function() use ($router)
		{
			$router->get('qux', function() {});
		})->where('foo', 'bar');
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$this->assertEquals('/{foo}/qux', $routes[0]->getPath());

		$compiled = $routes[0]->compile();

		$this->assertEquals('#^/(?P<foo>bar)/qux$#s', $compiled->getRegex());
	}


	public function testHostRequirementsAreSetOnGroups()
	{
		$router = new Router;
		$router->group(array('domain' => '{host}.test'), function() use ($router)
		{
			$router->get('foo', function() {});
		})->where('host', 'bar');
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$this->assertEquals('bar', $routes[0]->getRequirement('host'));
	}


	public function testArrayOfRequirementsAreTransformedIntoRegex()
	{
		$router = new Router;
		$router->group(array('prefix' => '{foo}'), function() use ($router)
		{
			$router->get('qux', function() {});
		})->where('foo', ['bar', 'baz']);
		$routes = array_values($router->getRoutes()->getIterator()->getArrayCopy());

		$compiled = $routes[0]->compile();
		
		$this->assertEquals('#^/(?P<foo>(bar|baz))/qux$#s', $compiled->getRegex());
	}


}