<?php
    namespace Features\WorkTracker;

    class Task {
        private $id;
        private $userId;
        private $title;
        private $description;
        private $status; // 'pending', 'in-progress', 'completed', 'cancelled'
        private $reference;
        private $createdAt;
        private $updatedAt;

        public function __construct($userId, $title, $description = null, $status = 'pending', $reference = null, $id = null, $createdAt = null, $updatedAt = null) {
            $this->id = $id;
            $this->userId = $userId;
            $this->title = $title;
            $this->description = $description;
            $this->status = $status ?? 'pending';
            $this->reference = $reference;
            $this->createdAt = $createdAt ?? new \DateTime();
            $this->updatedAt = $updatedAt ?? new \DateTime();
        }

        public function getId() {
            return $this->id;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function getUserId() {
            return $this->userId;
        }

        public function setUserId($userId) {
            $this->userId = $userId;
        }

        public function getTitle() {
            return $this->title;
        }

        public function setTitle($title) {
            $this->title = $title;
        }

        public function getDescription() {
            return $this->description;
        }

        public function setDescription($description) {
            $this->description = $description;
        }

        public function getStatus() {
            return $this->status;
        }

        public function setStatus($status) {
            $validStatuses = ['pending', 'in-progress', 'completed', 'cancelled'];
            if (in_array($status, $validStatuses)) {
                $this->status = $status;
            }
        }

        public function getReference() {
            return $this->reference;
        }

        public function setReference($reference) {
            $this->reference = $reference;
        }

        public function getCreatedAt() {
            return $this->createdAt;
        }

        public function setCreatedAt($createdAt) {
            $this->createdAt = $createdAt;
        }

        public function getUpdatedAt() {
            return $this->updatedAt;
        }

        public function setUpdatedAt($updatedAt) {
            $this->updatedAt = $updatedAt;
        }

        public function toArray() {
            return [
                'id' => $this->id,
                'userId' => $this->userId,
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'reference' => $this->reference,
                'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
                'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            ];
        }

        public static function fromArray($data) {
            return new self(
                $data['user_id'],
                $data['title'],
                $data['description'] ?? null,
                $data['status'] ?? 'pending',
                $data['reference'] ?? null,
                $data['id'] ?? null,
                isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
                isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null
            );
        }
    }
