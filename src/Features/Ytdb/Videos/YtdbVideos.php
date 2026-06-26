<?php

    namespace Features\Ytdb\Videos;

    class YtdbVideos {
        private $id;
        private $link;
        private $title;
        private $duration;
        private $thumbnailUrl;
        private $listId;
        private $description;
        private $createdAt;
        private $updatedAt;
        private $listName;
        private $userName;
        private $userAvatarId;
        private $visibility;

    public function __construct($id, $link, $title, $duration, $thumbnailUrl, $listId, $description, $createdAt, $updatedAt, $listName = null, $userName = null, $visibility = null, $userAvatarId = null) {
            $this->id = $id;
            $this->link = $link;
            $this->title = $title;
            $this->duration = $duration;
            $this->thumbnailUrl = $thumbnailUrl;
            $this->listId = $listId;
            $this->description = $description;
            $this->createdAt = $createdAt ?? new \DateTime();
            $this->updatedAt = $updatedAt ?? new \DateTime();
            $this->listName = $listName;
            $this->userName = $userName;
            $this->userAvatarId = $userAvatarId;
            $this->visibility = $visibility;
        }

        public function getId() {
            return $this->id;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function getLink() {
            return $this->link;
        }

        public function setLink($link) {
            $this->link = $link;
        }

        public function getTitle() {
            return $this->title;
        }

        public function setTitle($title) {
            $this->title = $title;
        }

        public function getDuration() {
            return $this->duration;
        }

        public function setDuration($duration) {
            $this->duration = $duration;
        }

        public function getThumbnailUrl() {
            return $this->thumbnailUrl;
        }

        public function setThumbnailUrl($thumbnailUrl) {
            $this->thumbnailUrl = $thumbnailUrl;
        }

        public function getListId() {
            return $this->listId;
        }

        public function setListId($listId) {
            $this->listId = $listId;
        }

        public function getDescription() {
            return $this->description;
        }

        public function setDescription($description) {
            $this->description = $description;
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

        public function getListName() {
            return $this->listName;
        }

        public function setListName($listName) {
            $this->listName = $listName;
        }

        public function getUserName() {
            return $this->userName;
        }

        public function setUserName($userName) {
            $this->userName = $userName;
        }

        public function getUserAvatarId() {
            return $this->userAvatarId;
        }

        public function setUserAvatarId($userAvatarId) {
            $this->userAvatarId = $userAvatarId;
        }

        public function getVisibility() {
            return $this->visibility;
        }

        public function setVisibility($visibility) {
            $this->visibility = $visibility;
        }

        private function formatDateValue($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }

            if ($value !== null && $value !== '') {
                return (new \DateTime($value))->format('Y-m-d H:i:s');
            }

            return null;
        }

        public function toArray() {
            $payload = [
                'id' => $this->id,
                'link' => $this->link,
                'title' => $this->title,
                'duration' => $this->duration,
                'thumbnailUrl' => $this->thumbnailUrl,
                'listId' => $this->listId,
                'description' => $this->description,
                'createdAt' => $this->formatDateValue($this->createdAt),
                'updatedAt' => $this->formatDateValue($this->updatedAt),
            ];

            if ($this->listName !== null) {
                $payload['listName'] = $this->listName;
            }

            if ($this->userName !== null) {
                $payload['userName'] = $this->userName;
            }

            if ($this->userAvatarId !== null) {
                $payload['userAvatarId'] = $this->userAvatarId;
            }

            if ($this->visibility !== null) {
                $payload['visibility'] = $this->visibility;
            }

            return $payload;
        }

        public static function fromArray($data) {
            return new self(
                $data['id'],
                $data['link'],
                $data['title'],
                $data['duration'],
                $data['thumbnailUrl'],
                $data['listId'],
                $data['description'],
                isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null,
                isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null,
                $data['listName'] ?? null,
                $data['userName'] ?? null,
                $data['visibility'] ?? null,
                $data['userAvatarId'] ?? null
            );
        }
    }
