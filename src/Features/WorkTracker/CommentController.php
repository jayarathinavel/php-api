<?php

namespace Features\WorkTracker;

use Core\BaseController;
use Core\ApiException;

class CommentController extends BaseController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CommentService();
    }

    /**
     * POST /task-manager/{taskId}/comments - Create a comment
     */
    public function create($taskId)
    {
        try {
            $userId = $this->getUserIdFromToken();
            $data = $this->getJsonBody(true);
            
            $result = $this->service->create($data, $userId, (int) $taskId);
            $this->sendResult($result);
            
        } catch (ApiException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * GET /task-manager/{taskId}/comments - Get all comments for a task
     */
    public function getAll($taskId)
    {
        try {
            $userId = $this->getUserIdFromToken();
            
            $result = $this->service->getByTaskId((int) $taskId, $userId);
            $this->sendResult($result);
            
        } catch (ApiException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * GET /task-manager/{taskId}/comments/count - Get comment count for a task
     */
    public function getCount($taskId)
    {
        try {
            $userId = $this->getUserIdFromToken();
            
            $result = $this->service->getCommentCount((int) $taskId, $userId);
            $this->sendResult($result);
            
        } catch (ApiException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * PATCH /task-manager/{taskId}/comments/{id} - Update a comment
     */
    public function update($taskId, $id)
    {
        try {
            $userId = $this->getUserIdFromToken();
            $data = $this->getJsonBody(true);
            
            $result = $this->service->update((int) $id, $data, $userId, (int) $taskId);
            $this->sendResult($result);
            
        } catch (ApiException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * DELETE /task-manager/{taskId}/comments/{id} - Delete a comment
     */
    public function delete($taskId, $id)
    {
        try {
            $userId = $this->getUserIdFromToken();
            
            $result = $this->service->delete((int) $id, $userId, (int) $taskId);
            $this->sendResult($result);
            
        } catch (ApiException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
}

// Made with Bob
