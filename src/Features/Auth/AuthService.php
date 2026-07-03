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
                $this->logSecurityEvent('registration_disabled', $data['email'] ?? 'unknown', $appId);
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
            $passwordValidation = $this->validatePasswordStrengthMedium($data['password']);
            if (!$passwordValidation['valid']) {
                $this->logSecurityEvent('registration_weak_password', $data['email'], $appId);
                return ['success' => false, 'message' => $passwordValidation['message']];
            }

            // Validate and sanitize role - only allow 'user' role during registration
            $allowedRoles = ['user'];
            $role = isset($data['role']) && in_array($data['role'], $allowedRoles) ? $data['role'] : 'user';

            // Check if user exists for this app
            if ($this->authRepository->findByEmail($data['email'], $appId)) {
                $this->logSecurityEvent('registration_duplicate_email', $data['email'], $appId);
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Create user
            $auth = new Auth($data['name'], $data['email'], $data['password'], $role, $appId);
            $savedAuth = $this->authRepository->create($auth);

            $this->logSecurityEvent('registration_success', $data['email'], $appId, ['user_id' => $savedAuth->getId()]);

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

            // Check if account is locked due to too many failed attempts
            $lockoutCheck = $this->checkAccountLockout($data['email'], $appId);
            if ($lockoutCheck['locked']) {
                $this->logSecurityEvent('login_attempt_locked', $data['email'], $appId);
                return [
                    'success' => false,
                    'message' => $lockoutCheck['message']
                ];
            }

            // Mitigate timing attacks by always performing password verification
            $user = $this->authRepository->findByEmail($data['email'], $appId);
            $dummyHash = '$2y$10$abcdefghijklmnopqrstuv.WXYZ0123456789ABCDEFGHIJKLMNOPQRS';
            
            if (!$user) {
                // Perform dummy password verification to maintain consistent timing
                password_verify($data['password'], $dummyHash);
                $this->recordFailedLoginAttempt($data['email'], $appId);
                $this->logSecurityEvent('login_failed_user_not_found', $data['email'], $appId);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!password_verify($data['password'], $user->getPassword())) {
                $this->recordFailedLoginAttempt($data['email'], $appId);
                $this->logSecurityEvent('login_failed_invalid_password', $data['email'], $appId);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Successful login - clear failed attempts
            $this->clearFailedLoginAttempts($data['email'], $appId);
            $this->logSecurityEvent('login_success', $data['email'], $appId);

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

        private function validatePasswordStrengthMedium($password) {
            if (strlen($password) < 8) {
                return [
                    'valid' => false,
                    'message' => 'Password must be at least 8 characters long'
                ];
            }

            if (strlen($password) > 128) {
                return [
                    'valid' => false,
                    'message' => 'Password is too long (max 128 characters)'
                ];
            }

            return ['valid' => true];
        }


        /**
         * Check if account is locked due to failed login attempts
         */
        private function checkAccountLockout($email, $appId) {
            $maxAttempts = 5;
            $lockoutDuration = 900; // 15 minutes in seconds
            
            $qb = $this->connection->createQueryBuilder();
            $result = $qb
                ->select('COUNT(*) as attempt_count', 'MAX(attempted_at) as last_attempt')
                ->from('login_attempts')
                ->where('email = :email')
                ->andWhere('app_id = :app_id')
                ->andWhere('attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)')
                ->setParameter('email', $email)
                ->setParameter('app_id', $appId)
                ->executeQuery();
            
            $row = $result->fetchAssociative();
            
            if ($row && $row['attempt_count'] >= $maxAttempts) {
                $lastAttempt = strtotime($row['last_attempt']);
                $unlockTime = $lastAttempt + $lockoutDuration;
                $remainingTime = ceil(($unlockTime - time()) / 60);
                
                if ($remainingTime > 0) {
                    return [
                        'locked' => true,
                        'message' => "Account temporarily locked due to too many failed login attempts. Please try again in $remainingTime minutes."
                    ];
                }
            }
            
            return ['locked' => false];
        }

        /**
         * Record a failed login attempt
         */
        private function recordFailedLoginAttempt($email, $appId) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            try {
                $this->connection->insert('login_attempts', [
                    'email' => $email,
                    'app_id' => $appId,
                    'ip_address' => $ipAddress,
                    'attempted_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the login process
                error_log("Failed to record login attempt: " . $e->getMessage());
            }
        }

        /**
         * Clear failed login attempts after successful login
         */
        private function clearFailedLoginAttempts($email, $appId) {
            try {
                $qb = $this->connection->createQueryBuilder();
                $qb->delete('login_attempts')
                    ->where('email = :email')
                    ->andWhere('app_id = :app_id')
                    ->setParameter('email', $email)
                    ->setParameter('app_id', $appId)
                    ->executeQuery();
            } catch (\Exception $e) {
                // Log error but don't fail the login process
                error_log("Failed to clear login attempts: " . $e->getMessage());
            }
        }

        /**
         * Log security events for audit trail
         */
        private function logSecurityEvent($eventType, $email, $appId, $additionalData = []) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'event_type' => $eventType,
                'email' => $email,
                'app_id' => $appId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ];
            
            if (!empty($additionalData)) {
                $logData['additional_data'] = $additionalData;
            }
            
            // Log to error log (in production, this should go to a proper logging system)
            error_log(sprintf(
                "[SECURITY] %s | Event: %s | Email: %s | App: %s | IP: %s",
                $logData['timestamp'],
                $eventType,
                $email,
                $appId,
                $ipAddress
            ));
        }
    }
