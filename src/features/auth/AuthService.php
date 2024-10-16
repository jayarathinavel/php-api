<?php
    namespace Features\Auth;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class AuthService {
        private $authRepository;
        private $jwtConfig;

        public function __construct() {
            $this->authRepository = new AuthRepository();
            $this->jwtConfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/jwt.php';
        }

        public function register($data) {
            // Validate data
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Name, email, and password are required'];
            }

            // Optionally, allow role assignment (ensure this is secure)
            $role = isset($data['role']) ? $data['role'] : 'user';
            if ($role == 'admin') {
                return ['success' => false, 'message' => 'Cannot Register a Admin User'];
            }

            // Check if user exists
            if ($this->authRepository->findByEmail($data['email'])) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Create user
            $auth = new Auth($data['name'], $data['email'], $data['password'],  $role);
            $savedAuth = $this->authRepository->create($auth);

            return ['success' => true, 'message' => 'User registered successfully', 'user' => $savedAuth->toArray()];
        }

        public function login($data) {
            if (empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            $user = $this->authRepository->findByEmail($data['email']);
            if (!$user || !password_verify($data['password'], $user->getPassword())) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $token = $this->generateJWT($user);

            return ['success' => true, 'message' => 'Login successful', 'token' => $token];
        }

        private function generateJWT($user) {
            $payload = [
                'iss' => $this->jwtConfig['issuer'],
                'aud' => $this->jwtConfig['audience'],
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + $this->jwtConfig['expiration_time'],
                'data' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'role'  => $user->getRole(),
                ],
            ];

            return JWT::encode($payload, $this->jwtConfig['secret_key'], 'HS256');
        }

        public function validateJWT($token) {
            try {
                $decoded = JWT::decode($token, new Key($this->jwtConfig['secret_key'], 'HS256'));
                return $decoded->data;
            } catch (\Exception $e) {
                return null;
            }
        }
    }
