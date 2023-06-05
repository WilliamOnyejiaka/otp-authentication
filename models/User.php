<?php

declare(strict_types=1);
namespace Model;

ini_set('display_errors', true);

require_once __DIR__ . "/../vendor/autoload.php";

class User extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->tbl_name = "users";
        $this->create_query = "CREATE TABLE $this->tbl_name (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            email VARCHAR(80) NOT NULL,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

        )";
    }

    public function create_user(string $name, string $email,string $password)
    {
        $query = "INSERT INTO $this->tbl_name(name,email,password) VALUES(?,?,?)";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $email = htmlspecialchars(strip_tags($email));
        $password = htmlspecialchars(strip_tags($password));

        $stmt->bind_param("sss", $name, $email,$password);
        return $stmt->execute() ? true : false;
    }

    public function get_user_with_id(int $id)
    {
        $query = "SELECT * FROM $this->tbl_name WHERE id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("i", $id);

        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

    public function get_user_with_email(string $email)
    {
        $query = "SELECT * FROM $this->tbl_name WHERE email = ?";
        $stmt = $this->connection->prepare($query);

        $email = htmlspecialchars(strip_tags($email));

        $stmt->bind_param("s", $email);

        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

// public function get_auth_with_email(string $email)
// {
//     $query = "SELECT * FROM $this->tbl_name WHERE user_id = ?";
//     $stmt = $this->connection->prepare($query);

//     $stmt->bind_param("i", $user_id);

//     $executed = $stmt->execute() ? true : false;
//     $this->execution_error($executed);
//     return $stmt->get_result();
// }
}