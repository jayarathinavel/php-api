<?php

    namespace Features\Ytdb\Videos;

    use Features\Ytdb\Videos\YtdbVideosService;
    use Features\Ytdb\YtdbBaseController;

    class YtdbVideosController extends YtdbBaseController {
        
        private $service;

        public function __construct() {
            parent::__construct();
            $this->service = new YtdbVideosService();
        }

        public function createVideo() {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendError("User Id not provided");
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $response = $this->service->createVideo($data);
            return $this->sendResult($response);
        }

        public function updateVideo($id) {
            $userId = $this->getUserIdFromToken();
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $this->service->updateVideo($id, $data, $userId);
            return $this->sendResult($response);
        }

        public function deleteVideo($id) {
            $userId = $this->getUserIdFromToken();
            $response = $this->service->deleteVideo($id, $userId);
            return $this->sendResult($response);
        }

        public function getMyVideos() {
            $userId = $this->getUserIdFromToken();
            $response = $this->service->getMyVideos($userId);
            return $this->sendResult($response);
        }

        public function getAllVideos() {
            $userId = $this->getUserIdFromToken();
            $response = $this->service->getAllVideos($userId);
            return $this->sendResult($response);
        }

        public function getAllVideosByListId($listId) {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendError("User Id not provided");
            }

            $response = $this->service->getAllVideosByListId($listId, $userId);
            return $this->sendResult($response);
        }

        public function getVideoById($id) {
            $userId = $this->getUserIdFromToken();
            $response = $this->service->getVideoById($id, $userId);
            return $this->sendResult($response);
        }
    }
