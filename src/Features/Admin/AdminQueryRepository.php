<?php
    namespace Features\Admin;

    use Core\Database;

    class AdminQueryRepository {
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function execute($query, $params) {
            return $this->connection->executeStatement($query, $params);
        }
    }
