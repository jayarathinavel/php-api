<?php

namespace Features\WorkTracker;

class CommentService
{
    private $commentRepository;
    private $taskRepository;

    public function __construct()
    {
        $this->commentRepository = new CommentRepository();
        $this->taskRepository = new TaskRepository();
    }

    /**
     * Create a new comment
     */
    public function create(array $data, int $userId, int $taskId): array
    {
        // Validate comment text
        if (empty($data['commentText'])) {
            return [
                'success' => false,
                'error' => 'Comment text is required',
                'statusCode' => 400
            ];
        }

        $commentText = trim($data['commentText']);
        
        // Validate max length (1000 characters)
        if (strlen($commentText) > 1000) {
            return [
                'success' => false,
                'error' => 'Comment text must not exceed 1000 characters',
                'statusCode' => 400
            ];
        }

        // Verify task exists and belongs to user
        $task = $this->taskRepository->findByIdAndUserId($taskId, $userId);
        if (!$task) {
            $existingTask = $this->taskRepository->findById($taskId);
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

        // Create comment
        $comment = new Comment(
            taskId: $taskId,
            userId: $userId,
            commentText: $commentText
        );

        $created = $this->commentRepository->create($comment);

        return [
            'success' => true,
            'data' => $created->toArray(),
            'statusCode' => 201
        ];
    }

    /**
     * Get all comments for a task
     */
    public function getByTaskId(int $taskId, int $userId): array
    {
        // Verify task exists and belongs to user
        $task = $this->taskRepository->findByIdAndUserId($taskId, $userId);
        if (!$task) {
            $existingTask = $this->taskRepository->findById($taskId);
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

        $comments = $this->commentRepository->findByTaskId($taskId);

        return [
            'success' => true,
            'data' => array_map(function ($comment) {
                return $comment->toArray();
            }, $comments),
            'statusCode' => 200
        ];
    }

    /**
     * Update a comment
     */
    public function update(int $id, array $data, int $userId, int $taskId): array
    {
        // Validate comment text
        if (empty($data['commentText'])) {
            return [
                'success' => false,
                'error' => 'Comment text is required',
                'statusCode' => 400
            ];
        }

        $commentText = trim($data['commentText']);
        
        // Validate max length (1000 characters)
        if (strlen($commentText) > 1000) {
            return [
                'success' => false,
                'error' => 'Comment text must not exceed 1000 characters',
                'statusCode' => 400
            ];
        }

        // Verify comment exists and belongs to user
        $comment = $this->commentRepository->findByIdAndUserId($id, $userId);
        if (!$comment) {
            $existingComment = $this->commentRepository->findById($id);
            if ($existingComment) {
                return [
                    'success' => false,
                    'error' => 'You can only edit your own comments',
                    'statusCode' => 403
                ];
            }

            return [
                'success' => false,
                'error' => 'Comment not found',
                'statusCode' => 404
            ];
        }

        // Verify comment belongs to the specified task
        if ($comment->getTaskId() !== $taskId) {
            return [
                'success' => false,
                'error' => 'Comment does not belong to this task',
                'statusCode' => 400
            ];
        }

        // Update comment
        $comment->setCommentText($commentText);
        $updated = $this->commentRepository->update($comment);

        return [
            'success' => true,
            'data' => $updated->toArray(),
            'statusCode' => 200
        ];
    }

    /**
     * Delete a comment
     */
    public function delete(int $id, int $userId, int $taskId): array
    {
        // Verify comment exists and belongs to user
        $comment = $this->commentRepository->findByIdAndUserId($id, $userId);
        if (!$comment) {
            $existingComment = $this->commentRepository->findById($id);
            if ($existingComment) {
                return [
                    'success' => false,
                    'error' => 'You can only delete your own comments',
                    'statusCode' => 403
                ];
            }

            return [
                'success' => false,
                'error' => 'Comment not found',
                'statusCode' => 404
            ];
        }

        // Verify comment belongs to the specified task
        if ($comment->getTaskId() !== $taskId) {
            return [
                'success' => false,
                'error' => 'Comment does not belong to this task',
                'statusCode' => 400
            ];
        }

        // Delete comment
        $deleted = $this->commentRepository->delete($id, $userId);

        if (!$deleted) {
            return [
                'success' => false,
                'error' => 'Failed to delete comment',
                'statusCode' => 500
            ];
        }

        return [
            'success' => true,
            'data' => ['message' => 'Comment deleted successfully'],
            'statusCode' => 200
        ];
    }

    /**
     * Get comment count for a task
     */
    public function getCommentCount(int $taskId, int $userId): array
    {
        // Verify task exists and belongs to user
        $task = $this->taskRepository->findByIdAndUserId($taskId, $userId);
        if (!$task) {
            $existingTask = $this->taskRepository->findById($taskId);
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

        $count = $this->commentRepository->getCommentCountByTaskId($taskId);

        return [
            'success' => true,
            'data' => ['count' => $count],
            'statusCode' => 200
        ];
    }
}

// Made with Bob
