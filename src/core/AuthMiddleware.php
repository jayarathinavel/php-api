<?php
    namespace Core;

    use Features\Auth\AuthService;

    class AuthMiddleware {
        private $authService;

        public function __construct() {
            $this->authService = new AuthService();
        }

        public function handle() {
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                $this->unauthorized();
            }

            $authHeader = $headers['Authorization'];
            list($type, $token) = explode(' ', $authHeader, 2);

            if (strcasecmp($type, 'Bearer') != 0 || !$token) {
                $this->unauthorized();
            }

            $userData = $this->authService->validateJWT($token);
            if (!$userData) {
                $this->unauthorized();
            }

            return true;
        }

        private function unauthorized() {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
