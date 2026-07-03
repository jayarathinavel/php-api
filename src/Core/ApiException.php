<?php

namespace Core;

use Exception;

/**
 * Base exception class for all API exceptions
 * Provides consistent error response structure across all features
 */
class ApiException extends Exception {
    private $statusCode;
    private $errorType;

    /**
     * Create a new API exception
     * 
     * @param string $message Human-readable error message
     * @param int $statusCode HTTP status code (default: 500)
     * @param string $errorType Machine-readable error type (default: 'INTERNAL_SERVER_ERROR')
     * @param Exception|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message, 
        int $statusCode = 500, 
        string $errorType = 'INTERNAL_SERVER_ERROR', 
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
    }

    /**
     * Get HTTP status code
     * 
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * Get error type
     * 
     * @return string
     */
    public function getErrorType(): string {
        return $this->errorType;
    }

    /**
     * Convert exception to array format for JSON response
     * 
     * @return array
     */
    public function toArray(): array {
        return [
            'success' => false,
            'error' => $this->getMessage(),
            'errorType' => $this->errorType,
            'statusCode' => $this->statusCode
        ];
    }
}

// Made with Bob