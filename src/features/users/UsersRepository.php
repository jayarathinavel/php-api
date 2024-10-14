<?php
    namespace Features\Users;
    use Core\Database;

    class UsersRepository {
        private $connection;

        public function __construct() {
            $this->connection = Database::getInstance()->getConnection();
        }

        public function save(User $user) {
            $data = [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ];

            if ($user->getId()) {
                // Update existing user
                $this->connection->update('users', $data, ['id' => $user->getId()]);
            } else {
                // Insert new user
                $this->connection->insert('users', $data);
                $user->setId($this->connection->lastInsertId());
            }

            return $user;
        }

        public function findAll() {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from('users')
                ->executeQuery();

            $users = [];
            while ($row = $result->fetchAssociative()) {
                $users[] = User::fromArray($row);
            }

            return $users;
        }

        public function findById($id) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $result = $queryBuilder
                ->select('*')
                ->from('users')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->executeQuery();

            $userData = $result->fetchAssociative();
            return $userData ? User::fromArray($userData) : null;
        }

        public function delete($id) {
            return $this->connection->delete('users', ['id' => $id]) > 0;
        }
    }
