<?php
$currentPage = basename(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Sidebar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/066bf74adc.js" crossorigin="anonymous"></script>
    <script>
        // Set initial sidebar state before page renders to prevent flickering
        (function() {
            // Apply state immediately
            const isCollapsed = localStorage.getItem('sidebarCollapsed');
            if (isCollapsed === 'true') {
                // Add class to html element for CSS rules to apply immediately
                document.documentElement.classList.add('sidebar-collapsed');
            } else if (isCollapsed === 'false') {
                document.documentElement.classList.add('sidebar-expanded');
            } else {
                // If no saved state, default to expanded on desktop, collapsed on mobile
                if (window.innerWidth <= 768) {
                    document.documentElement.classList.add('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    document.documentElement.classList.add('sidebar-expanded');
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            }
        })();
    </script>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #fff;
            color: black;
            box-shadow: 5px 0 15px rgba(25, 188, 63, 0.32);
            transition: width 0.3s;
            z-index: 1000;
            display: flex;
            border-radius: 0 10px 10px 0;
            flex-direction: column;
            overflow-x: hidden; /* Prevent horizontal scroll */
            width: 250px; /* Default expanded state */
        }

        /* Collapsed state applied directly through HTML class */
        html.sidebar-collapsed .sidebar {
            width: 4.5rem;
        }
        
        html.sidebar-collapsed .content {
            margin-left: 4.5rem;
        }
        
        html.sidebar-collapsed .sidebar .menu-text {
            opacity: 0;
        }
        
        html.sidebar-collapsed .toggle-btn {
            left: 0.75rem;
            opacity: 0;
        }

        /* Apply expanded class by default if html has sidebar-expanded class */
        html.sidebar-expanded .sidebar {
            width: 250px;
        }
        
        html.sidebar-expanded .content {
            margin-left: 250px;
        }
        
        html.sidebar-expanded .sidebar .menu-text {
            opacity: 1;
        }
        
        html.sidebar-expanded .toggle-btn {
            left: 200px;
            opacity: 1;
        }

        .sidebar.collapsed {
            width: 4.5rem;
        }

        .sidebar.expanded {
            width: 250px;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            min-height: 65px;
            position: relative; /* For absolute positioning of children */
            min-width: 250px; /* Match expanded width */
        }

        .sidebar-header img {
            position: absolute;
            left: 1rem;
        }

        .brand-text {
            margin: 0;
            position: absolute;
            left: 3rem;
            opacity: 1;
            transition: opacity 0.3s;
        }

        .toggle-btn {
            background: transparent;
            border: none;
            color: black;
            cursor: pointer;
            padding: 0.5rem;
            position: absolute;
            left: 200px; /* Position from left */
            transition: all 0.3s;
        }

        .sidebar.collapsed .toggle-btn {
            left: 0.75rem; /* Center when collapsed */
            opacity: 0;
        }

        .toggle-btn:hover {
            color: rgba(0, 0, 0, 0.8);
        }

        .sidebar-link {
            color: rgba(0, 0, 0, 0.8);
            text-decoration: none;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
            min-width: 250px; /* Match expanded width */
        }

        .sidebar-link .icon {
            min-width: 2rem;
            text-align: center;
        }

        .sidebar-link:hover {
            color: black;
            background: #d1f7c4;
        }

        .sidebar-link.active {
            color: black;
            background: #d1f7c4;
        }   

        .sidebar-link i {
            font-size: 1.25rem;
            min-width: 2rem;
            text-align: center;
        }

        .menu-text {
            opacity: 1;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .menu-text {
            opacity: 0;
        }

        .sidebar-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu {
            flex: 1;
        }

        .content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .content.collapsed {
            margin-left: 4.5rem;
        }

         @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 4.5rem;
            }
            .content {
                margin-left: 4.5rem;
            }
            .menu-text {
                opacity: 0;
            }
            .toggle-btn {
                left: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header border-bottom border-secondary">
            <img src="../../../../public/images/main-logo.png" alt="logo" height="35px">
            <h5 class="ms-3 brand-text menu-text">KingLang</h5>
            <button class="toggle-btn" id="toggleBtn">
                <i class="bi bi-chevron-left fs-4"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <!-- Sidebar Menu -->
            <div class="sidebar-menu pb-2 ">
                <a href="/admin/dashboard" class="sidebar-link <?= $currentPage == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-grid"></i>
                    <span class="menu-text">Dashboard</span>
                </a>   
                <a href="/admin/booking-requests" class="sidebar-link <?= $currentPage == 'booking-requests' || $currentPage == 'rebooking-requests' ? 'active' : ''; ?>">
                    <i class="bi bi-journals fs-5"></i>
                    <span class="menu-text">Bookings</span>
                </a>
                <!-- <a href="/admin/create-booking" class="sidebar-link <?= $currentPage == 'create-booking' ? 'active' : ''; ?>">
                    <i class="bi bi-plus-circle"></i>
                    <span class="menu-text">Create Booking</span>
                </a> -->
                <?php if ($_SESSION['role'] == 'Super Admin'): ?>
                    <a href="/admin/users" class="sidebar-link <?= $currentPage == 'users' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">Users</span>
                    </a>
                    <a href="/admin/audit-trail" class="sidebar-link <?= $currentPage == 'audit-trail' ? 'active' : ''; ?>">
                        <i class="bi bi-clock-history"></i>
                        <span class="menu-text">Audit Trail</span>
                    </a>
                <?php endif; ?>
                
                <a href="/admin/payment-management" class="sidebar-link <?= $currentPage == 'payment-management' ? 'active' : ''; ?>">
                    <i class="bi bi-wallet2"></i>
                    <span class="menu-text">Payments</span>
                </a>
                <a href="/admin/reports" class="sidebar-link <?= $currentPage == 'reports' ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="menu-text">Reports</span>  
                </a>
                <a href="/admin/testimonials" class="sidebar-link <?= $currentPage == 'testimonials' || $currentPage == 'testimonial-management' ? 'active' : ''; ?>">
                    <i class="bi bi-star"></i>
                    <span class="menu-text">Testimonials</span>  
                </a>
                <a href="/admin/slideshow" class="sidebar-link <?= $currentPage == 'slideshow' ? 'active' : ''; ?>">
                    <i class="bi bi-images"></i>
                    <span class="menu-text">Slideshow</span>  
                </a>
                <a href="/admin/bus-management" class="sidebar-link <?= $currentPage == 'bus-management' ? 'active' : ''; ?>">
                    <i class="bi bi-bus-front"></i>
                    <span class="menu-text">Buses</span>  
                </a>
                <a href="/admin/driver-management" class="sidebar-link <?= $currentPage == 'driver-management' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge"></i>
                    <span class="menu-text">Drivers</span>
                </a>
                <a href="/admin/settings" class="sidebar-link <?= $currentPage == 'settings' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span class="menu-text">Settings</span>
                </a>
                <a href="/admin/chat" class="sidebar-link <?= $currentPage == 'chat' ? 'active' : ''; ?>">
                    <i class="bi bi-chat"></i>
                    <span class="menu-text">Chats</span>
                </a>

            </div>

            <!-- Sidebar Footer -->
            <div class="border-top border-secondary">
                <a href="/admin/logout" class="sidebar-link">
                    <i class="bi bi-box-arrow-left"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Handle sidebar logout confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLogoutLink = document.querySelector('.sidebar-link[href="/admin/logout"]');
            
            if (sidebarLogoutLink) {
                sidebarLogoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Logout Confirmation',
                        text: 'Are you sure you want to logout?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Logout',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            popup: 'swal2-popup-custom',
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-secondary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading state
                            Swal.fire({
                                title: 'Logging out...',
                                text: 'Please wait while we log you out.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Redirect to logout
                            setTimeout(() => {
                                window.location.href = '/admin/logout';
                            }, 1000);
                        }
                    });
                });
            }
        });
    </script>
    
    <style>
        /* SweetAlert2 custom styling for sidebar */
        .swal2-popup-custom {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        }
        
        .swal2-popup-custom .swal2-title {
            color: #198754 !important;
            font-weight: 600 !important;
        }
        
        .swal2-popup-custom .swal2-html-container {
            color: #6c757d !important;
        }
        
        .swal2-popup-custom .swal2-confirm {
            background-color: #198754 !important;
            border-color: #198754 !important;
            border-radius: 0.5rem !important;
            font-weight: 500 !important;
            padding: 0.5rem 1.5rem !important;
        }
        
        .swal2-popup-custom .swal2-confirm:hover {
            background-color: #0d6a3e !important;
            border-color: #0d6a3e !important;
        }
        
        .swal2-popup-custom .swal2-cancel {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            border-radius: 0.5rem !important;
            font-weight: 500 !important;
            padding: 0.5rem 1.5rem !important;
        }
        
        .swal2-popup-custom .swal2-cancel:hover {
            background-color: #5a6268 !important;
            border-color: #5a6268 !important;
        }
    </style>
</body>
</html>