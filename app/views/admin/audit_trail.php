<?php
$pageTitle = "Audit Trail Management";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link rel="shortcut icon" href="/../../../public/images/kinglang-removebg.png">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Audit Trail Management</title>
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
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
        /* Audit details modal styling */
        .audit-detail-section {
            margin-bottom: 1.5rem;
        }
        .audit-detail-section h6 {
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #28a745;
        }
        .audit-detail-section p {
            margin-bottom: 0.5rem;
        }
        .audit-detail-section:last-child {
            margin-bottom: 0;
        }
        .audit-detail-section strong {
            color: #495057;
        }
        #auditDetailsModal .modal-header {
            background-color: var(--light-green);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        #auditDetailsModal .modal-body {
            padding: 20px;
        }
        #auditDetailsModal .modal-content {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        #auditDetailsModal .badge {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
        }
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .quick-filter.active {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
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
        /* Lightweight loading state to avoid full-reload feel */
        .table-container.loading { opacity: 0.6; transition: opacity 150ms ease; }
    </style>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-4 px-xl-4">
            <!-- Header with admin profile -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-clock-history me-2 text-success"></i>Audit Trail Management</h3>
                    <p class="text-muted mb-0">Monitor and track all system activities and changes</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>  
            <hr>            
            
            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2 mt-3">
                <div class="col-xl-4 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Records</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalAuditRecords">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-subtle text-success">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Today's Activity</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="todayAuditRecords">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-shield-exclamation"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Failed Logins</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="failedLoginAttempts">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-sm-6 d-none">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info-subtle text-info">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Active Users</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="activeUsersCount">-</h3>
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
                                        <input type="text" id="searchAudit" class="form-control border-start-0" placeholder="Search by user or entity...">
                                        <!-- <button id="searchBtn" class="btn btn-success">Search</button> -->
                                    </div>
                                </div>
                                
                                <!-- Records Per Page -->
                                <div class="col-lg-4 col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-list-ol"></i>
                                        </span>
                                        <select name="per_page" id="perPageSelect" class="form-select">
                                            <option value="10">10 rows</option>
                                            <option value="20" selected>20 rows</option>
                                            <option value="50">50 rows</option>
                                            <option value="100">100 rows</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters -->
                <div class="col-xl-6">
                    <div class="card mb-0 border-0 shadow-sm filter-card">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <!-- Date Range -->
                                <div class="col-lg-6 col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar-range"></i>
                                        </span>
                                        <input type="date" id="dateFromFilter" class="form-control" placeholder="From">
                                        <input type="date" id="dateToFilter" class="form-control" placeholder="To">
                                    </div>
                                </div>
                                
                                <!-- Entity Type Filter -->
                                <div class="col-lg-6 col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-filter"></i>
                                        </span>
                                        <select id="entityTypeFilter" class="form-select">
                                            <option value="">All Entities</option>
                                            <option value="user">Users</option>
                                            <option value="booking">Bookings</option>
                                            <option value="payment">Payments</option>
                                            <option value="bus">Buses</option>
                                            <option value="driver">Drivers</option>
                                            <option value="setting">Settings</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pills & Export Tools Row -->
            <div class="row g-3 mb-3">
                <div class="col-xl-10">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary quick-filter active" data-action="">
                            <i class="bi bi-funnel"></i> All Actions
                        </button>
                        <button class="btn btn-sm btn-outline-primary quick-filter" data-action="create">
                            <i class="bi bi-plus-circle"></i> Create
                        </button>
                        <button class="btn btn-sm btn-outline-warning quick-filter" data-action="update">
                            <i class="bi bi-pencil-square"></i> Update
                        </button>
                        <button class="btn btn-sm btn-outline-danger quick-filter" data-action="delete">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <button class="btn btn-sm btn-outline-success quick-filter" data-action="login">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                        <button class="btn btn-sm btn-outline-info quick-filter" data-action="logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                        <button class="btn btn-sm btn-outline-danger quick-filter" data-action="login_failed">
                            <i class="bi bi-shield-exclamation"></i> Failed Login
                        </button>
                    </div>
                </div>
                <div class="col-xl-2">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-success" id="exportAuditTrails">
                            <i class="bi bi-download"></i> Export CSV
                        </button>
                        <button class="btn btn-sm btn-outline-success" id="refreshAudit">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover text-secondary overflow-hidden rounded shadow-sm" id="auditTrailTable">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="12%">User</th>
                                <th width="8%">Role</th>
                                <th width="10%">Action</th>
                                <th width="12%">Entity</th>
                                <th width="8%">Entity ID</th>
                                <th width="15%">Date/Time</th>
                                <th width="12%">IP Address</th>
                                <th width="18%" style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <!-- Data will be loaded dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message -->
            <div id="noResultsFound" class="text-center my-4" style="display:none;">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h4 class="mt-3">No audit records found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
            </div>

            <!-- Pagination Container -->
            <div id="paginationContainer" class="d-flex justify-content-center mt-3">
                <nav aria-label="Audit trail pagination">
                    <ul class="pagination">
                        <!-- Pagination links will be dynamically generated -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Audit Details Modal -->
    <div class="modal fade" id="auditDetailsModal" tabindex="-1" aria-labelledby="auditDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="auditDetailsModalLabel">
                        <i class="bi bi-info-circle me-2 text-success"></i>Audit Record Details
                    </h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="audit-detail-section">
                                <h6>Audit Information</h6>
                                <p><strong>Audit ID:</strong> <span id="auditDetailId">-</span></p>
                                <p><strong>Date/Time:</strong> <span id="auditDetailDateTime">-</span></p>
                                <p><strong>Action:</strong>     <span id="auditDetailAction">-</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="audit-detail-section">
                                <h6>User Information</h6>
                                <p><strong>User:</strong> <span id="auditDetailUser">-</span></p>
                                <p><strong>Role:</strong> <span id="auditDetailRole">-</span></p>
                                <p><strong>IP Address:</strong> <span id="auditDetailIP">-</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="audit-detail-section">
                        <h6>Entity Information</h6>
                        <p><strong>Entity:</strong> <span id="auditDetailEntity">-</span></p>
                        <p><strong>User Agent:</strong> <small class="text-muted" id="auditDetailUserAgent">-</small></p>
                    </div>
                    
                    <div class="audit-detail-section">
                        <h6>Changed Values</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="changesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Field</th>
                                        <th width="35%">Old Value</th>
                                        <th width="35%">New Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Changes will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Entity History Modal -->
    <div class="modal fade" id="entityHistoryModal" tabindex="-1" aria-labelledby="entityHistoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="entityHistoryModalLabel">
                        <i class="bi bi-clock-history me-2 text-success"></i>Entity History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="entityHistoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">ID</th>
                                    <th width="18%">User</th>
                                    <th width="12%">Role</th>
                                    <th width="12%">Action</th>
                                    <th width="18%">Date/Time</th>
                                    <th width="12%">IP Address</th>
                                    <th width="20%">Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- History will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>

    <script>
    $(document).ready(function() {
        // Global variables
        let currentPage = 1;
        const perPage = 20;
        let totalPages = 0;
        let currentFilters = {};
        
        // Initial load of audit trails
        loadAuditTrails();
        loadStats();
        
        // Search functionality
        // Debounced search to feel responsive without flicker
        let searchDebounceTimer = null;
        $("#searchAudit").on("input", function(e) {
            e.preventDefault();
            currentPage = 1;
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                loadAuditTrails();
            }, 250);
        });
        
        // Per page selector
        $("#perPageSelect").on("change", function() {
            currentPage = 1;
            loadAuditTrails();
        });
        
        // Date filters
        $("#dateFromFilter, #dateToFilter, #entityTypeFilter").on("change", function() {
            currentPage = 1;
            loadAuditTrails();
        });
        
        // Quick filter pills
        $(".quick-filter").on("click", function() {
            $(".quick-filter").removeClass("active");
            $(this).addClass("active");
            
            const action = $(this).data("action");
            currentFilters.action = action;
            currentPage = 1;
            loadAuditTrails();
        });
        
        // Reset filters
        $("#resetFilters").on("click", function() {
            $("#searchAudit").val("");
            $("#dateFromFilter").val("");
            $("#dateToFilter").val("");
            $("#entityTypeFilter").val("");
            $(".quick-filter").removeClass("active");
            $(".quick-filter[data-action='']").addClass("active");
            currentPage = 1;
            currentFilters = {};
            loadAuditTrails();
        });
        
        // Export functionality
        $("#exportAuditTrails").on("click", function() {
            const queryParams = new URLSearchParams();
            
            // Add current filters to export
            if ($("#searchAudit").val()) queryParams.append('search', $("#searchAudit").val());
            if ($("#dateFromFilter").val()) queryParams.append('date_from', $("#dateFromFilter").val());
            if ($("#dateToFilter").val()) queryParams.append('date_to', $("#dateToFilter").val());
            if ($("#entityTypeFilter").val()) queryParams.append('entity_type', $("#entityTypeFilter").val());
            if (currentFilters.action) queryParams.append('action', currentFilters.action);
            
            window.location.href = '/admin/export-audit-trails?' + queryParams.toString();
        });
        
        // Refresh button
        $("#refreshAudit").on("click", function() {
            loadAuditTrails();
            loadStats();
        });
        
        // Pagination click handler
        $(document).on("click", ".page-link", function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) {
                return;
            }
            
            if ($(this).data('page') === 'prev') {
                if (currentPage > 1) {
                    currentPage--;
                }
            } else if ($(this).data('page') === 'next') {
                if (currentPage < totalPages) {
                    currentPage++;
                }
            } else {
                currentPage = parseInt($(this).data('page'));
            }
            
            loadAuditTrails();
        });
        
        // Load audit trails with pagination and filters
        function loadAuditTrails() {
            // Lightweight loading indicator
            $(".table-container").addClass('loading');
            
            // Prepare data to send
            const data = {
                page: currentPage,
                per_page: parseInt($("#perPageSelect").val()) || 20,
                search: $("#searchAudit").val(),
                date_from: $("#dateFromFilter").val(),
                date_to: $("#dateToFilter").val(),
                entity_type: $("#entityTypeFilter").val(),
                action: currentFilters.action || ""
            };
            
            // Remove empty filters
            Object.keys(data).forEach(key => {
                if (data[key] === "" || data[key] === null) {
                    delete data[key];
                }
            });
            
            // AJAX request
            $.ajax({
                url: "/admin/get-audit-trails",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(response) {
                    // Update table with records
                    displayAuditTrails(response.records);
                    console.log("Audit records: ", response.records);
                    
                    // Update pagination
                    totalPages = Math.ceil(response.total / response.per_page);
                    updatePagination(response.page, totalPages, response.total, response.per_page);
                    
                    // Show/hide no re  sults message
                    if (response.records.length === 0) {
                        $("#noResultsFound").show();
                        $("#paginationContainer").hide();
                    } else {
                        $("#noResultsFound").hide();
                        $("#paginationContainer").show();
                    }
                },
                error: function() {
                    $("#auditTrailTable tbody").html('<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>');
                },
                complete: function() {
                    $(".table-container").removeClass('loading');
                }
            });
        }
        
        // Load statistics
        function loadStats() {
            // For now, we'll calculate from the audit trail data
            // In a production environment, you might want dedicated endpoints for stats
            $.ajax({
                url: "/admin/get-audit-trails",
                method: "POST", 
                data: { page: 1, per_page: 1 }, // Just get the total count
                dataType: "json",
                success: function(response) {
                    $("#totalAuditRecords").text(response.total || 0);
                }
            });
            
            // Get today's activity
            const today = new Date().toISOString().split('T')[0];
            $.ajax({
                url: "/admin/get-audit-trails",
                method: "POST",
                data: { page: 1, per_page: 1, date_from: today, date_to: today },
                dataType: "json",
                success: function(response) {
                    $("#todayAuditRecords").text(response.total || 0);
                }
            });
            
            // Get failed login attempts
            $.ajax({
                url: "/admin/get-audit-trails", 
                method: "POST",
                data: { page: 1, per_page: 1, action: "login_failed" },
                dataType: "json",
                success: function(response) {
                    $("#failedLoginAttempts").text(response.total || 0);
                }
            });
            
            // For active users, we'll show a placeholder for now
            $("#activeUsersCount").text("-");
        }
        
        // Display audit trails in the table
        function displayAuditTrails(records) {
            const tbody = $("#auditTrailTable tbody");
            tbody.empty();
            
            if (records.length === 0) {
                // tbody.html('<tr><td colspan="9" class="text-center">No records found</td></tr>');
                return;
            }
            
            for (const record of records) {
                console.log("details: ", record);
                const row = $("<tr>");
                row.append(`<td class="fw-bold">${record.audit_id}</td>`);
                row.append(`<td>${record.username || '<span class="text-muted">Unknown</span>'}</td>`);
                
                // Role badge
                const roleBadge = getRoleBadge(record.user_role);
                row.append(`<td>${roleBadge}</td>`);
                
                // Action badge
                const actionBadge = getActionBadge(record.action);
                row.append(`<td>${actionBadge}</td>`);
                
                row.append(`<td>${formatEntityType(record.entity_type)} ${record.entity_id ? '<small class="text-muted">(#' + record.entity_id + ')</small>' : ''}</td>`);
                row.append(`<td class="text-center">${record.entity_id || '<span class="text-muted">-</span>'}</td>`);
                row.append(`<td><small>${record.created_at_formatted}</small></td>`);
                row.append(`<td><small class="font-monospace">${record.ip_address}</small></td>`);
                
                // Action buttons
                const actionsCell = $("<td class='text-left'>");
                
                // View details button
                const viewBtn = $(`<button class="btn btn-outline-primary btn-sm me-1 view-audit-details" data-id="${record.audit_id}" title="View Details">
                    <i class="bi bi-info-circle"></i> Details
                </button>`);
                
                // Entity history button
                const historyBtn = $(`<button class="btn btn-outline-secondary btn-sm view-entity-history" 
                    data-entity-type="${record.entity_type}" 
                    data-entity-id="${record.entity_id}" title="View History">
                    <i class="bi bi-clock-history"></i> History
                </button>`);
                
                actionsCell.append(viewBtn);
                if (record.entity_id) {
                    actionsCell.append(historyBtn);
                }
                row.append(actionsCell);
                
                tbody.append(row);
            }
        }
        
        // Helper functions for badges
        function getRoleBadge(role) {
            if (!role) return '<span class="badge bg-light text-dark">Unknown</span>';
            
            const badgeClass = role === 'Super Admin' ? 'bg-danger' : 
                              role === 'Admin' ? 'bg-warning' : 'bg-primary';
            return `<span class="badge ${badgeClass}">${role}</span>`;
        }
        
        function getActionBadge(action) {
            if (!action) return '<span class="badge bg-secondary">Unknown</span>';
            
            const badgeClass = action === 'create' ? 'bg-success' :
                              action === 'update' ? 'bg-warning' :
                              action === 'delete' ? 'bg-danger' :
                              action === 'login' ? 'bg-info' :
                              action === 'logout' ? 'bg-secondary' :
                              action === 'login_failed' ? 'bg-danger' : 'bg-primary';
            
            const icon = action === 'create' ? 'plus-circle' :
                        action === 'update' ? 'pencil-square' :
                        action === 'delete' ? 'trash' :
                        action === 'login' ? 'box-arrow-in-right' :
                        action === 'logout' ? 'box-arrow-right' :
                        action === 'login_failed' ? 'shield-exclamation' : 'activity';
                        
            return `<span class="badge ${badgeClass}"><i class="bi bi-${icon} me-1"></i>${formatAction(action)}</span>`;
        }
        
        // Update pagination controls
        function updatePagination(currentPage, totalPages, totalRecords, perPage) {
            const pagination = $("#paginationContainer .pagination");
            pagination.empty();
            
            if (totalPages <= 1) {
                $("#paginationContainer").hide();
                return;
            }
            
            $("#paginationContainer").show();
            
            // Previous button
            const prevLi = $('<li class="page-item">');
            const prevLink = $('<a class="page-link" href="#" data-page="prev">&laquo;</a>');
            if (currentPage === 1) {
                prevLi.addClass('disabled');
            }
            prevLi.append(prevLink);
            pagination.append(prevLi);
            
            // Page numbers
            const maxPages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
            let endPage = Math.min(totalPages, startPage + maxPages - 1);
            
            if (endPage - startPage + 1 < maxPages) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageLi = $('<li class="page-item">');
                if (i === currentPage) {
                    pageLi.addClass('active');
                }
                const pageLink = $(`<a class="page-link" href="#" data-page="${i}">${i}</a>`);
                pageLi.append(pageLink);
                pagination.append(pageLi);
            }
            
            // Next button
            const nextLi = $('<li class="page-item">');
            const nextLink = $('<a class="page-link" href="#" data-page="next">&raquo;</a>');
            if (currentPage === totalPages) {
                nextLi.addClass('disabled');
            }
            nextLi.append(nextLink);
            pagination.append(nextLi);
        }
        
        // View audit details
        $(document).on("click", ".view-audit-details", function() {
            const auditId = $(this).data("id");
            
            // AJAX request to get audit details
            $.ajax({
                url: "/admin/get-audit-details",
                method: "POST",
                data: { audit_id: auditId },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        alert(response.error);
                        return;
                    }
                    
                    // Fill modal with data
                    $("#auditDetailId").text(response.audit_id);
                    $("#auditDetailDateTime").text(response.created_at_formatted);
                    $("#auditDetailUser").text(response.username || 'Unknown');
                    $("#auditDetailRole").html(getRoleBadge(response.user_role));
                    $("#auditDetailAction").html(getActionBadge(response.action));
                    $("#auditDetailEntity").text(`${formatEntityType(response.entity_type)} ${response.entity_id ? '(ID: ' + response.entity_id + ')' : ''}`);
                    $("#auditDetailIP").text(response.ip_address);
                    $("#auditDetailUserAgent").text(response.user_agent || 'N/A');
                    
                    // Display changes if available
                    const changesTable = $("#changesTable tbody");
                    changesTable.empty();
                    
                    let hasChanges = false;
                    
                    // For create actions, show new values
                    if (response.action === 'create' && response.new_values) {
                        for (const key in response.new_values) {
                            const row = $("<tr>");
                            row.append(`<td class="fw-bold">${formatFieldName(key)}</td>`);
                            row.append(`<td><span class="text-muted fst-italic">N/A (new record)</span></td>`);
                            row.append(`<td><span class="text-success">${formatValue(response.new_values[key])}</span></td>`);
                            changesTable.append(row);
                            hasChanges = true;
                        }
                    }
                    // For delete actions, show old values
                    else if (response.action === 'delete' && response.old_values) {
                        for (const key in response.old_values) {
                            const row = $("<tr>");
                            row.append(`<td class="fw-bold">${formatFieldName(key)}</td>`);
                            row.append(`<td><span class="text-danger">${formatValue(response.old_values[key])}</span></td>`);
                            row.append(`<td><span class="text-muted fst-italic">N/A (deleted)</span></td>`);
                            changesTable.append(row);
                            hasChanges = true;
                        }
                    }
                    // For update actions, compare old and new values
                    else if (response.action === 'update' && response.entity_type === 'bookings' && response.old_values && response.new_values) {
                        console.log("Old values: ", response.old_values);
                        console.log("New values: ", response.new_values);   
                        for (const key in response.new_values) {
                            if (JSON.stringify(response.old_values[key]) == JSON.stringify(response.new_values[key])) {
                                continue; // Skip unchanged values
                            }
                            
                            if (key === 'booking_costs' || key === 'trip_distances' || key === 'addresses' || key === 'balance') continue;
                            
                            if (key === 'stops') {
                                if (!Array.isArray(response.new_values[key]) || !Array.isArray(response.old_values[key])) continue; // Skip if stops are not arrays
                                
                                for (const stop of response.new_values[key]) {
                                    const oldStop = response.old_values[key].find(s => s.booking_stops_id === stop.booking_stops_id);
                                    if (oldStop && JSON.stringify(oldStop.location) == JSON.stringify(stop.location)) {
                                        continue; // Skip unchanged stops
                                    }
                                    
                                    const row = $("<tr>");
                                    row.append(`<td class="fw-bold">${formatFieldName(key)} (Stop ID: ${stop.booking_stops_id})</td>`);
                                    row.append(`<td><span class="text-danger">${formatValue(oldStop.location || 'N/A')}</span></td>`);
                                    row.append(`<td><span class="text-success">${formatValue(stop.location)}</span></td>`);
                                    changesTable.append(row);
                                    hasChanges = true;
                                }
                                continue;
                            }
                            
                            const row = $("<tr>");
                            row.append(`<td class="fw-bold">${formatFieldName(key)}</td>`);
                            row.append(`<td><span class="text-danger">${formatValue(response.old_values[key])}</span></td>`);
                            row.append(`<td><span class="text-success">${formatValue(response.new_values[key])}</span></td>`);
                            changesTable.append(row);
                            hasChanges = true;
                        }
                    }

                    else if (response.action === 'update' && response.entity_type !== 'bookings' && response.old_values && response.new_values) {
                        // console.log("Old values: ", response.old_values);
                        // console.log("New values: ", response.new_values);   
                        for (const key in response.new_values) {
                            if (JSON.stringify(response.old_values[key]) == JSON.stringify(response.new_values[key])) {
                                continue; // Skip unchanged values
                            }
                            
                            const row = $("<tr>");
                            row.append(`<td class="fw-bold">${formatFieldName(key)}</td>`);
                            row.append(`<td><span class="text-danger">${formatValue(response.old_values[key])}</span></td>`);
                            row.append(`<td><span class="text-success">${formatValue(response.new_values[key])}</span></td>`);
                            changesTable.append(row);
                            hasChanges = true;
                        }
                    }
                    
                    if (!hasChanges) {
                        changesTable.html('<tr><td colspan="3" class="text-center text-muted fst-italic">No changes recorded</td></tr>');
                    }
                    
                    // Show the modal
                    $("#auditDetailsModal").modal("show");
                },
                error: function() {
                    alert("Error fetching audit details. Please try again.");
                }
            });
        });
        
        // View entity history
        $(document).on("click", ".view-entity-history", function() {
            const entityType = $(this).data("entity-type");
            const entityId = $(this).data("entity-id");
            
            if (!entityType || !entityId) {
                alert("Entity information is missing");
                return;
            }
            
            // Update modal title
            $("#entityHistoryModalLabel").html(`<i class="bi bi-clock-history me-2 text-success"></i>History for ${formatEntityType(entityType)} #${entityId}`);
            
            // AJAX request to get entity history
            $.ajax({
                url: "/admin/get-entity-history",
                method: "POST",
                data: { 
                    entity_type: entityType,
                    entity_id: entityId
                },
                dataType: "json",
                success: function(response) {
                    // Display entity history
                    const tbody = $("#entityHistoryTable tbody");
                    tbody.empty();
                    
                    if (response.length === 0) {
                        tbody.html('<tr><td colspan="7" class="text-center text-muted fst-italic">No history records found</td></tr>');
                    } else {
                        for (const record of response) {
                            const row = $("<tr>");
                            row.append(`<td class="fw-bold">${record.audit_id}</td>`);
                            row.append(`<td>${record.username || 'Unknown'}</td>`);
                            row.append(`<td>${getRoleBadge(record.user_role)}</td>`);
                            row.append(`<td>${getActionBadge(record.action)}</td>`);
                            row.append(`<td><small>${record.created_at_formatted}</small></td>`);
                            row.append(`<td><small class="font-monospace">${record.ip_address}</small></td>`);
                            
                            // Summarize changes
                            const changesSummary = generateChangesSummary(record);
                            row.append(`<td><small>${changesSummary}</small></td>`);
                            
                            tbody.append(row);
                        }
                    }
                    
                    // Show the modal
                    $("#entityHistoryModal").modal("show");
                },
                error: function() {
                    alert("Error fetching entity history. Please try again.");
                }
            });
        });
        
        // Helper functions
        function formatAction(action) {
            if (!action) return 'Unknown';
            return action.charAt(0).toUpperCase() + action.slice(1).replace('_', ' ');
        }
        
        function formatEntityType(entityType) {
            if (!entityType) return 'Unknown';
            return entityType.charAt(0).toUpperCase() + entityType.slice(1);
        }
        
        function formatFieldName(fieldName) {
            if (!fieldName) return '';
            return fieldName
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }
        
        function formatValue(value) {
            if (value === null || value === undefined) {
                return '<span class="text-muted fst-italic">NULL</span>';
            }
            
            if (value === '') {
                return '<span class="text-muted fst-italic">Empty</span>';
            }
            
            if (typeof value === 'object') {
                return '<pre class="small mb-0">' + JSON.stringify(value, null, 2) + '</pre>';
            }
            
            if (typeof value === 'boolean') {
                return value ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>';
            }
            
            return value.toString();
        }
        
        function generateChangesSummary(record) {
            let summary = '';
            let count = 0;
            
            // For create actions
            if (record.action === 'create' && record.new_values) {
                const fields = Object.keys(record.new_values);
                count = fields.length;
                summary = `<span class="text-success">Created with ${count} field${count !== 1 ? 's' : ''}</span>`;
            }
            // For delete actions
            else if (record.action === 'delete' && record.old_values) {
                const fields = Object.keys(record.old_values);
                count = fields.length;
                summary = `<span class="text-danger">Deleted record with ${count} field${count !== 1 ? 's' : ''}</span>`;
            }
            // For update actions
            else if (record.action === 'update' && record.old_values && record.new_values) {
                const changedFields = [];
                
                for (const key in record.new_values) {
                    if (!record.old_values[key] || JSON.stringify(record.old_values[key]) !== JSON.stringify(record.new_values[key])) {
                        changedFields.push(formatFieldName(key));
                        count++;
                    }
                }
                
                if (count > 0) {
                    if (count <= 3) {
                        summary = `<span class="text-warning">Changed: ${changedFields.join(', ')}</span>`;
                    } else {
                        summary = `<span class="text-warning">Changed ${count} fields including: ${changedFields.slice(0, 2).join(', ')}...</span>`;
                    }
                } else {
                    summary = '<span class="text-muted">No changes detected</span>';
                }
            }
            // For other actions
            else {
                summary = '<span class="text-info">No detailed information available</span>';
            }
            
            return summary;
        }
    });
    </script>
</body>
</html> 