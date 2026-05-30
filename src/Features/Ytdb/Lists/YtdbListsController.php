<?php

    namespace Features\Ytdb\Lists;

    use Features\Ytdb\Lists\YtdbListsService;
    use Features\Ytdb\YtdbBaseController;

    
    class YtdbListsController extends YtdbBaseController {
        private $service;


        public function __construct() {
            parent::__construct();
            $this->service = new YtdbListsService();
        }

        public function createList() {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendError("User Id not provided");
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->service->createList($data, $userId);
            return $this->sendResult($result);
        }

        public function updateList($id) {
            $userId = $this->getUserIdFromToken();
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->service->updateList($id, $data, $userId);
            return $this->sendResult($result);
        }

        public function deleteList($id) {
            $userId = $this->getUserIdFromToken();
            $result = $this->service->deleteList($id, $userId);
            return $this->sendResult($result);
        }

        public function getLists() {
            $userId = $this->getUserIdFromToken();
            $result = $this->service->getLists($userId);
            return $this->sendResult($result);
        }

        public function getAllLists() {
            $userId = $this->getUserIdFromToken();
            $result = $this->service->getAllLists($userId);
            return $this->sendResult($result);
        }
        
    }
