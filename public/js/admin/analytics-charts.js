// ==========================================
// ADVANCED ANALYTICS CHARTS
// ==========================================

// Global variables for chart instances
let analyticsCharts = {
    dailyRevenueChart: null,
    mostUsedBusesChart: null,
    busiestDaysChart: null,
    maintenanceAlertsChart: null,
    feedbackAnalysisChart: null,
    bookingForecastChart: null,
    customerBehaviorChart: null,
    marketingAnalyticsChart: null
};

// Initialize all analytics charts
async function initializeAnalyticsCharts() {
    console.log('Initializing analytics charts...');
    try {
        
        console.log('Loading busiest days chart...');
        await loadBusiestDaysChart();
        
        console.log('Loading maintenance alerts chart...');
        await loadMaintenanceAlertsChart();
        
        console.log('Loading feedback analysis chart...');
        await loadFeedbackAnalysisChart();
        
        console.log('Loading booking forecast chart...');
        await loadBookingForecastChart();
        
        console.log('Loading customer behavior chart...');
        await loadCustomerBehaviorChart();
        
        console.log('All analytics charts initialized successfully');
    } catch (error) {
        console.error('Error initializing analytics charts:', error);
    }
}

// Busiest Days Chart
async function loadBusiestDaysChart() {
    try {
        const response = await fetch('/routes/analytics.php?action=busiest-days', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: window.filters ? window.filters.startDate : '2024-01-01',
                end_date: window.filters ? window.filters.endDate : new Date().toISOString().split('T')[0]
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const ctx = document.getElementById('busiestDaysChart');
            if (ctx) {
                if (analyticsCharts.busiestDaysChart) {
                    analyticsCharts.busiestDaysChart.destroy();
                }
                
                const labels = result.data.map(item => item.day_of_week);
                const bookings = result.data.map(item => item.booking_count);
                
                analyticsCharts.busiestDaysChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Bookings',
                                data: bookings,
                                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                                borderColor: 'rgb(255, 206, 86)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Busiest Booking Days'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error loading busiest days chart:', error);
    }
}

// Maintenance Alerts Chart
async function loadMaintenanceAlertsChart() {
    try {
        const response = await fetch('/routes/analytics.php?action=maintenance-alerts');
        const result = await response.json();
        
        if (result.success && result.data) {
            const ctx = document.getElementById('maintenanceAlertsChart');
            if (ctx) {
                if (analyticsCharts.maintenanceAlertsChart) {
                    analyticsCharts.maintenanceAlertsChart.destroy();
                }
                
                const urgentBuses = result.data.filter(item => item.status === 'URGENT');
                const warningBuses = result.data.filter(item => item.status === 'WARNING');
                
                analyticsCharts.maintenanceAlertsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Urgent', 'Warning', 'OK'],
                        datasets: [
                            {
                                data: [urgentBuses.length, warningBuses.length, 0],
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.7)',
                                    'rgba(255, 206, 86, 0.7)',
                                    'rgba(75, 192, 192, 0.7)'
                                ],
                                borderColor: [
                                    'rgb(255, 99, 132)',
                                    'rgb(255, 206, 86)',
                                    'rgb(75, 192, 192)'
                                ],
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Maintenance Alerts'
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error loading maintenance alerts chart:', error);
    }
}

// Feedback Analysis Chart
async function loadFeedbackAnalysisChart() {
    try {
        const response = await fetch('/routes/analytics.php?action=feedback-analysis', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: window.filters ? window.filters.startDate : '2024-01-01',
                end_date: window.filters ? window.filters.endDate : new Date().toISOString().split('T')[0]
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const ctx = document.getElementById('feedbackAnalysisChart');
            if (ctx) {
                if (analyticsCharts.feedbackAnalysisChart) {
                    analyticsCharts.feedbackAnalysisChart.destroy();
                }
                
                const data = result.data;
                
                analyticsCharts.feedbackAnalysisChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Average Rating', 'Total Feedback', 'Low Ratings'],
                        datasets: [
                            {
                                label: 'Feedback Metrics',
                                data: [data.avg_rating, data.total_feedback, data.low_ratings],
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.7)',
                                    'rgba(54, 162, 235, 0.7)',
                                    'rgba(255, 99, 132, 0.7)'
                                ],
                                borderColor: [
                                    'rgb(75, 192, 192)',
                                    'rgb(54, 162, 235)',
                                    'rgb(255, 99, 132)'
                                ],
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Customer Feedback Analysis'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error loading feedback analysis chart:', error);
    }
}

// Booking Forecast Chart (using ApexCharts) - Improved Version
async function loadBookingForecastChart() {
    try {
        const response = await fetch('/routes/analytics.php?action=booking-forecast');
        const result = await response.json();
        
        if (result.success && result.data) {
            const forecastContainer = document.getElementById('bookingForecastChart');
            if (forecastContainer) {
                // Destroy existing chart if it exists
                if (analyticsCharts.bookingForecastChart) {
                    analyticsCharts.bookingForecastChart.destroy();
                }
                
                const historical = result.data.historical || [];
                const forecast = result.data.forecast || [];
                
                // Combine historical and forecast data for a continuous line
                const allData = [];
                
                // Add historical data
                historical.forEach(item => {
                    allData.push({
                        x: new Date(item.date).getTime(),
                        y: item.bookings,
                        type: 'historical'
                    });
                });
                
                // Add forecast data
                forecast.forEach(item => {
                    allData.push({
                        x: new Date(item.forecast_date).getTime(),
                        y: item.predicted_bookings,
                        type: 'forecast',
                        upper: item.upper_bound,
                        lower: item.lower_bound
                    });
                });
                
                // Sort by date
                allData.sort((a, b) => a.x - b.x);
                
                // Create confidence interval data for forecast area
                const confidenceData = forecast.map(item => [
                    new Date(item.forecast_date).getTime(),
                    item.lower_bound,
                    item.upper_bound
                ]);
                
                const options = {
                    series: [
                        {
                            name: 'Bookings',
                            type: 'line',
                            data: allData.map(item => [item.x, item.y])
                        }
                    ],
                    chart: {
                        type: 'line',
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    title: {
                        text: '30-Day Booking Forecast',
                        align: 'left',
                        style: {
                            fontSize: '18px',
                            fontWeight: 'bold',
                            color: '#198754'
                        }
                    },
                    subtitle: {
                        text: 'Historical data and 7-day forecast with confidence intervals',
                        align: 'left',
                        style: {
                            fontSize: '12px',
                            color: '#6c757d'
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#198754'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#28a745'],
                            inverseColors: false,
                            opacityFrom: 0.8,
                            opacityTo: 0.1,
                            stops: [0, 100]
                        }
                    },
                    markers: {
                        size: 6,
                        colors: ['#198754'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 8
                        }
                    },
                    xaxis: {
                        type: 'datetime',
                        title: {
                            text: 'Date',
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold'
                            }
                        },
                        labels: {
                            format: 'MMM dd',
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Bookings',
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        min: 0
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        x: {
                            format: 'MMM dd, yyyy'
                        },
                        y: {
                            formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                                const dataPoint = allData[dataPointIndex];
                                if (dataPoint && dataPoint.type === 'forecast') {
                                    return `${value} bookings (forecast)<br/>
                                            Range: ${dataPoint.lower} - ${dataPoint.upper}`;
                                }
                                return `${value} bookings (historical)`;
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '12px'
                    },
                    grid: {
                        borderColor: '#e9ecef',
                        strokeDashArray: 3,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    },
                    annotations: {
                        xaxis: [
                            {
                                x: new Date().getTime(),
                                borderColor: '#dc3545',
                                strokeDashArray: 5,
                                label: {
                                    text: 'Today',
                                    style: {
                                        color: '#dc3545',
                                        fontSize: '12px',
                                        fontWeight: 'bold'
                                    }
                                }
                            }
                        ]
                    },
                    dataLabels: {
                        enabled: false
                    },
                    responsive: [
                        {
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 300
                                },
                                title: {
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            }
                        }
                    ]
                };
                
                analyticsCharts.bookingForecastChart = new ApexCharts(forecastContainer, options);
                analyticsCharts.bookingForecastChart.render();
                
                // Update summary cards
                updateForecastSummary(result.data);
            }
        } else {
            // Show message if no data
            const forecastContainer = document.getElementById('bookingForecastChart');
            if (forecastContainer) {
                forecastContainer.innerHTML = `
                    <div class="text-center p-4">
                        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-2">No Forecast Data Available</h5>
                        <p class="text-muted">Insufficient historical data to generate forecast</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading booking forecast chart:', error);
        const forecastContainer = document.getElementById('bookingForecastChart');
        if (forecastContainer) {
            forecastContainer.innerHTML = `
                <div class="text-center p-4">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    <h5 class="text-warning mt-2">Error Loading Forecast</h5>
                    <p class="text-muted">Unable to load booking forecast data</p>
                </div>
            `;
        }
    }
}

// Customer Behavior Chart
async function loadCustomerBehaviorChart() {
    try {
        const response = await fetch('/routes/analytics.php?action=customer-behavior', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: window.filters ? window.filters.startDate : '2024-01-01',
                end_date: window.filters ? window.filters.endDate : new Date().toISOString().split('T')[0]
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            // Peak Hours Chart
            const peakHoursCtx = document.getElementById('peakHoursChart');
            if (peakHoursCtx) {
                if (analyticsCharts.peakHoursChart) {
                    analyticsCharts.peakHoursChart.destroy();
                }
                
                const labels = result.data.peak_hours.map(item => `${item.hour}:00`);
                const counts = result.data.peak_hours.map(item => item.count);
                
                analyticsCharts.peakHoursChart = new Chart(peakHoursCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Bookings',
                                data: counts,
                                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                                borderColor: 'rgb(153, 102, 255)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Peak Booking Hours'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
        }
    } catch (error) {
        console.error('Error loading customer behavior chart:', error);
    }
}

// Refresh all analytics charts
async function refreshAnalyticsCharts() {
    await initializeAnalyticsCharts();
}

// Export functions for use in dashboard.js
window.analyticsCharts = {
    initialize: initializeAnalyticsCharts,
    refresh: refreshAnalyticsCharts
};

// Also make functions available globally
window.initializeAnalyticsCharts = initializeAnalyticsCharts;
window.refreshAnalyticsCharts = refreshAnalyticsCharts;

// Forecast summary update function
function updateForecastSummary(data) {
    const stats = data.statistics || {};
    const forecast = data.forecast || [];
    
    // Update summary cards
    document.getElementById('avgDailyBookings').textContent = stats.avg_daily_bookings || '0';
    
    const trendElement = document.getElementById('forecastTrend');
    const trend = stats.trend || 'stable';
    trendElement.textContent = trend;
    trendElement.className = 'mb-0 ' + (trend === 'increasing' ? 'text-success' : 
                                       trend === 'decreasing' ? 'text-danger' : 'text-warning');
    
    // Calculate average confidence
    const avgConfidence = forecast.length > 0 ? 
        Math.round(forecast.reduce((sum, item) => sum + (item.confidence || 0), 0) / forecast.length) : 0;
    document.getElementById('forecastConfidence').textContent = avgConfidence + '%';
    
    // Calculate total forecast
    const totalForecast = forecast.reduce((sum, item) => sum + (item.predicted_bookings || 0), 0);
    document.getElementById('totalForecast').textContent = totalForecast;
}

// Refresh forecast chart function
window.refreshForecastChart = function() {
    loadBookingForecastChart();
};

// Export forecast data function
window.exportForecastData = function() {
    // This would typically make an API call to get the data and export it
    // For now, we'll just show an alert
    alert('Export functionality would be implemented here. This would download the forecast data as CSV or PDF.');
};
