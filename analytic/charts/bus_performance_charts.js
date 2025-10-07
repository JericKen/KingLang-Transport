// ==========================================
// BUS PERFORMANCE CHARTS (Chart.js)
// ==========================================

// 1. MOST-USED BUSES (Horizontal Bar Chart)
const mostUsedBusesData = {
  labels: [
    'Bus Alpha',
    'Bus Beta',
    'Bus Gamma',
    'Bus Delta',
    'Bus Epsilon',
    'Bus Zeta',
    'Bus Eta',
    'Bus Theta',
  ],
  datasets: [
    {
      label: 'Number of Bookings',
      data: [145, 132, 128, 115, 98, 87, 75, 68],
      backgroundColor: [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(199, 199, 199, 0.7)',
        'rgba(83, 102, 255, 0.7)',
      ],
      borderColor: [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 206, 86)',
        'rgb(75, 192, 192)',
        'rgb(153, 102, 255)',
        'rgb(255, 159, 64)',
        'rgb(199, 199, 199)',
        'rgb(83, 102, 255)',
      ],
      borderWidth: 1,
    },
  ],
};

const mostUsedBusesConfig = {
  type: 'bar',
  data: mostUsedBusesData,
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Most-Used Buses (Ranked by Booking Count)',
        font: { size: 18 },
      },
      legend: {
        display: false,
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            return 'Bookings: ' + context.parsed.x;
          },
        },
      },
    },
    scales: {
      x: {
        beginAtZero: true,
      },
    },
  },
};

const mostUsedBusesChart = new Chart(
  document.getElementById('mostUsedBusesChart'),
  mostUsedBusesConfig
);

// 2. BUS REVENUE PERFORMANCE (Bubble Chart)
const busRevenuePerformanceData = {
  datasets: [
    {
      label: 'Bus Performance (Bookings vs Revenue)',
      data: [
        { x: 145, y: 2900000, r: 15, bus: 'Bus Alpha' },
        { x: 132, y: 2640000, r: 14, bus: 'Bus Beta' },
        { x: 128, y: 2560000, r: 13, bus: 'Bus Gamma' },
        { x: 115, y: 2300000, r: 12, bus: 'Bus Delta' },
        { x: 98, y: 1960000, r: 10, bus: 'Bus Epsilon' },
        { x: 87, y: 1740000, r: 9, bus: 'Bus Zeta' },
        { x: 75, y: 1500000, r: 8, bus: 'Bus Eta' },
        { x: 68, y: 1360000, r: 7, bus: 'Bus Theta' },
      ],
      backgroundColor: 'rgba(54, 162, 235, 0.6)',
      borderColor: 'rgb(54, 162, 235)',
      borderWidth: 1,
    },
  ],
};

const busRevenuePerformanceConfig = {
  type: 'bubble',
  data: busRevenuePerformanceData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Bus Performance: Bookings vs Revenue',
        font: { size: 18 },
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const point = context.raw;
            return [
              point.bus,
              'Bookings: ' + point.x,
              'Revenue: ₱' + point.y.toLocaleString(),
            ];
          },
        },
      },
    },
    scales: {
      x: {
        title: {
          display: true,
          text: 'Number of Bookings',
        },
      },
      y: {
        title: {
          display: true,
          text: 'Total Revenue (₱)',
        },
        ticks: {
          callback: function (value) {
            return '₱' + (value / 1000000).toFixed(1) + 'M';
          },
        },
      },
    },
  },
};

const busRevenuePerformanceChart = new Chart(
  document.getElementById('busRevenuePerformanceChart'),
  busRevenuePerformanceConfig
);

// 3. UNDERPERFORMING BUSES (Radar Chart)
const underperformingBusesData = {
  labels: [
    'Bookings vs Avg',
    'Revenue vs Avg',
    'Rating',
    'Completion Rate',
    'Maintenance Status',
  ],
  datasets: [
    {
      label: 'Bus Omega (Underperforming)',
      data: [45, 50, 55, 60, 40],
      fill: true,
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgb(255, 99, 132)',
      pointBackgroundColor: 'rgb(255, 99, 132)',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: 'rgb(255, 99, 132)',
    },
    {
      label: 'Average Bus Performance',
      data: [100, 100, 100, 100, 100],
      fill: true,
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgb(75, 192, 192)',
      pointBackgroundColor: 'rgb(75, 192, 192)',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: 'rgb(75, 192, 192)',
    },
  ],
};

const underperformingBusesConfig = {
  type: 'radar',
  data: underperformingBusesData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Underperforming Bus Analysis',
        font: { size: 18 },
      },
      legend: {
        position: 'top',
      },
    },
    scales: {
      r: {
        beginAtZero: true,
        max: 120,
        ticks: {
          stepSize: 20,
        },
      },
    },
  },
};

const underperformingBusesChart = new Chart(
  document.getElementById('underperformingBusesChart'),
  underperformingBusesConfig
);

// 4. MAINTENANCE ALERTS DASHBOARD (Mixed Chart)
const maintenanceAlertsData = {
  labels: [
    'Bus A',
    'Bus B',
    'Bus C',
    'Bus D',
    'Bus E',
    'Bus F',
    'Bus G',
    'Bus H',
  ],
  datasets: [
    {
      type: 'bar',
      label: 'Mileage Since Maintenance',
      data: [12000, 9500, 11200, 7800, 10500, 8200, 13500, 6500],
      backgroundColor: 'rgba(255, 99, 132, 0.7)',
      borderColor: 'rgb(255, 99, 132)',
      borderWidth: 1,
      yAxisID: 'y',
    },
    {
      type: 'line',
      label: 'Maintenance Threshold',
      data: [10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000],
      borderColor: 'rgb(255, 205, 86)',
      borderWidth: 3,
      borderDash: [10, 5],
      fill: false,
      yAxisID: 'y',
    },
    {
      type: 'line',
      label: 'Days Since Maintenance',
      data: [195, 145, 178, 120, 165, 135, 210, 98],
      borderColor: 'rgb(54, 162, 235)',
      backgroundColor: 'rgba(54, 162, 235, 0.1)',
      fill: true,
      tension: 0.4,
      yAxisID: 'y1',
    },
  ],
};

const maintenanceAlertsConfig = {
  type: 'bar',
  data: maintenanceAlertsData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Upcoming Maintenance Alerts',
        font: { size: 18 },
      },
      legend: {
        display: true,
        position: 'top',
      },
    },
    scales: {
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        title: {
          display: true,
          text: 'Mileage (km)',
        },
        beginAtZero: true,
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        title: {
          display: true,
          text: 'Days',
        },
        grid: {
          drawOnChartArea: false,
        },
        beginAtZero: true,
      },
    },
  },
};

const maintenanceAlertsChart = new Chart(
  document.getElementById('maintenanceAlertsChart'),
  maintenanceAlertsConfig
);

// 5. BUS UTILIZATION RATE (Doughnut Chart)
const busUtilizationData = {
  labels: [
    'High Utilization (>100 bookings)',
    'Medium Utilization (50-100)',
    'Low Utilization (<50)',
  ],
  datasets: [
    {
      data: [8, 12, 5],
      backgroundColor: [
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(255, 99, 132, 0.8)',
      ],
      borderColor: [
        'rgb(75, 192, 192)',
        'rgb(255, 206, 86)',
        'rgb(255, 99, 132)',
      ],
      borderWidth: 2,
    },
  ],
};

const busUtilizationConfig = {
  type: 'doughnut',
  data: busUtilizationData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Fleet Utilization Distribution',
        font: { size: 18 },
      },
      legend: {
        position: 'bottom',
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((context.parsed / total) * 100).toFixed(1);
            return (
              context.label +
              ': ' +
              context.parsed +
              ' buses (' +
              percentage +
              '%)'
            );
          },
        },
      },
    },
  },
};

const busUtilizationChart = new Chart(
  document.getElementById('busUtilizationChart'),
  busUtilizationConfig
);
