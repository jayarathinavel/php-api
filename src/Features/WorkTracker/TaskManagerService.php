<?php
    namespace Features\WorkTracker;

    class TaskManagerService {
        private $repository;

        public function __construct() {
            $this->repository = new TaskRepository();
        }

        /**
         * Create a new task
         */
        public function create($data, $userId) {
            $status = $data['status'] ?? 'pending';
            $validStatuses = ['pending', 'in-progress', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'error' => 'Invalid status. Must be one of: pending, in-progress, completed, cancelled',
                    'statusCode' => 400
                ];
            }

            $task = new Task(
                $userId,
                $data['title'],
                $data['description'] ?? null,
                $status,
                $data['reference'] ?? null
            );

            $created = $this->repository->create($task);

            return [
                'success' => true,
                'data' => $created->toArray(),
                'statusCode' => 201
            ];
        }

        /**
         * Get all tasks for a user with comment counts
         */
        public function getAllByUserId($userId) {
            $tasksWithCounts = $this->repository->findAllByUserIdWithCommentCounts($userId);

            return [
                'success' => true,
                'data' => array_map(function($taskData) {
                    $task = Task::fromArray($taskData);
                    $taskArray = $task->toArray();
                    // Add comment count to the response
                    $taskArray['commentCount'] = (int) $taskData['comment_count'];
                    return $taskArray;
                }, $tasksWithCounts),
                'statusCode' => 200
            ];
        }

        /**
         * Get a specific task by ID
         */
        public function getById($id, $userId) {
            $task = $this->repository->findByIdAndUserId($id, $userId);

            if (!$task) {
                $existingTask = $this->repository->findById($id);
                if ($existingTask) {
                    return [
                        'success' => false,
                        'error' => 'Access denied',
                        'statusCode' => 403
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'Task not found',
                    'statusCode' => 404
                ];
            }

            return [
                'success' => true,
                'data' => $task->toArray(),
                'statusCode' => 200
            ];
        }

        /**
         * Update a task
         */
        public function update($id, $data, $userId) {
            $task = $this->repository->findByIdAndUserId($id, $userId);

            if (!$task) {
                $existingTask = $this->repository->findById($id);
                if ($existingTask) {
                    return [
                        'success' => false,
                        'error' => 'Access denied',
                        'statusCode' => 403
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'Task not found',
                    'statusCode' => 404
                ];
            }

            if (isset($data['title'])) {
                $task->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $task->setDescription($data['description']);
            }
            if (isset($data['status'])) {
                $validStatuses = ['pending', 'in-progress', 'completed', 'cancelled'];
                if (!in_array($data['status'], $validStatuses)) {
                    return [
                        'success' => false,
                        'error' => 'Invalid status. Must be one of: pending, in-progress, completed, cancelled',
                        'statusCode' => 400
                    ];
                }
                $task->setStatus($data['status']);
            }
            if (isset($data['reference'])) {
                $task->setReference($data['reference']);
            }

            $updated = $this->repository->update($task);

            return [
                'success' => true,
                'data' => $updated->toArray(),
                'statusCode' => 200
            ];
        }

        /**
         * Delete a task
         */
        public function delete($id, $userId) {
            $task = $this->repository->findByIdAndUserId($id, $userId);

            if (!$task) {
                $existingTask = $this->repository->findById($id);
                if ($existingTask) {
                    return [
                        'success' => false,
                        'error' => 'Access denied',
                        'statusCode' => 403
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'Task not found',
                    'statusCode' => 404
                ];
            }

            $this->repository->delete($id, $userId);

            return [
                'success' => true,
                'message' => "Task with ID $id deleted",
                'statusCode' => 200
            ];
        }
    }
