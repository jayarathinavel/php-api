<?php
    namespace Features\Admin;

    class AdminQueryService {
        private $adminQueryRepository;

        public function __construct() {
            $this->adminQueryRepository = new AdminQueryRepository();
        }

        public function execute($payload) {
            $params = [];

            if (is_string($payload)) {
                $query = $payload;
            } elseif (is_array($payload)) {
                $query = $payload['query'] ?? null;
                $params = $payload['params'] ?? [];
            } else {
                return $this->error(400, 'Body must be a raw SQL string or JSON object');
            }

            if (!is_string($query) || trim($query) === '') {
                return $this->error(400, 'SQL query is required');
            }

            if (!is_array($params)) {
                return $this->error(400, 'params must be an array or object');
            }

            $normalizedQuery = $this->normalizeQuery($query);
            if (!$normalizedQuery) {
                return $this->error(400, 'Only one SQL statement is allowed');
            }

            if (!$this->isAllowedStatement($normalizedQuery)) {
                return $this->error(400, 'Only DDL and DML statements are allowed');
            }

            try {
                $affectedRows = $this->adminQueryRepository->execute($normalizedQuery, $params);

                return [
                    'status' => 200,
                    'body' => [
                        'success' => true,
                        'message' => 'Query executed successfully',
                        'affected_rows' => $affectedRows,
                    ],
                ];
            } catch (\Throwable $exception) {
                return $this->error(400, $exception->getMessage());
            }
        }

        private function normalizeQuery($query) {
            $query = trim($query);
            $query = preg_replace('/;+\s*$/', '', $query);

            return strpos($query, ';') === false ? $query : null;
        }

        private function isAllowedStatement($query) {
            $query = $this->stripLeadingComments($query);
            if (!preg_match('/^\s*([a-zA-Z]+)/', $query, $matches)) {
                return false;
            }

            $keyword = strtoupper($matches[1]);
            $allowedKeywords = [
                'ALTER',
                'CREATE',
                'DELETE',
                'DROP',
                'INSERT',
                'RENAME',
                'REPLACE',
                'TRUNCATE',
                'UPDATE',
            ];

            return in_array($keyword, $allowedKeywords, true);
        }

        private function stripLeadingComments($query) {
            do {
                $before = $query;
                $query = preg_replace('/^\s*--[^\r\n]*(\r\n|\r|\n|$)/', '', $query);
                $query = preg_replace('/^\s*#[^\r\n]*(\r\n|\r|\n|$)/', '', $query);
                $query = preg_replace('/^\s*\/\*.*?\*\/\s*/s', '', $query);
            } while ($query !== $before);

            return $query;
        }

        private function error($status, $message) {
            return [
                'status' => $status,
                'body' => [
                    'success' => false,
                    'message' => $message,
                ],
            ];
        }
    }
