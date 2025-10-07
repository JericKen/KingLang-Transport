<?php 
require_client_auth(); // Use helper function
?>
<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/../../../public/css/client/payment_styles.css">   
    <link rel="stylesheet" href="/../../../public/css/client/booking_requests.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <title>My Bookings</title>
    <style>
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
        .table-container {
            margin-bottom: 1rem;
        }
        .actions-compact {
            display: flex;
            gap: 0.25rem;
        }
        .actions-compact .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
        .table thead th {
            background-color: #d1f7c4;
            font-weight: 600;
            padding: 12px 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
        }
        .table thead th:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }
        .table thead th.active:after {
            content: attr(data-order) === "asc" ? " ↑" : " ↓";
            font-size: 0.8rem;
            margin-left: 5px;
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
        /* Card header style to match admin */
        .card .card-header {
            background-color: #d1f7c4; /* similar to bg-success-subtle */
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 0.5rem 0.75rem; /* reduce height similar to admin */
        }
        .card .card-header h5,
        .card .card-header h6 {
            margin: 0;
        }
        .card.border-0 .card-header {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
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
        .booking-detail-section p {
            margin-bottom: 0.5rem;
        }
        .booking-detail-section:last-child {
            margin-bottom: 0;
        }
        .booking-detail-section strong {
            color: #495057;
        }
        #bookingDetailsModal .modal-header {
            background-color: var(--light-green);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        #bookingDetailsModal .modal-body {
            padding: 20px;
        }
        #bookingDetailsModal .modal-content {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        #bookingDetailsModal .badge {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
        }
        /* ID pill on card headers */
        .id-badge {
            border-radius: 12px;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
        /* Normalize header icon and text sizing */
        .booking-card .card-header .status-icon {
            font-size: 1rem;
        }
        .booking-card .card-header .fw-semibold {
            font-size: 0.95rem;
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
        
        /* Cancellation Reason Modal Styling */
        .cancellation-reason-modal .form-check {
            padding-left: 10px;
            margin-bottom: 0.5rem;
        }
        .cancellation-reason-modal .form-check-input {
            margin-top: 0.1rem;
            margin-left: 0;
            position: static;
        }
        .cancellation-reason-modal .form-check-label {
            cursor: pointer;
            padding: 0.25rem 0;
            margin-left: 1.5rem;
            display: block;
        }
        /* .cancellation-reason-modal .form-check-label:hover {
            background-color: rgba(0, 123, 255, 0.05);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem -0.5rem 0.25rem 1rem;
        } */
        
        /* Hide scrollbar completely */
        .cancellation-reason-modal [style*="max-height: 300px"]::-webkit-scrollbar {
            display: none;
        }
        .cancellation-reason-modal [style*="max-height: 300px"] {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/sidebar.php"; ?> 
    
    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <!-- Header with user profile -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-bookmark-check me-2 text-success"></i>My Bookings</h3>
                    <p class="text-muted mb-0">Manage and track all your booking requests</p>
                </div>
                <?php include_once __DIR__ . "/../assets/user_profile.php"; ?>
            </div>
            <hr>

            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Bookings</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalBookingsCount">-</h3>
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
                                    <h3 class="fw-bold mb-0 stats-number" id="confirmedBookingsCount">-</h3>
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
                                    <h3 class="fw-bold mb-0 stats-number" id="pendingBookingsCount">-</h3>
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
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Upcoming Tours</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="upcomingToursCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Booking Reminder (if any) -->
            <div id="upcomingReminder" class="alert alert-info d-flex align-items-center mb-3" style="display: none !important;">
                <i class="bi bi-bell me-3 fs-4"></i>
                <div>
                    <strong>Upcoming Tour:</strong> 
                    <span id="upcomingTourDetails">You have an upcoming tour to <b id="upcomingDestination"></b> on <b id="upcomingDate"></b>.</span>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="row g-3 mb-3">
                <!-- Search and Filters Bar -->
                <div class="col-xl-6">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <!-- Search -->
                                <div class="col-lg-8 col-md-8">
                                    <div class="input-group" style="gap: 0;">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchBookings" class="form-control rounded-end" placeholder="Search destinations or pickup points...">
                                        <!-- <button id="searchBtn" class="btn btn-success">Search</button> -->
                                    </div>
                                </div>
                                
                                <!-- Status Filter -->
                                <div class="col-lg-0 col-md-0 d-none">
                                    <div class="input-group" style="gap: 0;">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-filter"></i>
                                        </span>
                                        <select name="status" id="statusSelect" class="form-select">
                                            <option value="All">All Bookings</option>
                                            <option value="Pending" selected>Pending</option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Canceled">Canceled</option>
                                            <option value="Rejected">Rejected</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Records Per Page -->
                                <div class="col-lg-4 col-md-4">
                                    <div class="input-group" style="gap: 0;">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-2"></div>
                
                <!-- View Switcher -->
                <div class="col-xl-4">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="btn-group w-100" role="group" aria-label="View options">
                                <input type="radio" class="btn-check" name="viewOption" id="tableView" autocomplete="off" checked>
                                <label class="btn btn-outline-secondary" for="tableView">
                                    <i class="bi bi-table"></i> Table
                                </label>

                                <input type="radio" class="btn-check" name="viewOption" id="calendarView" autocomplete="off">
                                <label class="btn btn-outline-secondary d-none" for="calendarView">
                                    <i class="bi bi-calendar3"></i> Calendar
                                </label>
                                
                                <input type="radio" class="btn-check" name="viewOption" id="cardView" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="cardView">
                                    <i class="bi bi-grid-3x3-gap"></i> Cards
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pills & Export Tools Row -->
            <div class="row g-3 mb-3">
                <div class="col-xl-8">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="all">
                            <i class="bi bi-funnel"></i> All
                        </button>
                        <button class="btn btn-sm btn-outline-warning quick-filter" data-status="pending">
                            <i class="bi bi-hourglass-split"></i> Pending
                        </button>
                        <button class="btn btn-sm btn-outline-success quick-filter" data-status="confirmed">
                            <i class="bi bi-check-circle"></i> Confirmed
                        </button>
                        <button class="btn btn-sm btn-outline-info quick-filter" data-status="processing">
                            <i class="bi bi-arrow-repeat"></i> Processing
                        </button>
                        <button class="btn btn-sm btn-outline-info quick-filter" data-status="rebooking">
                            <i class="bi bi-arrow-repeat"></i> Rebooking
                        </button>
                        <button class="btn btn-sm btn-outline-primary quick-filter" data-date="upcoming">
                            <i class="bi bi-calendar-check"></i> Upcoming
                        </button>
                        <button class="btn btn-sm btn-outline-primary quick-filter" data-date="past">
                            <i class="bi bi-calendar-x"></i> Past
                        </button>
                        <button class="btn btn-sm btn-outline-danger quick-filter" data-status="canceled">
                            <i class="bi bi-x-circle"></i> Canceled
                        </button>
                        <button class="btn btn-sm btn-outline-danger quick-filter" data-balance="unpaid">
                            <i class="bi bi-cash"></i> Unpaid
                        </button>
                        <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="rejected">
                            <i class="bi bi-dash-circle"></i> Rejected
                        </button>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="d-flex gap-2 justify-content-end d-none ">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-pdf text-danger"></i> Export as PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi bi-file-spreadsheet text-success"></i> Export as CSV</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-sm btn-outline-success" id="refreshBookings">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Filter Description -->
            <div id="currentFilter" class="alert alert-light border small text-muted mb-3" style="display: none;"></div>

            <!-- TABLE VIEW -->
            <div id="tableViewContainer">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover overflow-hidden rounded shadow-sm text-secondary">
                            <thead>
                                <tr>
                                    <th class="sort" data-order="asc" data-column="booking_id" style="white-space: nowrap;">ID</th>
                                    <th class="sort" data-order="asc" data-column="destination" style="white-space: nowrap;">Destination</th>
                                    <th class="sort" data-order="asc" data-column="date_of_tour" style="white-space: nowrap;">Date of Tour</th>
                                    <th class="sort" data-order="asc" data-column="end_of_tour" style="white-space: nowrap;">End of Tour</th>
                                    <th class="sort" data-order="asc" data-column="number_of_days" style="white-space: nowrap;">Days</th>
                                    <th class="sort" data-order="asc" data-column="number_of_buses" style="white-space: nowrap;">Buses</th>
                                    <th class="sort" data-order="asc" data-column="total_cost" style="white-space: nowrap;">Total Cost</th>
                                    <th class="sort" data-order="asc" data-column="balance" style="white-space: nowrap;">Balance</th>
                                    <th class="sort" data-order="asc" data-column="status" style="white-space: nowrap;">Remarks</th>
                                    <th style="text-align: center; width: 18%; white-space: nowrap;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider" id="tableBody"></tbody>
                        </table>     
                    </div>
                </div>
            </div>

            <!-- CARD VIEW -->
            <div id="cardViewContainer" class="row g-0" style="display:none;"></div>

            <!-- CALENDAR VIEW -->
            <div id="calendarViewContainer" class="card border-0 shadow-sm" style="display:none;">
                <div class="card-body p-2">
                    <div id="bookingCalendar"></div>
                </div>
            </div>

            <!-- Pagination Container -->
            <div id="paginationContainer" class="d-flex justify-content-center mt-3"></div>

            <!-- No Results Message -->
            <div id="noResultsFound" class="text-center my-4" style="display:none;">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h4 class="mt-3">No bookings found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade payment-modal" aria-labelledby="paymentModal" tabindex="-1" id="paymentModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="payment-content modal-content" action="" id="paymentForm" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="bi bi-credit-card-2-front me-2"></i>Payment Details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body container">
                    <div class="row">
                        <!-- Left Column - Payment Options -->
                        <div class="col-md-6">
                            <p class="lead mb-4">Payment Options:</p>
                            <div class="d-flex flex-column gap-3">
                                <div class="text-bg-success p-3 rounded-3 amount-payment" id="fullAmnt">
                                    <h3>Full payment</h3>
                                    <p id="fullAmount" class="amount"></p>  
                                </div>

                                <div class="text-bg-danger p-3 rounded-3 amount-payment">
                                    <h3 id="downPayment">Down payment</h3>
                                    <p id="partialAmount" class="amount"></p>
                                </div>
                            </div>
                            
                            <div class="mt-3 total-amount">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Selected Amount:</span>
                                    <span id="amount" class="text-success"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Payment Method & Upload -->
                        <div class="col-md-6">
                            <div class="payment-method">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select name="payment_method" id="paymentMethod" class="form-select" aria-label="Payment method selection">
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="GCash">GCash (PayMongo)</option>
                                    <!-- <option value="Online Payment">Online Payment</option>
                                    <option value="Maya">Maya</option> -->
                                </select>
                            </div>

                            <!-- Account Information Section -->
                            <div id="accountInfoSection" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Account Details</h5>
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>Bank:</strong> <span id="bankName">BPI Cainta Ortigas Extension Branch</span></p>
                                        <p class="mb-1"><strong>Name:</strong> <span id="accountName">KINGLANG TOURS AND TRANSPORT SERVICES INC.</span></p>
                                        <p class="mb-1"><strong>Number:</strong> <span id="accountNumber">4091-0050-05</span></p>
                                        <p class="mb-0"><strong>Swift Code:</strong> <span id="swiftCode">BPOIPHMM</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- PayMongo GCash Info -->
                            <div id="paymongoSection" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <h5 class="alert-heading"><i class="bi bi-shield-check me-2"></i>Secure PayMongo Payment</h5>
                                    <div class="mt-2">
                                        <p class="mb-2">Pay securely using GCash through PayMongo's encrypted payment gateway.</p>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <small>SSL Encrypted & PCI Compliant</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <small>Instant Payment Confirmation</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <small>No Upload Required</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GCash/Maya Info -->
                            <div id="mobilePaymentSection" class="mt-3" style="display: none;">
                                <div class="alert alert-primary">
                                    <h5 class="alert-heading"><i class="bi bi-phone me-2"></i><span id="mobilePaymentTitle">Mobile Payment</span></h5>
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>Name:</strong> <span id="mobileName">Kinglang Bus</span></p>
                                        <p class="mb-0"><strong>Number:</strong> <span id="mobileNumber">09123456789</span></p>
                                        <div id="qrCodeContainer" class="text-center mt-2">
                                            <!-- QR code will be displayed here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Proof of Payment Upload Section -->
                            <div id="proofUploadSection" class="mt-3" style="display: none;">
                                <label for="proofOfPayment" class="form-label">Upload Proof</label>
                                <input type="file" class="form-control" id="proofOfPayment" name="proof_of_payment" accept="image/*,.pdf">
                                <small class="text-muted">Upload receipt (JPG, PNG, PDF)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="hidden" name="booking_id" id="bookingID">
                    <input type="hidden" name="user_id" id="userID">
                    <input type="hidden" name="amount" id="amountInput">
                </div>
                                        
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-outline-success pay" type="submit"><i class="bi bi-check-circle me-2"></i>Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printContractBtn">
                        <i class="bi bi-printer"></i> Print Contract
                    </button>
                </div> -->
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/chat_widget_core.php'; ?>

    <script>
        // Set user login status for chat widget
        var userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="../../../public/js/utils/pagination.js"></script>
    <script src="../../../public/js/client/booking_request.js"></script>    
    <script src="../../../public/js/assets/sidebar.js"></script>

</body>
</html>