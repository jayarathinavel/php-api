<?php

namespace Features\Ytdb\Users;

use Features\Ytdb\YtdbBaseController;
use Features\Ytdb\YtdbException;

class YtdbUsersController extends YtdbBaseController {
    private $service;

    public function __construct() {
        parent::__construct();
        $this->service = new YtdbUsersService();
    }

    /**
     * Get request data from JSON body
     */
    private function getRequestData() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    /**
     * Send JSON response
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    /**
     * Update user profile (name only)
     * PUT /ytdb/users/profile
     */
    public function updateProfile() {
        try {
            $userId = $this->getUserIdFromToken();
            $data = $this->getRequestData();

            $result = $this->service->updateProfile($userId, $data);
            $this->sendJsonResponse($result, 200);
        } catch (YtdbException $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     * GET /ytdb/users/profile
     */
    public function getProfile() {
        try {
            $userId = $this->getUserIdFromToken();
            $result = $this->service->getProfile($userId);
            $this->sendJsonResponse($result, 200);
        } catch (YtdbException $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}

// Made with Bob
