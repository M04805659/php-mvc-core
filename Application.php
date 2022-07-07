<?php

namespace app\core;

use app\core\db\Database;
use app\models\User;

class Application
{
	public string $layout = 'main';
	public Router $router;
	public Request $request;
	public Response $response;
	public Database $db;
	public static Application $app;
	public ?Controller $controller = null;
	public Session $session;
	public ?UserModel $user;
	public string $userClass;
	public View $view;
	public static string $ROOT_DIR;

	public function __construct($rootPath, array $config)
	{
		self::$ROOT_DIR  = $rootPath;
		self::$app       = $this;
		$this->request   = new Request();
		$this->response  = new Response();
		$this->session   = new Session();
		$this->router    = new Router($this->request, $this->response);
		$this->db        = new Database($config['db']);
		$this->userClass = $config['userClass'];
		$this->view      = new View();

		$primaryValue = $this->session->get('user');

		if($primaryValue) {
			$primaryKey = (new $this->userClass)->primaryKey();
			$this->user = (new $this->userClass)->findOne([$primaryKey => $primaryValue]);
		} else {
			$this->user = null;
		}
	}

	public static function isGuest()
	{
		return !self::$app->user;
	}

	public function run()
	{
		try {
			echo $this->router->resolve();
		} catch (\Exception $exception) {
			$this->response->setStatusCode($exception->getCode());
			echo $this->router->renderView('_error', [
				'exception' => $exception
			]);
		}
	}
	/**
	 * @return Controller
	 */
	public function getController(): Controller
	{
		return $this->controller;
	}

	/**
	 * @param Controller $controller
	 */
	public function setController(Controller $controller): void
	{
		$this->controller = $controller;
	}

	public function login(UserModel $user)
	{
		$this->user   = $user;
		$primaryKey   = $user->primaryKey();
		$primaryValue = $user->{$primaryKey};
		$this->session->set('user', $primaryValue);
		return true;
	}

	public function logout()
	{
		$this->user = null;
		$this->session->remove('user');
	}

}
