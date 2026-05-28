<?php
    namespace Features\WorkTracker;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class WorkLogController {
        private $service;
        private $jwtConfig;

        public function __construct() {
            $this->service = new WorkLogService();
            $this->jwtConfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/jwt.php';
        }

        private function getUserIdFromToken() {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

            if (!$authHeader) {
                return null;
            }

            $parts = explode(' ', $authHeader);
            if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
                return null;
            }

            try {
                $decoded = JWT::decode($parts[1], new Key($this->jwtConfig['secret_key'], 'HS256'));
                return $decoded->data->id ?? null;
            } catch (\Exception $e) {
                return null;
            }
        }

        private function sendUnauthorized() {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
        }

        private function sendResult($result) {
            header('Content-Type: application/json');
            http_response_code($result['statusCode']);

            if ($result['statusCode'] === 204) {
                return;
            }

            if (!empty($result['success'])) {
                if (array_key_exists('data', $result)) {
                    echo json_encode($result['data']);
                    return;
                }
                if (isset($result['message'])) {
                    echo json_encode(['message' => $result['message']]);
                    return;
                }
                echo json_encode(new \stdClass());
                return;
            }

            echo json_encode(['error' => $result['error'] ?? 'Unknown error']);
        }

        /**
         * POST /work-log - Create a work log
         */
        public function create() {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendUnauthorized();
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->service->create($data, $userId);
            $this->sendResult($result);
        }

        /**
         * GET /work-log - Get all work logs
         */
        public function getAll() {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendUnauthorized();
                return;
            }

            $result = $this->service->getAllByUserId($userId);
            $this->sendResult($result);
        }

        /**
         * GET /work-log/{id} - Get a specific work log
         */
        public function getById($id) {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendUnauthorized();
                return;
            }

            $result = $this->service->getById($id, $userId);
            $this->sendResult($result);
        }

        /**
         * PATCH /work-log/{id} - Update a work log
         */
        public function update($id) {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendUnauthorized();
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->service->update($id, $data, $userId);
            $this->sendResult($result);
        }

        /**
         * DELETE /work-log/{id} - Delete a work log
         */
        public function delete($id) {
            $userId = $this->getUserIdFromToken();
            if (!$userId) {
                $this->sendUnauthorized();
                return;
            }

            $result = $this->service->delete($id, $userId);
            $this->sendResult($result);
        }
    }
