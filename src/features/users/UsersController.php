<?php
    namespace Features\Users;

    class UsersController {
        private $usersService;

        public function __construct() {
            $this->usersService = new UsersService();
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

        public function createUser() {
            $data = json_decode(file_get_contents('php://input'), true);
            $appId = $this->getAppId();
            $result = $this->usersService->createUser($data, $appId);

            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(201);
            } else {
                http_response_code(400);
            }
            echo json_encode($result);
        }

        public function getUsers() {
            $appId = $this->getAppId();
            $users = $this->usersService->getUsers($appId);

            header('Content-Type: application/json');
            echo json_encode($users);
        }

        public function getUser($id) {
            $appId = $this->getAppId();
            $user = $this->usersService->getUser($id, $appId);

            header('Content-Type: application/json');
            if ($user) {
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }
        }

        public function updateUser($id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $appId = $this->getAppId();
            $result = $this->usersService->updateUser($id, $data, $appId);

            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        }

        public function deleteUser($id) {
            $appId = $this->getAppId();
            $result = $this->usersService->deleteUser($id, $appId);

            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(204);
            } else {
                http_response_code(404);
                echo json_encode($result);
            }
        }
    }
