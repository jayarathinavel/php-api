<?php
    namespace Core;

    class InputValidator {
        /**
         * Validate email format and length
         */
        public static function validateEmail($email) {
            if (empty($email)) {
                return ['valid' => false, 'message' => 'Email is required'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'message' => 'Invalid email format'];
            }

            if (strlen($email) > 255) {
                return ['valid' => false, 'message' => 'Email is too long (max 255 characters)'];
            }

            return ['valid' => true];
        }

        /**
         * Validate string length
         */
        public static function validateStringLength($value, $fieldName, $minLength = 1, $maxLength = 255) {
            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            $length = strlen($value);

            if ($length < $minLength) {
                return ['valid' => false, 'message' => "$fieldName is too short (min $minLength characters)"];
            }

            if ($length > $maxLength) {
                return ['valid' => false, 'message' => "$fieldName is too long (max $maxLength characters)"];
            }

            return ['valid' => true];
        }

        /**
         * Validate integer value
         */
        public static function validateInteger($value, $fieldName, $min = null, $max = null) {
            if (!is_numeric($value) || (int)$value != $value) {
                return ['valid' => false, 'message' => "$fieldName must be an integer"];
            }

            $intValue = (int)$value;

            if ($min !== null && $intValue < $min) {
                return ['valid' => false, 'message' => "$fieldName must be at least $min"];
            }

            if ($max !== null && $intValue > $max) {
                return ['valid' => false, 'message' => "$fieldName must be at most $max"];
            }

            return ['valid' => true, 'value' => $intValue];
        }

        /**
         * Validate positive integer (ID)
         */
        public static function validateId($value, $fieldName = 'ID') {
            $result = self::validateInteger($value, $fieldName, 1);
            return $result;
        }

        /**
         * Sanitize string input
         */
        public static function sanitizeString($value) {
            return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
        }

        /**
         * Validate URL format
         */
        public static function validateUrl($url, $fieldName = 'URL') {
            if (empty($url)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return ['valid' => false, 'message' => "Invalid $fieldName format"];
            }

            if (strlen($url) > 2048) {
                return ['valid' => false, 'message' => "$fieldName is too long (max 2048 characters)"];
            }

            return ['valid' => true];
        }

        /**
         * Validate date format (YYYY-MM-DD)
         */
        public static function validateDate($date, $fieldName = 'Date') {
            if (empty($date)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            $d = \DateTime::createFromFormat('Y-m-d', $date);
            if (!$d || $d->format('Y-m-d') !== $date) {
                return ['valid' => false, 'message' => "$fieldName must be in YYYY-MM-DD format"];
            }

            return ['valid' => true];
        }

        /**
         * Validate datetime format (YYYY-MM-DD HH:MM:SS)
         */
        public static function validateDateTime($datetime, $fieldName = 'DateTime') {
            if (empty($datetime)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            $d = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
            if (!$d || $d->format('Y-m-d H:i:s') !== $datetime) {
                return ['valid' => false, 'message' => "$fieldName must be in YYYY-MM-DD HH:MM:SS format"];
            }

            return ['valid' => true];
        }

        /**
         * Validate enum value against allowed values
         */
        public static function validateEnum($value, $allowedValues, $fieldName = 'Value') {
            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            if (!in_array($value, $allowedValues, true)) {
                $allowed = implode(', ', $allowedValues);
                return ['valid' => false, 'message' => "$fieldName must be one of: $allowed"];
            }

            return ['valid' => true];
        }

        /**
         * Validate boolean value
         */
        public static function validateBoolean($value, $fieldName = 'Value') {
            if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                return ['valid' => false, 'message' => "$fieldName must be a boolean value"];
            }

            return ['valid' => true, 'value' => filter_var($value, FILTER_VALIDATE_BOOLEAN)];
        }

        /**
         * Validate array and check if it's not empty
         */
        public static function validateArray($value, $fieldName = 'Array') {
            if (!is_array($value)) {
                return ['valid' => false, 'message' => "$fieldName must be an array"];
            }

            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName cannot be empty"];
            }

            return ['valid' => true];
        }

        /**
         * Validate JSON string
         */
        public static function validateJson($value, $fieldName = 'JSON') {
            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['valid' => false, 'message' => "$fieldName must be valid JSON"];
            }

            return ['valid' => true];
        }

        /**
         * Validate alphanumeric string (letters and numbers only)
         */
        public static function validateAlphanumeric($value, $fieldName = 'Value') {
            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            if (!ctype_alnum($value)) {
                return ['valid' => false, 'message' => "$fieldName must contain only letters and numbers"];
            }

            return ['valid' => true];
        }

        /**
         * Validate slug format (lowercase letters, numbers, hyphens)
         */
        public static function validateSlug($value, $fieldName = 'Slug') {
            if (empty($value)) {
                return ['valid' => false, 'message' => "$fieldName is required"];
            }

            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
                return ['valid' => false, 'message' => "$fieldName must contain only lowercase letters, numbers, and hyphens"];
            }

            return ['valid' => true];
        }
    }

// Made with Bob
