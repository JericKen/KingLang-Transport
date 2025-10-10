<?php
require_admin_auth(); // Use helper function
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="../../../public/icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../public/css/admin/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
        }
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
        .filters {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        /* Flatpickr custom styles */
        .date-input-wrapper {
            position: relative;
        }
        .date-input-wrapper i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
            color: #198754;
        }
        /* Customizing Flatpickr */
        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange, 
        .flatpickr-day.selected.inRange, 
        .flatpickr-day.startRange.inRange, 
        .flatpickr-day.endRange.inRange, 
        .flatpickr-day.selected:focus, 
        .flatpickr-day.startRange:focus, 
        .flatpickr-day.endRange:focus,
        .flatpickr-day.selected:hover, 
        .flatpickr-day.startRange:hover, 
        .flatpickr-day.endRange:hover {
            background: #198754;
            border-color: #198754;
        }
        /* Quick filter buttons */
        .quick-filter {
            transition: all 0.2s ease;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .quick-filter.active {
            background-color: var(--primary-green) !important;
            color: white !important;
            border-color: var(--primary-green) !important;
        }
        /* Stats icon styling to match other modules */
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
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
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-speedometer2 me-2 text-success"></i>Dashboard</h3>
                    <p class="text-muted mb-0">Overview of bookings, revenue, and activity</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <hr>

            <!-- Date Range Filters -->
            <div class="filters mt-0">
                <div class="row">
                    <div class="col-md-5">
                        <label for="startDate" class="form-label">Start Date</label>
                        <div class="date-input-wrapper">
                            <input type="text" class="form-control" id="startDate" placeholder="Select start date">
                            <i class="bi bi-calendar-date"></i>
                        </div>  
                    </div>
                    <div class="col-md-5">
                        <label for="endDate" class="form-label">End Date</label>
                        <div class="date-input-wrapper">
                            <input type="text" class="form-control" id="endDate" placeholder="Select end date">
                            <i class="bi bi-calendar-date"></i>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button id="applyFilters" class="btn btn-success flex-grow-1">Apply</button>
                        <button id="resetFilters" class="btn btn-outline-secondary" title="Reset to default date range">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="today">Today</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="yesterday">Yesterday</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="this-week">This Week</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="last-week">Last Week</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="this-month">This Month</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="last-month">Last Month</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="this-year">This Year</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-filter" data-range="last-year">Last Year</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-filter" data-range="all-time">All Time</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-4 col-xl mb-4">
                    <div class="summary-metrics-card d-flex gap-4 align-items-center p-4 h-100">
                        <div class="stats-icon bg-primary-subtle text-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Bookings</h6>
                            <h4 id="totalBookings" class="fw-bolder"></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 col-xl mb-4">
                    <div class="summary-metrics-card d-flex gap-4 align-items-center p-4 h-100">
                        <div class="stats-icon bg-success-subtle text-success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Revenue</h6>
                            <h4 id="totalRevenue" class="fw-bolder"></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 col-xl mb-4">
                    <div class="summary-metrics-card d-flex gap-4 align-items-center p-4 h-100">
                        <div class="stats-icon bg-info-subtle text-info">
                            <i class="bi bi-bus-front"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Upcoming Trips</h6>
                            <h4 id="upcomingTrips" class="fw-bolder"></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 col-xl mb-4">
                    <div class="summary-metrics-card d-flex gap-4 align-items-center p-4 h-100">
                        <div class="stats-icon bg-warning-subtle text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Pending Bookings</h6>
                            <h4 id="pendingBookings" class="fw-bolder"></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 col-xl mb-4 d-none">
                    <div class="summary-metrics-card d-flex gap-4 align-items-center p-4 h-100">
                        <div class="stats-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h4 id="flaggedBookings" class="fw-bolder"></h4>
                            <p class="text-secondary">Flagged Bookings</p>
                        </div>
                    </div>
                </div>  
            </div>

            <div class="row mt-2">
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Booking Trends</h4>
                        <canvas id="monthlyTrendsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Revenue Trends</h4>
                        <canvas id="revenueTrendsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Top Destinations</h4>
                        <canvas id="destinationsChart" height="300"></canvas>
                    </div>
                </div>
            </div>  

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h5>Payment Method Distribution</h5>
                        <canvas id="paymentMethodChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h5>Unpaid/Partially Paid Bookings</h5>
                        <canvas id="unpaidBookingsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Peak Booking Periods</h4>
                        <canvas id="peakBookingPeriodsChart" height="300"></canvas>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Peak Booking Hours</h4>
                        <canvas id="peakHoursChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- New row for additional charts -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Booking Status</h4>
                        <canvas id="bookingStatusChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Cancellations by Reason</h4>
                        <canvas id="cancellationsByReasonChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Avg Revenue per Trip</h4>
                        <canvas id="avgRevenuePerTripChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Operational & Client Insights -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Active vs Unavailable Buses</h4>
                        <canvas id="busAvailabilityChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Driver Assignments per Day</h4>
                        <canvas id="driverAssignmentsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Average Trip Duration</h4>
                        <canvas id="avgTripDurationChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Repeat Clients</h4>
                        <canvas id="repeatClientsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>New Clients (Selected Range)</h4>
                        <div class="d-flex align-items-center justify-content-center" style="height:300px;">
                            <h2 id="newClientsCount" class="mb-0 text-success"></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Client Satisfaction</h4>
                        <canvas id="clientSatisfactionChart" height="300"></canvas>
                        <div class="text-center mt-2"><small>Avg Rating: <span id="avgSatisfactionRating">-</span> / 5</small></div>
                    </div>
                </div>
            </div>


            <!-- Booking Patterns and Maintenance -->
            <div class="row d-none">
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Busiest Booking Days</h4>
                        <canvas id="busiestDaysChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Peak Booking Hours</h4>
                        <canvas id="peakHoursChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Customer Feedback Analysis</h4>
                        <canvas id="feedbackAnalysisChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-6 mb-4 d-none">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Maintenance Alerts</h4>
                        <canvas id="maintenanceAlertsChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Financial Analytics -->
            <div class="row mt-4">
                <div class="col-12">
                    <h3><i class="bi bi-cash-stack me-2 text-success"></i>Financial Analytics</h3>
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-currency-dollar text-success me-2"></i>Total Income Trends</h4>
                        <canvas id="totalIncomeChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-exclamation-circle text-warning me-2"></i>Outstanding Balances</h4>
                        <canvas id="outstandingBalancesChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-people-fill text-primary me-2"></i>Top-Paying Clients</h4>
                        <canvas id="topPayingClientsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-percent text-info me-2"></i>Discounts Given</h4>
                        <canvas id="discountsGivenChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Payment and Booking Analysis -->
            <div class="row d-none">
                <div class="col-md-6 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-exclamation-triangle text-warning me-2"></i>Unpaid/Partially Paid Bookings</h4>
                        <canvas id="unpaidBookingsChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4><i class="bi bi-graph-up text-success me-2"></i>Peak Booking Periods</h4>
                        <canvas id="peakBookingPeriodsChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Predictive Analytics -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">7-Day Booking Forecast</h4>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success" onclick="refreshForecastChart()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="exportForecastData()">
                                    <i class="bi bi-download"></i> Export
                                </button>
                            </div>
                        </div>
                        
                        <!-- Forecast Summary Cards -->
                        <div class="row mb-3" id="forecastSummary">
                            <div class="col-md-3">
                                <div class="text-center p-2 bg-light rounded">
                                    <h6 class="text-muted mb-1">Avg Daily</h6>
                                    <h5 class="mb-0 text-primary" id="avgDailyBookings">-</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-2 bg-light rounded">
                                    <h6 class="text-muted mb-1">Trend</h6>
                                    <h5 class="mb-0" id="forecastTrend">-</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-2 bg-light rounded">
                                    <h6 class="text-muted mb-1">Confidence</h6>
                                    <h5 class="mb-0 text-success" id="forecastConfidence">-</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-2 bg-light rounded">
                                    <h6 class="text-muted mb-1">Next 7 Days</h6>
                                    <h5 class="mb-0 text-info" id="totalForecast">-</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div id="bookingForecastChart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Customer Behavior Analytics -->
            <div class="row d-none">
                <div class="col-md-6 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Peak Booking Hours</h4>
                        <canvas id="peakHoursChart" height="300"></canvas>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="rounded p-4 summary-metrics-card">
                        <h4>Customer Feedback Analysis</h4>
                        <canvas id="feedbackAnalysisChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <!-- <script src="/../../../public/jquery/jquery-3.6.4.min.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="../../../public/js/admin/dashboard.js" type="module"></script>
    <script src="../../../public/js/admin/analytics-charts.js"></script>
</body>
</html>