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
    
        /**
         * Find all tasks by user ID with comment counts
         * This method efficiently fetches tasks with their comment counts in a single query
         */
        public function findAllByUserIdWithCommentCounts($userId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $results = $queryBuilder
                ->select('t.*', 'COALESCE(COUNT(c.id), 0) as comment_count')
                ->from(self::TABLE_NAME, 't')
                ->leftJoin('t', 'workTracker_task_comments', 'c', 't.id = c.task_id')
                ->where('t.user_id = ?')
                ->setParameter(0, $userId)
                ->groupBy('t.id', 't.user_id', 't.title', 't.description', 't.status', 't.reference', 't.created_at', 't.updated_at')
                ->orderBy('t.created_at', 'DESC')
                ->executeQuery()
                ->fetchAllAssociative();
    
            return $results; // Return raw array with comment_count included
        }
    }
