<?php namespace JasonLewis\EnhancedRouter;

use Symfony\Component\Routing\RouteCollection;

class RouteGroup {

	/**
	 * The route collection instance.
	 * 
	 * @var \Symfony\Component\Routing\RouteCollection
	 */
	protected $routes;

	/**
	 * Array of before filters on group.
	 * 
	 * @var array
	 */
	protected $beforeFilters = array();

	/**
	 * Array of after filters on group.
	 * 
	 * @var array
	 */
	protected $afterFilters = array();

	/**
	 * Depth of group.
	 * 
	 * @var int
	 */
	protected $groupDepth;

	/**
	 * Create a new route group instance.
	 * 
	 * @param  \Symfony\Component\Routing\RouteCollection  $routes
	 * @param  array  $groupDepth
	 * @return void
	 */
	public function __construct(RouteCollection $routes, $groupDepth)
	{
		$this->routes = $routes;
		$this->groupDepth = $groupDepth;
	}

	/**
	 * Force a given parameter to match a regular expression.
	 *
	 * @param  string  $name
	 * @param  string  $expression
	 * @return \Illuminate\Routing\Route
	 */
	public function where($name, $expression = null)
	{
		if (is_array($expression))
		{
			$expression = '('.implode('|', $expression).')';
		}
		
		if (is_array($name)) return $this->setArrayOfWheres($name);

		$this->routes->addRequirements(array($name => $expression));

		return $this;
	}

	/**
	 * Force a given parameters to match the expressions.
	 *
	 * @param  array $wheres
	 * @return \Illuminate\Routing\Route
	 */
	protected function setArrayOfWheres(array $wheres)
	{
		foreach ($wheres as $name => $expression)
		{
			$this->where($name, $expression);
		}

		return $this;
	}

	/**
	 * Set the before filters on the route.
	 *
	 * @param  dynamic
	 * @return \Illuminate\Routing\Route
	 */
	public function before()
	{
		$this->beforeFilters = array_unique(array_merge($this->beforeFilters, func_get_args()));

		return $this;
	}

	/**
	 * Set the after filters on the route.
	 *
	 * @param  dynamic
	 * @return \Illuminate\Routing\Route
	 */
	public function after()
	{
		$this->afterFilters = array_unique(array_merge($this->afterFilters, func_get_args()));

		return $this;
	}

	/**
	 * Get the depth of this group.
	 * 
	 * @return int
	 */
	public function getGroupDepth()
	{
		return $this->groupDepth;
	}

	/**
	 * Retrieve the entire route collection.
	 * 
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Get the before filters applied to the group.
	 * 
	 * @return array
	 */
	public function getBeforeFilters()
	{
		return $this->beforeFilters;
	}

	/**
	 * Get the after filters applied to the group.
	 * 
	 * @return array
	 */
	public function getAfterFilters()
	{
		return $this->afterFilters;
	}

}