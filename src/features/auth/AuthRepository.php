<?php
namespace Features\Auth;

use Core\Database;

class AuthRepository {
    private $connection;

    public function __construct() {
        $this->connection = Database::getInstance()->getConnection();
    }

    public function findByEmail($email, $appId) {
        if (empty($appId)) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('*')
            ->from('users')
            ->where('email = :email')
            ->andWhere('app_id = :app_id')
            ->setParameter('email', $email)
            ->setParameter('app_id', $appId)
            ->executeQuery();

        $userData = $result->fetchAssociative();
        return $userData ? Auth::fromArray($userData) : null;
    }

    public function create(Auth $auth) {
        $data = [
            'name' => $auth->getName(),
            'email' => $auth->getEmail(),
            'password' => password_hash($auth->getPassword(), PASSWORD_BCRYPT),
            'role' => $auth->getRole(),
            'app_id' => $auth->getAppId()
        ];

        $this->connection->insert('users', $data);
        $auth->setId($this->connection->lastInsertId());
        return $auth;
    }
}
