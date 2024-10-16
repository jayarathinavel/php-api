<?php
namespace Features\Auth;

use Core\Database;

class AuthRepository {
    private $connection;

    public function __construct() {
        $this->connection = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('*')
            ->from('users')
            ->where('email = :email')
            ->setParameter('email', $email)
            ->executeQuery();

        $userData = $result->fetchAssociative();
        return $userData ? Auth::fromArray($userData) : null;
    }

    public function create(Auth $auth) {
        $data = [
            'name' => $auth->getName(),
            'email' => $auth->getEmail(),
            'password' => password_hash($auth->getPassword(), PASSWORD_BCRYPT),
            'role' => $auth->getRole()
        ];

        $this->connection->insert('users', $data);
        $auth->setId($this->connection->lastInsertId());
        return $auth;
    }
}
