<?php
    namespace Core;
    use Doctrine\DBAL\DriverManager;

    class Database {
        private static $instance = null;
        private $connection;

        private function __construct() {
            $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
            $connectionParams = [
                'dbname' => $config['database'],
                'user' => $config['username'],
                'password' => $config['password'],
                'host' => $config['host'],
                'driver' => 'pdo_mysql',
            ];
            $this->connection = DriverManager::getConnection($connectionParams);
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnection() {
            return $this->connection;
        }
    }
