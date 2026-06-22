<?php

    namespace Features\Ytdb\Videos;

    use Core\Database;

    class YtdbVideosRepository {

        private const TABLE_NAME = 'ytdb_videos';
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function createVideo(YtdbVideos $video) {
            $this ->connection-> insert(self::TABLE_NAME, [
                'link' => $video->getLink(),
                'title' => $video->getTitle(),
                'duration' => $video->getDuration(),
                'thumbnail_url' => $video->getThumbnailUrl(),
                'list_id' => $video->getListId(),
                'description' => $video->getDescription(),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            $id = $this->connection->lastInsertId();
            $video->setId($id);

            return $video;
        }

        public function updateVideo(YtdbVideos $video) {
            $this->connection->update(self::TABLE_NAME, [
                'link' => $video->getLink(),
                'title' => $video->getTitle(),
                'duration' => $video->getDuration(),
                'thumbnail_url' => $video->getThumbnailUrl(),
                'list_id' => $video->getListId(),
                'description' => $video->getDescription(),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ], ['id' => $video->getId()]);

            return $video;
        }

        public function deleteVideo($id) {
            $this->connection->delete(self::TABLE_NAME, ['id' => $id]);
            return true;
        }

        public function getMyVideos($userId) {
            $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at, l.name AS list_name, u.name AS user_name
                    FROM " . self::TABLE_NAME . " v
                    JOIN ytdb_lists l ON v.list_id = l.id
                    JOIN users u ON l.user_id = u.id
                    WHERE l.user_id = :userId
                    ORDER BY v.created_at DESC";

            $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
            $rows = $result->fetchAllAssociative();
            return $this->rowsToVideos($rows);
        }

        public function getAllVideos($userId) {
            $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at,
                             l.name AS list_name, u.name AS user_name
                    FROM " . self::TABLE_NAME . " v
                    JOIN ytdb_lists l ON v.list_id = l.id
                    JOIN users u ON l.user_id = u.id
                    WHERE l.visibility = 'public'
                      AND l.user_id != :userId
                    ORDER BY v.created_at DESC";

            $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
            $rows = $result->fetchAllAssociative();
            return $this->rowsToVideos($rows);
        }

        public function getAllVideosByListId($listId, $userId) {
            $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at,
                             l.name AS list_name, l.visibility, u.name AS user_name
                    FROM " . self::TABLE_NAME . " v
                    JOIN ytdb_lists l ON v.list_id = l.id
                    JOIN users u ON l.user_id = u.id
                    WHERE v.list_id = :listId
                      AND (l.visibility = 'public' OR l.user_id = :userId)
                    ORDER BY v.created_at DESC";

            $result = $this->connection->executeQuery($sql, ['listId' => $listId, 'userId' => $userId]);
            $rows = $result->fetchAllAssociative();
            return $this->rowsToVideos($rows);
        }

        public function checkIfVideoBelongsToTheUser($id, $userId) {
            $sql = "SELECT id FROM " . self::TABLE_NAME . " WHERE id = :id AND list_id IN (SELECT id FROM ytdb_lists WHERE user_id = :userId)";
            $result = $this->connection->executeQuery($sql, ['id' => $id, 'userId' => $userId]);
            return $result->rowCount() > 0;
        }

        public function getVideoById($id) {
            $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at
                    FROM " . self::TABLE_NAME . " v
                    WHERE v.id = :id";
            $result = $this->connection->executeQuery($sql, ['id' => $id]);
            $rows = $result->fetchAllAssociative();
            return $this->rowsToVideos($rows)[0];
        }

        public function rowsToVideos($rows) {
            $videos = [];
            foreach ($rows as $row) {
                $videos[] = (new YtdbVideos(
                    $row['id'],
                    $row['link'],
                    $row['title'],
                    $row['duration'],
                    $row['thumbnail_url'],
                    $row['list_id'],
                    $row['description'],
                    $row['created_at'],
                    $row['updated_at'],
                    $row['list_name'] ?? null,
                    $row['user_name'] ?? null,
                    $row['visibility'] ?? null
                ))->toArray();
            }
            return $videos;
        }
    }
