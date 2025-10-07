<?php
require_once __DIR__ . "/../../controllers/admin/BookingManagementController.php";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Booking Management</title>   
    <style>
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
        /* Table header styles */
        .table thead th {
            background-color: #d1f7c4;
            font-weight: 600;
            padding: 12px 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
        }
        .table thead th:hover {
            background-color:rgba(40, 167, 69, 0.2);
        }
        /* .table thead th.active {
            background-color: #9ed368;
        } */
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
        /* Prevent word wrap in payment status column */
        .table th[data-column="payment_status"],
        .table td:nth-child(9) {
            white-space: nowrap;
        }
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
        /* Quick Filter Carousel Styles */
        .filter-carousel-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .filter-carousel {
            display: flex;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            gap: 8px;
            padding: 8px 0;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
            position: relative;
            flex: 1;
        }
        
        /* Hide scrollbar for WebKit browsers (Chrome, Safari, Edge) */
        .filter-carousel::-webkit-scrollbar {
            display: none;
        }
        
        .filter-carousel .btn {
            white-space: nowrap;
            flex-shrink: 0;
            min-width: fit-content;
            transition: all 0.2s ease;
        }
        
        /* Navigation arrows */
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            /* background: transparent; */
            border: 1px solid transparent;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            background: rgba(255, 255, 255);
            /* transition: all 0.2s ease;    */
        }
        
        .carousel-arrow:hover {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #dee2e6;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .carousel-arrow.left {
            left: -16px;
        }
        
        .carousel-arrow.right {
            right: -16px;
        }
        
        .carousel-arrow i {
            font-size: 16px;
            font-weight: 600;
            color: #6c757d;
        }
        
        .carousel-arrow:hover i {
            color: #495057;
        }
        
        /* Hide arrows when not needed */
        .carousel-arrow.disabled {
            opacity: 0;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        /* .filter-carousel .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        } */

        /* Custom Scrollbar for other elements */
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
        <div class="container-fluid py-3 px-4 px-xl-4">
            <!-- Header with admin profile -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-calendar-check me-2 text-success"></i>Booking Management</h3>
                    <p class="text-muted mb-0">Manage and track all booking requests from clients</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <?php include_once __DIR__ . "/../assets/admin_navtab.php"; ?>
            
            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2 mt-3">
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

            <div class="row g-3 mb-3">
                <!-- Search and Filters Bar -->
                <div class="col-xl-6">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <!-- Search -->
                                <div class="col-lg-8 col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchBookings" class="form-control border-start-0" placeholder="Search destinations or clients...">
                                        <!-- <button id="searchBtn" class="btn btn-success">Search</button> -->
                                    </div>
                                </div>
                                
                                <!-- Status Filter -->
                                <div class="col-lg-0 col-md-0 d-none">
                                    <div class="input-group">
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
                                    <div class="input-group">
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

                                <input type="radio" class="btn-check d-none" name="viewOption" id="calendarView" autocomplete="off">
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
                <div class="col-xl-6">
                    <div class="filter-carousel-container">
                        <button class="carousel-arrow left" id="scrollLeft">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="filter-carousel" id="filterCarousel">
                            <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="All">
                                <i class="bi bi-funnel"></i> All
                            </button>
                            <button class="btn btn-sm btn-outline-warning quick-filter" data-status="Pending">
                                <i class="bi bi-hourglass-split"></i> Pending
                            </button>
                            <button class="btn btn-sm btn-outline-success quick-filter" data-status="Confirmed">
                                <i class="bi bi-check-circle"></i> Confirmed
                            </button>
                            <button class="btn btn-sm btn-outline-info quick-filter" data-status="Processing">
                                <i class="bi bi-arrow-repeat"></i> Processing
                            </button>
                            <button class="btn btn-sm btn-outline-primary quick-filter" data-status="Upcoming">
                                <i class="bi bi-calendar-check"></i> Upcoming
                            </button>
                            <button class="btn btn-sm btn-outline-danger quick-filter" data-status="Canceled">
                                <i class="bi bi-x-circle"></i> Canceled
                            </button>
                            <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="Rejected">
                                <i class="bi bi-dash-circle"></i> Rejected
                            </button>
                            <button class="btn btn-sm btn-outline-primary quick-filter" data-status="Completed">
                                <i class="bi bi-check-all"></i> Completed
                            </button>
                            <button class="btn btn-sm btn-outline-danger quick-filter" data-payment="Unpaid">
                                <i class="bi bi-cash"></i> Unpaid
                            </button>
                            <button class="btn btn-sm btn-outline-warning quick-filter" data-payment="Partially Paid">
                                <i class="bi bi-cash-coin"></i> Partially Paid
                            </button>
                        </div>
                        <button class="carousel-arrow right" id="scrollRight">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="col-xl-4"></div>
                <div class="col-xl-2 d-none">
                    <div class="d-flex gap-2 justify-content-end">
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

            <!-- TABLE VIEW -->
            <div id="tableViewContainer">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover text-secondary overflow-hidden rounded shadow-sm">
                        <thead>
                            <tr>
                                    <th class="sort" data-order="asc" data-column="booking_id">
                                        ID <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="client_name">
                                        Client Name <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="contact_number">
                                        Contact Number <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="destination">
                                        Destination <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="total_cost">
                                        Total Cost <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="date_of_tour">
                                        Date of Tour <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="number_of_days">
                                        Days <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="number_of_buses">
                                        Buses <span class="sort-icon">↑</span>
                                    </th>
                                    <th class="sort" data-order="asc" data-column="payment_status">
                                        Payment Status <span class="sort-icon">↑</span>
                                    </th>
                                    <th style="text-align: center; width: 15%;">Action</th>
                                </tr>
                        </thead>
                            <tbody class="table-group-divider" id="tableBody"> 
                           
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

            <!-- CARD  VIEW -->
            <div id="cardViewContainer" class="row g-3" style="display:none;"></div>

            <!-- CALENDAR VIEW -->
            <div id="calendarViewContainer" class="card border  -0 shadow-sm" style="display:none;">
                <div class="card-body p-2">
                    <div id="bookingCalendar"></div>
                </div>
            </div>

            <!-- No Results Message -->
            <div id="noResultsFound" class="text-center my-4" style="display:none;">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h4 class="mt-3">No bookings found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>

            <!-- Pagination Container -->
            <div id="paginationContainer" class="d-flex justify-content-center mt-3"></div>
        </div>
    </div>
    
    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fs-4" id="bookingDetailsModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <!-- <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="viewFullDetails">View Full Details</button>
                </div> -->
            </div>
        </div>
    </div>
    
    <!-- Create Booking Modal -->
    <div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBookingModalLabel">Create New Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="adminBookingForm">
                        <!-- Client Information Section -->
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-success-subtle">
                                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Client Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="clientName" class="form-label">Client Name</label>
                                        <input type="text" class="form-control" id="clientName" name="clientName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="contactNumber" name="contactNumber" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tour Details Section -->
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-success-subtle">
                                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Tour Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="destination" class="form-label">Main Destination</label>
                                        <input type="text" class="form-control" id="destination" name="destination" required>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label for="pickupPoint" class="form-label">Pickup Point</label>
                                        <input type="text" class="form-control" id="pickupPoint" name="pickupPoint" required>
                                    </div>
                                    
                                    <!-- Stops Section -->
                                    <div class="col-md-12">
                                        <label class="form-label">Stops Along The Way (Optional)</label>
                                        <div id="stopsContainer">
                                            <div class="stop-entry mb-2">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="stops[]" placeholder="Enter a stop location">
                                                    <button type="button" class="btn btn-outline-danger remove-stop"><i class="bi bi-dash-circle"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addStopBtn">
                                            <i class="bi bi-plus-circle"></i> Add Another Stop
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="dateOfTour" class="form-label">Date of Tour</label>
                                        <input type="date" class="form-control" id="dateOfTour" name="dateOfTour" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="numberOfDays" class="form-label">Number of Days</label>
                                        <input type="number" class="form-control" id="numberOfDays" name="numberOfDays" min="1" value="1" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="numberOfBuses" class="form-label">Number of Buses</label>
                                        <input type="number" class="form-control" id="numberOfBuses" name="numberOfBuses" min="1" value="1" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="estimatedPax" class="form-label">Estimated Number of Passengers</label>
                                        <input type="number" class="form-control" id="estimatedPax" name="estimatedPax" min="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information Section -->
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-success-subtle">
                                <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="totalCost" class="form-label">Total Cost (PHP)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="totalCost" name="totalCost" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="discount" class="form-label">Discount (%)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" value="0" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <hr>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="initialPaymentCheck" name="initialPaymentCheck">
                                            <label class="form-check-label" for="initialPaymentCheck">
                                                Record Initial Payment
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 initialPaymentField" style="display: none;">
                                        <label for="amountPaid" class="form-label">Amount Paid (PHP)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="amountPaid" name="amountPaid" min="0" step="0.01">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 initialPaymentField" style="display: none;">
                                        <label for="paymentMethod" class="form-label">Payment Method</label>
                                        <select class="form-select" id="paymentMethod" name="paymentMethod">
                                            <option value="Cash">Cash</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="GCash">GCash</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Check">Check</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-12 initialPaymentField" style="display: none;">
                                        <label for="paymentReference" class="form-label">Reference Number (Optional)</label>
                                        <input type="text" class="form-control" id="paymentReference" name="paymentReference">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes Section -->
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-success-subtle">
                                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Additional Notes</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitBookingBtn">Create Booking</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/utils/pagination.js"></script>
    <script src="../../../public/js/admin/booking_management.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
    
    <script>
        // Carousel navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('filterCarousel');
            const leftArrow = document.getElementById('scrollLeft');
            const rightArrow = document.getElementById('scrollRight');
            
            function updateArrowVisibility() {
                const scrollLeft = carousel.scrollLeft;
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;
                
                // Show/hide left arrow
                if (scrollLeft <= 0) {
                    leftArrow.classList.add('disabled');
                } else {
                    leftArrow.classList.remove('disabled');
                }
                
                // Show/hide right arrow
                if (scrollLeft >= maxScroll - 1) { // -1 for rounding issues
                    rightArrow.classList.add('disabled');
                } else {
                    rightArrow.classList.remove('disabled');
                }
            }
            
            // Initial check
            updateArrowVisibility();
            
            // Left arrow click
            leftArrow.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    carousel.scrollBy({
                        left: -200,
                        behavior: 'smooth'
                    });
                }
            });
            
            // Right arrow click
            rightArrow.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    carousel.scrollBy({
                        left: 200,
                        behavior: 'smooth'
                    });
                }
            });
            
            // Update arrows on scroll
            carousel.addEventListener('scroll', updateArrowVisibility);
            
            // Update arrows on window resize
            window.addEventListener('resize', updateArrowVisibility);
        });
    </script>
</body>
</html>