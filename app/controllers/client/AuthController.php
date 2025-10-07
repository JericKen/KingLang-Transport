<?php

require_once __DIR__ . '/../../../config/database.php';

require_once __DIR__ . '/../../models/client/AuthModel.php';

require_once __DIR__ . "/../AuditTrailTrait.php";



class ClientAuthController {

    use AuditTrailTrait;

    private $authModel;



    public function __construct() {

        $this->authModel = new ClientAuthModel();

    }



    public function loginForm() {

        require_once __DIR__ . "/../../views/client/login.php";

    }



    public function signupForm() {

        require_once __DIR__ . "/../../views/client/signup.php";

    }



    public function manageAccountForm() {

        require_once __DIR__ . "/../../views/client/user_account.php";

    }



    public function signup() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);

        

        $first_name = trim($data["firstName"]);

        $last_name = trim($data["lastName"]);

        $company_name = isset($data["companyName"]) ? trim($data["companyName"]) : null;

        $email = trim($data["email"]);

        $contact_number = trim($data["contactNumber"]);

        $password = trim($data["password"]);

        $confirm_password = trim($data["confirmPassword"]);

        

        if (empty($first_name) || empty($last_name) || empty($email) || empty($contact_number) || empty($password) || empty($confirm_password)) {

            echo json_encode(["success" => false, "message" => "Please fill out all fields."]);

            return;

        }



        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            echo json_encode(["success" => false, "message" => "Invalid email address."]);

            return;

        }

        

        if (!empty($contact_number)) {

            $isValid = (preg_match('/^\+63 \d{3} \d{3} \d{4}$/', $contact_number));        // +63 917 123 4567

            

            if (!$isValid) {

                echo json_encode([

                    "success" => false,

                    "message" => "Contact number must be in the format 09XXXXXXXXX, 09XX-XXX-XXXX, 639XXXXXXXXX, or +63 XXX XXX XXXX."

                ]);

                return;

            }

        }



    

        if ($password !== $confirm_password) {

            echo json_encode(["success" => false, "message" => "Password did not match."]);

            return;

        }

        

        $message = $this->authModel->signup($first_name, $last_name, $company_name, $email, $contact_number, $password);

    

        if ($message === "success") {

            echo json_encode(["success" => true, "message" => "Sign up successfully."]);

        } else {

            echo json_encode(["success" => false, "message" => $message]);

        }

        

    }



    public function login() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);



        $email = trim($data["email"]);

        $password = trim($data["password"]);



        if (empty($email) || empty($password)) {

            echo json_encode(["success" => false, "message" => "Please fill out required fields."]);

            return;

        } 



        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {   

           echo json_encode(["success" => false, "message" => "Invalid email address."]);

           return;

        }

    

        $result = $this->authModel->login($email, $password);



        if ($result === "success") {

            // Log successful client login to audit trail

            $loginData = [

                'email' => $email,

                'login_time' => date('Y-m-d H:i:s'),

                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,

                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null

            ];

            $this->logAudit('login', 'user', $_SESSION['user_id'] ?? null, null, $loginData, $_SESSION['user_id']);

            

            echo json_encode(["success" => true, "redirect" => "/home/booking-requests"]);

        } else {

            // Log failed client login attempt

            $failedLoginData = [

                'email' => $email,

                'attempt_time' => date('Y-m-d H:i:s'),

                'failure_reason' => $result,

                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
 
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null

            ];

            $this->logAudit('login_failed', 'user', null, null, $failedLoginData, $_SESSION['user_id'] ?? null);

            

            echo json_encode(["success" => false, "message" => $result]);

        }

    }



    public function googleLogin() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        

        if (empty($data["credential"])) {

            echo json_encode(["success" => false, "message" => "Invalid Google credentials"]);

            return;

        }

        

        $credential = $data["credential"];

        

        // Decode the JWT token from Google

        $parts = explode('.', $credential);

        if (count($parts) !== 3) {

            echo json_encode(["success" => false, "message" => "Invalid token format"]);

            return;

        }

        

        // Get the payload part (second part of the JWT)

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        

        if (!$payload || !isset($payload['email']) || !isset($payload['name'])) {

            echo json_encode(["success" => false, "message" => "Invalid token payload"]);

            return;

        }

        

        $email = $payload['email'];

        $name = $payload['name'];

        $given_name = $payload['given_name'] ?? '';

        $family_name = $payload['family_name'] ?? '';

        $picture = $payload['picture'] ?? '';

        

        // If first and last names are not provided, split the full name

        if (empty($given_name) || empty($family_name)) {

            $nameParts = explode(' ', $name);

            $given_name = $nameParts[0];

            $family_name = count($nameParts) > 1 ? end($nameParts) : '';

        }

        

        // Check if the user exists, if not, create a new account

        $result = $this->authModel->googleLogin($email, $given_name, $family_name, $picture);

        

        if ($result === "success") {

            // Log successful client login to audit trail

            $loginData = [

                'email' => $email,

                'login_time' => date('Y-m-d H:i:s'),

                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,

                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null

            ];

            $this->logAudit('login', 'user', $_SESSION['user_id'] ?? null, null, $loginData, $_SESSION['user_id']);    

            

            echo json_encode(["success" => true, "redirect" => "/home/booking-requests"]);

        } else {

            echo json_encode(["success" => false, "message" => $result]);

        }

    }



    public function getClientInformation() {

        $client = $this->authModel->getClientInformation();



        header("Content-Type: application/json");



        if (is_array($client)) {

            echo json_encode(['success' => true, 'client' => $client]);

        } else {

            echo json_encode(['success' => false, 'message' => $client]);

        }

    }



    public function updateClientInformation() {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $data = json_decode(file_get_contents("php://input"), true);



            $first_name = $data["firstName"];

            $last_name = $data["lastName"];

            $company_name = isset($data["companyName"]) ? $data["companyName"] : null;

            $contact_number = $data["contactNumber"];

            $email_address = $data["email"];

            $address = isset($data["address"]) ? $data["address"] : null;

            

            // Validate phone number (11 digits starting with 09)

            // Check both formats: with hyphens (09XX-XXX-XXXX) or without (09XXXXXXXX)

            if (!empty($contact_number)) {



                $isValid = (

                    preg_match('/^09\d{9}$/', $contact_number) ||                       // 09123456789

                    preg_match('/^639\d{9}$/', $contact_number) ||                     // 639123456789

                    preg_match('/^09\d{2}-\d{3}-\d{4}$/', $contact_number) ||          // 09XX-XXX-XXXX

                    preg_match('/^\+63 \d{3} \d{3} \d{4}$/', $contact_number)          // +63 917 123 4567

                );



                if (!$isValid) {

                    header("Content-Type: application/json");

                    echo json_encode([

                        "success" => false,

                        "message" => "Contact number must be in the format 09XXXXXXXXX, 639XXXXXXXXX, 09XX-XXX-XXXX, or +63 XXX XXX XXXX."

                    ]);

                    return;

                }

            }







            $result = $this->authModel->updateClientInformation($first_name, $last_name, $company_name, $contact_number, $email_address, $address);



            header("Content-Type: application/json");



            if ($result === "success") {

                echo json_encode(['success' => true, 'message' => 'Updated successfully!']);

            } else {

                echo json_encode(['success' => false, 'message' => $result]);

            }

        }

    }



    public function logout() {

        if (isset($_SESSION['user_id'])) {

            $logoutData = [

                'logout_time' => date('Y-m-d H:i:s'),

                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,

                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null

            ];

            $this->logAudit('logout', 'user', $_SESSION['user_id'], null, $logoutData, $_SESSION['user_id'] ?? null);

        }



        // Only unset client-specific session variables

        unset($_SESSION["user_id"]);

        unset($_SESSION["email"]);

        unset($_SESSION["client_name"]);

        // Don't destroy the entire session as it affects admin login

        // $_SESSION = array();

        // session_destroy();

        header("Location: /home");

        exit();

    }



    public function showForgotForm() {

        include __DIR__ . '/../../views/client/forgot_password.php';

    }



    public function showResetForm($token) {

        $user = $this->authModel->findByToken($token);

        if ($user) {

            include __DIR__ . '/../../views/client/reset_password.php';

        } else {

            echo "Invalid or expired token.";

        }

    }



    public function sendResetLink() {

        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $email = trim($data["email"]);  



        $user = $this->authModel->findByEmail($email);

        if ($user) {

            $token = bin2hex(random_bytes(16));

            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->authModel->saveResetToken($email, $token, $expiry);



            // PHPMailer setup

            require_once __DIR__ . '/../../../vendor/autoload.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer();

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';

            $mail->SMTPAuth = true;

            $mail->Username = 'vjericken@gmail.com';

            $mail->Password = 'bhlo vzae uepw ypxl';

            $mail->SMTPSecure = 'tls';

            $mail->Port = 587;



            // configure mail (host, SMTP, etc.)

            $mail->setFrom('vjericken@gmail.com', 'Booking System');

            $mail->addAddress($email);

            $mail->isHTML(true);

            $mail->Subject = "Reset Password";

            $mail->Body = "Click here to reset your password: 

                <a href='http://localhost:9999/reset-password/$token'>Reset Password</a>";

            $mail->send();

            echo json_encode(["success" => true, "message" => "Reset link sent to your email."]);

        } else {

            echo json_encode(["success" => false, "message" => "Email not found."]);

        }

    }



    public function resetPassword() {

        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $token = trim($data["token"]);

        $newPassword = trim($data["newPassword"]);



        $validPassword = $this->authModel->isValidPassword($newPassword);

        if (!$validPassword) {

            echo json_encode(["success" => false, "message" => "Invalid password."]);

            return;

        }



        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $result = $this->authModel->updatePassword($token, $hashedPassword);

        if ($result) {

            echo json_encode(["success" => true, "message" => "Password reset successfully."]);

        } else {

            echo json_encode(["success" => false, "message" => "Failed to reset password."]);

        }

    }



    /**

     * Update password for logged-in user (AJAX endpoint)

     */

    public function updateClientPassword() {

        header("Content-Type: application/json");

        if (!isset($_SESSION["user_id"])) {

            echo json_encode(["success" => false, "message" => "Not authenticated."]);

            return;

        }

        $data = json_decode(file_get_contents("php://input"), true);

        $currentPassword = $data["currentPassword"] ?? '';

        $newPassword = $data["newPassword"] ?? '';

        $confirmPassword = $data["confirmPassword"] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {

            echo json_encode(["success" => false, "message" => "All fields are required."]);

            return;

        }

        if ($newPassword !== $confirmPassword) {

            echo json_encode(["success" => false, "message" => "New passwords do not match."]);

            return;

        }

        $result = $this->authModel->updatePasswordForLoggedInUser($_SESSION["user_id"], $currentPassword, $newPassword);

        echo json_encode($result);

    }



    public function uploadProfileImage() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            header("Content-Type: application/json");
            
            // Check if file was uploaded
            if (!isset($_FILES['profileImage']) || $_FILES['profileImage']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(["success" => false, "message" => "No image file uploaded or upload error"]);
                return;
            }
            
            $file = $_FILES['profileImage'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(["success" => false, "message" => "Invalid file type. Please upload a JPEG, PNG, or GIF image."]);
                return;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(["success" => false, "message" => "File size too large. Please upload an image smaller than 5MB."]);
                return;
            }
            
            // Additional security check - verify it's actually an image
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                echo json_encode(["success" => false, "message" => "Invalid image file. Please upload a valid image."]);
                return;
            }
            
            // Check image dimensions (max 2000x2000 pixels)
            if ($imageInfo[0] > 2000 || $imageInfo[1] > 2000) {
                echo json_encode(["success" => false, "message" => "Image dimensions too large. Please upload an image smaller than 2000x2000 pixels."]);
                return;
            }
            
            $result = $this->authModel->uploadProfileImage($file);
            
            if ($result['success']) {
                // Update session with new profile picture
                $_SESSION["profile_picture"] = $result['image_url'];
                echo json_encode(["success" => true, "message" => "Profile image updated successfully", "image_url" => $result['image_url']]);
            } else {
                echo json_encode(["success" => false, "message" => $result['message']]);
            }
        }
    }

    public function removeProfileImage() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            header("Content-Type: application/json");
            
            $result = $this->authModel->removeProfileImage();
            
            if ($result['success']) {
                // Remove from session
                unset($_SESSION["profile_picture"]);
                echo json_encode(["success" => true, "message" => "Profile image removed successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => $result['message']]);
            }
        }
    }

}

?>