<?php
    namespace Features\Auth;

    class AuthController {
        private $authService;

        public function __construct() {
            $this->authService = new AuthService();
        }

        public function register() {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->authService->register($data);

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
            $result = $this->authService->login($data);

            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(401);
            }
            echo json_encode($result);
        }
    }
