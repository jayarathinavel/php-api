<?php
    namespace Features\Users;

    class UsersService {
        private $usersRepository;

        public function __construct() {
            $this->usersRepository = new UsersRepository();
        }

        public function createUser($data) {
            // Validate data
            if (empty($data['name']) || empty($data['email'])) {
                return ['success' => false, 'message' => 'Name and email are required'];
            }

            // Create user
            $user = new User($data['name'], $data['email']);
            $savedUser = $this->usersRepository->save($user);

            if ($savedUser->getId()) {
                return ['success' => true, 'message' => 'User created successfully', 'user' => $savedUser->toArray()];
            } else {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
        }

        public function getUsers() {
            $users = $this->usersRepository->findAll();
            return array_map(function($user) { return $user->toArray(); }, $users);
        }

        public function getUser($id) {
            $user = $this->usersRepository->findById($id);
            return $user ? $user->toArray() : null;
        }

        public function updateUser($id, $data) {
            $user = $this->usersRepository->findById($id);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            if (isset($data['name'])) {
                $user->setName($data['name']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }

            $updatedUser = $this->usersRepository->save($user);
            return ['success' => true, 'message' => 'User updated successfully', 'user' => $updatedUser->toArray()];
        }

        public function deleteUser($id) {
            $result = $this->usersRepository->delete($id);
            return ['success' => $result, 'message' => $result ? 'User deleted successfully' : 'Failed to delete user'];
        }
    }
