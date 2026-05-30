<?php

    namespace Features\Ytdb\Lists;

    use Core\Database;

    class YtdbListsRepository {

        private const TABLE_NAME = 'ytdb_lists';
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function createList(YtdbLists $list) {
            $this ->connection-> insert(self::TABLE_NAME, [
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
        }

        public function updateList(YtdbLists $list) {
            $this->connection->update(self::TABLE_NAME, [
                'name' => $list->getName(),
                'description' => $list->getDescription(),
                'visibility' => $list->getVisibility(),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ], ['id' => $list->getId()]);

            return $list;
        }

        public function deleteList($id) {
            $this->connection->delete(self::TABLE_NAME, ['id' => $id]);
            return true;
        }

        public function getLists($userId){
            $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE user_id = :userId";
            $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
            return $result->fetchAllAssociative();
        }


        public function getAllLists($userId){
            $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE visibility = 'public' AND user_id != :userId";
            $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
            return $result->fetchAllAssociative();
        }

        public function checkIfListBelongsToTheUser($id, $userId) {
            $sql = "SELECT id FROM " . self::TABLE_NAME . " WHERE id = :id AND user_id = :userId";
            $result = $this->connection->executeQuery($sql, ['id' => $id, 'userId' => $userId]);
            return $result->rowCount() > 0;
        }
    }