<?php
    namespace Features\WorkTracker;

    use Core\Database;

    class TaskRepository {
        private const TABLE_NAME = 'workTracker_tasks';

        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function create(Task $task) {
            $this->connection->insert(self::TABLE_NAME, [
                'user_id' => $task->getUserId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'reference' => $task->getReference(),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            $id = $this->connection->lastInsertId();
            $task->setId($id);
            return $task;
        }

        public function findById($id) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where('id = ?')
                ->setParameter(0, $id)
                ->executeQuery()
                ->fetchAssociative();

            if (!$result) {
                return null;
            }

            return Task::fromArray($result);
        }

        public function findByIdAndUserId($id, $userId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where('id = ? AND user_id = ?')
                ->setParameter(0, $id)
                ->setParameter(1, $userId)
                ->executeQuery()
                ->fetchAssociative();

            if (!$result) {
                return null;
            }

            return Task::fromArray($result);
        }

        public function findAllByUserId($userId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $results = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where('user_id = ?')
                ->setParameter(0, $userId)
                ->orderBy('created_at', 'DESC')
                ->executeQuery()
                ->fetchAllAssociative();

            return array_map(function($data) {
                return Task::fromArray($data);
            }, $results);
        }

        public function update(Task $task) {
            $this->connection->update(self::TABLE_NAME, [
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'reference' => $task->getReference(),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ], ['id' => $task->getId(), 'user_id' => $task->getUserId()]);

            return $task;
        }

        public function delete($id, $userId) {
            $this->connection->delete(self::TABLE_NAME, ['id' => $id, 'user_id' => $userId]);
            return true;
        }
    }
