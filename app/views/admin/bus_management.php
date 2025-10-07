<?php
require_once __DIR__ . "/../../controllers/admin/BusManagementController.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Bus Management</title>
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
        }
        .table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
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
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-4 px-xl-4">
            <!-- Header with admin profile -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-bus-front me-2 text-success"></i>Bus Management</h3>
                    <p class="text-muted mb-0">Manage and monitor your fleet of buses</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <hr>
            
            <!-- Stats Dashboard Cards -->
            <div class="row stats-dashboard g-2 mt-3">
                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-bus-front"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Buses</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalBusesCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-subtle text-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Active Buses</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="activeBusesCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm stats-card compact-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Under Maintenance</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="maintenanceBusesCount">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="row mt-3">
                <!-- Bus List -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0"><i class="bi bi-list-ul text-success me-2"></i>Bus Fleet</h5>
                            <div>
                                <button id="refreshBusesBtn" class="btn btn-outline-secondary btn-sm me-2">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                                <button id="addBusBtn" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-lg"></i> Add Bus
                                </button>
                                <div class="form-check form-switch d-inline-flex align-items-center ms-3">
                                    <input class="form-check-input" type="checkbox" id="toggleBusTrash">
                                    <label class="form-check-label ms-1" for="toggleBusTrash"><i class="bi bi-trash3"></i> Show Trash</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>License Plate</th>
                                            <th>Model</th>
                                            <th>Year</th>
                                            <th>Capacity</th>
                                            <th>Last Maintenance</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="busTableBody">
                                        <tr>
                                            <td colspan="9" class="text-center py-3">Loading buses...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bus Statistics -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-graph-up text-success me-2"></i>Most Used Buses</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush" id="mostUsedBusesList">
                                <li class="list-group-item text-center">Loading data...</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-calendar-check text-success me-2"></i>Current Month Usage</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush" id="currentMonthBusesList">
                                <li class="list-group-item text-center">Loading data...</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bus Availability Section -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-calendar-week text-success me-2"></i>Bus Availability</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="availabilityStartDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="availabilityStartDate">
                                </div>
                                <div class="col-md-4">
                                    <label for="availabilityEndDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="availabilityEndDate">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button id="checkAvailabilityBtn" class="btn btn-success">
                                        <i class="bi bi-search"></i> Check Availability
                                    </button>
                                </div>
                            </div>
                            <div id="busAvailabilityContainer">
                                <div class="alert alert-info">
                                    Select a date range and click "Check Availability" to see bus availability.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Bus Modal -->
    <div class="modal fade" id="addBusModal" tabindex="-1" aria-labelledby="addBusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="addBusModalLabel"><i class="bi bi-bus-front text-success me-2"></i>Add New Bus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addBusForm">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="busName" class="form-label small fw-bold">Bus Name*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" id="busName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="busCapacity" class="form-label small fw-bold">Capacity*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-people"></i></span>
                                    <input type="number" class="form-control" id="busCapacity" name="capacity" min="1" max="99" value="49" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="busLicensePlate" class="form-label small fw-bold">License Plate</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                    <input type="text" class="form-control" id="busLicensePlate" name="license_plate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="busModel" class="form-label small fw-bold">Model</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-truck"></i></span>
                                    <input type="text" class="form-control" id="busModel" name="model">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="busYear" class="form-label small fw-bold">Year</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                    <input type="number" class="form-control" id="busYear" name="year" min="1900" max="2100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="busLastMaintenance" class="form-label small fw-bold">Last Maintenance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tools"></i></span>
                                    <input type="date" class="form-control" id="busLastMaintenance" name="last_maintenance">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="busStatus" class="form-label small fw-bold">Status*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-toggle-on"></i></span>
                                    <select class="form-select" id="busStatus" name="status" required>
                                        <option value="Active">Active</option>
                                        <option value="Maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i>Add Bus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Bus Modal -->
    <div class="modal fade" id="editBusModal" tabindex="-1" aria-labelledby="editBusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="editBusModalLabel"><i class="bi bi-pencil-square text-success me-2"></i>Edit Bus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBusForm">
                    <div class="modal-body p-4">
                        <input type="hidden" id="editBusId" name="bus_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editBusName" class="form-label small fw-bold">Bus Name*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" id="editBusName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editBusCapacity" class="form-label small fw-bold">Capacity*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-people"></i></span>
                                    <input type="number" class="form-control" id="editBusCapacity" name="capacity" min="1" max="99" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editBusLicensePlate" class="form-label small fw-bold">License Plate</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                    <input type="text" class="form-control" id="editBusLicensePlate" name="license_plate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editBusModel" class="form-label small fw-bold">Model</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-truck"></i></span>
                                    <input type="text" class="form-control" id="editBusModel" name="model">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editBusYear" class="form-label small fw-bold">Year</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                    <input type="number" class="form-control" id="editBusYear" name="year" min="1900" max="2100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editBusLastMaintenance" class="form-label small fw-bold">Last Maintenance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tools"></i></span>
                                    <input type="date" class="form-control" id="editBusLastMaintenance" name="last_maintenance">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="editBusStatus" class="form-label small fw-bold">Status*</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-toggle-on"></i></span>
                                    <select class="form-select" id="editBusStatus" name="status" required>
                                        <option value="Active">Active</option>
                                        <option value="Maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>Update Bus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bus Schedule Modal -->
    <div class="modal fade" id="busScheduleModal" tabindex="-1" aria-labelledby="busScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="busScheduleModalLabel"><i class="bi bi-calendar-week text-success me-2"></i>Bus Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="scheduleBusId">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label for="scheduleStartDate" class="form-label small fw-bold">Start Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control" id="scheduleStartDate">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="scheduleEndDate" class="form-label small fw-bold">End Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control" id="scheduleEndDate">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button id="loadScheduleBtn" class="btn btn-success w-100" onclick="loadBusSchedule(document.getElementById('scheduleBusId').value)">
                                <i class="bi bi-search me-1"></i>Load
                            </button>
                        </div>
                    </div>
                    <div id="busScheduleContainer" class="mt-2">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Select a date range and click "Load" to see the bus schedule.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/../../../public/js/admin/bus_management.js"></script>
    <!-- Sidebar -->
    <script src="../../../public/js/assets/sidebar.js"></script>
</body>
</html> 