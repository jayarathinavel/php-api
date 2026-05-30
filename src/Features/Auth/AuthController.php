<?php
    namespace Features\Auth;

    class AuthController {
        private $authService;

        public function __construct() {
            $this->authService = new AuthService();
        }

        private function getAppId() {
            $headers = getallheaders();
            if (isset($headers['X-App-Id'])) {
                return $headers['X-App-Id'];
            }
            if (isset($headers['x-app-id'])) {
                return $headers['x-app-id'];
            }
            $data = json_decode(file_get_contents('php://input'), true);
            return $data['app_id'] ?? null;
        }

        public function register() {
            $data = json_decode(file_get_contents('php://input'), true);
            $appId = $this->getAppId();
            $result = $this->authService->register($data, $appId);

            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(201);
            } else {
                http_response_code(400);
            }
            echo json_encode($result);
        }

        public function login() {
            $data = json_decode(file_get_contents('php://input'), true);
            $appId = $this->getAppId();
            $result = $this->authService->login($data, $appId);

            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(401);
            }
            echo json_encode($result);
        }
        
        public function apiCheck() {
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'API is working']);
        }
    }
