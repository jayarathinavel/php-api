<?php

    namespace Features\Ytdb\Lists;

    use Core\Database;
    use Features\Ytdb\YtdbException;

    class YtdbListsRepository {

        private const TABLE_NAME = 'ytdb_lists';
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function createList(YtdbLists $list) {
            try {
                $this->connection->insert(self::TABLE_NAME, [
                    'user_id' => $list->getUserId(),
                    'name' => $list->getName(),
                    'description' => $list->getDescription(),
                    'visibility' => $list->getVisibility(),
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);

                $id = $this->connection->lastInsertId();
                $list->setId($id);

                return $list;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                throw new YtdbException('A list with this name already exists', 409, 'DUPLICATE_ENTRY');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                throw new YtdbException('Invalid user reference', 400, 'FOREIGN_KEY_VIOLATION');
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in createList: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in createList: ' . $e->getMessage());
                throw new YtdbException('Failed to create list', 500, 'DATABASE_ERROR');
            }
        }

        public function updateList(YtdbLists $list) {
            try {
                $this->connection->update(self::TABLE_NAME, [
                    'name' => $list->getName(),
                    'description' => $list->getDescription(),
                    'visibility' => $list->getVisibility(),
                    'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                ], ['id' => $list->getId()]);

                return $list;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                throw new YtdbException('A list with this name already exists', 409, 'DUPLICATE_ENTRY');
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in updateList: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in updateList: ' . $e->getMessage());
                throw new YtdbException('Failed to update list', 500, 'DATABASE_ERROR');
            }
        }

        public function deleteList($id) {
            try {
                $this->connection->delete(self::TABLE_NAME, ['id' => $id]);
                return true;
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                throw new YtdbException('Cannot delete list with existing videos. Please delete videos first', 400, 'FOREIGN_KEY_VIOLATION');
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in deleteList: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in deleteList: ' . $e->getMessage());
                throw new YtdbException('Failed to delete list', 500, 'DATABASE_ERROR');
            }
        }

        public function getLists($userId){
            try {
                $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE user_id = :userId";
                $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
                return $result->fetchAllAssociative();
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getLists: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getLists: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch lists', 500, 'DATABASE_ERROR');
            }
        }


        public function getAllLists($userId){
            try {
                $sql = "SELECT l.*, u.name AS user_name FROM " . self::TABLE_NAME . " l JOIN users u ON l.user_id = u.id WHERE visibility = 'public' AND user_id != :userId";
                $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
                return $result->fetchAllAssociative();
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getAllLists: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getAllLists: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch public lists', 500, 'DATABASE_ERROR');
            }
        }

        public function checkIfListBelongsToTheUser($id, $userId) {
            try {
                $sql = "SELECT id FROM " . self::TABLE_NAME . " WHERE id = :id AND user_id = :userId";
                $result = $this->connection->executeQuery($sql, ['id' => $id, 'userId' => $userId]);
                return $result->rowCount() > 0;
            } catch (\Exception $e) {
                error_log('Database error in checkIfListBelongsToTheUser: ' . $e->getMessage());
                return false;
            }
        }

        public function getListById($id) {
            try {
                $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id";
                $result = $this->connection->executeQuery($sql, ['id' => $id]);
                $rows = $result->fetchAllAssociative();
                if (count($rows) === 0) {
                    return null;
                }
                return $rows[0];
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getListById: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getListById: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch list', 500, 'DATABASE_ERROR');
            }
        }
    }