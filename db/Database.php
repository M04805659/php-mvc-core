<?php

namespace app\core\db;

use app\core\Application;

class Database
{
	public \PDO $pdo;
	public function __construct( array $config)
	{
		$dsn       = $config['dsn'] ?? '';
		$user      = $config['user'] ?? '';
		$password  = $config['password'] ?? '';
		$this->pdo = new \PDO($dsn, $user, $password);
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function applyMigrations()
	{
		$this->createMigrationsTable();
		$appliedMigrations = $this->getAppliedMigrations();

		$file = scandir(Application::$ROOT_DIR . '/migrations');
		$toApplyMigrations = array_diff($file, $appliedMigrations);

		$newMigrations = [];
		foreach ($toApplyMigrations as $migration) {
			if($migration === '.' || $migration === '..') { continue;}

			require_once Application::$ROOT_DIR . '/migrations/' . $migration;
			$className = pathinfo($migration, PATHINFO_FILENAME);
			$instance  = new $className();
			$this->log("Applying migration $migration");
			$instance->up();
			$this->log("Applying migration $migration");
			$newMigrations[] = $migration;
		}

		if(!empty($newMigrations)) {
			$this->saveMigrations($newMigrations);
		} else {
			$this->log("All migrations are applied");
		}
	}

	public function createMigrationsTable()
	{
		$this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
						id INT AUTO_INCREMENT PRIMARY KEY,
						migration VARCHAR(255),
						created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
					) ENGINE=INNODB;");
	}

	private function getAppliedMigrations()
	{
		$statement = $this->pdo->prepare("SELECT migration FROM migrations");
		$statement->execute();
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}

	private function saveMigrations(array $newMigrations)
	{
		 $str = implode(', ',array_map(fn($migration) => "('$migration')", $newMigrations));

		$statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES 
		$str
 		");
 		$statement->execute();
	}

	protected function log($message)
	{
		echo '['. date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
	}

	public function prepare($sql)
	{
		return Application::$app->db->pdo->prepare($sql);
	}

}