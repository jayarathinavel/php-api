<?php

    namespace Features\Ytdb\Lists;

    use Features\Ytdb\YtdbException;

    class YtdbListsService {
        private $repository;

        public function __construct() {
            $this->repository = new YtdbListsRepository();
        }

        public function createList($data, $userId) {
            try {
                // Validate required fields
                if (empty($data['name'])) {
                    throw new YtdbException('List name is required', 400, 'MISSING_REQUIRED_FIELD');
                }
    
                // Validate visibility
                $validVisibilities = ['public', 'private'];
                if (isset($data['visibility']) && !in_array($data['visibility'], $validVisibilities)) {
                    throw new YtdbException('Invalid visibility value. Must be "public" or "private"', 400, 'INVALID_INPUT_FORMAT');
                }
    
                $list = new YtdbLists(
                    null,
                    $userId,
                    $data['name'],
                    $data['description'] ?? null,
                    $data['emoji'] ?? '📋',
                    $data['visibility'] ?? 'private',
                    null,
                    null
                );
                $createdData = $this->repository->createList($list);
                
                // Handle both array and object responses
                $responseData = is_array($createdData) ? $createdData : $createdData->toArray();
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'statusCode' => 201
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in createList: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while creating the list',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function updateList($id, $data, $userId) {
            try {
                // Validate list ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid list ID', 400, 'INVALID_LIST_ID');
                }
    
                // Validate required fields
                if (empty($data['name'])) {
                    throw new YtdbException('List name is required', 400, 'MISSING_REQUIRED_FIELD');
                }
    
                // Validate visibility
                $validVisibilities = ['public', 'private'];
                if (isset($data['visibility']) && !in_array($data['visibility'], $validVisibilities)) {
                    throw new YtdbException('Invalid visibility value. Must be "public" or "private"', 400, 'INVALID_INPUT_FORMAT');
                }
    
                $existingList = $this->repository->getListById($id);
                if ($existingList === null) {
                    throw new YtdbException('List not found', 404, 'LIST_NOT_FOUND');
                }
    
                if (!$this->checkIfListBelongsToTheUser($id, $userId)) {
                    throw new YtdbException('You do not have permission to update this list', 403, 'RESOURCE_NOT_OWNED');
                }
    
                $list = new YtdbLists(
                    $id,
                    $userId,
                    $data['name'],
                    $data['description'] ?? null,
                    $data['emoji'] ?? '📋',
                    $data['visibility'] ?? 'private',
                    null,
                    null
                );
                $updatedData = $this->repository->updateList($list);
                
                // Handle both array and object responses
                $responseData = is_array($updatedData) ? $updatedData : $updatedData->toArray();
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in updateList: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while updating the list',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function deleteList($id, $userId) {
            try {
                // Validate list ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid list ID', 400, 'INVALID_LIST_ID');
                }

                $existingList = $this->repository->getListById($id);
                if ($existingList === null) {
                    throw new YtdbException('List not found', 404, 'LIST_NOT_FOUND');
                }

                if (!$this->checkIfListBelongsToTheUser($id, $userId)) {
                    throw new YtdbException('You do not have permission to delete this list', 403, 'RESOURCE_NOT_OWNED');
                }

                $data = $this->repository->deleteList($id, $userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in deleteList: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while deleting the list',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function getLists($userId) {
            try {
                $data = $this->repository->getLists($userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (\Exception $e) {
                error_log('Unexpected error in getLists: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching lists',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }

        public function getAllLists($userId) {
            try {
                $data = $this->repository->getAllLists($userId);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (\Exception $e) {
                error_log('Unexpected error in getAllLists: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching public lists',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }
    
        public function getListById($id, $userId) {
            try {
                // Validate list ID
                if (empty($id) || !is_numeric($id)) {
                    throw new YtdbException('Invalid list ID', 400, 'INVALID_LIST_ID');
                }
    
                $listData = $this->repository->getListById($id);
                if ($listData === null) {
                    throw new YtdbException('List not found', 404, 'LIST_NOT_FOUND');
                }
    
                // Check if user can access this list (owner or public list)
                $isOwner = $this->checkIfListBelongsToTheUser($id, $userId);
                $isPublic = isset($listData['visibility']) && $listData['visibility'] === 'public';
    
                if (!$isOwner && !$isPublic) {
                    throw new YtdbException('You do not have permission to view this list', 403, 'RESOURCE_NOT_ACCESSIBLE');
                }
    
                return [
                    'success' => true,
                    'data' => $listData,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in getListById: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching the list',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }
    
        public function checkIfListBelongsToTheUser($id, $userId) {
            return $this->repository->checkIfListBelongsToTheUser($id, $userId);
        }

        public function getCommunityUsers($appId, $limit = null) {
            try {
                $data = $this->repository->getCommunityUsers($appId, $limit);
                return [
                    'success' => true,
                    'data' => $data,
                    'statusCode' => 200
                ];
            } catch (YtdbException $e) {
                return $e->toArray();
            } catch (\Exception $e) {
                error_log('Unexpected error in getCommunityUsers: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'An unexpected error occurred while fetching community users',
                    'errorType' => 'INTERNAL_SERVER_ERROR',
                    'statusCode' => 500
                ];
            }
        }
    }
