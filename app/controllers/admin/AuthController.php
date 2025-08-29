<?php
require_once __DIR__ . "/../../models/admin/AuthModel.php";
require_once __DIR__ . "/../AuditTrailTrait.php";

class AuthController {
    use AuditTrailTrait;
    public $authModel;

    public function __construct() {
        $this->authModel = new AuthModel();
    }

    public function loginForm() {
        require_once __DIR__ . "/../../views/admin/login.php";
    }

    public function adminDashBoard() {
        require_once __DIR__ . "/../../views/admin/dashboard.php";
    }

    public function login() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $email = trim($data["email"]);
        $password = trim($data["password"]);

        if (empty($email) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Please fill out all fields"]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email."]);
            return;
        }

        $message = $this->authModel->login($email, $password);

        if ($message === "success") {
            // Log successful login to audit trail
            $loginData = [
                'email' => $email,
                'login_time' => date('Y-m-d H:i:s'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
            $this->logAudit('login', 'user', $_SESSION['admin_id'] ?? null, null, $loginData, $_SESSION['admin_id'] ?? null);
            
            echo json_encode(["success" => true, "redirect" => "/admin/dashboard"]);
        } else {
            // Log failed login attempt
            $failedLoginData = [
                'email' => $email,
                'attempt_time' => date('Y-m-d H:i:s'),
                'failure_reason' => $message,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
            $this->logAudit('login_failed', 'user', null, null, $failedLoginData, $_SESSION['admin_id'] ?? null);
            
            echo json_encode(["success" => false, "message" => $message]);
        }      
    }

    public function logout() {
        // Log logout to audit trail before clearing session
        if (isset($_SESSION['admin_id'])) {
            $logoutData = [
                'logout_time' => date('Y-m-d H:i:s'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
            $this->logAudit('logout', 'user', $_SESSION['admin_id'], null, $logoutData, $_SESSION['admin_id'] ?? null);
        }
        
        // Only unset admin-specific session variables
        unset($_SESSION["role"]);
        unset($_SESSION["admin_name"]);
        unset($_SESSION["admin_id"]);
        // Don't destroy the entire session as it affects client login
        // $_SESSION = array();
        // session_destroy();
        header("Location: /admin/login");
        exit();
    }
}


?>