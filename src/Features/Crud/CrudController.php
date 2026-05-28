<?php
    namespace Features\Crud;

    class CrudController {
        private $crudService;

        public function __construct() {
            $this->crudService = new CrudService();
        }

        public function list($appId, $featureName) {
            $this->respond($this->crudService->list($appId, $featureName));
        }

        public function get($appId, $featureName, $id) {
            $this->respond($this->crudService->get($appId, $featureName, $id));
        }

        public function create($appId, $featureName) {
            $payload = $this->getJsonBody();
            $this->respond($this->crudService->create($appId, $featureName, $payload));
        }

        public function createMany($appId, $featureName) {
            $payload = $this->getJsonBody();
            $this->respond($this->crudService->createMany($appId, $featureName, $payload));
        }

        public function update($appId, $featureName, $id) {
            $payload = $this->getJsonBody();
            $this->respond($this->crudService->update($appId, $featureName, $id, $payload));
        }

        public function delete($appId, $featureName, $id) {
            $this->respond($this->crudService->delete($appId, $featureName, $id));
        }

        private function getJsonBody() {
            $body = file_get_contents('php://input');
            if ($body === '') {
                return null;
            }

            $payload = json_decode($body, true);
            return json_last_error() === JSON_ERROR_NONE ? $payload : null;
        }

        private function respond($result) {
            header('Content-Type: application/json');
            http_response_code($result['status']);

            if ($result['status'] !== 204) {
                echo json_encode($result['body']);
            }
        }
    }
