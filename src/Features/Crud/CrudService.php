<?php
    namespace Features\Crud;

    class CrudService {
        private $crudRepository;

        public function __construct() {
            $this->crudRepository = new CrudRepository();
        }

        public function list($appId, $featureName) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            return $this->success(200, [
                'data' => $this->crudRepository->findAll($resource['table_name']),
            ]);
        }

        public function get($appId, $featureName, $id) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            $record = $this->crudRepository->findById($resource['table_name'], $resource['primary_key'], $id);
            if (!$record) {
                return $this->error(404, 'Record not found');
            }

            return $this->success(200, ['data' => $record]);
        }

        public function create($appId, $featureName, $payload) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            $validation = $this->validatePayload($resource, $payload, false);
            if (!$validation['valid']) {
                return $this->error(400, $validation['message']);
            }

            $id = $this->crudRepository->insert($resource['table_name'], $validation['data']);
            $record = $this->crudRepository->findById($resource['table_name'], $resource['primary_key'], $id);

            return $this->success(201, ['message' => 'Record created successfully', 'data' => $record]);
        }

        public function createMany($appId, $featureName, $payload) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            if (!is_array($payload) || $this->isAssociative($payload)) {
                return $this->error(400, 'Body must be an array of records');
            }

            $validatedRecords = [];
            foreach ($payload as $index => $record) {
                $validation = $this->validatePayload($resource, $record, false);
                if (!$validation['valid']) {
                    return $this->error(400, 'Record ' . $index . ': ' . $validation['message']);
                }

                $validatedRecords[] = $validation['data'];
            }

            $created = [];
            foreach ($validatedRecords as $record) {
                $id = $this->crudRepository->insert($resource['table_name'], $record);
                $created[] = $this->crudRepository->findById($resource['table_name'], $resource['primary_key'], $id);
            }

            return $this->success(201, ['message' => 'Records created successfully', 'data' => $created]);
        }

        public function update($appId, $featureName, $id, $payload) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            if (!$this->crudRepository->findById($resource['table_name'], $resource['primary_key'], $id)) {
                return $this->error(404, 'Record not found');
            }

            $validation = $this->validatePayload($resource, $payload, true);
            if (!$validation['valid']) {
                return $this->error(400, $validation['message']);
            }

            $this->crudRepository->update($resource['table_name'], $resource['primary_key'], $id, $validation['data']);
            $record = $this->crudRepository->findById($resource['table_name'], $resource['primary_key'], $id);

            return $this->success(200, ['message' => 'Record updated successfully', 'data' => $record]);
        }

        public function delete($appId, $featureName, $id) {
            $resource = $this->getResource($appId, $featureName);
            if (!$resource) {
                return $this->error(404, 'CRUD resource not found');
            }

            $deleted = $this->crudRepository->delete($resource['table_name'], $resource['primary_key'], $id);
            if (!$deleted) {
                return $this->error(404, 'Record not found');
            }

            return ['status' => 204, 'body' => null];
        }

        private function getResource($appId, $featureName) {
            if (!$this->isSafeName($appId) || !$this->isSafeName($featureName)) {
                return null;
            }

            $resource = $this->crudRepository->findResource($appId, $featureName);
            if (!$resource || !$this->isSafeName($resource['table_name']) || !$this->isSafeName($resource['primary_key'])) {
                return null;
            }

            return $resource;
        }

        private function validatePayload($resource, $payload, $partial) {
            if (!is_array($payload) || !$this->isAssociative($payload)) {
                return ['valid' => false, 'message' => 'Body must be a JSON object'];
            }

            $schema = $this->crudRepository->getTableSchema($resource['table_name']);
            if (empty($schema)) {
                return ['valid' => false, 'message' => 'Target table does not exist'];
            }

            $allowedColumns = [];
            foreach ($schema as $column => $definition) {
                if ($column === $resource['primary_key'] || $definition['auto_increment']) {
                    continue;
                }
                $allowedColumns[$column] = $definition;
            }

            $unknownColumns = array_diff(array_keys($payload), array_keys($allowedColumns));
            if (!empty($unknownColumns)) {
                return ['valid' => false, 'message' => 'Unknown column(s): ' . implode(', ', $unknownColumns)];
            }

            if (!$partial) {
                $missingColumns = [];
                foreach ($allowedColumns as $column => $definition) {
                    $hasDefault = $definition['default'] !== null;
                    if (!$definition['nullable'] && !$hasDefault && !array_key_exists($column, $payload)) {
                        $missingColumns[] = $column;
                    }
                }

                if (!empty($missingColumns)) {
                    return ['valid' => false, 'message' => 'Missing required column(s): ' . implode(', ', $missingColumns)];
                }
            }

            if ($partial && empty($payload)) {
                return ['valid' => false, 'message' => 'Body must contain at least one column'];
            }

            foreach ($payload as $column => $value) {
                $error = $this->validateValue($column, $value, $allowedColumns[$column]);
                if ($error) {
                    return ['valid' => false, 'message' => $error];
                }
            }

            return ['valid' => true, 'data' => $payload];
        }

        private function validateValue($column, $value, $definition) {
            if ($value === null) {
                return $definition['nullable'] ? null : $column . ' cannot be null';
            }

            $type = strtolower($definition['type']);
            $stringTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'date', 'datetime', 'timestamp', 'time', 'year', 'json'];
            $integerTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];
            $decimalTypes = ['decimal', 'float', 'double'];

            if (in_array($type, $integerTypes, true) && !is_int($value)) {
                return $column . ' must be an integer';
            }

            if (in_array($type, $decimalTypes, true) && !is_int($value) && !is_float($value)) {
                return $column . ' must be a number';
            }

            if (in_array($type, $stringTypes, true)) {
                if (!is_string($value)) {
                    return $column . ' must be a string';
                }

                if ($definition['max_length'] !== null && strlen($value) > (int) $definition['max_length']) {
                    return $column . ' must not exceed ' . $definition['max_length'] . ' characters';
                }
            }

            return null;
        }

        private function isSafeName($value) {
            return is_string($value) && preg_match('/^[a-zA-Z0-9_]+$/', $value);
        }

        private function isAssociative($value) {
            if (!is_array($value)) {
                return false;
            }

            return array_keys($value) !== range(0, count($value) - 1);
        }

        private function success($status, $body) {
            return ['status' => $status, 'body' => $body];
        }

        private function error($status, $message) {
            return ['status' => $status, 'body' => ['success' => false, 'message' => $message]];
        }
    }
