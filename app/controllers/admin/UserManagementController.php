<?php

require_once __DIR__ . "/../../models/admin/UserManagementModel.php";

require_once __DIR__ . "/../AuditTrailTrait.php";



class UserManagementController {

    use AuditTrailTrait;

    private $userModel;

    

    public function __construct() {

        $this->userModel = new UserManagementModel();

        

        // Check if session is started

        if (session_status() == PHP_SESSION_NONE) {

            session_start();

        }

        

        // Only check authentication if this is an admin route

        if (!$this->isAdminLoginPage()) {

            // Only redirect if not on login paths

            if (!isset($_SESSION['role'])) {

                header('Location: /admin/login');

                exit();

            } else if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Super Admin') {

                header('Location: /admin/login');

                exit();

            }

        }

    }

    

    // Helper method to check if current page is admin login

    private function isAdminLoginPage() {

        $requestUri = $_SERVER['REQUEST_URI'];

        return strpos($requestUri, '/admin/login') !== false || 

               strpos($requestUri, '/admin/submit-login') !== false ||

               strpos($requestUri, '/home') === 0 ||

               $requestUri === '/';

    }

    

    public function showUserManagement() {

        require_once __DIR__ . "/../../views/admin/user_management.php";

    }

    

    public function getUserListing() {

        // Check if it's a POST request with JSON data

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

            // Get JSON data

            $json = file_get_contents('php://input');

            $data = json_decode($json, true);

            

            $page = isset($data['page']) ? (int)$data['page'] : 1;

            $limit = isset($data['limit']) ? (int)$data['limit'] : 10;

            $searchTerm = isset($data['search']) ? $data['search'] : '';

            $sortColumn = isset($data['sortColumn']) ? $data['sortColumn'] : 'created_at';

            $sortDirection = isset($data['sortDirection']) ? $data['sortDirection'] : 'DESC';

            $roleFilter = isset($data['roleFilter']) ? $data['roleFilter'] : '';

        } else {

            // Handle regular GET requests for backward compatibility

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

            $sortColumn = isset($_GET['sortColumn']) ? $_GET['sortColumn'] : 'created_at';

            $sortDirection = isset($_GET['sortDirection']) ? $_GET['sortDirection'] : 'DESC';

            $roleFilter = isset($_GET['roleFilter']) ? $_GET['roleFilter'] : '';

        }

        

        $offset = ($page - 1) * $limit;
        
        // Support viewing deleted users
        $includeDeleted = isset($data) ? ($data['includeDeleted'] ?? false) : ($_GET['includeDeleted'] ?? false);

        if ($includeDeleted) {
            $users = $this->userModel->getDeletedUsers($offset, $limit, $searchTerm, $sortColumn, $sortDirection, $roleFilter);
            $totalUsers = $this->userModel->getTotalDeletedUsersCount($searchTerm, $roleFilter);
        } else {
            $users = $this->userModel->getAllUsers($offset, $limit, $searchTerm, $sortColumn, $sortDirection, $roleFilter);
            $totalUsers = $this->userModel->getTotalUsersCount($searchTerm, $roleFilter);
        }
        
        $totalPages = ceil($totalUsers / $limit);
        
        header('Content-Type: application/json');
        echo json_encode([
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'roleFilter' => $roleFilter,
            'includeDeleted' => (bool)$includeDeleted
        ]);

    }

    

    public function getUserDetails() {

        // Check if it's a POST request with JSON data

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

            // Get JSON data

            $json = file_get_contents('php://input');

            $data = json_decode($json, true);

            

            if (!isset($data['userId'])) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'User ID is required']);

                return;

            }

            

            $userId = (int)$data['userId'];

        } else {

            // Handle regular GET requests for backward compatibility

            if (!isset($_GET['userId'])) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'User ID is required']);

                return;

            }

            

            $userId = (int)$_GET['userId'];

        }

        

        $user = $this->userModel->getUserById($userId);

        

        header('Content-Type: application/json');

        echo json_encode($user);

    }

    

    public function addUser() {

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid request method']);

            return;

        }

        

        // Check if it's a JSON request

        if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

            // Get JSON data

            $json = file_get_contents('php://input');

            $data = json_decode($json, true);

            

            $firstName = isset($data['firstName']) ? trim($data['firstName']) : '';

            $lastName = isset($data['lastName']) ? trim($data['lastName']) : '';

            $email = isset($data['email']) ? trim($data['email']) : '';

            $contactNumber = isset($data['contactNumber']) ? trim($data['contactNumber']) : '';

            $password = isset($data['password']) ? $data['password'] : '';

            $role = isset($data['role']) ? $data['role'] : 'Client';

        } else {

            // Get traditional POST data

            $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';

            $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';

            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            $contactNumber = isset($_POST['contactNumber']) ? trim($_POST['contactNumber']) : '';

            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $role = isset($_POST['role']) ? $_POST['role'] : 'Client';

        }

        

        // Validate input

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'All fields are required']);

            return;

        }

        

        // Validate email

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid email format']);

            return;

        }

        

        // Validate contact number (if provided)

        if (

            !empty($contactNumber) &&

            !preg_match('/^09\d{2}-\d{3}-\d{4}$/', $contactNumber) &&     // 09XX-XXX-XXXX

            !preg_match('/^09\d{9}$/', $contactNumber) &&                 // 09123456789

            !preg_match('/^639\d{9}$/', $contactNumber) &&                // 639123456789

            !preg_match('/^\+63 \d{3} \d{3} \d{4}$/', $contactNumber)     // +63 938 438 4943

        ) {

            header('Content-Type: application/json');

            echo json_encode([

                'error' => 'Contact number must be in one of the following formats: 09XX-XXX-XXXX, 09123456789, 639XXXXXXXXX, or +63 XXX XXX XXXX'

            ]);

            return;

        }

        

        

        

        // Validate password (minimum 6 characters)

        if (strlen($password) < 8) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must be at least 8 characters']);

            return;

        }

        

        // Enhanced password validation

        if (!preg_match('/[A-Z]/', $password)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must contain at least one uppercase letter']);

            return;

        }

        

        if (!preg_match('/[a-z]/', $password)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must contain at least one lowercase letter']);

            return;

        }

        

        if (!preg_match('/[0-9]/', $password)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must contain at least one number']);

            return;

        }

        

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must contain at least one special character']);

            return;

        }

        

        // Validate role

        $validRoles = ['Client', 'Admin', 'Super Admin'];

        if (!in_array($role, $validRoles)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid role']);

            return;

        }

        

        // Create user

        $result = $this->userModel->createUser($firstName, $lastName, $email, $contactNumber, $password, $role);

        

        // Log to audit trail if successful

        if ($result['success'] && isset($result['user_id'])) {

            $newUserData = [

                'first_name' => $firstName,

                'last_name' => $lastName,

                'email' => $email,

                'contact_number' => $contactNumber,

                'role' => $role

            ];

            $this->logAudit('create', 'user', $result['user_id'], null, $newUserData, $_SESSION['admin_id']);

        }

        

        header('Content-Type: application/json');

        echo json_encode($result);

    }

    

    public function updateUser() {

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid request method']);

            return;

        }

        

        // Check if it's a JSON request

        if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

            // Get JSON data

            $json = file_get_contents('php://input');

            $data = json_decode($json, true);

            

            $userId = isset($data['userId']) ? (int)$data['userId'] : 0;

            $firstName = isset($data['firstName']) ? trim($data['firstName']) : '';

            $lastName = isset($data['lastName']) ? trim($data['lastName']) : '';

            $email = isset($data['email']) ? trim($data['email']) : '';

            $contactNumber = isset($data['contactNumber']) ? trim($data['contactNumber']) : '';

            $password = isset($data['password']) && !empty($data['password']) ? $data['password'] : null;

            $role = isset($data['role']) ? $data['role'] : 'Client';

            $companyName = isset($data['companyName']) ? $data['companyName'] : '';

        } else {

            // Get traditional POST data

            $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;

            $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';

            $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';

            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            $contactNumber = isset($_POST['contactNumber']) ? trim($_POST['contactNumber']) : '';

            $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;

            $role = isset($_POST['role']) ? $_POST['role'] : 'Client';

            $companyName = isset($_POST['companyName']) ? $_POST['companyName'] : '';

        }

        

        // Validate input

        if ($userId <= 0 || empty($firstName) || empty($lastName) || empty($email)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'User ID, first name, last name, and email are required']);

            return;

        }

        

        // Validate email

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid email format']);

            return;

        }

        

        // Validate contact number (if provided)

        if (

            !empty($contactNumber) &&

            !preg_match('/^09\d{2}-\d{3}-\d{4}$/', $contactNumber) &&     // 09XX-XXX-XXXX

            !preg_match('/^09\d{9}$/', $contactNumber) &&                 // 09123456789

            !preg_match('/^639\d{9}$/', $contactNumber) &&                // 639123456789

            !preg_match('/^\+63 \d{3} \d{3} \d{4}$/', $contactNumber)     // +63 938 438 4943

        ) {

            header('Content-Type: application/json');

            echo json_encode([

                'error' => 'Contact number must be in one of the following formats: 09XX-XXX-XXXX, 09123456789, 639XXXXXXXXX, or +63 XXX XXX XXXX'

            ]);

            return;

        }

        

        

        

        // Validate password (if provided)

        if ($password !== null && strlen($password) < 8) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Password must be at least 8 characters']);

            return;

        }

        

        // Enhanced password validation (if password is provided)

        if ($password !== null && !empty($password)) {

            if (!preg_match('/[A-Z]/', $password)) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'Password must contain at least one uppercase letter']);

                return;

            }

            

            if (!preg_match('/[a-z]/', $password)) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'Password must contain at least one lowercase letter']);

                return;

            }

            

            if (!preg_match('/[0-9]/', $password)) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'Password must contain at least one number']);

                return;

            }

            

            if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {

                header('Content-Type: application/json');

                echo json_encode(['error' => 'Password must contain at least one special character']);

                return;

            }

        }

        

        // Validate role

        $validRoles = ['Client', 'Admin', 'Super Admin'];

        if (!in_array($role, $validRoles)) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid role']);

            return;

        }

        

        // Get old user data for audit trail

        $oldUserData = $this->getEntityBeforeUpdate('users', 'user_id', $userId);

        

        // Update user

        $result = $this->userModel->updateUser($userId, $firstName, $lastName, $email, $contactNumber, $role, $companyName, $password);

        

        // Log to audit trail if successful

        if ($result['success']) {

            $newUserData = [

                'first_name' => $firstName,

                'last_name' => $lastName,

                'email' => $email,

                'contact_number' => $contactNumber,

                'role' => $role,

                'company_name' => $companyName

            ];

            if ($password !== null) {

                $newUserData['password_changed'] = true;

            }

            $this->logAudit('update', 'user', $userId, $oldUserData, $newUserData, $_SESSION['admin_id']);

        }

        

        header('Content-Type: application/json');

        echo json_encode($result);

    }

    

    public function deleteUser() {

        // Check if it's a POST request

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid request method']);

            return;

        }

        

        // Check if it's a JSON request

        if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {

            // Get JSON data

            $json = file_get_contents('php://input');

            $data = json_decode($json, true);

            

            $userId = isset($data['userId']) ? (int)$data['userId'] : 0;

        } else {

            // Get traditional POST data

            $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;

        }

        

        if ($userId <= 0) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'User ID is required']);

            return;

        }

        

        // Get user data before deletion for audit trail

        $oldUserData = $this->getEntityBeforeUpdate('users', 'user_id', $userId);

        

        // Delete user

        $result = $this->userModel->deleteUser($userId);

        

        // Log to audit trail if successful

        if (!empty($result['success'])) {

            $this->logAudit('delete', 'user', $userId, $oldUserData, null, $_SESSION['admin_id']);

        }

        

        header('Content-Type: application/json');

        echo json_encode($result);

    }

    public function restoreUser() {

        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid request method']);
            return;
        }

        // Check if it's a JSON request
        if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            // Get JSON data
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $userId = isset($data['userId']) ? (int)$data['userId'] : 0;
        } else {
            // Get traditional POST data
            $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
        }

        if ($userId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User ID is required']);
            return;
        }

        // Get user data before restore for audit trail
        $oldUserData = $this->getEntityBeforeUpdate('users', 'user_id', $userId);

        // Restore user
        $result = $this->userModel->restoreUser($userId);

        // Log to audit trail if successful
        if (!empty($result['success'])) {
            $this->logAudit('restore', 'user', $userId, $oldUserData, null, $_SESSION['admin_id']);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    

    public function getUserStats() {

        header('Content-Type: application/json');

        

        $method = $_SERVER['REQUEST_METHOD'];

        

        if ($method === 'GET') {

            try {

                $model = new UserManagementModel();

                $stats = $model->getUserStatistics();

                

                echo json_encode([

                    'status' => 'success',

                    'data' => $stats

                ]);

            } catch (Exception $e) {

                echo json_encode([

                    'status' => 'error',

                    'message' => $e->getMessage()

                ]);

            }

        } else {

            echo json_encode([

                'status' => 'error',

                'message' => 'Method not allowed'

            ]);

        }

    }

}

?> 