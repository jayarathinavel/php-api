<?php
    namespace Features\Auth;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Core\Database;

    class AuthService {
        private $authRepository;
        private $jwtConfig;
        private $connection;

        public function __construct() {
            $this->authRepository = new AuthRepository();
            $this->jwtConfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/jwt.php';
            $this->connection = Database::getInstance()->getConnection();
        }

        public function register($data, $appId) {
            if (!$this->isRegistrationEnabled()) {
                return ['success' => false, 'message' => "Registration is disabled"];
            }

            // Validate data
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Name, email, and password are required'];
            }

            if (empty($appId)) {
                return ['success' => false, 'message' => 'app_id is required'];
            }

            // Optionally, allow role assignment (ensure this is secure)
            $role = isset($data['role']) ? $data['role'] : 'user';
            if ($role == 'admin') {
                return ['success' => false, 'message' => 'Cannot Register a Admin User'];
            }

            // Check if user exists for this app
            if ($this->authRepository->findByEmail($data['email'], $appId)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Create user
            $auth = new Auth($data['name'], $data['email'], $data['password'], $role, $appId);
            $savedAuth = $this->authRepository->create($auth);

            return ['success' => true, 'message' => 'User registered successfully', 'user' => $savedAuth->toArray()];
        }

        private function isRegistrationEnabled() {
            $envValue = getenv('REGISTRATION_ENABLED');
            if ($envValue === false) {
                return true;
            }

            $normalized = strtolower(trim($envValue));
            return !in_array($normalized, ['0', 'false', 'no', 'off'], true);
        }

        public function login($data, $appId) {
            if (empty($appId)) {
                return ['success' => false, 'message' => 'app_id is required'];
            }

            if (empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            $user = $this->authRepository->findByEmail($data['email'], $appId);
            if (!$user || !password_verify($data['password'], $user->getPassword())) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $token = $this->generateJWT($user);

            $userData = $user->toArray();

            // Fetch avatar_id from ytdb_users for ytdb app users
            if ($appId === 'ytdb') {
                $qb = $this->connection->createQueryBuilder();
                $result = $qb
                    ->select('avatar_id')
                    ->from('ytdb_users')
                    ->where('user_id = :uid')
                    ->setParameter('uid', $user->getId())
                    ->executeQuery();
                $row = $result->fetchAssociative();
                $userData['avatar_id'] = $row ? $row['avatar_id'] : null;
            }

            return ['success' => true, 'message' => 'Login successful', 'token' => $token, 'user' => $userData];
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
                    'app_id' => $user->getAppId(),
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
