<?php
namespace app\core;

use app\core\exceptions\NotFoundException;

class Router
{
	protected array $routes = [];
	public Request $request;
	public Response $response;

	public function __construct(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;
	}

	public function get($path, $callback)
	{
		$this->routes['get'][$path] = $callback;
	}

	public function post($path, $callback)
	{
		$this->routes['post'][$path] = $callback;
	}

	public function resolve()
	{
		$path     = $this->request->getPath();
		$method   = $this->request->method();
		$callBack = $this->routes[$method][$path] ?? false;
		if($callBack === false) {
			throw new \app\core\exceptions\NotFoundException();
		}
		if(is_string($callBack)) {
			return $this->renderView($callBack);
		}

		if(is_array($callBack)) {
			/** @var Controller $controller */
			$controller         =  new $callBack[0]();
			Application::$app->controller = $controller;
			$controller->action = $callBack[1];
			$callBack[0] = $controller;

			foreach ($controller->getMiddlewares() as $middleware) {
				$middleware->execute();
			}
		}
		return call_user_func($callBack, $this->request, $this->response);
	}

	public function renderView(string $view, $params = [])
	{
		return Application::$app->view->renderView( $view, $params);
	}

	public function layoutContent()
	{
		return Application::$app->view->layoutContent();
	}

	public function renderOnlyView($view, $params)
	{
		return Application::$app->view->renderOnlyView($view, $params);
	}
}