<?php

    namespace Features\Ytdb\Videos;

    use Core\Database;
    use Features\Ytdb\YtdbException;

    class YtdbVideosRepository {

        private const TABLE_NAME = 'ytdb_videos';
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function createVideo(YtdbVideos $video) {
            try {
                $this->connection->insert(self::TABLE_NAME, [
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
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                throw new YtdbException('This video already exists in the list', 409, 'DUPLICATE_ENTRY');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                throw new YtdbException('The specified list does not exist', 400, 'FOREIGN_KEY_VIOLATION');
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in createVideo: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in createVideo: ' . $e->getMessage());
                throw new YtdbException('Failed to create video', 500, 'DATABASE_ERROR');
            }
        }

        public function updateVideo(YtdbVideos $video) {
            try {
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
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                throw new YtdbException('This video already exists in the list', 409, 'DUPLICATE_ENTRY');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                throw new YtdbException('The specified list does not exist', 400, 'FOREIGN_KEY_VIOLATION');
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in updateVideo: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in updateVideo: ' . $e->getMessage());
                throw new YtdbException('Failed to update video', 500, 'DATABASE_ERROR');
            }
        }

        public function deleteVideo($id) {
            try {
                $this->connection->delete(self::TABLE_NAME, ['id' => $id]);
                return true;
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in deleteVideo: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in deleteVideo: ' . $e->getMessage());
                throw new YtdbException('Failed to delete video', 500, 'DATABASE_ERROR');
            }
        }

        public function getMyVideos($userId) {
            try {
                $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at, l.name AS list_name, u.name AS user_name
                        FROM " . self::TABLE_NAME . " v
                        JOIN ytdb_lists l ON v.list_id = l.id
                        JOIN users u ON l.user_id = u.id
                        WHERE l.user_id = :userId
                        ORDER BY v.created_at DESC";

                $result = $this->connection->executeQuery($sql, ['userId' => $userId]);
                $rows = $result->fetchAllAssociative();
                return $this->rowsToVideos($rows);
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getMyVideos: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getMyVideos: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch videos', 500, 'DATABASE_ERROR');
            }
        }

        public function getAllVideos($userId) {
            try {
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
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getAllVideos: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getAllVideos: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch public videos', 500, 'DATABASE_ERROR');
            }
        }

        public function getAllVideosByListId($listId, $userId) {
            try {
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
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getAllVideosByListId: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getAllVideosByListId: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch videos by list', 500, 'DATABASE_ERROR');
            }
        }

        public function checkIfVideoBelongsToTheUser($id, $userId) {
            try {
                $sql = "SELECT id FROM " . self::TABLE_NAME . " WHERE id = :id AND list_id IN (SELECT id FROM ytdb_lists WHERE user_id = :userId)";
                $result = $this->connection->executeQuery($sql, ['id' => $id, 'userId' => $userId]);
                return $result->rowCount() > 0;
            } catch (\Exception $e) {
                error_log('Database error in checkIfVideoBelongsToTheUser: ' . $e->getMessage());
                return false;
            }
        }

        public function getVideoById($id) {
            try {
                $sql = "SELECT v.id, v.link, v.title, v.duration, v.thumbnail_url, v.list_id, v.description, v.created_at, v.updated_at
                        FROM " . self::TABLE_NAME . " v
                        WHERE v.id = :id";
                $result = $this->connection->executeQuery($sql, ['id' => $id]);
                $rows = $result->fetchAllAssociative();
                if (count($rows) === 0) {
                    return null;
                }
                $videos = $this->rowsToVideos($rows);
                return $videos[0];
            } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
                error_log('Database connection error in getVideoById: ' . $e->getMessage());
                throw new YtdbException('Database connection failed', 500, 'DATABASE_CONNECTION_ERROR');
            } catch (\Exception $e) {
                error_log('Database error in getVideoById: ' . $e->getMessage());
                throw new YtdbException('Failed to fetch video', 500, 'DATABASE_ERROR');
            }
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
