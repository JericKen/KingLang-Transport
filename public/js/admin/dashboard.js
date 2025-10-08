// Global filters state

window.filters = {

    startDate: null,

    endDate: null

};



// Initialize date pickers

let startDatePicker = null;

let endDatePicker = null;



$(document).ready(function() {

    initializeDatePickers();

    renderSummaryMetrics();

    renderCharts();

    // Initialize advanced analytics charts
    initializeAnalyticsCharts();

    triggerAutoCancellation();

    

    // Event listener for apply filters button

    $('#applyFilters').on('click', applyFilters);

    

    // Event listener for quick filter buttons

    $('.quick-filter').on('click', function() {

        const range = $(this).data('range');

        applyQuickFilter(range);

    });

    

    // Event listener for reset button

    $('#resetFilters').on('click', resetFilters);

});



// Function to reset filters to default (This Year)

function resetFilters() {

    // Reset to default date range (This Year)

    applyQuickFilter('this-year');

}



function initializeDatePickers() {

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

    

    window.filters.startDate = formatDate(firstDayOfYear);

    window.filters.endDate = formatDate(today);

    

    // Highlight the "This Year" button by default

    $('.quick-filter[data-range="this-year"]').addClass('active').removeClass('btn-outline-success');

}



// Helper function to format date

function formatDate(date) {

    const year = date.getFullYear();

    const month = String(date.getMonth() + 1).padStart(2, '0');

    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;

}



function applyFilters() {

    window.filters.startDate = startDatePicker.input.value;

    window.filters.endDate = endDatePicker.input.value;

    

    // Clear active state from all quick filter buttons when manually applying filters

    $('.quick-filter').removeClass('active').addClass('btn-outline-success');

    

    renderSummaryMetrics();

    renderCharts();

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

    window.filters.startDate = formatDate(startDate);

    window.filters.endDate = formatDate(endDate);

    

    // Apply the filters

    renderSummaryMetrics();

    renderCharts();

    // Refresh advanced analytics charts
    if (typeof refreshAnalyticsCharts === 'function') {
        refreshAnalyticsCharts();
    }

}



// Global variable to hold chart instances

let monthlyTrendsChartInstance = null;

let paymentMethodChartInstance = null;  

let destinationsChartInstance = null;

let bookingStatusChartInstance = null;

let revenueTrendsChartInstance = null;

let unpaidBookingsChartInstance = null;

let peakBookingPeriodsChartInstance = null;

let totalIncomeChartInstance = null;

let outstandingBalancesChartInstance = null;

let topPayingClientsChartInstance = null;

let discountsGivenChartInstance = null;



async function renderCharts() {

    try {

        await renderPaymentMethodChart();

        await renderMonthlyTrendsChart();

        await renderTopDestinationsChart();

        await renderBookingStatusChart();

        await renderRevenueTrendsChart();

        await renderUnpaidBookingsChart();

        await renderPeakBookingPeriodsChart();

        await renderTotalIncomeChart();

        await renderOutstandingBalancesChart();

        await renderTopPayingClientsChart();

        await renderDiscountsGivenChart();

    } catch (error) {

        console.error("Error rendering charts:", error);

    }

}



// Check for bookings that need urgent review

async function checkUrgentReviewBookings() {

    try {

        const response = await $.ajax({

            url: "/admin/urgent-review-bookings",

            type: "GET",

            dataType: "json"    

        }); 



        if (response.success && response.count > 0) {

            showUrgentReviewAlert(response.bookings);

        }

    } catch (error) {

        console.error("Error checking urgent review bookings:", error);

    }

}



// Display an alert for urgent booking reviews

function showUrgentReviewAlert(bookings) {

    // Create the alert container

    const alertContainer = $('<div class="alert alert-warning alert-dismissible fade show" role="alert">');

    

    // Create the alert content

    const alertTitle = $('<h4 class="alert-heading">').text('Urgent Booking Reviews Needed!');

    const alertText = $('<p>').text(`You have ${bookings.length} booking request(s) that need urgent review. These bookings will be automatically cancelled if not reviewed by their tour date.`);

    

    // Create the booking list

    const bookingList = $('<ul class="list-group mt-3 mb-3">');

    

    bookings.forEach(booking => {

        const daysText = booking.days_remaining == 0 ? 

            'TODAY' : 

            (booking.days_remaining == 1 ? 'TOMORROW' : `in ${booking.days_remaining} days`);

            

        const urgencyClass = booking.days_remaining == 0 ? 

            'list-group-item-danger' : 

            (booking.days_remaining == 1 ? 'list-group-item-warning' : 'list-group-item-info');

            

        const bookingItem = $(`

            <li class="list-group-item ${urgencyClass} d-flex justify-content-between align-items-center">

                <div>

                    <strong>ID #${booking.booking_id}:</strong> ${booking.client_name} - ${booking.destination}

                    <br><small>Tour date: ${booking.formatted_date} (${daysText})</small>

                </div>

                <a href="print-invoice/${booking.booking_id}" target="blank" class="btn btn-sm btn-primary">Review</a>

            </li>

        `);

        

        bookingList.append(bookingItem);

    });

    

    // Check if there are any bookings with days_remaining <= 0 (overdue)

    const hasOverdueBookings = bookings.some(booking => booking.days_remaining <= 0);

    

    // If there are overdue bookings, add an auto-cancel button

    let autoCancelButton = '';

    if (hasOverdueBookings) {

        autoCancelButton = $(`

            <div class="d-flex justify-content-end mt-3">

                <button id="autoCancelOverdueBtn" class="btn btn-danger">

                    <i class="bi bi-x-circle"></i> Auto-Cancel Overdue Bookings

                </button>

            </div>

        `);

    }

    

    // Create the dismiss button

    const dismissButton = $('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">');

    

    // Assemble the alert

    alertContainer.append(alertTitle, alertText, bookingList);

    if (hasOverdueBookings) {

        alertContainer.append(autoCancelButton);

    }

    alertContainer.append(dismissButton);

    

    // Add the alert to the page

    alertContainer.insertAfter('hr:first');

    

    // Add event listener for auto-cancel button

    $('#autoCancelOverdueBtn').on('click', function() {

        if (confirm('Are you sure you want to automatically cancel all overdue booking requests? This action cannot be undone.')) {

            triggerAutoCancellation();

        }

    });

}



// Function to trigger auto-cancellation

async function triggerAutoCancellation() {

    try {

        // Show loading state

        $('#autoCancelOverdueBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        

        const response = await $.ajax({

            url: "/admin/manual-auto-cancellation",

            type: "GET",

            dataType: "json"

        });

        

        if (response.success && response.cancelled_bookings && response.cancelled_bookings.length > 0) {

            // Create success alert only if something was actually cancelled

            const successAlert = $(`

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <h4 class="alert-heading">Auto-Cancellation Complete</h4>

                    <p>${response.message}</p>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                </div>

            `);

            

            // If there were cancelled bookings, show details

            const cancelledList = $('<ul class="list-group mt-3 mb-3">');

            response.cancelled_bookings.forEach(booking => {

                const formattedDate = new Date(booking.date_of_tour).toLocaleDateString('en-US', {

                    year: 'numeric', 

                    month: 'long', 

                    day: 'numeric'

                });

                const bookingItem = $(`

                    <li class="list-group-item list-group-item-danger">

                        <strong>ID #${booking.booking_id}:</strong> ${booking.client_name} - ${booking.destination}

                        <br><small>Tour date: ${formattedDate}</small>

                    </li>

                `);

                cancelledList.append(bookingItem);

            });

            successAlert.append(cancelledList);

            // Add to page and remove original alert if present

            successAlert.insertAfter('hr:first');

            $('.alert-warning').alert('close');

            

            // Refresh metrics after a short delay

            setTimeout(() => {

                renderSummaryMetrics();

            }, 1000);

        } else {

            // No overdue bookings cancelled; ensure no success banner is shown

            // Optionally close any stale warning alert

            $('.alert-warning').alert('close');

        }

    } catch (error) {

        console.error("Error triggering auto-cancellation:", error);

        // Avoid noisy alerts when nothing to cancel; only show if explicit failure

        // alert('An error occurred while processing auto-cancellations. Please try again.');

        $('#autoCancelOverdueBtn').prop('disabled', false).html('<i class="bi bi-x-circle"></i> Auto-Cancel Overdue Bookings');

    }

}



// Helper function to display error message in chart container

function displayChartError(containerId, message) {

    const container = $(`#${containerId}`).parent();

    container.html(`

        <div class="text-center py-5">

            <div class="text-danger mb-2"><i class="bi bi-exclamation-triangle fs-3"></i></div>

            <p class="text-muted">${message}</p>

            <button class="btn btn-sm btn-outline-secondary mt-2" onclick="location.reload()">

                <i class="bi bi-arrow-clockwise"></i> Reload

            </button>

        </div>

    `);

}



async function getSummaryMetrics() {

    try {

        const response = await $.ajax({

            url: "/admin/summary-metrics",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });

    

        return response;

    } catch (error) {

        console.error("Error fetching data: ", error);

    }

}



async function renderSummaryMetrics() {

    try {

        const summaryMetrics = await getSummaryMetrics();

        

        if (!summaryMetrics) {

            console.error("Invalid summary metrics received:", summaryMetrics);

            return;

        }



        console.log(summaryMetrics);



        $("#totalBookings").text(summaryMetrics.total_bookings || 0);

        $("#totalRevenue").text(parseFloat(summaryMetrics.total_revenue || 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }));

        $("#upcomingTrips").text(summaryMetrics.upcoming_trips || 0);

        $("#pendingBookings").text(summaryMetrics.pending_bookings || 0);

        $("#flaggedBookings").text(summaryMetrics.flagged_bookings || 0); 

    } catch (error) {

        console.error("Error rendering summary metrics:", error);

        

        // Set default values in case of error

        $("#totalBookings").text("0");

        $("#totalRevenue").text("0");

        $("#upcomingTrips").text("0");

        $("#pendingBookings").text("0");

        $("#flaggedBookings").text("0");

    }

}



// Payment Method Chart

async function getPaymentMethodData() {

    try {

        const response = await $.ajax({

            url: "/admin/payment-method-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Payment Method Data:", response);

        

        // Check if the response contains an error message

        if (response && response.error) {

            console.error("Error from server:", response.error);

            return null;

        }

        

        return response;

    } catch (error) {

        console.error("Error fetching data: ", error);

        return null;

    }

}



async function renderPaymentMethodChart() {

    try {

        const paymentMethodData = await getPaymentMethodData();

        

        console.log("Original Payment Method Data:", paymentMethodData);

        

        if (!paymentMethodData || !paymentMethodData.labels || !paymentMethodData.counts) {

            console.error("Invalid payment method data received:", paymentMethodData);

            displayChartError("paymentMethodChart", "Unable to load payment method data. Please try again later.");

            return;

        }

        

        // If there's no data, show a message

        // if (paymentMethodData.labels.length === 0 || paymentMethodData.counts.every(count => count === 0)) {

        //     displayChartError("paymentMethodChart", "No payment data available yet.");

        //     return;

        // }



        const ctx = $("#paymentMethodChart")[0].getContext("2d");



        if (paymentMethodChartInstance) {

            paymentMethodChartInstance.destroy();

        }



        paymentMethodChartInstance = new Chart(ctx, {

            type: "doughnut",

            data: {

                labels: paymentMethodData.labels,

                datasets: [{

                    label: "Payment Methods",

                    data: paymentMethodData.counts,

                    backgroundColor: [

                        'rgb(255, 99, 132)',

                        'rgb(54, 162, 235)',

                        'rgb(255, 205, 86)',

                        'rgb(75, 192, 192)'

                    ],

                    hoverOffset: 4

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        position: 'bottom',

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const value = context.raw;

                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);

                                const percentage = ((value / total) * 100).toFixed(1);

                                const amount = paymentMethodData.amounts[context.dataIndex];

                                return `${context.label}: ${value} (${percentage}%) - ₱${amount.toLocaleString()}`;

                            }

                        }

                    }

                }

            }

        });

    } catch (error) {

        console.error("Error rendering payment method chart:", error);

        displayChartError("paymentMethodChart", "Error rendering chart. Please try again.");

    }

}



// Monthly Booking Trends Chart

async function getMonthlyTrendsData() {

    try {

        const response = await $.ajax({

            url: "/admin/monthly-booking-trends",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Monthly Trends Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching monthly trends data: ", error);

        return null;

    }

}



async function renderMonthlyTrendsChart() {

    try {

        const trendsData = await getMonthlyTrendsData();

        

        console.log("Monthly Trends Data:", trendsData);

        

        if (!trendsData || !trendsData.labels || !trendsData.counts) {

            console.error("Invalid monthly trends data received:", trendsData);

            displayChartError("monthlyTrendsChart", "Unable to load monthly trend data. Please try again later.");

            return;

        }

        

        const ctx = $("#monthlyTrendsChart")[0].getContext("2d");



        if (monthlyTrendsChartInstance) {

            monthlyTrendsChartInstance.destroy();

        }



        monthlyTrendsChartInstance = new Chart(ctx, {

            type: "line",

            data: {

                labels: trendsData.labels,

                datasets: [

                    {

                        label: "Bookings",

                        data: trendsData.counts,

                        fill: true,

                        borderColor: 'rgb(75, 192, 192)',

                        backgroundColor: 'rgba(75, 192, 192, 0.2)',

                        tension: 0.4,

                        yAxisID: 'y'

                    }

                    // {

                    //     label: "Revenue",

                    //     data: trendsData.revenues,

                    //     fill: false,

                    //     borderColor: 'rgb(255, 99, 132)',

                    //     tension: 0.4,

                    //     yAxisID: 'y1'

                    // }

                ]

            },

            options: {

                responsive: true,

                interaction: {

                    mode: 'index',

                    intersect: false,

                },

                plugins: {

                    legend: {

                        position: 'top',

                    },

                    title: {

                        display: true,

                        text: `Booking Trends`,

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const label = context.dataset.label;

                                const value = context.raw;

                                if (label === "Revenue") {

                                    return `${label}: ₱${value.toLocaleString()}`;

                                }

                                return `${label}: ${value}`;

                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        type: 'linear',

                        display: true,

                        position: 'left',

                        title: {

                            display: true,

                            text: 'Number of Bookings'

                        },

                        ticks: {

                            precision: 0

                        }

                    },

                    // y1: {

                    //     beginAtZero: true,

                    //     type: 'linear',

                    //     display: true,

                    //     position: 'right',

                    //     title: {

                    //         display: false,

                    //         text: 'Revenue (₱)'

                    //     },

                    //     grid: {

                    //         drawOnChartArea: false

                    //     },

                    //     ticks: {

                    //         callback: function(value) {

                    //             return '₱' + value.toLocaleString();

                    //         }

                    //     }

                    // }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering monthly trends chart:", error);

        displayChartError("monthlyTrendsChart", "Error rendering chart. Please try again.");

    }

}





// Top Destinations Chart

async function getTopDestinationsData() {

    try {

        const response = await $.ajax({

            url: "/admin/top-destinations",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Top Destinations Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching top destinations data: ", error);

        return null;

    }

}



async function renderTopDestinationsChart() {

    try {

        const destinationsData = await getTopDestinationsData();

        

        // console.log("Top Destinations Data:", destinationsData);

        

        if (!destinationsData || !destinationsData.labels || !destinationsData.counts) {

            console.error("Invalid destination data received:", destinationsData);

            displayChartError("destinationsChart", "Unable to load destination data. Please try again later.");

            return;

        }

        

        // Check if there's real data

        // if (destinationsData.labels.length === 0 || destinationsData.labels[0] === 'No Data Available') {

        //     displayChartError("destinationsChart", "No destination datas available yet.");

        //     return;

        // }



        const ctx = $("#destinationsChart")[0].getContext("2d");



        if (destinationsChartInstance) {

            destinationsChartInstance.destroy();

        }



        destinationsChartInstance = new Chart(ctx, {

            type: "polarArea",

            data: { 

                labels: destinationsData.labels,

                datasets: [{

                    data: destinationsData.counts,

                    backgroundColor: [

                        'rgba(255, 99, 132, 0.7)',

                        'rgba(54, 162, 235, 0.7)',

                        'rgba(255, 206, 86, 0.7)',

                        'rgba(75, 192, 192, 0.7)',

                        'rgba(153, 102, 255, 0.7)'

                    ],

                    borderColor: [

                        'rgb(255, 99, 132)',

                        'rgb(54, 162, 235)',

                        'rgb(255, 206, 86)',

                        'rgb(75, 192, 192)',

                        'rgb(153, 102, 255)'

                    ],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                animation: {

                    animateRotate: true,

                    animateScale: true,

                    duration: 2000

                },

                scales: {

                    r: {

                        ticks: {

                            display: false

                        },

                        grid: {

                            color: 'rgba(0, 0, 0, 0.1)'

                        },

                        angleLines: {

                            color: 'rgba(0, 0, 0, 0.1)'

                        },

                        beginAtZero: true

                    }

                },

                plugins: {

                    legend: {

                        position: 'right',

                        align: 'center',

                        labels: {

                            boxWidth: 15,

                            padding: 15

                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const index = context.dataIndex;

                                const count = destinationsData.counts[index];

                                const revenue = destinationsData.revenues[index];

                                const totalBookings = destinationsData.counts.reduce((a, b) => a + b, 0);

                                const percentage = Math.round((count / totalBookings) * 100);

                                

                                return [

                                    `Bookings: ${count} (${percentage}%)`,

                                    `Revenue: ₱${revenue.toLocaleString()}`

                                ];

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Popular Destinations',

                        font: {

                            size: 16,

                            weight: 'bold'

                        },

                        padding: {

                            top: 10,

                            bottom: 20

                        }

                    }

                }

            }

        });

    } catch (error) {

        console.error("Error rendering top destinations chart:", error);

        displayChartError("destinationsChart", "Error rendering chart. Please try again.");

    }

}



// Booking Status Distribution Chart

async function getBookingStatusData() {

    try {

        const response = await $.ajax({

            url: "/admin/booking-status-distribution",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Booking Status Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching booking status data: ", error);

        return null;

    }

}



async function renderBookingStatusChart() {

    try {

        const statusData = await getBookingStatusData();

        

        console.log("Booking Status Data:", statusData);

        

        if (!statusData || !statusData.labels || !statusData.counts) {

            console.error("Invalid booking status data received:", statusData);

            displayChartError("bookingStatusChart", "Unable to load booking status data. Please try again later.");

            return;

        }

        

        // if (statusData.labels.length === 0) {

        //     displayChartError("bookingStatusChart", "No booking status data available yet.");

        //     return;

        // }

        

        const ctx = $("#bookingStatusChart")[0].getContext("2d");



        if (bookingStatusChartInstance) {

            bookingStatusChartInstance.destroy();

        }



        bookingStatusChartInstance = new Chart(ctx, {

            type: "pie",

            data: {

                labels: statusData.labels,

                datasets: [{

                    data: statusData.counts,

                    backgroundColor: [

                        'rgba(54, 162, 235, 0.7)', // Confirmed

                        'rgba(255, 206, 86, 0.7)', // Pending

                        'rgba(75, 192, 192, 0.7)', // Completed

                        'rgba(255, 99, 132, 0.7)', // Canceled

                        'rgba(153, 102, 255, 0.7)' // Rejected

                    ],

                    borderColor: [

                        'rgb(54, 162, 235)', // Confirmed

                        'rgb(255, 206, 86)', // Pending

                        'rgb(75, 192, 192)', // Completed

                        'rgb(255, 99, 132)', // Canceled

                        'rgb(153, 102, 255)' // Rejected

                    ],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        position: 'bottom'

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const label = context.label;

                                const value = context.raw;

                                const totalBookings = statusData.counts.reduce((a, b) => a + b, 0);

                                const percentage = ((value / totalBookings) * 100).toFixed(1);

                                const totalValue = statusData.values[context.dataIndex];

                                

                                return [

                                    `${label}: ${value} (${percentage}%)`,

                                    `Total Value: ₱${totalValue.toLocaleString()}`

                                ];

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Booking Status Distribution',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                }

            }

        });

    } catch (error) {

        console.error("Error rendering booking status chart:", error);

        displayChartError("bookingStatusChart", "Error rendering chart. Please try again.");

    }

}



// Revenue Trends Chart

async function getRevenueTrendsData() {

    try {

        const response = await $.ajax({

            url: "/admin/revenue-trends",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Revenue Trends Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching revenue trends data: ", error);

        return null;

    }

}



async function renderRevenueTrendsChart() {

    try {

        const revenueData = await getRevenueTrendsData();

        

        console.log("Revenue Trends Data:", revenueData);

        

        if (!revenueData || !revenueData.labels || !revenueData.revenues) {

            console.error("Invalid revenue trends data received:", revenueData);

            displayChartError("revenueTrendsChart", "Unable to load revenue trend data. Please try again later.");

            return;

        }

        

        // if (revenueData.labels.length === 0) {

        //     displayChartError("revenueTrendsChart", "No revenue data available yet.");

        //     return;

        // }

        

        const ctx = $("#revenueTrendsChart")[0].getContext("2d");



        if (revenueTrendsChartInstance) {

            revenueTrendsChartInstance.destroy();

        }



        revenueTrendsChartInstance = new Chart(ctx, {

            type: "bar",

            data: {

                labels: revenueData.labels,

                datasets: [

                    {

                        type: 'bar',

                        label: "Revenue",

                        data: revenueData.revenues,

                        backgroundColor: 'rgba(255, 99, 132, 0.7)',

                        borderColor: 'rgb(255, 99, 132)',

                        borderWidth: 1,

                        yAxisID: 'y'

                    }

                    // {

                    //     type: 'line',

                    //     label: "Bookings",

                    //     data: revenueData.counts,

                    //     fill: false,

                    //     borderColor: 'rgb(255, 99, 132)',

                    //     tension: 0.4,

                    //     borderWidth: 2,

                    //     pointStyle: 'circle',

                    //     pointRadius: 5,

                    //     pointHoverRadius: 8,

                    //     yAxisID: 'y1'

                    // }

                ]

            },

            options: {

                responsive: true,

                interaction: {

                    mode: 'index',

                    intersect: false,

                },

                plugins: {

                    legend: {

                        position: 'top',

                    },

                    title: {

                        display: true,

                        text: 'Revenue Trends',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const label = context.dataset.label;

                                const value = context.raw;

                                if (label === "Revenue") {

                                    return `${label}: ₱${value.toLocaleString()}`;

                                }

                                return `${label}: ${value}`;

                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        type: 'linear',

                        display: true,

                        position: 'left',

                        title: {

                            display: true,

                            text: 'Revenue (₱)'

                        },

                        ticks: {

                            callback: function(value) {

                                return '₱' + value.toLocaleString();

                            }

                        }

                    },

                    y1: {

                        beginAtZero: true,

                        type: 'linear',

                        display: true,

                        position: 'right',

                        title: {

                            display: false,

                            text: 'Number of Bookings'

                        },

                        grid: {

                            drawOnChartArea: false

                        },

                        ticks: {

                            precision: 0

                        }

                    }

                }

            }

        });

    } catch (error) {

        console.error("Error rendering revenue trends chart:", error);

        displayChartError("revenueTrendsChart", "Error rendering chart. Please try again.");

    }

}



// Unpaid/Partially Paid Bookings Chart

async function getUnpaidBookingsData() {

    try {

        const response = await $.ajax({

            url: "/admin/unpaid-bookings-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Unpaid Bookings Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching unpaid bookings data: ", error);

        return null;

    }

}



async function renderUnpaidBookingsChart() {

    try {

        const unpaidData = await getUnpaidBookingsData();

        

        console.log("Unpaid Bookings Data:", unpaidData);

        

        if (!unpaidData || !unpaidData.labels || !unpaidData.counts) {

            console.error("Invalid unpaid bookings data received:", unpaidData);

            displayChartError("unpaidBookingsChart", "Unable to load unpaid bookings data. Please try again later.");

            return;

        }

        

        const ctx = $("#unpaidBookingsChart")[0].getContext("2d");



        if (unpaidBookingsChartInstance) {

            unpaidBookingsChartInstance.destroy();

        }



        unpaidBookingsChartInstance = new Chart(ctx, {

            type: "doughnut",

            data: {

                labels: unpaidData.labels,

                datasets: [{

                    data: unpaidData.counts,

                    backgroundColor: [

                        'rgba(255, 99, 132, 0.7)', // Unpaid

                        'rgba(255, 206, 86, 0.7)',  // Partially Paid

                        'rgba(75, 192, 192, 0.7)'   // Paid

                    ],

                    borderColor: [

                        'rgb(255, 99, 132)', // Unpaid

                        'rgb(255, 206, 86)',  // Partially Paid

                        'rgb(75, 192, 192)'   // Paid

                    ],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        position: 'bottom'

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const label = context.label;

                                const value = context.raw;

                                const totalBookings = unpaidData.counts.reduce((a, b) => a + b, 0);

                                const percentage = ((value / totalBookings) * 100).toFixed(1);

                                const amount = unpaidData.amounts[context.dataIndex];

                                

                                return [

                                    `${label}: ${value} (${percentage}%)`,

                                    `Amount: ₱${amount.toLocaleString()}`

                                ];

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Payment Status Distribution',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering unpaid bookings chart:", error);

        displayChartError("unpaidBookingsChart", "Error rendering chart. Please try again.");

    }

}



// Peak Booking Periods Chart

async function getPeakBookingPeriodsData() {

    try {

        const response = await $.ajax({

            url: "/admin/peak-booking-periods-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Peak Booking Periods Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching peak booking periods data: ", error);

        return null;

    }

}



async function renderPeakBookingPeriodsChart() {

    try {

        const peakData = await getPeakBookingPeriodsData();

        

        console.log("Peak Booking Periods Data:", peakData);

        

        if (!peakData || !peakData.labels || !peakData.counts) {

            console.error("Invalid peak booking periods data received:", peakData);

            displayChartError("peakBookingPeriodsChart", "Unable to load peak booking periods data. Please try again later.");

            return;

        }

        

        const ctx = $("#peakBookingPeriodsChart")[0].getContext("2d");



        if (peakBookingPeriodsChartInstance) {

            peakBookingPeriodsChartInstance.destroy();

        }



        peakBookingPeriodsChartInstance = new Chart(ctx, {

            type: "bar",

            data: {

                labels: peakData.labels,

                datasets: [{

                    label: "Bookings",

                    data: peakData.counts,

                    backgroundColor: 'rgba(25, 135, 84, 0.7)',

                    borderColor: 'rgb(25, 135, 84)',

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const value = context.raw;

                                const totalBookings = peakData.counts.reduce((a, b) => a + b, 0);

                                const percentage = ((value / totalBookings) * 100).toFixed(1);

                                

                                return `Bookings: ${value} (${percentage}%)`;

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Peak Booking Periods',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        title: {

                            display: true,

                            text: 'Number of Bookings'

                        }

                    },

                    x: {

                        title: {

                            display: true,

                            text: 'Period'

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering peak booking periods chart:", error);

        displayChartError("peakBookingPeriodsChart", "Error rendering chart. Please try again.");

    }

}



// Total Income Chart

async function getTotalIncomeData() {

    try {

        const response = await $.ajax({

            url: "/admin/total-income-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Total Income Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching total income data: ", error);

        return null;

    }

}



async function renderTotalIncomeChart() {

    try {

        const incomeData = await getTotalIncomeData();

        

        console.log("Total Income Data:", incomeData);

        

        if (!incomeData || !incomeData.labels || !incomeData.amounts) {

            console.error("Invalid total income data received:", incomeData);

            displayChartError("totalIncomeChart", "Unable to load total income data. Please try again later.");

            return;

        }

        

        const ctx = $("#totalIncomeChart")[0].getContext("2d");



        if (totalIncomeChartInstance) {

            totalIncomeChartInstance.destroy();

        }



        totalIncomeChartInstance = new Chart(ctx, {

            type: "line",

            data: {

                labels: incomeData.labels,

                datasets: [{

                    label: "Total Income",

                    data: incomeData.amounts,

                    borderColor: 'rgb(25, 135, 84)',

                    backgroundColor: 'rgba(25, 135, 84, 0.1)',

                    borderWidth: 3,

                    fill: true,

                    tension: 0.4

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return `Income: ₱${context.raw.toLocaleString()}`;

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Income Trends',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        title: {

                            display: true,

                            text: 'Amount (₱)'

                        },

                        ticks: {

                            callback: function(value) {

                                return '₱' + value.toLocaleString();

                            }

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering total income chart:", error);

        displayChartError("totalIncomeChart", "Error rendering chart. Please try again.");

    }

}



// Outstanding Balances Chart

async function getOutstandingBalancesData() {

    try {

        const response = await $.ajax({

            url: "/admin/outstanding-balances-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Outstanding Balances Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching outstanding balances data: ", error);

        return null;

    }

}



async function renderOutstandingBalancesChart() {

    try {

        const balancesData = await getOutstandingBalancesData();

        

        console.log("Outstanding Balances Data:", balancesData);

        

        if (!balancesData || !balancesData.labels || !balancesData.amounts) {

            console.error("Invalid outstanding balances data received:", balancesData);

            displayChartError("outstandingBalancesChart", "Unable to load outstanding balances data. Please try again later.");

            return;

        }

        

        const ctx = $("#outstandingBalancesChart")[0].getContext("2d");



        if (outstandingBalancesChartInstance) {

            outstandingBalancesChartInstance.destroy();

        }



        outstandingBalancesChartInstance = new Chart(ctx, {

            type: "bar",

            data: {

                labels: balancesData.labels,

                datasets: [{

                    label: "Outstanding Amount",

                    data: balancesData.amounts,

                    backgroundColor: 'rgba(255, 193, 7, 0.7)',

                    borderColor: 'rgb(255, 193, 7)',

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return `Outstanding: ₱${context.raw.toLocaleString()}`;

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Outstanding Balances',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        title: {

                            display: true,

                            text: 'Amount (₱)'

                        },

                        ticks: {

                            callback: function(value) {

                                return '₱' + value.toLocaleString();

                            }

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering outstanding balances chart:", error);

        displayChartError("outstandingBalancesChart", "Error rendering chart. Please try again.");

    }

}



// Top-Paying Clients Chart

async function getTopPayingClientsData() {

    try {

        const response = await $.ajax({

            url: "/admin/top-paying-clients-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Top-Paying Clients Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching top-paying clients data: ", error);

        return null;

    }

}



async function renderTopPayingClientsChart() {

    try {

        const clientsData = await getTopPayingClientsData();

        

        console.log("Top-Paying Clients Data:", clientsData);

        

        if (!clientsData || !clientsData.labels || !clientsData.amounts) {

            console.error("Invalid top-paying clients data received:", clientsData);

            displayChartError("topPayingClientsChart", "Unable to load top-paying clients data. Please try again later.");

            return;

        }

        

        const ctx = $("#topPayingClientsChart")[0].getContext("2d");



        if (topPayingClientsChartInstance) {

            topPayingClientsChartInstance.destroy();

        }



        topPayingClientsChartInstance = new Chart(ctx, {

            type: "bar",

            data: {

                labels: clientsData.labels,

                datasets: [{

                    label: "Total Paid",

                    data: clientsData.amounts,

                    backgroundColor: 'rgba(13, 110, 253, 0.7)',

                    borderColor: 'rgb(13, 110, 253)',

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                indexAxis: 'y',

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return `Total Paid: ₱${context.raw.toLocaleString()}`;

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Top-Paying Clients',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                },

                scales: {

                    x: {

                        beginAtZero: true,

                        title: {

                            display: true,

                            text: 'Amount (₱)'

                        },

                        ticks: {

                            callback: function(value) {

                                return '₱' + value.toLocaleString();

                            }

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering top-paying clients chart:", error);

        displayChartError("topPayingClientsChart", "Error rendering chart. Please try again.");

    }

}



// Discounts Given Chart

async function getDiscountsGivenData() {

    try {

        const response = await $.ajax({

            url: "/admin/discounts-given-data",

            type: "POST",

            dataType: "json",

            contentType: "application/json",

            data: JSON.stringify({

                start_date: window.filters.startDate,

                end_date: window.filters.endDate

            })

        });



        console.log("Discounts Given Data:", response);

        return response;

    } catch (error) {

        console.error("Error fetching discounts given data: ", error);

        return null;

    }

}



async function renderDiscountsGivenChart() {

    try {

        const discountsData = await getDiscountsGivenData();

        

        console.log("Discounts Given Data:", discountsData);

        

        if (!discountsData || !discountsData.labels || !discountsData.amounts) {

            console.error("Invalid discounts given data received:", discountsData);

            displayChartError("discountsGivenChart", "Unable to load discounts given data. Please try again later.");

            return;

        }

        

        const ctx = $("#discountsGivenChart")[0].getContext("2d");



        if (discountsGivenChartInstance) {

            discountsGivenChartInstance.destroy();

        }



        discountsGivenChartInstance = new Chart(ctx, {

            type: "doughnut",

            data: {

                labels: discountsData.labels,

                datasets: [{

                    data: discountsData.amounts,

                    backgroundColor: [

                        'rgba(25, 135, 84, 0.7)',   // Total Revenue
                        'rgba(13, 202, 240, 0.7)'    // Total Discount Amount
                    ],

                    borderColor: [

                        'rgb(25, 135, 84)',
                        'rgb(13, 202, 240)'
                    ],

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {

                        position: 'bottom'

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const label = context.label;

                                const value = context.raw;

                                return `${label}: ₱${Number(value).toLocaleString()}`;

                            }

                        }

                    },

                    title: {

                        display: true,

                        text: 'Revenue vs Discounts',

                        font: {

                            size: 16,

                            weight: 'bold'

                        }

                    }

                }

            }

        });



    } catch (error) {

        console.error("Error rendering discounts given chart:", error);

        displayChartError("discountsGivenChart", "Error rendering chart. Please try again.");

    }

}