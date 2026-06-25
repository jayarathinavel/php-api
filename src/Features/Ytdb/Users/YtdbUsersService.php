<?php

namespace Features\Ytdb\Users;

use Features\Ytdb\YtdbException;

class YtdbUsersService {
    private $repository;

    public function __construct() {
        $this->repository = new YtdbUsersRepository();
    }

    /**
     * Update user profile (name only)
     * Only works for ytdb users
     */
    public function updateProfile($userId, $data) {
        // Validate user exists in ytdb
        if (!$this->repository->existsInYtdb($userId)) {
            throw new YtdbException('User not found or does not belong to ytdb app', 404);
        }

        // Validate name
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new YtdbException('Name is required and cannot be empty', 400);
        }

        $name = trim($data['name']);

        // Validate name length
        if (strlen($name) < 2) {
            throw new YtdbException('Name must be at least 2 characters long', 400);
        }

        if (strlen($name) > 255) {
            throw new YtdbException('Name cannot exceed 255 characters', 400);
        }

        // Update profile
        $success = $this->repository->updateProfile($userId, $name);

        if (!$success) {
            throw new YtdbException('Failed to update profile', 500);
        }

        // Return updated user data
        $updatedUser = $this->repository->findById($userId);
        
        if (!$updatedUser) {
            throw new YtdbException('Failed to retrieve updated user data', 500);
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $updatedUser
        ];
    }

    /**
     * Get user profile
     * Only works for ytdb users
     */
    public function getProfile($userId) {
        $user = $this->repository->findById($userId);

        if (!$user) {
            throw new YtdbException('User not found or does not belong to ytdb app', 404);
        }

        return [
            'success' => true,
            'user' => $user
        ];
    }
}

// Made with Bob
