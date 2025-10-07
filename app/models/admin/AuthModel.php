<?php

require_once __DIR__ . "/../../../config/database.php";



class AuthModel {

    private $conn;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }



    public function emailExists($email) {

        try {

            $stmt = $this->conn->prepare("SELECT email FROM users WHERE email = :email AND (role = 'Super Admin' OR role = 'Admin') AND deleted_at IS NULL");

            $stmt->execute([":email" => $email]);

            return $stmt->fetch() ? true : false;

        } catch (PDOException $e) {

            return "Database error.";

        }

    }



    public function login($email, $password) {

        try {

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email AND (role = 'Super Admin' OR role = 'Admin') AND deleted_at IS NULL");

            $stmt->execute([":email" => $email]);



            $user = $stmt->fetch(PDO::FETCH_ASSOC);



            if (!$user) {

                return "Email not found.";

            }



            if ($user && password_verify($password, $user["password"])) {

                $_SESSION["role"] = $user["role"];

                $_SESSION["admin_name"] = $user["first_name"] . " " . $user["last_name"];

                $_SESSION["admin_id"] = $user["user_id"];



                error_log("Admin login successful: " . $_SESSION["admin_id"]);

                return "success";

            } 



            return "Incorrect password";

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }

}

?>