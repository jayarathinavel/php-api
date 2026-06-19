<?php

namespace Features\WorkTracker;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CommentController
{
    private $service;
    private $jwtConfig;

    public function __construct()
    {
        $this->service = new CommentService();
        $this->jwtConfig = require $_SERVER['DOCUMENT_ROOT'] . '/config/jwt.php';
    }

    private function getUserIdFromToken()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            return null;
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return null;
        }

        try {
            $decoded = JWT::decode($parts[1], new Key($this->jwtConfig['secret_key'], 'HS256'));
            return $decoded->data->id ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function sendUnauthorized()
    {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }

    private function sendResult($result)
    {
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

    /**
     * POST /task-manager/{taskId}/comments - Create a comment
     */
    public function create($taskId)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            $this->sendUnauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->create($data, $userId, (int) $taskId);
        $this->sendResult($result);
    }

    /**
     * GET /task-manager/{taskId}/comments - Get all comments for a task
     */
    public function getAll($taskId)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            $this->sendUnauthorized();
            return;
        }

        $result = $this->service->getByTaskId((int) $taskId, $userId);
        $this->sendResult($result);
    }

    /**
     * GET /task-manager/{taskId}/comments/count - Get comment count for a task
     */
    public function getCount($taskId)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            $this->sendUnauthorized();
            return;
        }

        $result = $this->service->getCommentCount((int) $taskId, $userId);
        $this->sendResult($result);
    }

    /**
     * PATCH /task-manager/{taskId}/comments/{id} - Update a comment
     */
    public function update($taskId, $id)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            $this->sendUnauthorized();
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->update((int) $id, $data, $userId, (int) $taskId);
        $this->sendResult($result);
    }

    /**
     * DELETE /task-manager/{taskId}/comments/{id} - Delete a comment
     */
    public function delete($taskId, $id)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            $this->sendUnauthorized();
            return;
        }

        $result = $this->service->delete((int) $id, $userId, (int) $taskId);
        $this->sendResult($result);
    }
}

// Made with Bob
