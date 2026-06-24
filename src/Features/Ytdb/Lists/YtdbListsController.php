<?php

    namespace Features\Ytdb\Lists;

    use Features\Ytdb\Lists\YtdbListsService;
    use Features\Ytdb\YtdbBaseController;
    use Features\Ytdb\YtdbException;

    
    class YtdbListsController extends YtdbBaseController {
        private $service;


        public function __construct() {
            parent::__construct();
            $this->service = new YtdbListsService();
        }

        public function createList() {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $rawInput = file_get_contents('php://input');
                $data = json_decode($rawInput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new YtdbException(
                        'Invalid JSON in request body: ' . json_last_error_msg(),
                        400,
                        'INVALID_JSON_PAYLOAD'
                    );
                }

                if (empty($data)) {
                    throw new YtdbException('Request body cannot be empty', 400, 'INVALID_JSON_PAYLOAD');
                }

                $result = $this->service->createList($data, $userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in createList controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function updateList($id) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $rawInput = file_get_contents('php://input');
                $data = json_decode($rawInput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new YtdbException(
                        'Invalid JSON in request body: ' . json_last_error_msg(),
                        400,
                        'INVALID_JSON_PAYLOAD'
                    );
                }

                if (empty($data)) {
                    throw new YtdbException('Request body cannot be empty', 400, 'INVALID_JSON_PAYLOAD');
                }

                $result = $this->service->updateList($id, $data, $userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in updateList controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function deleteList($id) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $result = $this->service->deleteList($id, $userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in deleteList controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getLists() {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $result = $this->service->getLists($userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getLists controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getAllLists() {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }
    
                $result = $this->service->getAllLists($userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllLists controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }
    
        public function getListById($id) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }
    
                $result = $this->service->getListById($id, $userId);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getListById controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getCommunityUsers() {
            try {
                $appId = $this->getAppId();
                if (!$appId) {
                    throw new YtdbException('App ID is required', 400, 'MISSING_APP_ID');
                }

                // Get current user ID to exclude from results
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                // Get limit from query parameter
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

                $result = $this->service->getCommunityUsers($appId, $userId, $limit);
                return $this->sendResult($result);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getCommunityUsers controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }
        
    }
