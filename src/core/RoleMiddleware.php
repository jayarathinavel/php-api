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
                return false;
            }

            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') !== 0) {
                $this->unauthorized();
                return false;
            }

            $token = substr($authHeader, 7);
            $userData = $this->authService->validateJWT($token);

            if (!$userData) {
                $this->unauthorized();
                return false;
            }

            if ($userData->role !== $this->requiredRole) {
                $this->forbidden();
                return false;
            }

            // Optionally, set user data to a global state or context
            // For simplicity, we skip this step

            return true;
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
