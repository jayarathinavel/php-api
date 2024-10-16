<?php
    namespace Features\Auth;

    class Auth {
        private $id;
        private $name;
        private $email;
        private $password;
        private $role;

        public function __construct($name, $email, $password, $role = 'user', $id = null) {
            $this->id = $id;
            $this->name = $name;
            $this->email = $email;
            $this->password = $password;
            $this->role = $role;
        }

        public function getId() {
            return $this->id;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function getName() {
            return $this->name;
        }

        public function setName($name) {
            $this->name = $name;
        }


        public function getEmail() {
            return $this->email;
        }

        public function setEmail($email) {
            $this->email = $email;
        }

        public function getPassword() {
            return $this->password;
        }
    
        public function setPassword($password) {
            $this->password = $password;
        }

        public function getRole() {
            return $this->role;
        }
    
        public function setRole($role) {
            $this->role = $role;
        }

        public function toArray() {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'role'  => $this->role,
            ];
        }

        public static function fromArray($data) {
            return new self($data['name'], $data['email'], $data['password'], $data['role'] ?? 'user', $data['id'] ?? null);
        }
    }
