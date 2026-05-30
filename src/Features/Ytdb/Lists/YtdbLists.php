<?php

    namespace Features\Ytdb\Lists;

    class YtdbLists {
        private $id;
        private $userId;
        private $name;
        private $description;
        private $visibility; // 'public', 'private'
        private $createdAt;
        private $updatedAt;

        public function __construct($id, $userId, $name, $description, $visibility, $createdAt, $updatedAt) {
            $this->id = $id;
            $this->userId = $userId;
            $this->name = $name;
            $this->description = $description;
            $this->visibility = $visibility;
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

        public function getName() {
            return $this->name;
        }

        public function setName($name) {
            $this->name = $name;
        }

        public function getDescription() {
            return $this->description;
        }

        public function setDescription($description) {
            $this->description = $description;
        }

        public function getVisibility() {
            return $this->visibility;
        }

        public function setVisibility($visibility) {
            $this->visibility = $visibility;
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
                'name' => $this->name,
                'description' => $this->description,
                'visibility' => $this->visibility,
                'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
                'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            ];
        }

        public function fromArray($data) {
            return new self(
                $data['id'],
                $data['userId'],
                $data['name'],
                $data['description'],
                $data['visibility'],
                isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null,
                isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null
            );
        }
    }