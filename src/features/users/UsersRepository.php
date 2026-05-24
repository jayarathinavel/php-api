<?php
    namespace Features\Users;
    use Core\Database;

    class UsersRepository {
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function save(User $user) {
            if (empty($user->getAppId())) {
                throw new \InvalidArgumentException('app_id is required for user operations');
            }

            $data = [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role'  => $user->getRole(),
                'app_id' => $user->getAppId()
            ];

            if ($user->getPassword()) {
                $data['password'] = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            }

            if ($user->getId()) {
                // Update existing user scoped by app
                $this->connection->update('users', $data, ['id' => $user->getId(), 'app_id' => $user->getAppId()]);
            } else {
                // Insert new user
                $this->connection->insert('users', $data);
                $user->setId($this->connection->lastInsertId());
            }

            return $user;
        }

        public function findAll($appId) {
            if (empty($appId)) {
                return [];
            }

            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from('users')
                ->where('app_id = :app_id')
                ->setParameter('app_id', $appId)
                ->executeQuery();

            $users = [];
            while ($row = $result->fetchAssociative()) {
                $users[] = User::fromArray($row);
            }

            return $users;
        }

        public function findById($id, $appId) {
            if (empty($appId)) {
                return null;
            }

            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from('users')
                ->where('id = :id')
                ->andWhere('app_id = :app_id')
                ->setParameter('id', $id)
                ->setParameter('app_id', $appId)
                ->executeQuery();

            $userData = $result->fetchAssociative();
            return $userData ? User::fromArray($userData) : null;
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
            return $userData ? User::fromArray($userData) : null;
        }

        public function delete($id, $appId) {
            if (empty($appId)) {
                return false;
            }

            return $this->connection->delete('users', ['id' => $id, 'app_id' => $appId]) > 0;
        }
    }
