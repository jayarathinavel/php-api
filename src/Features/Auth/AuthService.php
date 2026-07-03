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
            if (!$this->isRegistrationEnabled($appId)) {
                return ['success' => false, 'message' => "Registration is disabled"];
            }

            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Name, email, and password are required'];
            }

            if (empty($appId)) {
                return ['success' => false, 'message' => 'app_id is required'];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Validate email length
            if (strlen($data['email']) > 255) {
                return ['success' => false, 'message' => 'Email is too long (max 255 characters)'];
            }

            // Validate name length
            if (strlen($data['name']) > 255) {
                return ['success' => false, 'message' => 'Name is too long (max 255 characters)'];
            }

            if (strlen($data['name']) < 2) {
                return ['success' => false, 'message' => 'Name is too short (min 2 characters)'];
            }

            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($data['password']);
            if (!$passwordValidation['valid']) {
                return ['success' => false, 'message' => $passwordValidation['message']];
            }

            // Validate and sanitize role - only allow 'user' role during registration
            $allowedRoles = ['user'];
            $role = isset($data['role']) && in_array($data['role'], $allowedRoles) ? $data['role'] : 'user';

            // Check if user exists for this app
            if ($this->authRepository->findByEmail($data['email'], $appId)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Create user
            $auth = new Auth($data['name'], $data['email'], $data['password'], $role, $appId);
            $savedAuth = $this->authRepository->create($auth);

            return ['success' => true, 'message' => 'User registered successfully', 'user' => $savedAuth->toArray()];
        }

        private function isRegistrationEnabled($appId = null) {
            $envValue = getenv('REGISTRATION_DISABLED');
            if ($envValue === false || trim($envValue) === '') {
                return true;
            }

            $disabledApps = array_map('trim', explode(',', strtolower($envValue)));
            return !in_array(strtolower($appId), $disabledApps, true);
        }

        public function login($data, $appId) {
            if (empty($appId)) {
                return ['success' => false, 'message' => 'app_id is required'];
            }

            if (empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Mitigate timing attacks by always performing password verification
            $user = $this->authRepository->findByEmail($data['email'], $appId);
            $dummyHash = '$2y$10$abcdefghijklmnopqrstuv.WXYZ0123456789ABCDEFGHIJKLMNOPQRS';
            
            if (!$user) {
                // Perform dummy password verification to maintain consistent timing
                password_verify($data['password'], $dummyHash);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!password_verify($data['password'], $user->getPassword())) {
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

        /**
         * Validate password strength
         * Requirements: minimum 12 characters, uppercase, lowercase, number, special character
         */
        private function validatePasswordStrength($password) {
            if (strlen($password) < 12) {
                return [
                    'valid' => false,
                    'message' => 'Password must be at least 12 characters long'
                ];
            }

            if (strlen($password) > 128) {
                return [
                    'valid' => false,
                    'message' => 'Password is too long (max 128 characters)'
                ];
            }

            if (!preg_match('/[A-Z]/', $password)) {
                return [
                    'valid' => false,
                    'message' => 'Password must contain at least one uppercase letter'
                ];
            }

            if (!preg_match('/[a-z]/', $password)) {
                return [
                    'valid' => false,
                    'message' => 'Password must contain at least one lowercase letter'
                ];
            }

            if (!preg_match('/[0-9]/', $password)) {
                return [
                    'valid' => false,
                    'message' => 'Password must contain at least one number'
                ];
            }

            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                return [
                    'valid' => false,
                    'message' => 'Password must contain at least one special character'
                ];
            }

            // Check against common passwords (basic list)
            $commonPasswords = [
                'password123!', 'Password123!', 'Admin123!', 'Welcome123!',
                'Qwerty123!', '123456789!', 'Passw0rd!', 'P@ssw0rd'
            ];
            
            if (in_array($password, $commonPasswords, true)) {
                return [
                    'valid' => false,
                    'message' => 'Password is too common, please choose a stronger password'
                ];
            }

            return ['valid' => true];
        }
    }
