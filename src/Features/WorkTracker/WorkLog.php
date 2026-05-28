<?php
    namespace Features\WorkTracker;

    class WorkLog {
        private $id;
        private $userId;
        private $date; // YYYY-MM-DD format
        private $done;
        private $todo;
        private $createdAt;
        private $updatedAt;

        public function __construct($userId, $date, $done = null, $todo = null, $id = null, $createdAt = null, $updatedAt = null) {
            $this->id = $id;
            $this->userId = $userId;
            $this->date = $date;
            $this->done = $done;
            $this->todo = $todo;
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

        public function getDate() {
            return $this->date;
        }

        public function setDate($date) {
            $this->date = $date;
        }

        public function getDone() {
            return $this->done;
        }

        public function setDone($done) {
            $this->done = $done;
        }

        public function getTodo() {
            return $this->todo;
        }

        public function setTodo($todo) {
            $this->todo = $todo;
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
                'date' => $this->date,
                'done' => $this->done,
                'todo' => $this->todo,
                'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
                'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            ];
        }

        public static function fromArray($data) {
            return new self(
                $data['user_id'],
                $data['date'],
                $data['done'] ?? null,
                $data['todo'] ?? null,
                $data['id'] ?? null,
                isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
                isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null
            );
        }
    }
