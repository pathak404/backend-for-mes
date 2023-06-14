<?php

namespace app\core\db;

use app\core\Application;

/**
 * Class Database
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Database
{
    public \PDO $pdo;
    public static string $APP_DIR;
    public function __construct(array $config)
    {
        self::$APP_DIR = $config['path']['app'];
        $dns = $config['db']['dsn'] ?? '';
        $username = $config['db']['username'] ?? '';
        $password = $config['db']['password'] ?? '';
        try {
            $this->pdo = new \PDO($dns, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e)
        {
            Application::$app->response->setHeader();
            echo json_encode(["status"=> 0, "data" => ["message" => [$e->getMessage()]]]);
            exit;
        }

    }

    public function applyMigrations(): void
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        $newMigrations = [];
        $files = scandir(self::$APP_DIR.'/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);
        foreach ($toApplyMigrations as $migration){
            if($migration === '.' || $migration === '..'){
                continue;
            }
            require_once self::$APP_DIR.'/migrations/'.$migration;
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }
        if(!empty($newMigrations)){
            $this->saveMigrations($newMigrations);
        }else{
            echo $this->log("all migrations are applied");
        }

    }

    public function createMigrationsTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;");
    }

    public function getAppliedMigrations(): bool|array
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveMigrations(array $migrations): void
    {
        $str = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES 
            $str             
        ");
        $statement->execute();
    }

    public function prepare($sql): bool|\PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    protected function log($message): void
    {
        echo '['.date('d-m-y H:i:s').'] - '. $message . PHP_EOL;
    }
}