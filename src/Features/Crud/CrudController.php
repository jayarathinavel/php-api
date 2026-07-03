<?php
    namespace Features\Crud;

    use Core\BaseController;
    use Core\ApiException;

    class CrudController extends BaseController {
        private $crudService;

        public function __construct() {
            parent::__construct();
            $this->crudService = new CrudService();
        }

        public function list($appId, $featureName) {
            try {
                $this->respond($this->crudService->list($appId, $featureName));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function get($appId, $featureName, $id) {
            try {
                $this->respond($this->crudService->get($appId, $featureName, $id));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function create($appId, $featureName) {
            try {
                $payload = $this->getJsonBodyForCrud();
                $this->respond($this->crudService->create($appId, $featureName, $payload));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function createMany($appId, $featureName) {
            try {
                $payload = $this->getJsonBodyForCrud();
                $this->respond($this->crudService->createMany($appId, $featureName, $payload));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function update($appId, $featureName, $id) {
            try {
                $payload = $this->getJsonBodyForCrud();
                $this->respond($this->crudService->update($appId, $featureName, $id, $payload));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function delete($appId, $featureName, $id) {
            try {
                $this->respond($this->crudService->delete($appId, $featureName, $id));
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        /**
         * Get JSON body for CRUD operations (allows null/empty for some operations)
         */
        private function getJsonBodyForCrud() {
            $body = file_get_contents('php://input');
            if ($body === '') {
                return null;
            }

            $payload = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException(
                    'Invalid JSON in request body: ' . json_last_error_msg(),
                    400,
                    'INVALID_JSON_PAYLOAD'
                );
            }
            return $payload;
        }

        private function respond($result) {
            header('Content-Type: application/json');
            http_response_code($result['status']);

            if ($result['status'] !== 204) {
                echo json_encode($result['body']);
            }
        }
    }

// Made with Bob
