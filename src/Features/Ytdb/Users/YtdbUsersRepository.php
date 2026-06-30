<?php

namespace Features\Ytdb\Users;

use Core\Database;

class YtdbUsersRepository {
    private $connection;
    private $avatarRepository;

    public function __construct() {
        $this->connection = Database::getInstance()->getConnection();
        $this->avatarRepository = new YtdbUsersAvatarRepository();
    }

    /**
     * Find a ytdb user by ID with avatar_id
     * Only returns users with app_id = 'ytdb'
     */
    public function findById($userId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('u.id', 'u.name', 'u.email', 'u.role', 'u.created_at', 'u.updated_at', 'yu.avatar_id')
            ->from('users', 'u')
            ->leftJoin('u', 'ytdb_users', 'yu', 'u.id = yu.user_id')
            ->where('u.id = :id')
            ->andWhere('u.app_id = :app_id')
            ->setParameter('id', $userId)
            ->setParameter('app_id', 'ytdb')
            ->executeQuery();

        $user = $result->fetchAssociative();
        
        // Ensure ytdb_users record exists for this user
        if ($user && !array_key_exists('avatar_id', $user)) {
            $this->avatarRepository->getOrCreate($userId);
            // Re-fetch to get the created record
            return $this->findById($userId);
        }

        return $user;
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
