<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rebooking Management</title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
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
        
        /* Stats cards styling */
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
        
        /* Table styling */
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
        .sort-icon {
            font-size: 0.75rem;
            margin-left: 5px;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        /* Action buttons */
        .actions-compact {
            display: flex;
            gap: 0.25rem;
        }
        .actions-compact .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Responsive container */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
        
        /* No results message */
        #noResultsFound {
            display: none;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
            margin: 2rem 0;
        }

        /* Pagination styling to match Booking Management */
        .pagination .page-link {
            color: #198754;
            border-radius: 5px;
            margin: 0 2px;
            padding: 0.375rem 0.75rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <!-- Header with admin profile styled like payment management -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-arrow-repeat me-2 text-success"></i>Rebooking Management</h3>
                    <p class="text-muted mb-0">Manage and review rebooking requests</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <?php include_once __DIR__ . "/../assets/admin_navtab.php"; ?>
            
            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2 mt-3">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Requests</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalRequestsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Pending</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="pendingRequestsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-subtle text-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Confirmed</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="confirmedRequestsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-danger-subtle text-danger">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Rejected</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="rejectedRequestsCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-6">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <div class="col-xl-8 col-lg-4 col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchRequests" class="form-control border-start-0" placeholder="Search client name or destination...">
                                        <!-- <button id="searchBtn" class="btn btn-success">Search</button> -->
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-2 col-md-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-list-ol"></i></span>
                                        <select id="rowsPerPage" class="form-select">
                                            <option value="5">5 rows</option>
                                            <option value="10" selected>10 rows</option>
                                            <option value="25">25 rows</option>
                                            <option value="50">50 rows</option>
                                            <option value="100">100 rows</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 d-flex align-items-center d-none">
                                    <small id="recordInfo" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        
            <!-- Search and Filter Row -->
            <div class="row g-3 mb-3 d-none">
                <!-- Search Bar -->
                <div class="col-xl-6">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-8 col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchRequests" class="form-control border-start-0" placeholder="Search client name or destination...">
                                        <button id="searchBtn" class="btn btn-success">Search</button>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 d-none">
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle">
                                            <i class="bi bi-filter"></i>
                                        </span>
                                        <select name="status" id="statusSelect" class="form-select">
                                            <option value="All">All Requests</option>
                                            <option value="Pending" selected>Pending</option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="col-xl-6 d-none">
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-sm btn-outline-success" id="refreshRequests">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-pdf text-danger"></i> Export as PDF</a></li>
                                        <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi bi-file-spreadsheet text-success"></i> Export as CSV</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filter Pills -->
            <div class="d-flex gap-2 flex-wrap mb-3">
                <button class="btn btn-sm btn-outline-secondary quick-filter" data-status="All">
                    <i class="bi bi-funnel"></i> All
                </button>
                <button class="btn btn-sm btn-outline-warning quick-filter active" data-status="Pending">
                    <i class="bi bi-hourglass-split"></i> Pending
                </button>
                <button class="btn btn-sm btn-outline-success quick-filter" data-status="Confirmed">
                    <i class="bi bi-check-circle"></i> Confirmed
                </button>
                <button class="btn btn-sm btn-outline-danger quick-filter" data-status="Rejected">
                    <i class="bi bi-x-circle"></i> Rejected
                </button>
            </div>
            
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover border overflow-hidden rounded shadow-sm">
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
                            <th class="sort" data-order="asc" data-column="email">
                                Email Address <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="date_of_tour">
                                Date of Tour <span class="sort-icon">↑</span>
                            </th>
                            <th class="sort" data-order="asc" data-column="status">
                                Status <span class="sort-icon">↑</span>
                            </th>
                            <th style="text-align: center; width: 15%;">Action</th>
                        </tr> 
                    </thead>
                    <tbody id="tableBody" class="fs-6">
                        <!-- Table content will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
            
             <!-- No Results Message -->
             <div id="noResultsFound" class="text-center mt-4 mb-4">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h4 class="mt-3">No rebooking requests found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="d-flex justify-content-center mt-3"></div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fs-4" id="bookingDetailsModalLabel">Rebooking Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="../../../public/js/admin/rebooking_requests.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/utils/pagination.js"></script>
</body>
</html>