<?php
require_once __DIR__ . "/../../controllers/admin/TestimonialManagementController.php";

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
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Testimonial Management</title>
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
            border-bottom: 2px solid #28a745;
            color: #155724;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .testimonial-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .rating-stars {
            color: #ffc107;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .featured-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333;
            border: 1px solid #ddd;
        }
        .testimonial-content {
            max-height: 100px;
            overflow-y: auto;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .filter-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1rem;
        }
        .filter-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .filter-tabs .nav-link.active {
            color: #28a745;
            border-bottom: 2px solid #28a745;
            background: none;
        }
        .bulk-actions {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            display: none;
        }
        .testimonial-modal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
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
                    <h3><i class="bi bi-star me-2 text-success"></i>Testimonial Management</h3>
                    <p class="text-muted mb-0">Manage and moderate client testimonials and reviews</p>
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
                                    <i class="bi bi-chat-quote"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Total Testimonials</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="totalTestimonialsCount">-</h3>
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
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Pending Approval</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="pendingTestimonialsCount">-</h3>
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
                                    <h6 class="mb-0 text-muted">Approved</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="approvedTestimonialsCount">-</h3>
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
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Average Rating</h6>
                                    <h3 class="fw-bold mb-0 stats-number" id="averageRating">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-10">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary quick-filter active" data-status="all">
                            <i class="bi bi-funnel"></i> All
                        </button>
                        <button class="btn btn-sm btn-outline-warning quick-filter" data-status="pending">
                            <i class="bi bi-hourglass-split"></i> Pending
                        </button>
                        <button class="btn btn-sm btn-outline-success quick-filter" data-status="approved">
                            <i class="bi bi-check-circle"></i> Approved
                        </button>
                        <button class="btn btn-sm btn-outline-info quick-filter" data-status="featured">
                            <i class="bi bi-star"></i> Featured
                        </button>
                    </div>
                </div>
                <div class="col-xl-2">
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

            <!-- Filter Tabs -->
            <div class="card border-0 shadow-sm p-0">
                <div class="card-body p-0">
                    <!-- Bulk Actions -->
                    <div class="bulk-actions" id="bulkActions">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><span id="selectedCount">0</span> testimonial(s) selected</span>
                            <div>
                                <button class="btn btn-success btn-sm me-2" onclick="bulkAction('approve')">
                                    <i class="bi bi-check-circle"></i> Approve Selected
                                </button>
                                <button class="btn btn-warning btn-sm me-2" onclick="bulkAction('reject')">
                                    <i class="bi bi-x-circle"></i> Reject Selected
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonials Table -->
                    <div class="table-responsive rounded">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Client</th>
                                    <th>Rating</th>
                                    <th>Title</th>
                                    <th>Preview</th>
                                    <th>Trip</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="testimonialsTableBody">
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Testimonials pagination">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial Details Modal -->
    <div class="modal fade testimonial-modal" id="testimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Testimonial Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="testimonialModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="/../../../public/js/admin/testimonial_management.js"></script>
    <script src="../../../public/css/bootstrap/bootstrap.bundle.min.js"></script>

</body>
</html>