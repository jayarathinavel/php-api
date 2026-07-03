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
            $parts = explode(' ', $authHeader, 2);
            
            if (count($parts) !== 2) {
                $this->unauthorized();
            }
            
            list($type, $token) = $parts;

            if (strcasecmp($type, 'Bearer') != 0 || !$token) {
                $this->unauthorized();
            }

            $userData = $this->authService->validateJWT($token);
            if (!$userData) {
                $this->unauthorized();
            }

            $appId = $this->getAppId();
            if (empty($appId) || empty($userData->app_id) || $userData->app_id !== $appId) {
                $this->unauthorized();
            }

            // Validate that the token's app_id is authorized to access the requested route
            if (!$this->isAuthorizedForRoute($userData->app_id)) {
                $this->unauthorized('Access denied: User not authorized for this API module');
            }

            return true;
        }

        private function getAppId() {
            $headers = getallheaders();
            if (isset($headers['X-App-Id'])) {
                return $headers['X-App-Id'];
            }
            if (isset($headers['x-app-id'])) {
                return $headers['x-app-id'];
            }

            $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $segments = explode('/', $path);
            return $segments[0] ?? null;
        }

        private function isAuthorizedForRoute($tokenAppId) {
            $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            
            // Extract the first segment of the path (the route prefix)
            $segments = explode('/', $path);
            $routePrefix = $segments[0] ?? '';
            
            // List of protected API module prefixes that require strict app_id matching
            $protectedModules = ['work-tracker', 'ytdb'];
            
            // Check if this is a protected module route
            if (in_array($routePrefix, $protectedModules, true)) {
                // Normalize both the route prefix and token app_id for comparison
                // Convert hyphens to underscores to match database format
                $normalizedRoutePrefix = str_replace('-', '_', $routePrefix);
                $normalizedTokenAppId = str_replace('-', '_', $tokenAppId);
                
                // The token's app_id must match the route prefix
                if ($normalizedRoutePrefix !== $normalizedTokenAppId) {
                    return false;
                }
            }
            
            // For non-module-specific routes (like /users, /register, /login, CRUD routes)
            // allow any valid app_id
            return true;
        }

        private function unauthorized($message = 'Unauthorized') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => $message]);
            exit;
        }
    }
