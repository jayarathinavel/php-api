<?php

    namespace Features\Ytdb\Videos;

    use Features\Ytdb\YtdbUtils;
    use Features\Ytdb\YtdbException;

    class YtdbVideosService{
        private $repository;

        public function __construct() {
            $this->repository = new YtdbVideosRepository();
        }

        public function createVideo($data) {
            try {
                // Validate required fields
                if (empty($data['link'])) {
                    throw new YtdbException('YouTube URL is required', 400, 'MISSING_REQUIRED_FIELD');
                }

                if (empty($data['listId'])) {
                    throw new YtdbException('List ID is required', 400, 'MISSING_REQUIRED_FIELD');
                }

                // Validate list ID format
                if (!is_numeric($data['listId'])) {
                    throw new YtdbException('Invalid list ID format', 400, 'INVALID_LIST_ID');
                }

                $apiKey = getenv('YOUTUBE_API_KEY');
                
                if (empty($apiKey)) {
                    throw new YtdbException('YouTube API is not configured. Please contact the administrator', 500, 'MISSING_API_KEY');
                }

                $ytdbUtils = new YtdbUtils($apiKey);
                
                // Extract video ID from URL
                $videoID = $ytdbUtils->getVideoId($data['link']);
                
                // Use optimized method to get all info in one API call
                $videoInfo = $ytdbUtils->getVideoInfo($videoID);

                $video = new YtdbVideos(
                    null,
                    $data['link'],
                    $videoInfo['title'],
                    $videoInfo['duration'],
                    $videoInfo['thumbnail'],
                    $data['listId'],
                    $data['description'] ?? '',
                    null,
                    null
                );
                
                $createdVideo = $this->repository->createVideo($video);
                
                return [
                    'success' => true,
                    'data' => $createdVideo->toArray(),
                    'statusCode' => 201
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in createVideo: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while creating the video',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function updateVideo($id, $data, $userId) {
            try {
                // Validate video ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid video ID', 400, 'INVALID_VIDEO_ID');
                }

                $existingVideo = $this->repository->getVideoById($id);
                if ($existingVideo === null) {
                    throw new YtdbException('Video not found', 404, 'VIDEO_NOT_FOUND');
                }

                if (!$this->checkIfVideoBelongsToTheUser($id, $userId)) {
                    throw new YtdbException('You do not have permission to update this video', 403, 'RESOURCE_NOT_OWNED');
                }

                // If link is being updated, fetch new video info from YouTube
                if (isset($data['link']) && $data['link'] !== $existingVideo['link']) {
                    $apiKey = getenv('YOUTUBE_API_KEY');
                    
                    if (empty($apiKey)) {
                        throw new YtdbException('YouTube API is not configured. Please contact the administrator', 500, 'MISSING_API_KEY');
                    }

                    $ytdbUtils = new YtdbUtils($apiKey);
                    $videoID = $ytdbUtils->getVideoId($data['link']);
                    $videoInfo = $ytdbUtils->getVideoInfo($videoID);
                    
                    // Update with new YouTube data
                    $data['title'] = $videoInfo['title'];
                    $data['duration'] = $videoInfo['duration'];
                    $data['thumbnailUrl'] = $videoInfo['thumbnail'];
                }

                $video = new YtdbVideos(
                    $id,
                    $data['link'] ?? $existingVideo['link'],
                    $data['title'] ?? $existingVideo['title'],
                    $data['duration'] ?? $existingVideo['duration'],
                    $data['thumbnailUrl'] ?? $existingVideo['thumbnailUrl'],
                    $data['listId'] ?? $existingVideo['listId'],
                    $data['description'] ?? $existingVideo['description'],
                    null,
                    null
                );

                $updatedVideo = $this->repository->updateVideo($video);

                return [
                    'success' => true,
                    'data' => $updatedVideo->toArray(),
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in updateVideo: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while updating the video',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function deleteVideo($id, $userId) {
            try {
                // Validate video ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid video ID', 400, 'INVALID_VIDEO_ID');
                }

                $existingVideo = $this->repository->getVideoById($id);
                if ($existingVideo === null) {
                    throw new YtdbException('Video not found', 404, 'VIDEO_NOT_FOUND');
                }

                if (!$this->checkIfVideoBelongsToTheUser($id, $userId)) {
                    throw new YtdbException('You do not have permission to delete this video', 403, 'RESOURCE_NOT_OWNED');
                }

                $data = $this->repository->deleteVideo($id);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in deleteVideo: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while deleting the video',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function getMyVideos($userId) {
            try {
                $data = $this->repository->getMyVideos($userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (\Exception $e) {
                error_log('Unexpected error in getMyVideos: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching videos',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function getAllVideos($userId) {
            try {
                $data = $this->repository->getAllVideos($userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllVideos: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching public videos',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function getAllVideosByListId($listId, $userId) {
            try {
                // Validate list ID
                if (empty($listId) || !is_numeric($listId)) {
                    throw new YtdbException('Invalid list ID', 400, 'INVALID_LIST_ID');
                }

                $data = $this->repository->getAllVideosByListId($listId, $userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllVideosByListId: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching videos',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function checkIfVideoBelongsToTheUser($id, $userId) {
            return $this->repository->checkIfVideoBelongsToTheUser($id, $userId);
        }

        public function getVideoById($id, $userId) {
            try {
                // Validate video ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid video ID', 400, 'INVALID_VIDEO_ID');
                }

                $existingVideo = $this->repository->getVideoById($id);
                if ($existingVideo === null) {
                    throw new YtdbException('Video not found', 404, 'VIDEO_NOT_FOUND');
                }

                if ($this->checkIfVideoBelongsToTheUser($id, $userId)) {
                    return [
                        'success' => true,
                        'data' => $existingVideo,
                        'statusCode' => 200
                    ];
                }

                throw new YtdbException('You do not have permission to access this video', 403, 'ACCESS_DENIED');
                
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in getVideoById: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching the video',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }
    }