<?php

namespace Project\Src;

use PDO;
use PDOException;

class DB
{

    private string $database = 'crud';
    private string $host = 'localhost';
    private string $user = 'root';
    private string $pass = '';
    private PDO $db;


    public function __construct()
    {
        $this->db = new PDO("mysql:host=$this->host;dbname=$this->database", $this->user, $this->pass);
    }

    public function query($rawSql, $params = []): bool|string|int
    {
        try {
            return $this->exec($rawSql, $params);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function exec($rawSql, $params = []): bool|int
    {
        $stmt = $this->db->prepare($rawSql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            return 0;
        }

        return true;

    }

    public function bindParams($stmt, $params = []): void
    {
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
    }

    public function select($rawSql, $params = []): array|string
    {
        try {
            $stmt = $this->db->prepare($rawSql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

}