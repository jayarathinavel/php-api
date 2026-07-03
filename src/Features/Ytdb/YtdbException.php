<?php

namespace Features\Ytdb;

use Core\ApiException;

/**
 * YTDB-specific exception class
 * Extends the base ApiException with YTDB-specific default error type
 */
class YtdbException extends ApiException {
    
    /**
     * Create a new YTDB exception
     *
     * @param string $message Human-readable error message
     * @param int $statusCode HTTP status code (default: 500)
     * @param string $errorType Machine-readable error type (default: 'YOUTUBE_API_ERROR')
     * @param Exception|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message,
        int $statusCode = 500,
        string $errorType = 'YOUTUBE_API_ERROR',
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $errorType, $previous);
    }
}

// Made with Bob
