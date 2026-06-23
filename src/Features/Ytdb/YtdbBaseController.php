<?php

    namespace Features\Ytdb;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class YtdbBaseController {

        private $jwtConfig;

        public function __construct() {
            $this->jwtConfig = require __DIR__ . '/../../../config/jwt.php';
        }

        /**
         * Extract and validate user ID from JWT token
         * @throws YtdbException if token is missing, invalid, or expired
         * @return int User ID
         */
        public function getUserIdFromToken() {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

            if (!$authHeader) {
                throw new YtdbException('Authorization header is required', 401, 'UNAUTHORIZED');
            }

            $parts = explode(' ', $authHeader);
            if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
                throw new YtdbException('Invalid authorization header format. Expected: Bearer <token>', 401, 'INVALID_TOKEN');
            }

            try {
                $decoded = JWT::decode($parts[1], new Key($this->jwtConfig['secret_key'], 'HS256'));
                $userId = $decoded->data->id ?? null;
                
                if (!$userId) {
                    throw new YtdbException('Invalid token payload', 401, 'INVALID_TOKEN');
                }
                
                return $userId;
            } catch (\Firebase\JWT\ExpiredException $e) {
                throw new YtdbException('Token has expired', 401, 'INVALID_TOKEN');
            } catch (\Firebase\JWT\SignatureInvalidException $e) {
                throw new YtdbException('Invalid token signature', 401, 'INVALID_TOKEN');
            } catch (YtdbException $e) {
                throw $e;
            } catch (\Exception $e) {
                error_log('JWT decode error: ' . $e->getMessage());
                throw new YtdbException('Invalid or expired token', 401, 'INVALID_TOKEN');
            }
        }

        /**
         * Handle exceptions and return appropriate response
         * @param \Exception $e The exception to handle
         * @return void
         */
        protected function handleException(\Exception $e) {
            if ($e instanceof YtdbException) {
                return $this->sendResult($e->toArray());
            }
            
            error_log('Unexpected error in controller: ' . $e->getMessage());
            
            return $this->sendResult([
                'success' => false,
                'error' => 'An unexpected error occurred',
                'errorType' => 'INTERNAL_SERVER_ERROR',
                'statusCode' => 500
            ]);
        }

        /**
         * Send error response (deprecated - use YtdbException instead)
         * @deprecated Use throw new YtdbException() instead
         */
        public function sendError($message) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => $message]);
            exit;
        }

        /**
         * Send unauthorized response (deprecated - use YtdbException instead)
         * @deprecated Use throw new YtdbException() instead
         */
        public function sendUnauthorized($message = 'Unauthorized') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => $message]);
            exit;
        }

        /**
         * Send standardized JSON response
         * @param array $result Response array with success, data, error, errorType, and statusCode
         * @return void
         */
        public function sendResult($result) {
            header('Content-Type: application/json');
            http_response_code($result['statusCode'] ?? 500);
    
            if (($result['statusCode'] ?? 0) === 204) {
                return;
            }
    
            // Success response
            if (!empty($result['success'])) {
                if (array_key_exists('data', $result)) {
                    echo json_encode($result['data']);
                    return;
                }
                if (isset($result['message'])) {
                    echo json_encode(['message' => $result['message']]);
                    return;
                }
                echo json_encode(new \stdClass());
                return;
            }
    
            // Error response
            $errorResponse = ['error' => $result['error'] ?? 'Unknown error'];
            
            // Include errorType if present for debugging
            if (isset($result['errorType'])) {
                $errorResponse['errorType'] = $result['errorType'];
            }
            
            echo json_encode($errorResponse);
        }

    }
