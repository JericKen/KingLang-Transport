<?php
require_once __DIR__ . "/../../controllers/admin/PaymentManagementController.php";

// if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Super Admin") {
//     header("Location: /admin/login");
//     exit(); 
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">  
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <title>Payment Management</title>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
        }
        
        /* .content.collapsed {
            margin-left: 78px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 78px);
        }

        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 250px);
        } */
        
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
        
        /* Table header styles */
        .table thead th {
            background-color: var(--light-green);
            font-weight: 600;
            padding: 12px 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
        }
        
        .table thead th:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }
        
        .sort-icon {
            font-size: 0.75rem;
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        .table-group-divider {
            border-top: 2px solid #dee2e6;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        /* Prevent word wrap in status column */
        .table th[data-column="status"],
        .table td:nth-child(6) {
            white-space: nowrap;
        }
        
        /* Custom scrollbar styling */
        #bookingResults, #clientResults {
            scrollbar-width: 4px;
            scrollbar-color: #888;
        }
        
        #bookingResults::-webkit-scrollbar, #clientResults::-webkit-scrollbar {
            width: 4px;
        }
        
        #bookingResults::-webkit-scrollbar-track, #clientResults::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }
        
        #bookingResults::-webkit-scrollbar-thumb, #clientResults::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 10px;
        }
        
        /* Status badges now use Bootstrap classes */

        .pagination .page-link {
            color: #198754;
            border-radius: 5px;
            margin: 0 2px;
            padding: 0.375rem 0.75rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #198754   ;
            border-color: #198754   ;
        }
        
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
        
        /* Booking details modal styling */
        .booking-detail-section {
            margin-bottom: 1.5rem;
        }
        
        .booking-detail-section h6 {
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #28a745;
        }
        
        .booking-details-container {
            border-left: 4px solid var(--secondary-green) !important;
        }
    </style>
</head>

<body> 
    <div class="modal fade" aria-labelledby="confirmPaymentModal" tabindex="-1" id="confirmPaymentModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="post" class="modal-content" id="confirmPaymentForm">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm Payment?</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">    
                    <p>Are you sure you want to confirm this payment?</p>
                    <p class="text-secondary">Note: This action cannot be undone.</p>
                </div>

                <div class="modal-footer">
                    <div class="d-flex gap-3 w-100">
                        <input type="hidden" name="payment_id" id="confirmPaymentId" value="">
                        <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="confirm" class="btn btn-success flex-grow-1">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" aria-labelledby="recordManualPaymentModal" tabindex="-1" id="recordManualPaymentModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" id="recordManualPaymentForm">
                <div class="modal-header bg-light">
                    <h4 class="modal-title"><i class="bi bi-credit-card me-2 text-success"></i>Record Manual Payment</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bookingSearch" class="form-label">Search for Booking</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="bookingSearch" placeholder="Search by ID, destination or client name">
                                <button class="btn btn-success rounded-end" type="button" id="searchBookingsBtn">Search</button>
                                <div class="border p-0 rounded position-absolute bg-white shadow w-100" style="max-height: 200px; overflow-y: auto; display: none; z-index: 1050; left: 0; top: 100%;" id="bookingResults">
                                    <div class="list-group" id="bookingResultsList"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="clientSearch" class="form-label">Search for Client</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="clientSearch" placeholder="Search by ID, name or email">
                                <button class="btn btn-success rounded-end" type="button" id="searchClientsBtn">Search</button>
                                <div class="border rounded position-absolute p-0 bg-white shadow w-100" style="max-height: 200px; overflow-y: auto; display: none; z-index: 1050; left: 0; top: 100%;" id="clientResults">
                                    <div class="list-group" id="clientResultsList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bookingId" class="form-label">Booking ID</label>
                                <input type="number" class="form-control" id="bookingId" name="booking_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="clientId" class="form-label">Client ID</label>
                                <input type="number" class="form-control" id="clientId" name="user_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select" id="paymentMethod" name="payment_method" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Online Payment">Online Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (PHP)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Optional notes about this payment"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-details-container mt-2 p-3 border rounded bg-light" style="display: none;" id="bookingDetailsContainer">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2 text-success"></i>Booking Details</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Destination:</div>
                            <div class="col-sm-8" id="detailDestination"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Client:</div>
                            <div class="col-sm-8" id="detailClient"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Total Cost:</div>
                            <div class="col-sm-8" id="detailTotalCost"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Balance Due:</div>
                            <div class="col-sm-8" id="detailBalance"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Payment Status:</div>
                            <div class="col-sm-8" id="detailPaymentStatus"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" aria-labelledby="rejectPaymentModal" tabindex="-1" id="rejectPaymentModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="post" class="modal-content" id="rejectPaymentForm">
                <div class="modal-header">
                    <h4 class="modal-title">Reject Payment?</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p>Are you sure you want to reject this payment?</p>
                    
                    <textarea class="form-control" placeholder="Kindly provide the reason here." name="reason" id="reason" style="height: 100px"></textarea>
                    
                    <p class="text-secondary mb-0 mt-4">Note: This action cannot be undone.</p>
                </div>

                <div class="modal-footer">
                    <div class="d-flex gap-3 w-100">
                        <input type="hidden" name="payment_id" id="rejectPaymentId" value="">
                        <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="reject" class="btn btn-danger flex-grow-1">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade message-modal" aria-labelledby="messageModal" tabindex="-1" id="messageModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="messageTitle"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p id="messageBody"></p>
                </div>

                <div class="modal-footer">
                    <div class="d-flex gap-3 w-25">
                        <button type="button" class="btn btn-outline-success btn-sm w-100" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <!-- Header with admin profile -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-credit-card me-2 text-success"></i>Payment Management</h3>
                    <p class="text-muted mb-0">Track and manage all payment transactions</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <hr>
            
            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2 mt-3">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-cash"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Payments</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalPaymentsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-subtle text-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Confirmed</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="confirmedPaymentsCount">-</h3>
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
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Pending</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="pendingPaymentsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-danger-subtle text-danger">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Rejected</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="rejectedPaymentsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="card mb-3 border-0 shadow-sm mt-3">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="searchPayments" class="form-control border-start-0" placeholder="Search by client or booking ID...">
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-4 d-none">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-filter"></i>
                                </span>
                                <select name="status" id="statusSelect" class="form-select">
                                    <option value="all">All Payments</option>
                                    <option value="PENDING">Pending</option>
                                    <option value="CONFIRMED">Confirmed</option>
                                    <option value="REJECTED">Rejected</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-list-ol"></i>
                                </span>
                                <select name="limit" id="limitSelect" class="form-select">
                                    <option value="5">5 per page</option>
                                    <option value="10" selected>10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-4 ms-auto">
                            <button id="recordManualPaymentBtn" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filters -->
            <div class="d-flex gap-2 flex-wrap mb-3">
                <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="all">
                    <i class="bi bi-funnel"></i> All
                </button>
                <button class="btn btn-sm btn-outline-warning quick-filter" data-status="PENDING">
                    <i class="bi bi-hourglass-split"></i> Pending
                </button>
                <button class="btn btn-sm btn-outline-success quick-filter" data-status="CONFIRMED">
                    <i class="bi bi-check-circle"></i> Confirmed
                </button>
                <button class="btn btn-sm btn-outline-danger quick-filter" data-status="REJECTED">
                    <i class="bi bi-x-circle"></i> Rejected
                </button>
            </div>

            <!-- Table View -->
            <div class="table-responsive">
                <table class="table table-hover text-secondary overflow-hidden border rounded mb-0">
                    <thead>
                        <tr>
                            <th class="sort" data-order="asc" data-column="booking_id">
                                Booking ID <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="client_name">
                                Client Name <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="amount">
                                Amount <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="payment_method">
                                Method <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="payment_date">
                                Date <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="status">
                                Status <span class="sort-icon">↑</span>
                            </th>
                            <th style="text-align: center; width: 15%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="table-group-divider">
                    </tbody>
                </table>
            </div>
            
            <!-- No Results Message -->
            <div id="noResultsFound" class="text-center my-4" style="display:none;">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h4 class="mt-3">No payments found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>
            
            <div id="paginationContainer" class="d-flex justify-content-center mt-3"></div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="../../../public/js/utils/pagination.js"></script>  
    <script src="../../../public/js/admin/payment_management.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="../../../public/css/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html> 