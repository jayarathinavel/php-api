<?php
    namespace Core;

    use Features\Auth\AuthService;

    class RoleMiddleware {
        private $requiredRole;
        private $authService;

        public function __construct($requiredRole) {
            $this->requiredRole = $requiredRole;
            $this->authService = new AuthService();
        }

        public function handle() {
            $headers = getallheaders();

            if (!isset($headers['Authorization'])) {
                $this->unauthorized();
            }

            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') !== 0) {
                $this->unauthorized();
            }

            $token = substr($authHeader, 7);
            $userData = $this->authService->validateJWT($token);

            if (!$userData) {
                $this->unauthorized();
            }

            $appId = $this->getAppId();
            if (empty($appId) || empty($userData->app_id) || $userData->app_id !== $appId) {
                $this->unauthorized();
            }

            if ($userData->role === 'admin') {
                return true;
            }

            if ($userData->role !== $this->requiredRole) {
                $this->forbidden();
            }

            return true;
        }

        private function getAppId() {
            $headers = getallheaders();
            if (isset($headers['X-App-Id'])) {
                return $headers['X-App-Id'];
            }
            if (isset($headers['x-app-id'])) {
                return $headers['x-app-id'];
            }
            return null;
        }

        private function unauthorized() {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        private function forbidden() {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
