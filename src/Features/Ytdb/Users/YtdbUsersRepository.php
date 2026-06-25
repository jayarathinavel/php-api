<?php

namespace Features\Ytdb\Users;

use Core\Database;

class YtdbUsersRepository {
    private $connection;

    public function __construct() {
        $this->connection = Database::getInstance()->getConnection();
    }

    /**
     * Find a ytdb user by ID
     * Only returns users with app_id = 'ytdb'
     */
    public function findById($userId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
            ->from('users')
            ->where('id = :id')
            ->andWhere('app_id = :app_id')
            ->setParameter('id', $userId)
            ->setParameter('app_id', 'ytdb')
            ->executeQuery();

        return $result->fetchAssociative();
    }

    /**
     * Update ytdb user profile (name only)
     * Only updates users with app_id = 'ytdb'
     */
    public function updateProfile($userId, $name) {
        $data = [
            'name' => $name,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $affectedRows = $this->connection->update(
            'users',
            $data,
            [
                'id' => $userId,
                'app_id' => 'ytdb'
            ]
        );

        return $affectedRows > 0;
    }

    /**
     * Check if user exists and belongs to ytdb app
     */
    public function existsInYtdb($userId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('COUNT(*) as count')
            ->from('users')
            ->where('id = :id')
            ->andWhere('app_id = :app_id')
            ->setParameter('id', $userId)
            ->setParameter('app_id', 'ytdb')
            ->executeQuery();

        $row = $result->fetchAssociative();
        return $row['count'] > 0;
    }
}

// Made with Bob
