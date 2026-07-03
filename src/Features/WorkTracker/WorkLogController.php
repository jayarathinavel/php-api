<?php
    namespace Features\WorkTracker;

    use Core\BaseController;
    use Core\ApiException;

    class WorkLogController extends BaseController {
        private $service;

        public function __construct() {
            parent::__construct();
            $this->service = new WorkLogService();
        }

        /**
         * POST /work-log - Create a work log
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
         * GET /work-log - Get all work logs
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
         * GET /work-log/{id} - Get a specific work log
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
         * PATCH /work-log/{id} - Update a work log
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
         * DELETE /work-log/{id} - Delete a work log
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
