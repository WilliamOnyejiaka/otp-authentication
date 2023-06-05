<?php

declare(strict_types=1);
namespace Model;

ini_set('display_errors', true);

require_once __DIR__ . "/../vendor/autoload.php";

class Authentication extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->tbl_name = "authentication";
        $this->create_query = "CREATE TABLE $this->tbl_name (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token TEXT NULL,
            token_expiration_time INT UNSIGNED NOT NULL,
            otp INT(4) UNSIGNED NOT NULL,
            otp_expiration_time INT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }

    public function create_auth(int $user_id,int $otp,int $otp_expiration_time)
    {
        $query = "INSERT INTO $this->tbl_name(user_id,otp,otp_expiration_time) VALUES(?,?,?)";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("iii", $user_id,$otp,$otp_expiration_time);
        return $stmt->execute() ? true : false;
    }

    public function get_auth_with_user_id(int $user_id)
    {
        $query = "SELECT * FROM $this->tbl_name WHERE user_id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("i", $user_id);

        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

    public function get_auth_with_id(int $id)
    {
        $query = "SELECT * FROM $this->tbl_name WHERE id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("i", $id);

        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

    public function update_auth_otp(int $user_id,int $otp, int $otp_expiration_time)
    {
        $query = "UPDATE $this->tbl_name SET otp = ?,otp_expiration_time = ? WHERE user_id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("iii",$otp,$otp_expiration_time,$user_id);
        return $stmt->execute() ?? false;
    }

    public function update_auth_token(int $user_id, string $token, int $token_expiration_time)
    {
        $query = "UPDATE $this->tbl_name SET token = ?,token_expiration_time = ? WHERE user_id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("sii", $token, $token_expiration_time, $user_id);
        return $stmt->execute() ?? false;
    }
    public function delete_auth(int $id)
    {
        $query = "DELETE FROM $this->tbl_name WHERE id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("i",$id);
        return $stmt->execute() ?? false;
    }
}