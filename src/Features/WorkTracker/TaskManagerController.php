<?php
    namespace Features\WorkTracker;

    use Core\BaseController;
    use Core\ApiException;

    class TaskManagerController extends BaseController {
        private $service;

        public function __construct() {
            parent::__construct();
            $this->service = new TaskManagerService();
        }

        /**
         * POST /task-manager - Create a task
         */
        public function create() {
            try {
                $userId = $this->getUserIdFromToken();
                $data = $this->getJsonBody(true);
                
                $result = $this->service->create($data, $userId);
                $this->sendResult($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        /**
         * GET /task-manager - Get all tasks
         */
        public function getAll() {
            try {
                $userId = $this->getUserIdFromToken();
                
                $result = $this->service->getAllByUserId($userId);
                $this->sendResult($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        /**
         * GET /task-manager/{id} - Get a specific task
         */
        public function getById($id) {
            try {
                $userId = $this->getUserIdFromToken();
                
                $result = $this->service->getById($id, $userId);
                $this->sendResult($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        /**
         * PATCH /task-manager/{id} - Update a task
         */
        public function update($id) {
            try {
                $userId = $this->getUserIdFromToken();
                $data = $this->getJsonBody(true);
                
                $result = $this->service->update($id, $data, $userId);
                $this->sendResult($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        /**
         * DELETE /task-manager/{id} - Delete a task
         */
        public function delete($id) {
            try {
                $userId = $this->getUserIdFromToken();
                
                $result = $this->service->delete($id, $userId);
                $this->sendResult($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
    }

// Made with Bob
