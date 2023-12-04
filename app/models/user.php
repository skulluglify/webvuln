<?php
// create connection
$pdo = new PDO(dsn: 'mysql:host=localhost;dbname=todos', username: 'root', password: '');

// // create table
// users
function create_table_users()
{
    global $pdo;

    $sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(32) NOT NULL
    )";

    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// todos
function create_table_todos()
{
    global $pdo;

    $sql = "CREATE TABLE IF NOT EXISTS todos (
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(30) NOT NULL,
    deadline TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL,
    finished_at TIMESTAMP NOT NULL,
    )";

    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// initial tables on database!
create_table_users();
create_table_todos()

// login
function login($email, $password)
{
    global $pdo;

    $sql = "SELECT * FROM users WHERE email=`" . $email . "` AND password=`" . $password . "`";
    $stmt = $pdo->prepare($sql);
    $temp = $stmt->fetchAll();

    if (count($temp) != 0) {
        return true;
    }

    return false;
}

// insert
function insert($description, $deadline, $created_at)
{
    global $pdo;
    $sql = "INSERT INTO todos (description, deadline, created_at) VALUES(`" . $description . "` , `" . $deadline . "`, `" . $created_at . "`)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// read
function read_all()
{
    global $pdo;

    $sql = "SELECT * FROM todos";
    $stmt = $pdo->prepare($sql);
    $temp = $stmt->fetchAll();

    return $temp;
}

// read_one
function read_one($id)
{
    global $pdo;

    // get row by id
    $sql = "SELECT * FROM todos WHERE id=`" . $id . "` LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $temp = $stmt->fetch();

    return $temp;
}

// update
function update($id, $description, $deadline)
{
    global $pdo;

    // update row by id
    $sql = "UPDATE todos SET description=`" . $description . "`, deadline=`" . $deadline . "` WHERE id=`" . $id . "`";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// delete
function delete($id)
{
    global $pdo;

    // update row by id
    $sql = "DELETE FROM todos WHERE id=`" . $id . "`";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
