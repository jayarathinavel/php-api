<?php

    namespace Features\Ytdb\Lists;

    class YtdbListsService {
        private $repository;

        public function __construct() {
            $this->repository = new YtdbListsRepository();
        }

        public function createList($data, $userId) {
            $list = new YtdbLists(
                null,
                $userId,
                $data['name'],
                $data['description'],
                $data['visibility'],
                null,
                null
            );
            $data = $this->repository->createList($list);
            return [
                'success' => true,
                'data' => $data->toArray(),
                'statusCode' => 201
            ];
        }

        public function updateList($id, $data, $userId) {
            if (!$this->checkIfListBelongsToTheUser($id, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Access denied',
                    'statusCode' => 403
                ];
            }
            $list = new YtdbLists(
                $id,
                $userId,
                $data['name'],
                $data['description'],
                $data['visibility'],
                null,
                null
            );
            $data = $this->repository->updateList($list);
            return [
                'success' => true,
                'data' => $data->toArray(),
                'statusCode' => 200
            ];
        }

        public function deleteList($id, $userId) {
            if (!$this->checkIfListBelongsToTheUser($id, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Access denied',
                    'statusCode' => 403
                ];
            }
            $data = $this->repository->deleteList($id, $userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function getLists($userId) {
            $data = $this->repository->getLists($userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function getAllLists($userId) {
            $data = $this->repository->getAllLists($userId);
            return [
                'success' => true,
                'data' => $data,
                'statusCode' => 200
            ];
        }

        public function checkIfListBelongsToTheUser($id, $userId) {
            return $this->repository->checkIfListBelongsToTheUser($id, $userId);
        }
    }
