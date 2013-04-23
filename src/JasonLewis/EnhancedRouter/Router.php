<?php namespace JasonLewis\EnhancedRouter;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Illuminate\Routing\Router as IlluminateRouter;

class Router extends IlluminateRouter {

	/**
	 * Array of route groups.
	 * 
	 * @var array
	 */
	protected $routeGroups = array();

	/**
	 * An alias of the group method but without the attributes.
	 * 
	 * @param  Closure  $callback
	 * @return \JasonLewis\EnhancedRouter\RouteGroup
	 */
	public function bunch(Closure $callback)
	{
		return $this->group(array(), $callback);
	}

	/**
	 * Create a route group with shared attributes. Overloading this method allows
	 * developers to chain requirements and filters to all routes within the
	 * group.
	 * 
	 * @param  array  $attributes
	 * @param  Closure  $callback
	 * @return \JasonLewis\EnhancedRouter\RouteGroup
	 */
	public function group(array $attributes, Closure $callback)
	{
		// Clone the original route collection so that we can re-apply this collection
		// as the set of routes. This will allow developers to apply requirements to
		// a group of routes intead of all routes.
		$original = clone $this->routes;

		parent::group($attributes, $callback);

		// We can now get the routes that were added in this group by comparing the
		// keys of the original routes and of the routes we have after the group
		// callback was fired.
		$routes = array_diff_key($this->routes->all(), $original->all());

		// With a brand new route collection we'll spin through all of the routes
		// defined within our group and add them to the collection.
		$collection = new RouteCollection;

		foreach ($routes as $key => $route)
		{
			$collection->add($key, $route);
		}

		// Reset the routes on the router to the original collection of routes that
		// we cloned earlier. This way we don't end up with any double ups when
		// the groups are merged later on.
		$this->routes = $original;

		return $this->routeGroups[] = new RouteGroup($collection, count($this->groupStack));
	}

	/**
	 * Merge route groups into the core route collection.
	 * 
	 * @return void
	 */
	protected function mergeRouteGroups()
	{
		//s($this->routeGroups);
		foreach ($this->routeGroups as $key => $group)
		{
			// Spin through every route and merge the group filters onto the route.
			foreach ($group->getRoutes() as $route)
			{
				// If the group is nested within other groups we need to spin over those
				// groups and merge in those filters as well. This allows a filter
				// applied to an outer group be used on all routes within that
				// group, even if they are within other groups.
				if ($group->getGroupDepth() > 0)
				{
					// For future reference this loop will start the iteration 
					for ($i = count($this->routeGroups) - $group->getGroupDepth(); $i < count($this->routeGroups); ++$i)
					{
						$this->mergeGroupFilters($route, $this->routeGroups[$i]);
					}
				}

				// After any outer group filters have been applied we can merge the
				// filters from the immediate parent group of the route. This is
				// so that outer group filters are run first, since they are
				// technically the first filters that are applied.
				$this->mergeGroupFilters($route, $group);
			}

			$this->routes->addCollection($group->getRoutes());
		}

		$this->routeGroups = array();
	}

	/**
	 * Merge a groups filters onto a route.
	 * 
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \JasonLewis\EnhancedRouter\RouteGroup  $group
	 * @return void
	 */
	protected function mergeGroupFilters($route, $group)
	{
		$before = array_unique(array_merge($route->getBeforeFilters(), $group->getBeforeFilters()));

		$route->setOption('_before', $before);

		$after = array_unique(array_merge($route->getAfterFilters(), $group->getAfterFilters()));

		$route->setOption('_after', $after);
	}

	/**
	 * Get the response for a given request.
	 * Overloaded so that we can merge route groups.
	 * 
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function dispatch(Request $request)
	{
		$this->mergeRouteGroups();

		return parent::dispatch($request);
	}

	/**
	 * Get the route collection instance.
	 * Overloaded so that we can merge route groups.
	 * 
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	public function getRoutes()
	{
		$this->mergeRouteGroups();

		return parent::getRoutes();
	}

	/**
	 * Get the array of route groups.
	 * 
	 * @return array
	 */
	public function getRouteGroups()
	{
		return $this->routeGroups;
	}

}