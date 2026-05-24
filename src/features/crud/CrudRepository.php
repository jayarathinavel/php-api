<?php
    namespace Features\Crud;

    use Core\Database;

    class CrudRepository {
        private $connection;
        private $schemaCache = [];

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function findResource($appId, $featureName) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from('crud_resources')
                ->where('app_id = :app_id')
                ->andWhere('feature_name = :feature_name')
                ->andWhere('is_active = 1')
                ->setParameter('app_id', $appId)
                ->setParameter('feature_name', $featureName)
                ->executeQuery();

            $resource = $result->fetchAssociative();
            return $resource ?: null;
        }

        public function getTableSchema($tableName) {
            if (isset($this->schemaCache[$tableName])) {
                return $this->schemaCache[$tableName];
            }

            $rows = $this->connection->fetchAllAssociative(
                'SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, EXTRA
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$tableName]
            );

            $columns = [];
            foreach ($rows as $row) {
                $columns[$row['COLUMN_NAME']] = [
                    'nullable' => $row['IS_NULLABLE'] === 'YES',
                    'default' => $row['COLUMN_DEFAULT'],
                    'type' => $row['DATA_TYPE'],
                    'max_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                    'auto_increment' => strpos($row['EXTRA'], 'auto_increment') !== false,
                ];
            }

            $this->schemaCache[$tableName] = $columns;
            return $columns;
        }

        public function findAll($tableName) {
            return $this->connection->fetchAllAssociative('SELECT * FROM ' . $this->quoteIdentifier($tableName));
        }

        public function findById($tableName, $primaryKey, $id) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from($this->quoteIdentifier($tableName))
                ->where($this->quoteIdentifier($primaryKey) . ' = :id')
                ->setParameter('id', $id)
                ->executeQuery();

            $row = $result->fetchAssociative();
            return $row ?: null;
        }

        public function insert($tableName, $data) {
            $this->connection->insert($tableName, $data);
            return $this->connection->lastInsertId();
        }

        public function update($tableName, $primaryKey, $id, $data) {
            return $this->connection->update($tableName, $data, [$primaryKey => $id]);
        }

        public function delete($tableName, $primaryKey, $id) {
            return $this->connection->delete($tableName, [$primaryKey => $id]);
        }

        private function quoteIdentifier($identifier) {
            return $this->connection->quoteIdentifier($identifier);
        }
    }
