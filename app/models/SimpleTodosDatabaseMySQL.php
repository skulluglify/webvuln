<?php
namespace App\Models;

use PDO;
use PDOException;

interface ISimpleTodosDatabaseMySQL
{
    public function init(): void;
    public function create_table_user(): bool;
    public function create_table_todos(): bool;
    public function add_user(string $name,string $email,string $password): bool;
    public function get_user(string $name,string $email = ''): ?array;
    public function add_todo(string $description,int $deadline): bool;
    public function get_todos(): ?array;
    public function edit_todo(int $id, string $description, int $deadline, bool $finished = false): bool;
    public function del_todo(int $id): bool;
}

class SimpleTodosDatabaseMySQL implements ISimpleTodosDatabaseMySQL
{
    private PDO $_pdo;
    public function __construct()
    {
        $pdo = new PDO(dsn: 'mysql:host=localhost;dbname=todos',
            username: 'user',
            password: '1234',
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $pdo;
    }
    public function init(): void
    {
        $this->create_table_user();
        $this->create_table_todos();
    }
    public function create_table_user(): bool
    {
        $pdo = $this->_pdo;
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(30) UNIQUE NOT NULL,
                `email` VARCHAR(50) UNIQUE NOT NULL,
                `password` VARCHAR(32) NOT NULL)";

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
    public function create_table_todos(): bool
    {
        $pdo = $this->_pdo;
        $sql = "CREATE TABLE IF NOT EXISTS `todos` (
                `id` INT(6) AUTO_INCREMENT PRIMARY KEY,
                `description` VARCHAR(200) UNIQUE NOT NULL,
                `deadline` TIMESTAMP NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                `finished_at` TIMESTAMP NULL DEFAULT NULL)";

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
    public function add_user(string $name,string $email,string $password): bool
    {
        if (empty($name) or empty($email) or empty($password)) return false;

        $pdo = $this->_pdo;
        $sql = "INSERT INTO `users`(`name`,`email`,`password`) 
                VALUES('".$name."','".$email."','".$password."')";

        try {
            $stmt = $pdo->prepare($sql);
            //$stmt->bindParam(':name',$name);
            //$stmt->bindParam(':email',$email);
            //$stmt->bindParam(':password',$password);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
    public function get_user(string $name = '', string $email = ''): ?array
    {
        if (empty($name) and empty($email)) return null;

        $pdo = $this->_pdo;
        $sql = "SELECT `name`,`email`,`password` 
                FROM `users` 
                WHERE `name` = '".$name."' OR `email` = '".$email."' 
                LIMIT 1";

        try {
            $stmt = $pdo->prepare($sql);
            $exec = $stmt->execute();

            if ($exec) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($data) && is_array($data)) return $data;
            }

        } catch (PDOException) {}
        return null;
    }
    public function add_todo(string $description,int $deadline): bool
    {
        if (empty($description) or empty($deadline)) return false;
        $created_at = time_unix();

        $pdo = $this->_pdo;
        $sql = "INSERT INTO `todos`(`description`,`deadline`,`created_at`) 
                VALUES('".$description."','".datetime_from_timestamp($deadline)."',
                '".datetime_from_timestamp($created_at)."')";

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
    public function get_todos(): ?array
    {
        $pdo = $this->_pdo;
        $sql = "SELECT `id`, `description`, `deadline`, `created_at`, `finished_at` 
                FROM `todos`";

        try {
            $stmt = $pdo->prepare($sql);
            $exec = $stmt->execute();
            if (!empty($exec)) {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($data)) return $data;
            }

        } catch (PDOException) {}
        return null;
    }
    public function edit_todo(int $id, string $description, int $deadline, bool $finished = false): bool
    {
        if (empty($id) or empty($description) or empty($deadline)) return false;
        $finished_at = $finished ? time_unix() : null;

        $pdo = $this->_pdo;
        if (!empty($finished_at))
            $sql = "UPDATE `todos` 
                    SET `description` = '".$description."',
                        `deadline` = '".datetime_from_timestamp($deadline)."',
                        `finished_at` = '".datetime_from_timestamp($finished_at)."' 
                    WHERE `id` = ".$id;
        else
            $sql = "UPDATE `todos` 
                    SET `description` = '".$description."',
                        `deadline` = '".datetime_from_timestamp($deadline)."'
                    WHERE `id` = ".$id;

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
    public function del_todo(int $id): bool
    {
        if (empty($id)) return false;

        $pdo = $this->_pdo;
        $sql = "DELETE FROM `todos` WHERE `id` = ".$id;

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();

        } catch (PDOException) {}
        return false;
    }
}
