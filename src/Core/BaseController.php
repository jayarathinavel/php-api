<?php

namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Base controller class with standardized error handling and common utilities
 * All feature controllers should extend this class for consistency
 */
class BaseController {
    
    protected $jwtConfig;

    public function __construct() {
        $this->jwtConfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/jwt.php';
    }

    /**
     * Extract App ID from headers or request body
     * 
     * @return string|null App ID
     */
    protected function getAppId() {
        $headers = getallheaders();
        if (isset($headers['X-App-Id'])) {
            return $headers['X-App-Id'];
        }
        if (isset($headers['x-app-id'])) {
            return $headers['x-app-id'];
        }
        
        // Fallback to request body
        $data = json_decode(file_get_contents('php://input'), true);
        return $data['app_id'] ?? null;
    }

    /**
     * Extract and validate user ID from JWT token
     * 
     * @throws ApiException if token is missing, invalid, or expired
     * @return int User ID
     */
    protected function getUserIdFromToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            throw new ApiException('Authorization header is required', 401, 'UNAUTHORIZED');
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            throw new ApiException('Invalid authorization header format. Expected: Bearer <token>', 401, 'INVALID_TOKEN');
        }

        try {
            $decoded = JWT::decode($parts[1], new Key($this->jwtConfig['secret_key'], 'HS256'));
            $userId = $decoded->data->id ?? null;
            
            if (!$userId) {
                throw new ApiException('Invalid token payload', 401, 'INVALID_TOKEN');
            }
            
            return $userId;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new ApiException('Token has expired', 401, 'TOKEN_EXPIRED');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new ApiException('Invalid token signature', 401, 'INVALID_TOKEN');
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            error_log('JWT decode error: ' . $e->getMessage());
            throw new ApiException('Invalid or expired token', 401, 'INVALID_TOKEN');
        }
    }

    /**
     * Parse and validate JSON request body
     * 
     * @param bool $required Whether request body is required
     * @throws ApiException if JSON is invalid or empty when required
     * @return array|null Parsed JSON data
     */
    protected function getJsonBody(bool $required = true) {
        $rawInput = file_get_contents('php://input');
        
        if (empty($rawInput)) {
            if ($required) {
                throw new ApiException('Request body cannot be empty', 400, 'EMPTY_REQUEST_BODY');
            }
            return null;
        }

        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Invalid JSON in request body: ' . json_last_error_msg(),
                400,
                'INVALID_JSON_PAYLOAD'
            );
        }

        if ($required && empty($data)) {
            throw new ApiException('Request body cannot be empty', 400, 'EMPTY_REQUEST_BODY');
        }

        return $data;
    }

    /**
     * Send standardized JSON response
     * 
     * @param array $result Response array with success, data, error, errorType, and statusCode
     * @return void
     */
    protected function sendResult($result) {
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

    /**
     * Handle exceptions and return appropriate response
     * 
     * @param \Exception $e The exception to handle
     * @return void
     */
    protected function handleException(\Exception $e) {
        if ($e instanceof ApiException) {
            return $this->sendResult($e->toArray());
        }
        
        error_log('Unexpected error in controller: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        return $this->sendResult([
            'success' => false,
            'error' => 'An unexpected error occurred',
            'errorType' => 'INTERNAL_SERVER_ERROR',
            'statusCode' => 500
        ]);
    }

    /**
     * Send unauthorized response (deprecated - use ApiException instead)
     * 
     * @deprecated Use throw new ApiException() instead
     * @param string $message Error message
     * @return void
     */
    protected function sendUnauthorized($message = 'Unauthorized') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => $message]);
    }
}

// Made with Bob