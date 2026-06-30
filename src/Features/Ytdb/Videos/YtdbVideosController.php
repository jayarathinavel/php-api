<?php

    namespace Features\Ytdb\Videos;

    use Features\Ytdb\Videos\YtdbVideosService;
    use Features\Ytdb\YtdbBaseController;
    use Features\Ytdb\YtdbException;

    class YtdbVideosController extends YtdbBaseController {
        
        private $service;

        public function __construct() {
            parent::__construct();
            $this->service = new YtdbVideosService();
        }

        public function createVideo() {
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

                $response = $this->service->createVideo($data);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in createVideo controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function updateVideo($id) {
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

                $response = $this->service->updateVideo($id, $data, $userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in updateVideo controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function deleteVideo($id) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $response = $this->service->deleteVideo($id, $userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in deleteVideo controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getMyVideos() {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $response = $this->service->getMyVideos($userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getMyVideos controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getAllVideos() {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $response = $this->service->getAllVideos($userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllVideos controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getAllVideosByListId($listId) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $response = $this->service->getAllVideosByListId($listId, $userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllVideosByListId controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }

        public function getVideoById($id) {
            try {
                $userId = $this->getUserIdFromToken();
                if (!$userId) {
                    throw new YtdbException('User authentication required', 401, 'UNAUTHORIZED');
                }

                $response = $this->service->getVideoById($id, $userId);
                return $this->sendResult($response);
                
            } catch (YtdbException $e) {
                return $this->sendResult($e->toArray());
            } catch (\Exception $e) {
                error_log('Unexpected error in getVideoById controller: ' . $e->getMessage());
                return $this->sendResult([
                    'success' => false,
                    'error' => 'An unexpected error occurred',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ]);
            }
        }
    }
