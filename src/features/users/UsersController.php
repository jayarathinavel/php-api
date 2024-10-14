<?php
    namespace Features\Users;

    class UsersController {
        private $usersService;

        public function __construct() {
            $this->usersService = new UsersService();
        }

        public function createUser() {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->usersService->createUser($data);
            
            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(201);
            } else {
                http_response_code(400);
            }
            echo json_encode($result);
        }

        public function getUsers() {
            $users = $this->usersService->getUsers();
            
            header('Content-Type: application/json');
            echo json_encode($users);
        }

        public function getUser($id) {
            $user = $this->usersService->getUser($id);
            
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
            $result = $this->usersService->updateUser($id, $data);
            
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode($result);
            }
        }

        public function deleteUser($id) {
            $result = $this->usersService->deleteUser($id);
            
            header('Content-Type: application/json');
            if ($result['success']) {
                http_response_code(204);
            } else {
                http_response_code(404);
                echo json_encode($result);
            }
        }
    }
