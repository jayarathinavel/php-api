<?php
    namespace Features\WorkTracker;

    use Core\Database;

    class WorkLogRepository {
        private const TABLE_NAME = 'workTracker_worklogs';

        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function create(WorkLog $workLog) {
            $this->connection->insert(self::TABLE_NAME, [
                'user_id' => $workLog->getUserId(),
                'date' => $workLog->getDate(),
                'done' => $workLog->getDone(),
                'todo' => $workLog->getTodo(),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            $id = $this->connection->lastInsertId();
            $workLog->setId($id);
            return $workLog;
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

            return WorkLog::fromArray($result);
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

            return WorkLog::fromArray($result);
        }

        public function findAllByUserId($userId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $results = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where('user_id = ?')
                ->setParameter(0, $userId)
                ->orderBy('date', 'DESC')
                ->executeQuery()
                ->fetchAllAssociative();

            return array_map(function($data) {
                return WorkLog::fromArray($data);
            }, $results);
        }

        public function update(WorkLog $workLog) {
            $this->connection->update(self::TABLE_NAME, [
                'date' => $workLog->getDate(),
                'done' => $workLog->getDone(),
                'todo' => $workLog->getTodo(),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ], ['id' => $workLog->getId(), 'user_id' => $workLog->getUserId()]);

            return $workLog;
        }

        public function delete($id, $userId) {
            $this->connection->delete(self::TABLE_NAME, ['id' => $id, 'user_id' => $userId]);
            return true;
        }
    }
