<?php
    namespace Features\Admin;

    class AdminQueryController {
        private $adminQueryService;

        public function __construct() {
            $this->adminQueryService = new AdminQueryService();
        }

        public function execute() {
            $payload = $this->getRequestPayload();
            $result = $this->adminQueryService->execute($payload);

            header('Content-Type: application/json');
            http_response_code($result['status']);
            echo json_encode($result['body']);
        }

        private function getRequestPayload() {
            $body = file_get_contents('php://input');
            if ($body === '') {
                return null;
            }

            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (stripos($contentType, 'application/json') === false) {
                return $body;
            }

            $payload = json_decode($body, true);
            return json_last_error() === JSON_ERROR_NONE ? $payload : null;
        }
    }
