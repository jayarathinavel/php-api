<?php

namespace Features\Ytdb;

use Exception;

class YtdbException extends Exception {
    private $statusCode;
    private $errorType;

    public function __construct(string $message, int $statusCode = 500, string $errorType = 'YOUTUBE_API_ERROR', ?Exception $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getErrorType(): string {
        return $this->errorType;
    }

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
