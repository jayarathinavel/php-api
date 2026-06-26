<?php

namespace Features\Ytdb\Users;

use Features\Ytdb\YtdbException;

class YtdbUsersService {
    private $repository;
    private $avatarRepository;

    public function __construct() {
        $this->repository = new YtdbUsersRepository();
        $this->avatarRepository = new YtdbUsersAvatarRepository();
    }

    /**
     * Update user profile (name and/or avatar_id)
     * Only works for ytdb users
     */
    public function updateProfile($userId, $data) {
        // Validate user exists in ytdb
        if (!$this->repository->existsInYtdb($userId)) {
            throw new YtdbException('User not found or does not belong to ytdb app', 404);
        }

        $updated = false;

        // Update name if provided
        if (isset($data['name'])) {
            if (empty(trim($data['name']))) {
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

            // Update profile name
            $success = $this->repository->updateProfile($userId, $name);

            if (!$success) {
                throw new YtdbException('Failed to update profile', 500);
            }
            $updated = true;
        }

        // Update avatar_id if provided
        if (isset($data['avatar_id'])) {
            $avatarId = $data['avatar_id'];

            // Validate avatar_id (must be null or 1-20)
            if ($avatarId !== null && (!is_numeric($avatarId) || $avatarId < 1 || $avatarId > 20)) {
                throw new YtdbException('Avatar ID must be between 1 and 20, or null', 400);
            }

            // Ensure ytdb_users record exists
            $this->avatarRepository->getOrCreate($userId);

            // Update avatar
            $success = $this->avatarRepository->updateAvatar($userId, $avatarId);

            if (!$success) {
                throw new YtdbException('Failed to update avatar', 500);
            }
            $updated = true;
        }

        if (!$updated) {
            throw new YtdbException('No valid fields provided for update', 400);
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
