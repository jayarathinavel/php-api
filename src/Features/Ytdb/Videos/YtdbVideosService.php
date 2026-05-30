<?php

    namespace Features\Ytdb\Videos;

    use Features\Ytdb\YtdbUtils;

    class YtdbVideosService{
        private $repository;

        public function __construct() {
            $this->repository = new YtdbVideosRepository();
        }

        public function createVideo($data) {
            $apiKey = getenv('YOUTUBE_API_KEY');
            $ytdbUtils = new YtdbUtils($apiKey);
            $videoID = $ytdbUtils->getVideoId($data['link']);
            $title = $ytdbUtils->getTitle($videoID);
            $duration = $ytdbUtils->getDuration($videoID);
            $thumbnailUrl = $ytdbUtils->getThumbnail($videoID);

            $video = new YtdbVideos(
                null,
                $data['link'],
                $title,
                $duration,
                $thumbnailUrl,
                $data['listId'],
                $data['description'] ?? '',
                null,
                null
            );
            $data = $this->repository->createVideo($video);
            return [
                'success' => true,
                'data' => $data->toArray(),
                'statusCode' => 201
            ];
        }

        public function updateVideo($id, $data, $userId) {
            if (!$this->checkIfVideoBelongsToTheUser($id, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Access denied',
                    'statusCode' => 403
                ];
            }

            $existingVideo = $this->repository->getVideoById($id);

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
        }

        public function deleteVideo($id, $userId) {
            if (!$this->checkIfVideoBelongsToTheUser($id, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Access denied',
                    'statusCode' => 403
                ];
            }
            $data = $this->repository->deleteVideo($id);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function getMyVideos($userId) {
            $data = $this->repository->getMyVideos($userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function getAllVideos($userId) {
            $data = $this->repository->getAllVideos($userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function getAllVideosByListId($listId, $userId) {
            $data = $this->repository->getAllVideosByListId($listId, $userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function checkIfVideoBelongsToTheUser($id, $userId) {
            return $this->repository->checkIfVideoBelongsToTheUser($id, $userId);
        }

        public function getVideoById($id, $userId) {
            if($this->checkIfVideoBelongsToTheUser($id, $userId)){
                $data = $this->repository->getVideoById($id);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Access denied',
                    'statusCode' => 403
                ];
            }
            
        }
    }