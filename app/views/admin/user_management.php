<?php
require_once __DIR__ . "/../../controllers/admin/UserManagementController.php";

if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
    header("Location: /admin/login");
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">  
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>User Management</title>
    <style>
        /* Compact form styling */
        /* .modal-body {
            padding: 1rem 1.5rem;
        }
        .compact-form .mb-3 {
            margin-bottom: 0.5rem !important;
        }
        .compact-form .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        .compact-form .form-control,
        .compact-form .form-select {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
        .compact-form .form-text {
            font-size: 0.75rem;
            margin-top: 0.1rem;
        }
        .modal-footer {
            padding: 0.5rem 1.5rem 1rem;
        } */
        /* Password field styling */
        .input-group {
            position: relative;
            z-index: 1;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover, .password-toggle:focus {
            color: #495057;
            outline: none;
        }
        .password-requirements {
            font-size: 0.75rem;
            line-height: 1.1;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.25rem;
            position: relative;
            z-index: 0;
            padding-top: 0.25rem;
        }
        .requirement {
            display: inline-flex;
            align-items: center;
            margin-right: 0.5rem;
        }
        .requirement i {
            font-size: 0.7rem;
            margin-right: 0.15rem;
        }
        .requirement i.bi-check-circle {
            color: #5db434 !important;
        }
        /* Table sorting styles */
        .table thead th {
            background-color: #d1f7c4;
            font-weight: 600;
            padding: 12px 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
            white-space: nowrap;
            position: relative;
        }   
        .table thead th:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }
        /* .table thead th.active {
            background-color: #b8e6a3;
        } */
        .sort-icon {
            font-size: 0.75rem;
            margin-left: 5px;
            vertical-align: middle;
        }
        /* Quick Filter Styles */
        .quick-filter {
            transition: all 0.2s ease;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .quick-filter.active {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        /* Stats card styling */
        .compact-card {
            padding: 0.5rem;
        }
        .compact-card .card-body {
            padding: 0.75rem;
        }
        .stats-dashboard {
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 1.5rem;
        }
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .pagination .page-link {
            color: #198754;
            border-radius: 5px;
            margin: 0 2px;
            padding: 0.375rem 0.75rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
        }
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
        
        /* Lighter modal backdrop */
        .modal-backdrop {
            opacity: 0.2 !important;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 0.5rem;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
    </style>
</head>
<body>
    <!-- Add User Modal -->
    <div class="modal fade" aria-labelledby="addUserModal" tabindex="-1"  id="addUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="post" class="modal-content compact-form" id="addUserForm">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="mb-3 d-none">
                        <label for="companyName" class="form-label">Company Name (Optional)</label>
                        <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Company or organization name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactNumber" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="+63 917 123 4567" maxlength="16">
                        <small class="form-text phone-validation"></small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            <button type="button" class="password-toggle" id="toggleAddPassword">
                                <i class="bi bi-eye" id="toggleAddPasswordIcon"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                        <!-- Container for password requirements -->
                        <div id="passwordRequirements" class="password-requirements"></div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <!-- <option value="Client">Client</option> -->
                            <option value="Admin">Admin</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                    </div>
                </div>  

                <div class="modal-footer">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveUserBtn" class="btn btn-success btn-sm w-50">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" aria-labelledby="editUserModal" tabindex="-1" id="editUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="post" class="modal-content compact-form" id="editUserForm">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="editUserId" name="userId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCompanyName" class="form-label">Company Name (Optional)</label>
                        <input type="text" class="form-control" id="editCompanyName" name="companyName" placeholder="Company or organization name">
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editContactNumber" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="editContactNumber" name="contactNumber" placeholder="+63 917 123 4567" maxlength="16">
                        <small class="form-text phone-validation"></small>
                    </div>
                    <div class="mb-3 d-none">
                        <label for="editPassword" class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" class="form-control" id="editPassword" name="password" placeholder="Leave blank to keep current password" minlength="8">
                            <button type="button" class="password-toggle" id="toggleEditPassword">
                                <i class="bi bi-eye" id="toggleEditPasswordIcon"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                        <!-- Container for password requirements -->
                        <div id="editPasswordRequirements" class="password-requirements"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="Client">Client</option>
                            <option value="Admin">Admin</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="updateUserBtn" class="btn btn-success btn-sm w-50">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-people me-2 text-success"></i>User Management</h3>
                    <p class="text-muted mb-0">Manage user accounts and access permissions</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <hr>

            <!-- User Statistics Cards -->
            <div class="row stats-dashboard g-2 mt-3">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-subtle text-success">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Users</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalUsersCount">--</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-calendar-plus"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">New Users (30 days)</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="recentUsersCount">--</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info-subtle text-info">
                                    <i class="bi bi-shield-fill"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Admin Users</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="activeUsersCount">--</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Client Users</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="inactiveUsersCount">--</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-3 my-3 flex-wrap">
                <div class="input-group" style="max-width: 400px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchUser" placeholder="Search by name, email or contact">
                    <!-- <button class="btn btn-success" type="button" id="searchBtn">Search</button> -->
                </div>
                <div class="input-group" style="max-width: 200px;">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-list-ol"></i>
                    </span>
                    <select name="limit" id="limitSelect" class="form-select">
                        <option value="5">5 rows</option>
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                    </select>
                </div>
                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-xl-8">
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- <span class="fw-bold me-2 pt-1">Role Filters:</span> -->
                        <button class="btn btn-sm btn-outline-secondary quick-filter active" data-role="All">
                            <i class="bi bi-funnel"></i> All
                        </button>
                        <button class="btn btn-sm btn-outline-info quick-filter" data-role="Client">
                            <i class="bi bi-person"></i> Client
                        </button>
                        <button class="btn btn-sm btn-outline-warning quick-filter" data-role="Admin">
                            <i class="bi bi-shield"></i> Admin
                        </button>
                        <button class="btn btn-sm btn-outline-danger quick-filter" data-role="Super Admin">
                            <i class="bi bi-shield-lock"></i> Super Admin
                        </button>
                        <div class="form-check form-switch ms-2 align-self-center">
                            <input class="form-check-input" type="checkbox" id="toggleTrash">
                            <label class="form-check-label" for="toggleTrash"><i class="bi bi-trash3"></i> Show Trash</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive-xl">
                <table class="table table-hover text-secondary overflow-hidden border rounded shadow-sm px-4">
                    <thead>
                        <tr>
                            <th class="sort" data-order="asc" data-column="user_id">
                                ID <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="first_name">
                                Name <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="email">
                                Email <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="contact_number">
                                Contact Number <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="role">
                                Role <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort active" data-order="desc" data-column="created_at">
                                Created At <span class="sort-icon">↓</span>
                            </th>
                            <th style="text-align: center; width: 15%; background-color: #d1f7c4; white-space: nowrap; cursor: default;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="table-group-divider">
                        <!-- Users data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div id="paginationContainer" class="mt-4"></div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="../../../public/js/utils/pagination.js"></script>
    <script src="../../../public/js/admin/user_management.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
    
    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            // Add toggle for password field in Add User form
            const toggleAddPasswordBtn = document.getElementById('toggleAddPassword');
            const toggleAddPasswordIcon = document.getElementById('toggleAddPasswordIcon');
            const passwordField = document.getElementById('password');
            
            toggleAddPasswordBtn.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleAddPasswordIcon.classList.remove('bi-eye');
                    toggleAddPasswordIcon.classList.add('bi-eye-slash');
                } else {
                    passwordField.type = 'password';
                    toggleAddPasswordIcon.classList.remove('bi-eye-slash');
                    toggleAddPasswordIcon.classList.add('bi-eye');
                }
            });
            
            // Add toggle for password field in Edit User form
            const toggleEditPasswordBtn = document.getElementById('toggleEditPassword');
            const toggleEditPasswordIcon = document.getElementById('toggleEditPasswordIcon');
            const editPasswordField = document.getElementById('editPassword');
            
            toggleEditPasswordBtn.addEventListener('click', function() {
                if (editPasswordField.type === 'password') {
                    editPasswordField.type = 'text';
                    toggleEditPasswordIcon.classList.remove('bi-eye');
                    toggleEditPasswordIcon.classList.add('bi-eye-slash');
                } else {
                    editPasswordField.type = 'password';
                    toggleEditPasswordIcon.classList.remove('bi-eye-slash');
                    toggleEditPasswordIcon.classList.add('bi-eye');
                }
            });

            // Reusable function to format Philippine mobile numbers
            function formatPhoneNumberInput(inputElement) {
                inputElement.addEventListener('input', function () {
                    let value = this.value.replace(/\D/g, '');

                    // Convert starting '09' to '639'
                    if (value.startsWith('09')) {
                        value = '63' + value.substring(1);
                    }

                    // Convert starting '9' (if no prefix) to '639'
                    if (value.length >= 10 && value.startsWith('9')) {
                        value = '63' + value;
                    }

                    // Only format if it starts with '639' and has at least 10 digits
                    if (value.startsWith('639')) {
                        const part1 = value.substring(2, 5); // 917
                        const part2 = value.substring(5, 8); // 123
                        const part3 = value.substring(8, 12); // 4567

                        let formatted = '+63';
                        if (part1) formatted += ' ' + part1;
                        if (part2) formatted += ' ' + part2;
                        if (part3) formatted += ' ' + part3;

                        this.value = formatted.trim();
                    } else {
                        this.value = value; // fallback (e.g., still typing or invalid start)
                    }
                });
            }

            // Get input elements
            const contactNumberInput = document.getElementById('contactNumber');
            const editContactNumberInput = document.getElementById('editContactNumber');

            // Apply formatting function to each input
            formatPhoneNumberInput(contactNumberInput);
            formatPhoneNumberInput(editContactNumberInput);
        });

    </script>
</body>
</html> 