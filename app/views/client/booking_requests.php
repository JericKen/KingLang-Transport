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
    <link rel="stylesheet" href="/../../../../public/css/chat-widget.css">
    <title>My Bookings | Kinglang Booking</title>
    <style>
        /* KingLang Chat Widget Styles */

.chat-widget-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Chat Bubble */
.chat-bubble {
    background: linear-gradient(135deg, #2b7de9 0%, #1e5bb8 100%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(43, 125, 233, 0.4);
    transition: all 0.3s ease;
    position: relative;
    z-index: 1001;
}

.chat-bubble:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 20px rgba(43, 125, 233, 0.5);
}

.chat-bubble i {
    font-size: 24px;
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4757;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    font-weight: bold;
    display: none;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

/* Chat Panel */
.chat-panel {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 380px;
    height: 550px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 999;
    transform: translateY(100%) scale(0.8);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-panel.active {
    transform: translateY(0) scale(1);
    opacity: 1;
    visibility: visible;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Chat Header */
.chat-header {
    background: linear-gradient(135deg, #2b7de9 0%, #1e5bb8 100%);
    color: white;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}

.chat-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-assistance, .btn-end-chat {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-assistance:hover, .btn-end-chat:hover {
    background: rgba(255, 255, 255, 0.3);
}

.btn-end-chat {
    background: rgba(231, 76, 60, 0.8);
}

.btn-end-chat:hover {
    background: rgba(231, 76, 60, 1);
}

.chat-close {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.8);
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.chat-close:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

/* Connection Status */
.connection-status {
    display: none;
    background: #d4edda;
    color: #155724;
    padding: 8px 16px;
    font-size: 12px;
    text-align: center;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-bottom: 1px solid #c3e6cb;
}

.connection-status.admin-connected {
    display: flex;
}

.status-indicator {
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
}

/* Quick Questions */
.quick-questions {
    background: #f8f9fa;
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
}

.quick-questions h5 {
    margin: 0 0 8px 0;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.question-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.btn-question {
    background: #2b7de9;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-question:hover {
    background: #1e5bb8;
    transform: translateY(-1px);
}

/* Chat Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message {
    display: flex;
    flex-direction: column;
    max-width: 85%;
    animation: fadeInUp 0.3s ease;
}

.message.client-message {
    align-self: flex-end;
    margin-left: auto;
}

.message.bot-message,
.message.admin-message,
.message.system-message {
    align-self: flex-start;
    margin-right: auto;
}

.message-content {
    padding: 10px 14px;
    border-radius: 12px;
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.client-message .message-content {
    background: #2b7de9;
    color: white;
    border-radius: 12px 12px 4px 12px;
}

.bot-message .message-content {
    background: white;
    color: #333;
    border: 1px solid #e9ecef;
    border-left: 3px solid #2b7de9;
}

.admin-message .message-content {
    background: #28a745;
    color: white;
    border-radius: 12px 12px 12px 4px;
}

.system-message .message-content {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
    font-style: italic;
    text-align: center;
    border-radius: 8px;
}

.message-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
    font-size: 11px;
    opacity: 0.9;
}

.client-message .message-meta {
    color: rgba(255, 255, 255, 0.9);
}

.message-text {
    font-size: 14px;
    line-height: 1.4;
    margin: 0;
}

/* Message Input */
.message-input-area {
    padding: 16px;
    background: white;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
}

.input-group {
    display: flex;
    gap: 8px;
    align-items: center;
}

.input-group input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.input-group input:focus {
    border-color: #2b7de9;
    background: white;
    box-shadow: 0 0 0 3px rgba(43, 125, 233, 0.1);
}

.input-group input:disabled {
    background-color: #f1f3f4;
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
    border-color: #dee2e6;
}

.send-button:disabled {
    background: #6c757d !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.send-button:disabled:hover {
    background: #6c757d !important;
    transform: none;
}

.send-button {
    width: 40px;
    height: 40px;
    background: #2b7de9;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.send-button:hover {
    background: #1e5bb8;
    transform: scale(1.05);
}

.send-button i {
    font-size: 16px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scrollbar */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive Design */
@media (max-width: 480px) {
    .chat-widget-container {
        bottom: 10px;
        right: 10px;
    }
    
    .chat-panel {
        width: calc(100vw - 20px);
        height: calc(100vh - 100px);
        right: 10px;
        bottom: 80px;
        border-radius: 12px 12px 0 0;
    }
    
    .chat-bubble {
        width: 56px;
        height: 56px;
    }
    
    .chat-bubble i {
        font-size: 20px;
    }
    
    .btn-question {
        font-size: 10px;
        padding: 3px 6px;
    }
    
    .message {
        max-width: 95%;
    }
    
    .chat-header {
        padding: 12px 16px;
    }
    
    .chat-header h4 {
        font-size: 14px;
    }
    
    .btn-assistance, .btn-end-chat {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .btn-text {
        display: none;
    }
}

/* Button styles in bot messages */
.message-text .btn {
    margin: 4px 2px;
    padding: 6px 12px;
    border-radius: 16px;
    border: none;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.message-text .btn-primary {
    background: #2b7de9;
    color: white;
}

.message-text .btn-primary:hover {
    background: #1e5bb8;
}

.message-text .btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 1px solid #6c757d;
}

.message-text .btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.message-text .button-container {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.message-text .mt-2 {
    margin-top: 8px;
}
        .content.collapsed {
            margin-left: 78px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 78px);
        }
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 250px);
        }
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
            /* max-height: calc(100vh - 350px); */
            /* overflow-y: auto; */
            margin-bottom: 1rem;
        }
        .table thead th {
            background-color: var(--light-green);
            font-weight: 600;
            padding: 12px 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            user-select: none;
        }
        .actions-compact {
            display: flex;
            gap: 0.25rem;
        }
        .actions-compact .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
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
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchBookings" class="form-control border-start-0" placeholder="Search destinations...">
                                        <button id="searchBtn" class="btn btn-success">Search</button>
                                    </div>
                                </div>
                                
                                <!-- Status Filter -->
                                <div class="col-lg-4 col-md-4 d-none">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-filter"></i>
                                        </span>
                                        <select name="status" id="statusSelect" class="form-select">
                                            <option value="all">All Bookings</option>
                                            <option value="pending" selected>Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="processing">Processing</option>
                                            <option value="canceled">Canceled</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="completed">Completed</option>
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
                                
                                <input type="radio" class="btn-check" name="viewOption" id="cardView" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="cardView">
                                    <i class="bi bi-grid-3x3-gap"></i> Cards
                                </label>
                                
                                <input type="radio" class="btn-check" name="viewOption" id="calendarView" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="calendarView">
                                    <i class="bi bi-calendar3"></i> Calendar
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
                        <button class="btn btn-sm btn-outline-warning quick-filter active" data-status="pending">
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
                    </div>
                </div>
                <div class="col-xl-4">
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

    <script>
    // Set user login status for chat widget
    var userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    // KingLang Chat Widget Core Functionality
class KingLangChat {
    constructor() {
        this.conversationId = null;
        this.isOpen = false;
        this.isAdminConnected = false;
        this.conversationEnded = false;
        this.pollingInterval = null;
        
        this.init();
    }
    
    init() {
        this.createWidget();
        this.bindEvents();
        this.loadStoredState();
        this.initializeConversation();
        this.startPolling();
    }
    
    createWidget() {
        const widgetHTML = `
            <div class="chat-widget-container" id="chat-widget">
                <div class="chat-bubble" id="chat-bubble">
                    <i class="fas fa-comments"></i>
                    <div class="unread-badge" id="unread-badge">0</div>
                </div>
                
                <div class="chat-panel" id="chat-panel">
                    <div class="chat-container">
                        <div class="chat-header">
                            <h4><i class="fas fa-bus"></i> KingLang Support</h4>
                            <div class="header-actions">
                                <button class="btn btn-assistance" onclick="chatWidget.requestHumanAssistance()">
                                    <i class="fas fa-user-headset"></i> Get Help
                                </button>
                                <button class="btn btn-end-chat" onclick="chatWidget.endConversation()" id="end-chat-btn" style="display: none;">
                                    <i class="fas fa-sign-out-alt"></i> End Chat
                                </button>
                                <div class="chat-close" onclick="chatWidget.toggleChat()">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>

                        <div class="connection-status" id="connection-status">
                            <span class="status-indicator"></span>
                            <span class="status-text">Connected to customer service agent</span>
                        </div>

                        <div class="quick-questions">
                            <h5>Quick Questions</h5>
                            <div class="question-buttons">
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What are your rental rates?')">
                                    <i class="fas fa-dollar-sign"></i> Pricing
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('How do I make a booking?')">
                                    <i class="fas fa-calendar-check"></i> Booking
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What is your cancellation policy?')">
                                    <i class="fas fa-times-circle"></i> Cancellation
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('How can I contact you?')">
                                    <i class="fas fa-phone"></i> Contact
                                </button>
                                <button class="btn-question" onclick="chatWidget.askPredefinedQuestion('What types of buses do you have?')">
                                    <i class="fas fa-bus"></i> Fleet
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chat-messages"></div>
                        
                        <div class="message-input-area">
                            <div class="input-group">
                                <input type="text" id="message-input" placeholder="Type your message..." onkeypress="chatWidget.handleKeyPress(event)">
                                <button type="button" class="send-button" onclick="chatWidget.sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
    }
    
    bindEvents() {
        const chatBubble = document.getElementById('chat-bubble');
        chatBubble.addEventListener('click', () => this.toggleChat());
    }
    
    async initializeConversation() {
        try {
            const response = await fetch('/api/chat/conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.conversationId = data.conversation_id;
                localStorage.setItem('kinglang_conversation_id', this.conversationId);
                this.loadMessages();
            }
        } catch (error) {
            console.error('Error initializing conversation:', error);
        }
    }
    
    async loadMessages() {
        if (!this.conversationId) return;
        
        try {
            const response = await fetch(`/api/chat/messages/${this.conversationId}`, {
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.innerHTML = '';
                
                data.messages.forEach(message => this.displayMessage(message));
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatPanel = document.getElementById('chat-panel');
        
        if (this.isOpen) {
            chatPanel.classList.add('active');
            this.hideUnreadBadge();
            this.scrollToBottom();
        } else {
            chatPanel.classList.remove('active');
        }
        
        localStorage.setItem('kinglang_chat_open', this.isOpen.toString());
    }
    
    displayMessage(message) {
        const chatMessages = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender_type}-message`;
        
        let senderName = '';
        switch(message.sender_type) {
            case 'client': senderName = 'You'; break;
            case 'admin': senderName = 'Customer Service'; break;
            case 'bot': senderName = 'KingLang Assistant'; break;
            case 'system': senderName = 'System'; break;
        }
        
        const timestamp = new Date(message.sent_at).toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', hour12: true
        });
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-meta">
                    <strong>${senderName}   </strong><small class="text-muted"> • ${timestamp}</small>
                </div>
                <div class="message-text">${message.message}</div>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        
        // Handle admin connection
        if (message.sender_type === 'admin') {
            this.isAdminConnected = true;
            this.showAdminConnected();
        }
    }
    
    async sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message || !this.conversationId) return;
        
        // Display immediately
        this.displayMessage({
            id: Date.now(),
            sender_type: 'client',
            message: message,
            sent_at: new Date().toISOString()
        });
        
        input.value = '';
        this.scrollToBottom();
        
        try {
            const response = await fetch('/api/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    conversation_id: this.conversationId,
                    message: message
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.bot_response) {
                    this.displayMessage(data.bot_response);
                    this.scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
    
    handleKeyPress(event) {
        if (event.key === 'Enter') {
            this.sendMessage();
        }
    }
    
    askPredefinedQuestion(question) {
        document.getElementById('message-input').value = question;
        this.sendMessage();
    }
    
    async requestHumanAssistance() {
        try {
            await fetch('/api/chat/request-human', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ conversation_id: this.conversationId })
            });
            this.loadMessages();
        } catch (error) {
            console.error('Error requesting human assistance:', error);
        }
    }
    
    async endConversation() {
        if (!this.conversationId) {
            console.error('No active conversation to end');
            return;
        }
        
        // Show confirmation dialog
        if (!confirm('Are you sure you want to end this conversation? This action cannot be undone.')) {
            return;
        }
        
        try {
            console.log('🔚 Ending conversation:', this.conversationId);
            
            const response = await fetch('/api/chat/end', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ conversation_id: this.conversationId })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Conversation ended successfully');
                    
                    // Mark conversation as ended
                    this.conversationEnded = true;
                    this.isAdminConnected = false;
                    
                    // Hide the end chat button and show assistance button
                    document.getElementById('end-chat-btn').style.display = 'none';
                    document.querySelector('.btn-assistance').style.display = 'flex';
                    
                    // Update connection status
                    document.getElementById('connection-status').classList.remove('admin-connected');
                    document.querySelector('.status-text').textContent = 'Connected to KingLang Assistant';
                    
                    // Display system message about conversation ending
                    this.displayMessage({
                        id: Date.now(),
                        sender_type: 'system',
                        message: data.message || 'This conversation has been ended. Thank you for contacting KingLang Support!',
                        sent_at: new Date().toISOString()
                    });
                    
                    // Disable message input temporarily
                    const messageInput = document.getElementById('message-input');
                    const sendButton = document.querySelector('.send-button');
                    messageInput.disabled = true;
                    messageInput.placeholder = 'Conversation ended. Starting new conversation...';
                    sendButton.disabled = true;
                    
                    this.scrollToBottom();
                    
                    // Auto-restart conversation after 3 seconds (like the original AI chatbot)
                    setTimeout(() => {
                        this.restartConversation();
                    }, 3000);
                    
                } else {
                    console.error('Failed to end conversation:', data.message);
                    alert('Failed to end conversation. Please try again.');
                }
            } else {
                throw new Error('Failed to end conversation');
            }
            
        } catch (error) {
            console.error('Error ending conversation:', error);
            alert('An error occurred while ending the conversation. Please try again.');
        }
    }
    
    async restartConversation() {
        console.log('🔄 Restarting conversation...');
        
        // Clear conversation state
        this.conversationId = null;
        this.conversationEnded = false;
        this.isAdminConnected = false;
        
        // Clear stored conversation ID
        localStorage.removeItem('kinglang_conversation_id');
        
        // Re-enable message input
        const messageInput = document.getElementById('message-input');
        const sendButton = document.querySelector('.send-button');
        messageInput.disabled = false;
        messageInput.placeholder = 'Type your message...';
        sendButton.disabled = false;
        
        // Display welcome message
        this.displayMessage({
            id: Date.now(),
            sender_type: 'system',
            message: 'New conversation started. How can we help you today?',
            sent_at: new Date().toISOString()
        });
        
        // Initialize new conversation
        await this.initializeConversation();
        
        console.log('✅ New conversation started');
    }
    
    showAdminConnected() {
        document.getElementById('connection-status').classList.add('admin-connected');
        document.querySelector('.btn-assistance').style.display = 'none';
        document.getElementById('end-chat-btn').style.display = 'flex';
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    hideUnreadBadge() {
        document.getElementById('unread-badge').style.display = 'none';
    }
    
    loadStoredState() {
        this.conversationId = localStorage.getItem('kinglang_conversation_id');
        this.isOpen = localStorage.getItem('kinglang_chat_open') === 'true';
        
        if (this.isOpen) {
            document.getElementById('chat-panel').classList.add('active');
        }
    }
    
    startPolling() {
        this.pollingInterval = setInterval(() => {
            if (this.conversationId) this.checkForNewMessages();
        }, 5000);
    }
    
    async checkForNewMessages() {
        try {
            const response = await fetch(`/api/chat/messages/${this.conversationId}`, {
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                const currentMessages = document.querySelectorAll('.message').length;
                
                if (data.messages.length > currentMessages) {
                    const newMessages = data.messages.slice(currentMessages);
                    newMessages.forEach(message => {
                        this.displayMessage(message);
                        if (!this.isOpen && message.sender_type !== 'client') {
                            this.showUnreadBadge();
                        }
                    });
                    this.scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error checking for new messages:', error);
        }
    }
    
    showUnreadBadge() {
        const badge = document.getElementById('unread-badge');
        let count = parseInt(badge.textContent) || 0;
        badge.textContent = ++count;
        badge.style.display = 'flex';
    }
}

// Initialize chat widget
let chatWidget;
document.addEventListener('DOMContentLoaded', function() {
    if (typeof userLoggedIn !== 'undefined' && userLoggedIn) {
        chatWidget = new KingLangChat();
    }
});
    </script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/utils/pagination.js"></script>
    <script src="../../../public/js/client/booking_request.js"></script>
    <script src="/public/js/chat-widget-core.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>

</body>
</html>