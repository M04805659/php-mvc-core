<?php

namespace app\core;

use app\core\middlewares\BaseMiddlewares;

class Controller
{
	public string $layout = 'main';
	public string $action = '';
	/**
	 * @var \app\core\middlewares\BaseMiddlewares[]
	 */
	protected array $middlewares = [];

	public function render($view, $params = [])
	{
		return Application::$app->router->renderView($view, $params);
	}

	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	public function registerMiddleware(BaseMiddlewares $middlewares)
	{
		$this->middlewares[] = $middlewares;
	}

	public function getMiddlewares()
	{
		return $this->middlewares;
	}
}