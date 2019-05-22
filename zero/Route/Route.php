<?php

namespace Zero\Route;

use Zero\Exception\BadRouteException;

class Route {
	private $prefix              = '';
	private $middleware          = [];
	private $namespace           = '\\App\\Http';
	private $middlewareNamespace = '\\App\\Middleware';
	/**
	 * @var mixed[][][]
	 */
	protected $routes = [];

	/**
	 * @param          $namespace
	 * @param callable $callable
	 */
	public function namespace($namespace, callable $callable) {
		$curNamespace    = $this->namespace;
		$this->namespace = $this->namespace . '\\' . trim($namespace, '\\');
		$callable($this);
		$this->namespace = $curNamespace;
	}

	/**
	 * @param          $middleware
	 * @param callable $callable
	 */
	public function middleware(array $middleware, callable $callable) {
		$curMiddleware    = $this->middleware;
		$middleware       = array_map(function ($item) {
			return $this->middlewareNamespace . '\\' . $item;
		}, $middleware);
		$this->middleware = array_merge($this->middleware, $middleware);
		$callable($this);
		$this->middleware = $curMiddleware;
	}

	/**
	 * @param string   $prefix
	 * @param string   $namespace
	 * @param callable $callable
	 * @param array    $middleware
	 */
	public function group($prefix, callable $callable, $namespace = '', array $middleware = []) {
		$curPrefix     = $this->prefix;
		$curNamespace  = $this->namespace;
		$curMiddleware = $this->middleware;
		$this->prefix  = $this->prefix . '/' . trim($prefix, '/');
		if ($namespace) {
			$this->namespace = $this->namespace . '\\' . trim($namespace, '\\');
		}
		if ($middleware) {
			$middleware = array_map(function ($item) {
				return $this->middlewareNamespace . '\\' . $item;
			}, $middleware);
		}
		$this->middleware = array_merge($this->middleware, $middleware);
		$callable($this);
		$this->prefix     = $curPrefix;
		$this->namespace  = $curNamespace;
		$this->middleware = $curMiddleware;
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function any($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('ANY', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function get($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('GET', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function post($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('POST', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function put($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('PUT', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function delete($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('DELETE', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function patch($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('PATCH', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param $route
	 * @param $handler
	 */
	public function head($route, $handler) {
		$route = $this->prefix . '/' . trim($route, '/');
		$this->addRoute('HEAD', $route, $handler, $this->namespace, $this->middleware);
	}

	/**
	 * @param        $httpMethod
	 * @param        $route
	 * @param        $handler
	 * @param string $namespace
	 * @param array  $middleware
	 * @throws BadRouteException
	 */
	private function addRoute($httpMethod, $route, $handler, $namespace = '', $middleware = []) {
		if (isset($this->routes[$httpMethod][$route])) {
			throw new BadRouteException(sprintf('Cannot register two routes matching "%s" for method "%s"', $route, $httpMethod));
		}
		if ($middleware) {
			foreach ($middleware as $_middleware) {
				if (!class_exists($_middleware)) {
					throw new BadRouteException(sprintf('Cannot found middleware[%s] for route ["%s"|"%s"]', $_middleware, $route, $httpMethod));
				}
			}
		}
		$this->routes[$httpMethod][$route] = [$handler, $namespace, $middleware];
	}

	public function setMiddlewareNamespace($namespace) {
		$this->middlewareNamespace = $namespace;
	}

	/**
	 * @return \mixed[][][]
	 */
	public function getRoutes() {
		return $this->routes;
	}
}
