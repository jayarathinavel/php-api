<?php
    namespace Features\WorkTracker;

    use Core\ApiException;

    class WorkLogService {
        private $repository;

        public function __construct() {
            $this->repository = new WorkLogRepository();
        }

        /**
         * Create a new work log
         */
        public function create($data, $userId) {
            if (empty($data['date'])) {
                throw new ApiException('Date is required', 400, 'MISSING_REQUIRED_FIELD');
            }

            // Validate date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                throw new ApiException('Date must be in YYYY-MM-DD format', 400, 'INVALID_DATE_FORMAT');
            }

            $workLog = new WorkLog(
                $userId,
                $data['date'],
                $data['done'] ?? null,
                $data['todo'] ?? null
            );

            $created = $this->repository->create($workLog);

            return [
                'success' => true,
                'data' => $created->toArray(),
                'statusCode' => 201
            ];
        }

        /**
         * Get all work logs for a user
         */
        public function getAllByUserId($userId) {
            $workLogs = $this->repository->findAllByUserId($userId);

            return [
                'success' => true,
                'data' => array_map(function($log) {
                    return $log->toArray();
                }, $workLogs),
                'statusCode' => 200
            ];
        }

        /**
         * Get a specific work log by ID
         */
        public function getById($id, $userId) {
            $workLog = $this->repository->findByIdAndUserId($id, $userId);

            if (!$workLog) {
                $existingWorkLog = $this->repository->findById($id);
                if ($existingWorkLog) {
                    throw new ApiException('Access denied', 403, 'RESOURCE_NOT_OWNED');
                }

                throw new ApiException('Work log not found', 404, 'WORKLOG_NOT_FOUND');
            }

            return [
                'success' => true,
                'data' => $workLog->toArray(),
                'statusCode' => 200
            ];
        }

        /**
         * Update a work log
         */
        public function update($id, $data, $userId) {
            $workLog = $this->repository->findByIdAndUserId($id, $userId);

            if (!$workLog) {
                $existingWorkLog = $this->repository->findById($id);
                if ($existingWorkLog) {
                    throw new ApiException('Access denied', 403, 'RESOURCE_NOT_OWNED');
                }

                throw new ApiException('Work log not found', 404, 'WORKLOG_NOT_FOUND');
            }

            if (isset($data['date'])) {
                // Validate date format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                    throw new ApiException('Date must be in YYYY-MM-DD format', 400, 'INVALID_DATE_FORMAT');
                }
                $workLog->setDate($data['date']);
            }
            if (isset($data['done'])) {
                $workLog->setDone($data['done']);
            }
            if (isset($data['todo'])) {
                $workLog->setTodo($data['todo']);
            }

            $updated = $this->repository->update($workLog);

            return [
                'success' => true,
                'data' => $updated->toArray(),
                'statusCode' => 200
            ];
        }

        /**
         * Delete a work log
         */
        public function delete($id, $userId) {
            $workLog = $this->repository->findByIdAndUserId($id, $userId);

            if (!$workLog) {
                $existingWorkLog = $this->repository->findById($id);
                if ($existingWorkLog) {
                    throw new ApiException('Access denied', 403, 'RESOURCE_NOT_OWNED');
                }

                throw new ApiException('Work log not found', 404, 'WORKLOG_NOT_FOUND');
            }

            $this->repository->delete($id, $userId);

            return [
                'success' => true,
                'statusCode' => 204
            ];
        }
    }
