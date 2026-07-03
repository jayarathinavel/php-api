<?php

namespace Features\Ytdb;

use Core\BaseController;

/**
 * Base controller for YTDB feature
 * Extends the shared BaseController with YTDB-specific functionality
 */
class YtdbBaseController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Extract and validate user ID from JWT token
     * Overrides parent to throw YtdbException instead of ApiException
     *
     * @throws YtdbException if token is missing, invalid, or expired
     * @return int User ID
     */
    public function getUserIdFromToken() {
        try {
            return parent::getUserIdFromToken();
        } catch (\Core\ApiException $e) {
            // Convert ApiException to YtdbException for backward compatibility
            throw new YtdbException($e->getMessage(), $e->getStatusCode(), $e->getErrorType());
        }
    }

    /**
     * Handle exceptions and return appropriate response
     * Overrides parent to handle YtdbException specifically
     *
     * @param \Exception $e The exception to handle
     * @return void
     */
    protected function handleException(\Exception $e) {
        if ($e instanceof YtdbException) {
            return $this->sendResult($e->toArray());
        }
        
        return parent::handleException($e);
    }

    /**
     * Send error response (deprecated - use YtdbException instead)
     *
     * @deprecated Use throw new YtdbException() instead
     * @param string $message Error message
     * @return void
     */
    public function sendError($message) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => $message]);
        exit;
    }
}

// Made with Bob
