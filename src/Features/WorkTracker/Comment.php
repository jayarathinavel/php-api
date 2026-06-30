<?php

namespace Features\WorkTracker;

use DateTime;

class Comment
{
    private ?int $id = null;
    private int $taskId;
    private int $userId;
    private string $commentText;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;

    public function __construct(
        int $taskId,
        int $userId,
        string $commentText,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->taskId = $taskId;
        $this->userId = $userId;
        $this->commentText = $commentText;
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCommentText(): string
    {
        return $this->commentText;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTaskId(int $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setCommentText(string $commentText): void
    {
        $this->commentText = $commentText;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Convert Comment object to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'taskId' => $this->taskId,
            'userId' => $this->userId,
            'commentText' => $this->commentText,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Create Comment instance from database row
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskId: (int) $data['task_id'],
            userId: (int) $data['user_id'],
            commentText: $data['comment_text'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTime($data['updated_at']) : null
        );
    }
}

// Made with Bob
