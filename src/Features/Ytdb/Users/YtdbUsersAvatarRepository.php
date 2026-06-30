<?php

namespace Features\Ytdb\Users;

use Core\Database;

class YtdbUsersAvatarRepository {
    private $connection;

    public function __construct() {
        $this->connection = Database::getInstance()->getConnection();
    }

    /**
     * Find ytdb_user record by user_id
     */
    public function findByUserId($userId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('id', 'user_id', 'avatar_id', 'created_at', 'updated_at')
            ->from('ytdb_users')
            ->where('user_id = :user_id')
            ->setParameter('user_id', $userId)
            ->executeQuery();

        return $result->fetchAssociative();
    }

    /**
     * Create ytdb_user record for a user
     */
    public function create($userId, $avatarId = null) {
        $data = [
            'user_id' => $userId,
            'avatar_id' => $avatarId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->connection->insert('ytdb_users', $data);
        return $this->connection->lastInsertId();
    }

    /**
     * Update avatar_id for a user
     */
    public function updateAvatar($userId, $avatarId) {
        $data = [
            'avatar_id' => $avatarId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $affectedRows = $this->connection->update(
            'ytdb_users',
            $data,
            ['user_id' => $userId]
        );

        return $affectedRows > 0;
    }

    /**
     * Get or create ytdb_user record for a user
     * Ensures every ytdb user has a record in ytdb_users table
     */
    public function getOrCreate($userId) {
        $ytdbUser = $this->findByUserId($userId);
        
        if (!$ytdbUser) {
            $this->create($userId);
            $ytdbUser = $this->findByUserId($userId);
        }

        return $ytdbUser;
    }

    /**
     * Check if ytdb_user record exists for a user
     */
    public function exists($userId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('COUNT(*) as count')
            ->from('ytdb_users')
            ->where('user_id = :user_id')
            ->setParameter('user_id', $userId)
            ->executeQuery();

        $row = $result->fetchAssociative();
        return $row['count'] > 0;
    }
}

// Made with Bob