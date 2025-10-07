// Global chart instances
let bookingStatusChart = null;
let paymentMethodChart = null;
let monthlyTrendsChart = null;
let topDestinationsChart = null;

// Flatpickr instances
let startDatePicker = null;
let endDatePicker = null;

// Current filters state
const filters = {
    startDate: null,
    endDate: null,
    page: 1,
    limit: 10
};

// Pagination state
let paginationData = {
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0
};

$(document).ready(function() {
    // Initialize date inputs with Flatpickr
    const today = new Date();
    const firstDayOfYear = new Date(today.getFullYear(), 0, 1);
    
    // Flatpickr configuration
    const dateConfig = {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        allowInput: false,
        theme: "light"
    };
    
    // Initialize start date picker
    startDatePicker = flatpickr("#startDate", {
        ...dateConfig,
        defaultDate: firstDayOfYear,
        allowInput: false,
        maxDate: today,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates[0]) {
                endDatePicker.set("minDate", selectedDates[0]);
                // Clear active state from all quick filter buttons
                $('.quick-filter').removeClass('active').addClass('btn-outline-success');
            }
        }
    });
    
    // Initialize end date picker
    endDatePicker = flatpickr("#endDate", {
        ...dateConfig,
        defaultDate: today,
        maxDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000),
        minDate: firstDayOfYear,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates[0]) {
                startDatePicker.set("maxDate", selectedDates[0]);
                // Clear active state from all quick filter buttons
                $('.quick-filter').removeClass('active').addClass('btn-outline-success');
            }
        }
    });
    
    filters.startDate = formatDate(firstDayOfYear);
    filters.endDate = formatDate(today);
    
    // Event listeners
    $('#applyFilters').on('click', applyFilters);
    $('#pageSize').on('change', function() {
        filters.limit = parseInt($(this).val());
        filters.page = 1;
        fetchDetailedBookingList();
    });
    $('#exportCsv').on('click', exportBookingReportToCsv);
    
    // Event listener for quick filter buttons
    $('.quick-filter').on('click', function() {
        const range = $(this).data('range');
        applyQuickFilter(range);
    });
    
    // Event listener for reset button
    $('#resetFilters').on('click', resetFilters);
    
    // Highlight the "This Year" button by default
    $('.quick-filter[data-range="this-year"]').addClass('active').removeClass('btn-outline-success');
    
    // Initial data load
    loadAllReports();
});

function applyFilters() {
    filters.startDate = startDatePicker.input.value;
    filters.endDate = endDatePicker.input.value;
    filters.page = 1;
    
    // Clear active state from all quick filter buttons when manually applying filters
    $('.quick-filter').removeClass('active').addClass('btn-outline-success');
    
    loadAllReports();
}

// Function to reset filters to default (This Year)
function resetFilters() {
    // Reset to default date range (This Year)
    applyQuickFilter('this-year');
}

// Function to apply quick filter based on predefined date ranges
function applyQuickFilter(range) {
    const today = new Date();
    let startDate, endDate;
    
    // Highlight the selected button
    $('.quick-filter').removeClass('active').addClass('btn-outline-success');
    $(`.quick-filter[data-range="${range}"]`).removeClass('btn-outline-success').addClass('active');
    
    switch (range) {
        case 'today':
            startDate = new Date(today);
            endDate = new Date(today);
            break;
            
        case 'yesterday':
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - 1);
            endDate = new Date(startDate);
            break;
            
        case 'this-week':
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - startDate.getDay()); // Start of week (Sunday)
            endDate = new Date(today);
            break;
            
        case 'last-week':
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - startDate.getDay() - 7); // Start of last week
            endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 6); // End of last week (Saturday)
            break;
            
        case 'this-month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today);
            break;
            
        case 'last-month':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
            
        case 'this-year':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today);
            break;
            
        case 'last-year':
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = new Date(today.getFullYear() - 1, 11, 31);
            break;
            
        case 'all-time':
            startDate = new Date(2000, 0, 1); // Far past date
            endDate = new Date(today);
            $('.quick-filter').removeClass('active').addClass('btn-outline-success');
            $(`.quick-filter[data-range="all-time"]`).removeClass('btn-outline-success').addClass('btn-outline-secondary active');
            break;
            
        default:
            return;
    }
    
    // Update the date pickers
    startDatePicker.setDate(startDate);
    endDatePicker.setDate(endDate);
    
    // Update the filters
    filters.startDate = formatDate(startDate);
    filters.endDate = formatDate(endDate);
    filters.page = 1;
    
    // Apply the filters
    loadAllReports();
}

function loadAllReports() {
    fetchBookingSummary();
    fetchMonthlyBookingTrend();
    fetchTopDestinations();
    fetchPaymentMethodDistribution();
    fetchDetailedBookingList();
    fetchCancellationReport();
    fetchFinancialSummary();
    loadClientList();
}

async function fetchBookingSummary() {
    try {
        console.log('Fetching booking summary with:', {
            start_date: filters.startDate,
            end_date: filters.endDate
        });
        
        const response = await fetch('/admin/reports/booking-summary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Booking summary data received:', data);
        updateSummaryCards(data);
        renderBookingStatusChart(data);
    } catch (error) {
        console.error('Error fetching booking summary:', error);
    }
}

function updateSummaryCards(data) {
    $('#totalBookings').text(data.total_bookings || 0);
    $('#totalRevenue').text(formatCurrency(data.total_revenue || 0));
    $('#outstandingBalance').text(formatCurrency(data.outstanding_balance || 0));
    $('#avgBookingValue').text(formatCurrency(data.average_booking_value || 0));
}

function renderBookingStatusChart(data) {
    const ctx = document.getElementById('bookingStatusChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (bookingStatusChart) {
        bookingStatusChart.destroy();
    }
    
    // Prepare data
    const labels = ['Confirmed', 'Pending', 'Completed', 'Canceled', 'Rejected'];
    const values = [
        data.confirmed_bookings || 0,
        data.pending_bookings || 0,
        data.completed_bookings || 0,
        data.canceled_bookings || 0,
        data.rejected_bookings || 0
    ];
    
    // Define colors
    const colors = [
        'rgba(40, 167, 69, 0.7)',  // Green for confirmed
        'rgba(255, 193, 7, 0.7)',  // Yellow for pending
        'rgba(23, 162, 184, 0.7)', // Teal for completed
        'rgba(220, 53, 69, 0.7)',  // Red for canceled
        'rgba(108, 117, 125, 0.7)' // Gray for rejected
    ];
    
    // Create chart
    bookingStatusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = values.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

async function fetchMonthlyBookingTrend() {
    try {
        const response = await fetch('/admin/reports/monthly-trend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        renderMonthlyTrendsChart(data);
    } catch (error) {
        console.error('Error fetching monthly trends:', error);
    }
}

function renderMonthlyTrendsChart(data) {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (monthlyTrendsChart) {
        monthlyTrendsChart.destroy();
    }
    
    // Prepare data
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const bookings = [];
    const revenue = [];
    
    data.forEach(item => {
        const monthIndex = item.month - 1;
        bookings[monthIndex] = item.total_bookings;
        revenue[monthIndex] = item.total_revenue;
    });
    
    // Create chart
    monthlyTrendsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Bookings',
                    data: bookings,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Revenue',
                    data: revenue,
                    type: 'line',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label === 'Revenue') {
                                return label + ': ' + formatCurrency(context.raw);
                            }
                            return label + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    },
                    position: 'left'
                },
                y1: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue'
                    },
                    position: 'right',
                    grid: {
                        drawOnChartArea: false // only show grid for left y-axis
                    }
                }
            }
        }
    });
}

async function fetchTopDestinations() {
    try {
        const response = await fetch('/admin/reports/top-destinations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate,
                limit: 10
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        renderTopDestinationsChart(data);
    } catch (error) {
        console.error('Error fetching top destinations:', error);
    }
}

function renderTopDestinationsChart(data) {
    const ctx = document.getElementById('topDestinationsChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (topDestinationsChart) {
        topDestinationsChart.destroy();
    }
    
    // Prepare data
    const destinations = data.map(item => item.destination);
    const bookingCount = data.map(item => item.booking_count);
    const revenue = data.map(item => item.total_revenue);
    
    // Generate colors array
    const backgroundColors = generateColorGradient('#28a745', '#1e7e34', destinations.length);
    
    // Create chart
    topDestinationsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: destinations,
            datasets: [
                {
                    label: 'Booking Count',
                    data: bookingCount,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors,
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            return 'Revenue: ' + formatCurrency(revenue[index]);
                        }
                    }
                }
            }
        }
    });
}

async function fetchPaymentMethodDistribution() {
    try {
        const response = await fetch('/admin/reports/payment-methods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        renderPaymentMethodChart(data);
    } catch (error) {
        console.error('Error fetching payment methods:', error);
    }
}

function renderPaymentMethodChart(data) {
    const ctx = document.getElementById('paymentMethodChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (paymentMethodChart) {
        paymentMethodChart.destroy();
    }
    
    // Prepare data
    const methods = data.map(item => item.payment_method);
    const counts = data.map(item => item.payment_count);
    
    // Define colors
    const colors = [
        'rgba(40, 167, 69, 0.7)',
        'rgba(0, 123, 255, 0.7)',
        'rgba(255, 193, 7, 0.7)',
        'rgba(108, 117, 125, 0.7)',
        'rgba(23, 162, 184, 0.7)'
    ];
    
    // Create chart
    paymentMethodChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: methods,
            datasets: [{
                data: counts,
                backgroundColor: colors.slice(0, methods.length),
                borderColor: colors.map(color => color.replace('0.7', '1')).slice(0, methods.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = counts.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

async function fetchDetailedBookingList() {
    try {
        const response = await fetch('/admin/reports/detailed-bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate,
                page: filters.page,
                limit: filters.limit
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        renderBookingTable(data.bookings);
        updatePagination(data);
    } catch (error) {
        console.error('Error fetching detailed bookings:', error);
    }
}

function renderBookingTable(bookings) {
    const tableBody = $('#bookingReportTableBody');
    tableBody.empty();
    
    if (bookings && bookings.length > 0) {
        bookings.forEach(booking => {
            // Format the date
            const tourDate = new Date(booking.date_of_tour);
            const formattedDate = tourDate.toLocaleDateString();
            
            // Status badge with icon
            let statusClass = 'bg-secondary';
            let statusIcon = 'bi-question-circle';
            if (booking.status === 'Confirmed') { statusClass = 'bg-success'; statusIcon = 'bi-check-circle'; }
            if (booking.status === 'Pending')   { statusClass = 'bg-warning text-dark'; statusIcon = 'bi-hourglass-split'; }
            if (booking.status === 'Canceled')  { statusClass = 'bg-danger'; statusIcon = 'bi-x-circle'; }
            if (booking.status === 'Completed') { statusClass = 'bg-info'; statusIcon = 'bi-flag'; }
            if (booking.status === 'Rejected')  { statusClass = 'bg-dark'; statusIcon = 'bi-slash-circle'; }
            
            // Payment status badge with icon
            let paymentStatusClass = 'bg-secondary';
            let paymentStatusIcon = 'bi-question-circle';
            if (booking.payment_status === 'Paid')            { paymentStatusClass = 'bg-success'; paymentStatusIcon = 'bi-cash-stack'; }
            if (booking.payment_status === 'Partially Paid')  { paymentStatusClass = 'bg-warning text-dark'; paymentStatusIcon = 'bi-cash'; }
            if (booking.payment_status === 'Unpaid')          { paymentStatusClass = 'bg-danger'; paymentStatusIcon = 'bi-exclamation-circle'; }
            
            tableBody.append(`
                <tr>
                    <td>${booking.client_name}</td>
                    <td>${booking.destination}</td>
                    <td>${formattedDate}</td>
                    <td>${formatCurrency(booking.total_cost)}</td>
                    <td>
                        <span class="badge badge-status rounded-pill px-3 py-2 ${statusClass}">
                            <i class="bi ${statusIcon}"></i> ${booking.status}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-status rounded-pill px-3 py-2 ${paymentStatusClass}">
                            <i class="bi ${paymentStatusIcon}"></i> ${booking.payment_status}
                        </span>
                    </td>
                </tr>
            `);
        });
    } else {
        tableBody.append(`
            <tr>
                <td colspan="6" class="text-center">No bookings found</td>
            </tr>
        `);
    }
}

function updatePagination(data) {
    paginationData = {
        page: data.page,
        limit: data.limit,
        total: data.total,
        totalPages: data.total_pages
    };
    
    renderPagination();
}

function renderPagination() {
    const container = $('#paginationControls');
    container.empty();
    
    // Don't show pagination if total records less than 10 or only one page
    if (paginationData.total < 10 || paginationData.totalPages <= 1) {
        return;
    }
    
    // Use the centralized pagination utility (using jQuery container)
    createPagination({
        containerId: "paginationControls",
        totalPages: paginationData.totalPages,
        currentPage: paginationData.page,
        paginationType: 'standard',
        onPageChange: (page) => {
            filters.page = page;
            fetchDetailedBookingList();
        }
    });
}

async function exportBookingReportToCsv() {
    try {
        // Show loading state
        const originalText = $('#exportCsv').text();
        $('#exportCsv').text('Exporting...').prop('disabled', true);
        
        const response = await fetch('/admin/reports/export-bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data && data.bookings && data.bookings.length > 0) {
            generateCsvDownload(data.bookings);
        } else {
            alert('No data to export.');
        }
        
        $('#exportCsv').text(originalText).prop('disabled', false);
    } catch (error) {
        console.error('Error exporting bookings:', error);
        $('#exportCsv').text(originalText).prop('disabled', false);
        alert('Error exporting data. Please try again.');
    }
}

function generateCsvDownload(bookings) {
    // CSV header
    let csv = 'Client Name,Contact Number,Destination,Pickup Point,Date of Tour,End of Tour,Days,Buses,Status,Payment Status,Total Cost,Balance\n';
    
    // Add rows
    bookings.forEach(booking => {
        const row = [
            `"${booking.client_name}"`,
            `"${booking.contact_number}"`,
            `"${booking.destination}"`,
            `"${booking.pickup_point}"`,
            `"${booking.date_of_tour}"`,
            `"${booking.end_of_tour}"`,
            booking.number_of_days,
            booking.number_of_buses,
            `"${booking.status}"`,
            `"${booking.payment_status}"`,
            booking.total_cost,
            booking.balance
        ];
        
        csv += row.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    link.setAttribute('href', url);
    link.setAttribute('download', `booking_report_${formatDateForFilename(new Date())}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Utility functions
function formatDate(date) {
    // Handle already formatted string
    if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return date;
    }
    
    const d = new Date(date);
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    const year = d.getFullYear();
    
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    
    return [year, month, day].join('-');
}

function formatDateForFilename(date) {
    const d = new Date(date);
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    const year = d.getFullYear();
    
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    
    return [year, month, day].join('');
}

function formatCurrency(amount) {
    return '₱ ' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function generateColorGradient(startColor, endColor, steps) {
    // Convert hex to RGB
    const startRGB = hexToRgb(startColor);
    const endRGB = hexToRgb(endColor);
    
    // Calculate step size for each RGB component
    const stepR = (endRGB.r - startRGB.r) / (steps - 1);
    const stepG = (endRGB.g - startRGB.g) / (steps - 1);
    const stepB = (endRGB.b - startRGB.b) / (steps - 1);
    
    // Generate colors
    const colors = [];
    for (let i = 0; i < steps; i++) {
        const r = Math.round(startRGB.r + stepR * i);
        const g = Math.round(startRGB.g + stepG * i);
        const b = Math.round(startRGB.b + stepB * i);
        colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
    }
    
    return colors;
}

function hexToRgb(hex) {
    // Remove # if present
    hex = hex.replace('#', '');
    
    // Parse the hex values
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    
    return { r, g, b };
}

// Fetch and display cancellation report
async function fetchCancellationReport() {
    try {
        const response = await fetch('/admin/reports/cancellations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        displayCancellationReport(data);
    } catch (error) {
        console.error('Error fetching cancellation report:', error);
        $('#cancellationReportTableBody').html('<tr><td colspan="5" class="text-center">Error loading cancellation data</td></tr>');
    }
}

function displayCancellationReport(data) {
    const tbody = $('#cancellationReportTableBody');
    tbody.empty();
    
    if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">No cancellation data found</td></tr>');
        return;
    }
    
    data.forEach(item => {
        const row = `
            <tr>
                <td>${item.reason || 'N/A'}</td>
                <td>${item.canceled_by || 'N/A'}</td>
                <td>${item.cancellation_count || 0}</td>
                <td>${formatCurrency(item.total_value || 0)}</td>
                <td>${formatCurrency(item.total_refunded || 0)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Fetch and display financial summary
async function fetchFinancialSummary() {
    try {
        const response = await fetch('/admin/reports/financial-summary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        displayFinancialSummary(data);
    } catch (error) {
        console.error('Error fetching financial summary:', error);
    }
}

function displayFinancialSummary(data) {
    $('#financialTotalRevenue').text(formatCurrency(data.total_revenue || 0));
    $('#financialCollectedRevenue').text(formatCurrency(data.collected_revenue || 0));
    $('#financialOutstandingBalance').text(formatCurrency(data.outstanding_balance || 0));
    $('#financialUniqueClients').text(data.unique_clients || 0);
}

// Load client list for booking history dropdown
async function loadClientList() {
    try {
        // We'll need to create an endpoint to get all clients
        // For now, we'll use a simple approach - get clients from detailed bookings
        const response = await fetch('/admin/reports/detailed-bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                page: 1,
                limit: 1000
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        populateClientDropdown(data.bookings);
    } catch (error) {
        console.error('Error loading client list:', error);
    }
}

function populateClientDropdown(bookings) {
    const clientSelect = $('#clientSelect');
    clientSelect.html('<option value="">Select a client...</option>');
    
    // Extract unique clients
    const clients = {};
    bookings.forEach(booking => {
        if (!clients[booking.user_id]) {
            clients[booking.user_id] = booking.client_name;
        }
    });
    
    // Populate dropdown
    Object.keys(clients).sort((a, b) => clients[a].localeCompare(clients[b])).forEach(userId => {
        clientSelect.append(`<option value="${userId}">${clients[userId]}</option>`);
    });
    
    // Add event listener for client booking history
    $('#loadClientHistory').off('click').on('click', fetchClientBookingHistory);
}

// Fetch and display client booking history
async function fetchClientBookingHistory() {
    const userId = $('#clientSelect').val();
    
    if (!userId) {
        alert('Please select a client first.');
        return;
    }
    
    try {
        const response = await fetch('/admin/reports/client-booking-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: filters.startDate,
                end_date: filters.endDate,
                user_id: parseInt(userId)
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Client booking history data:', data);
        displayClientBookingHistory(data);
    } catch (error) {
        console.error('Error fetching client booking history:', error);
        $('#clientHistoryTableBody').html('<tr><td colspan="7" class="text-center">Error loading client history</td></tr>');
    }
}

function displayClientBookingHistory(data) {
    const tbody = $('#clientHistoryTableBody');
    tbody.empty();
    
    if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center">No booking history found for this client</td></tr>');
        return;
    }
    
    data.forEach(booking => {
        const row = `
            <tr>
                <td>${booking.booking_id}</td>
                <td>${booking.destination || 'N/A'}</td>
                <td>${formatDate(booking.date_of_tour)}</td>
                <td>${formatCurrency(booking.total_cost || 0)}</td>
                <td><span class="badge bg-${getStatusBadgeClass(booking.status)} badge-status">${booking.status || 'N/A'}</span></td>
                <td><span class="badge bg-${getPaymentStatusBadgeClass(booking.payment_status)} badge-status">${booking.payment_status || 'N/A'}</span></td>
                <td>${formatCurrency(booking.balance || 0)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function getStatusBadgeClass(status) {
    switch (status?.toLowerCase()) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'completed': return 'info';
        case 'canceled': return 'danger';
        case 'rejected': return 'secondary';
        default: return 'dark';
    }
}

function getPaymentStatusBadgeClass(status) {
    switch (status?.toLowerCase()) {
        case 'paid': return 'success';
        case 'partially paid': return 'warning';
        case 'unpaid': return 'danger';
        default: return 'secondary';
    }
}

// Test direct fetch without jQuery
fetch('/admin/reports/booking-summary')
  .then(response => response.text())
  .then(text => {
    console.log('Direct fetch response:', text);
    try {
      const data = JSON.parse(text);
      console.log('Parsed data:', data);
    } catch (e) {
      console.error('Parse error:', e);
    }
  })
  .catch(err => console.error('Fetch error:', err)); 