<?php

namespace Zero\Route;

use Zero\Contract;
use Zero\Business\Http\Request;
use Zero\Business\Http\Response;
use Zero\Business\Controller;
use Zero\Middleware\AfterMiddleware;
use Zero\Middleware\BeforeMiddleware;
use Zero\Middleware\MiddlewareRejected;

class Dispatcher extends Contract {
	const NOT_FOUND          = 0;
	const FOUND              = 1;
	const METHOD_NOT_ALLOWED = 2;
	protected $routeMap = [];
	/**
	 * @var ErrorHandler;
	 */
	protected $errorHandler;

	public function __construct($routeMap) {
		$this->routeMap     = $routeMap;
		var_dump($routeMap);
		$this->errorHandler = new ErrorHandler();
	}

	public function parse($httpMethod, $uri) {
		if (isset($this->routeMap[$httpMethod][$uri])) {
			$handler = $this->routeMap[$httpMethod][$uri];
			return [self::FOUND, $handler];
		}
		if (isset($this->routeMap['ANY'][$uri])) {
			$handler = $this->routeMap['ANY'][$uri];
			return [self::FOUND, $handler];
		}

		if ($httpMethod === 'HEAD') {
			if (isset($this->routeMap['GET'][$uri])) {
				$handler = $this->routeMap['GET'][$uri];
				return [self::FOUND, $handler];
			}
		}

		$allowedMethods = [];
		foreach ($this->routeMap as $method => $uriMap) {
			if ($method !== $httpMethod && isset($uriMap[$uri])) {
				$allowedMethods[] = $method;
			}
		}

		if ($allowedMethods) {
			return [self::METHOD_NOT_ALLOWED, $allowedMethods];
		}

		return [self::NOT_FOUND];
	}

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function dispatch(Request $request) {
		$response  = new Response();
		$parseInfo = $this->parse($request->method(), $request->getUri());
		switch ($parseInfo[0]) {
			case self::NOT_FOUND:
				return $this->errorHandler->notFound();
			case self::METHOD_NOT_ALLOWED:
				return $this->errorHandler->notAllowed();
			case self::FOUND:
				try {
					list($handler, $namespace, $middleware) = $parseInfo[1];
					/**
					 * 处理中间件
					 */
					$retObj = $this->handleMiddleware($request, $middleware);
					if ($retObj instanceof MiddlewareRejected) {
						return $response->setStatus($retObj->getCode())
							->setResult($retObj->getResult());
					}

					list($request, $afterMiddleware) = $retObj;

					if (is_callable($handler)) {
						$result = call_user_func_array($handler, [$request, $response]);
						return $response->setResult($result);
					}

					list($class, $action) = explode('::', $handler);
					$class     = '\\' . str_replace('.', '\\', $class);
					$className = $namespace . $class;
					if (!class_exists($className)) {
						return $this->errorHandler->classNotFound();
					}
					/**
					 * @var $control Controller;
					 */
					$control = new $className($request, $response);
					if (!$control instanceof Controller) {
						return $this->errorHandler->classIllegal();
					}
					if (!method_exists($control, $action)) {
						return $this->errorHandler->methodNotFound();
					}
					if (!is_callable([$control, $action])) {
						return $this->errorHandler->classMethodNotAllowed();
					}

					$result = $control->$action();
					$response->setResult($result);
					//处理后置中间件
					if ($afterMiddleware) {
						$retObj = $this->handleAfterMiddleware($afterMiddleware, $request, $response);
						if ($retObj instanceof MiddlewareRejected) {
							return $response->setStatus($retObj->getCode())
								->setResult($retObj->getResult());
						}
						$response = $retObj;
					}
					return $response;
				} catch (\Throwable $e) {
					$this->logger()
						->error($e->getMessage(), $e->getTrace());
					return $this->errorHandler->serverError();
				}
				break;
			default:
				return $this->errorHandler->serverError();
		}
	}

	/**
	 * @param       $request
	 * @param array $middleware
	 * @return array|MiddlewareRejected
	 */
	public function handleMiddleware($request, $middleware = []) {
		$afterMiddleware = [];
		foreach ($middleware as $_middleware) {
			//中间件不存在
			if (!class_exists($_middleware)) {
				return new MiddlewareRejected('MIDDLEWARE NOT FOUND :' . $_middleware, 500);
			}
			$middleware = new $_middleware;
			//判断后置中间件
			if ($middleware instanceof AfterMiddleware) {
				$afterMiddleware[] = $middleware;
				continue;
			}
			//处理前置中间件
			if ($middleware instanceof BeforeMiddleware) {
				$retObj = $middleware->handle($request);
				//判断中间件终止
				if ($retObj instanceof MiddlewareRejected) {
					return $retObj;
				}
				$request = $retObj;
				continue;
			}
			//非法中件件
			return new MiddlewareRejected('MIDDLEWARE ILLEGAL :' . $_middleware, 500);
		}

		return [$request, $afterMiddleware];
	}

	/**
	 * @param $afterMiddleware
	 * @param $request
	 * @param $response
	 * @return Response
	 */
	public function handleAfterMiddleware($afterMiddleware, $request, $response) {
		/**
		 * @var AfterMiddleware[] $afterMiddleware
		 */
		foreach ($afterMiddleware as $_middleware) {
			$response = $_middleware->handle($request, $response);
			//判断中间件终止
			if ($response instanceof MiddlewareRejected) {
				return $response;
			}
		}
		return $response;
	}
}
