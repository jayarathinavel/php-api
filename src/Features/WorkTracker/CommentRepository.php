<?php

namespace Features\WorkTracker;

use Core\Database;

class CommentRepository
{
    private const TABLE_NAME = 'workTracker_task_comments';

    private $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
    }

    /**
     * Create a new comment
     */
    public function create(Comment $comment): Comment
    {
        $now = new \DateTime();

        $this->connection->insert(self::TABLE_NAME, [
            'task_id' => $comment->getTaskId(),
            'user_id' => $comment->getUserId(),
            'comment_text' => $comment->getCommentText(),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $id = $this->connection->lastInsertId();
        $comment->setId($id);
        $comment->setCreatedAt(clone $now);
        $comment->setUpdatedAt(clone $now);

        return $comment;
    }

    /**
     * Find comment by ID
     */
    public function findById(int $id): ?Comment
    {
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

        return Comment::fromArray($result);
    }

    /**
     * Find all comments for a task (newest first)
     */
    public function findByTaskId(int $taskId): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $results = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('task_id = ?')
            ->setParameter(0, $taskId)
            ->orderBy('created_at', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(function ($row) {
            return Comment::fromArray($row);
        }, $results);
    }

    /**
     * Find comment by ID and user ID (for ownership verification)
     */
    public function findByIdAndUserId(int $id, int $userId): ?Comment
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('id = ?')
            ->andWhere('user_id = ?')
            ->setParameter(0, $id)
            ->setParameter(1, $userId)
            ->executeQuery()
            ->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Comment::fromArray($result);
    }

    /**
     * Update a comment
     */
    public function update(Comment $comment): Comment
    {
        $updatedAt = new \DateTime();

        $this->connection->update(
            self::TABLE_NAME,
            [
                'comment_text' => $comment->getCommentText(),
                'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            ],
            ['id' => $comment->getId()]
        );

        $comment->setUpdatedAt($updatedAt);

        return $comment;
    }

    /**
     * Delete a comment
     */
    public function delete(int $id, int $userId): bool
    {
        $deleted = $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );

        return $deleted > 0;
    }

    /**
     * Get comment count for a task
     */
    public function getCommentCountByTaskId(int $taskId): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $count = $queryBuilder
            ->select('COUNT(*) as count')
            ->from(self::TABLE_NAME)
            ->where('task_id = ?')
            ->setParameter(0, $taskId)
            ->executeQuery()
            ->fetchAssociative();

        return (int) ($count['count'] ?? 0);
    }
}

// Made with Bob
