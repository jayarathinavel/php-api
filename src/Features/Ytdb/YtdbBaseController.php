<?php

    namespace Features\Ytdb;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class YtdbBaseController {

        private $jwtConfig;

        public function __construct() {
            $this->jwtConfig = require __DIR__ . '/../../../config/jwt.php';
        }

        public function getUserIdFromToken() {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
            $parts = explode(' ', $authHeader);
            if (count($parts) !== 2 || $parts[0] !== 'Bearer' || !$authHeader) {
                return null;
            }
    
            try {
                $decoded = JWT::decode($parts[1], new Key($this->jwtConfig['secret_key'], 'HS256'));
                return $decoded->data->id ?? null;
            } catch (\Exception $e) {
                return null;
            }
        }

        public function sendError($message) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => $message]);
            exit;
        }

        public function sendResult($result) {
            header('Content-Type: application/json');
            http_response_code($result['statusCode']);
    
            if ($result['statusCode'] === 204) {
                return;
            }
    
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
    
            echo json_encode(['error' => $result['error'] ?? 'Unknown error']);
        }

    }
