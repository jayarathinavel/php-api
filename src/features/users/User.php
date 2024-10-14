<?php
    namespace Features\Users;

    class User {
        private $id;
        private $name;
        private $email;

        public function __construct($name, $email, $id = null) {
            $this->id = $id;
            $this->name = $name;
            $this->email = $email;
        }

        public function getId() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function getEmail() {
            return $this->email;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function setName($name) {
            $this->name = $name;
        }

        public function setEmail($email) {
            $this->email = $email;
        }

        public function toArray() {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
            ];
        }

        public static function fromArray($data) {
            return new self($data['name'], $data['email'], $data['id'] ?? null);
        }
    }
