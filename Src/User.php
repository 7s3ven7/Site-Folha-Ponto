<?php

namespace Src;

require 'DB.php';

class User
{

    private DB $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function getUsers(): array
    {
        return $this->db->select('SELECT * FROM users');
    }

    public function getUser(int $id): array
    {
        return $this->db->select('SELECT * FROM users WHERE `id` = :id', [':id' => $id]);
    }

    public function saveUser(?string $name = null, ?string $password = null): string
    {
        return match (null) {
            $name || $password => json_encode(['error' => 'Not Found Data']),
            default => $this->db->query(
                "INSERT INTO users (`name`, `password`) VALUES (:name, :password)",
                [':name' => $name, ':password' => $password]),
        };
    }

    public function modifyUser(?int $id = null, ?string $name = null, ?string $password = null): string
    {

        return match (null) {
            $name || $password => json_encode(['error' => 'Not Found Data']),
            $id => json_encode(['error' => 'Not Found Id']),
            default => $this->db->query(
                'UPDATE users SET `name` = :name, `password` = :password WHERE `id` = :id',
                [':name' => $name, ':password' => $password, ':id' => $id]),
        };

    }

    public function partialModifyUser(?int $id = null, ?string $name = null, ?string $password = null): string
    {

        $fields = ['id', 'name', 'password'];
        $queryFields = [];
        $params = [];

        foreach ($fields as $field) {
            if (!isset($$field)) {
                continue;
            }

            $queryFields[] = $field . ' = :' . $field;
            $params[':' . $field] = $$field;
        }

        return match (null) {
            $name && $password => json_encode(['error' => 'Not Found Data']),
            $id => json_encode(['error' => 'Not Found Id']),
            default => $this->db->query(
                'UPDATE users SET ' . implode(',', $queryFields) . ' WHERE `id` = :id',
                $params),
        };

    }

    public function deleteUser(?int $id = null): int
    {
        if (!is_null($id) &&
            $this->db->query('DELETE FROM users WHERE `id` = :id', [':id' => $id]) &&
            !is_array($this->db->query('SELECT * FROM `users` WHERE `id` = :id', ['id' => $id]))) {
            return 1;
        };

        return 0;

    }

}