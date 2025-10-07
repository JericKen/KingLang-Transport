// ==========================================
// REVENUE TRENDS CHARTS (Chart.js)
// ==========================================

// 1. DAILY REVENUE LINE CHART
const dailyRevenueData = {
  labels: [
    '2025-01-01',
    '2025-01-02',
    '2025-01-03',
    '2025-01-04',
    '2025-01-05',
    '2025-01-06',
    '2025-01-07',
  ],
  datasets: [
    {
      label: 'Daily Revenue',
      data: [15000, 18500, 12300, 22100, 19800, 25400, 21200],
      borderColor: 'rgb(75, 192, 192)',
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      tension: 0.4,
      fill: true,
    },
    {
      label: 'Average',
      data: [19043, 19043, 19043, 19043, 19043, 19043, 19043], // Fixed average line
      borderColor: 'rgb(255, 99, 132)',
      borderDash: [5, 5],
      borderWidth: 2,
      fill: false,
    },
  ],
};

const dailyRevenueConfig = {
  type: 'line',
  data: dailyRevenueData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Daily Revenue Trends',
        font: { size: 18 },
      },
      legend: {
        display: true,
        position: 'top',
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            return (
              context.dataset.label + ': ₱' + context.parsed.y.toLocaleString()
            );
          },
        },
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return '₱' + value.toLocaleString();
          },
        },
      },
    },
  },
};

// Initialize chart
const dailyRevenueChart = new Chart(
  document.getElementById('dailyRevenueChart'),
  dailyRevenueConfig
);

// 2. MONTHLY REVENUE BAR CHART WITH GROWTH
const monthlyRevenueData = {
  labels: [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ],
  datasets: [
    {
      label: 'Monthly Revenue',
      data: [
        450000, 520000, 680000, 590000, 720000, 650000, 710000, 780000, 690000,
        820000, 750000, 890000,
      ],
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      borderColor: 'rgb(54, 162, 235)',
      borderWidth: 1,
    },
  ],
};

const monthlyRevenueConfig = {
  type: 'bar',
  data: monthlyRevenueData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Monthly Revenue Performance',
        font: { size: 18 },
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const value = context.parsed.y;
            const prevValue =
              context.dataIndex > 0
                ? context.dataset.data[context.dataIndex - 1]
                : value;
            const growth = (((value - prevValue) / prevValue) * 100).toFixed(2);
            return [
              'Revenue: ₱' + value.toLocaleString(),
              'Growth: ' + (growth > 0 ? '+' : '') + growth + '%',
            ];
          },
        },
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return '₱' + value / 1000 + 'K';
          },
        },
      },
    },
  },
};

const monthlyRevenueChart = new Chart(
  document.getElementById('monthlyRevenueChart'),
  monthlyRevenueConfig
);

// 3. REVENUE COMPARISON: WITH vs WITHOUT PROMO (Grouped Bar Chart)
const promoComparisonData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'With Promo',
      data: [180000, 220000, 250000, 210000, 280000, 240000],
      backgroundColor: 'rgba(75, 192, 192, 0.7)',
      borderColor: 'rgb(75, 192, 192)',
      borderWidth: 1,
    },
    {
      label: 'Without Promo',
      data: [270000, 300000, 430000, 380000, 440000, 410000],
      backgroundColor: 'rgba(153, 102, 255, 0.7)',
      borderColor: 'rgb(153, 102, 255)',
      borderWidth: 1,
    },
  ],
};

const promoComparisonConfig = {
  type: 'bar',
  data: promoComparisonData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Revenue: With Promo vs Without Promo',
        font: { size: 18 },
      },
      legend: {
        display: true,
        position: 'top',
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return '₱' + value / 1000 + 'K';
          },
        },
      },
    },
  },
};

const promoComparisonChart = new Chart(
  document.getElementById('promoComparisonChart'),
  promoComparisonConfig
);

// 4. REVENUE BY CLIENT TYPE (Pie Chart)
const clientRevenueData = {
  labels: ['School', 'Company', 'Individual', 'Government', 'Tour Group'],
  datasets: [
    {
      data: [1200000, 2500000, 800000, 450000, 650000],
      backgroundColor: [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
      ],
      borderColor: [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 206, 86)',
        'rgb(75, 192, 192)',
        'rgb(153, 102, 255)',
      ],
      borderWidth: 2,
    },
  ],
};

const clientRevenueConfig = {
  type: 'pie',
  data: clientRevenueData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Revenue Distribution by Client Type',
        font: { size: 18 },
      },
      legend: {
        position: 'right',
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((context.parsed / total) * 100).toFixed(2);
            return (
              context.label +
              ': ₱' +
              context.parsed.toLocaleString() +
              ' (' +
              percentage +
              '%)'
            );
          },
        },
      },
    },
  },
};

const clientRevenueChart = new Chart(
  document.getElementById('clientRevenueChart'),
  clientRevenueConfig
);

// 5. WEEKLY REVENUE TRENDS (Area Chart)
const weeklyRevenueData = {
  labels: [
    'Week 1',
    'Week 2',
    'Week 3',
    'Week 4',
    'Week 5',
    'Week 6',
    'Week 7',
    'Week 8',
  ],
  datasets: [
    {
      label: '2024',
      data: [85000, 92000, 78000, 105000, 98000, 110000, 88000, 115000],
      borderColor: 'rgba(201, 203, 207, 0.8)',
      backgroundColor: 'rgba(201, 203, 207, 0.2)',
      fill: true,
      tension: 0.4,
    },
    {
      label: '2025',
      data: [95000, 102000, 88000, 125000, 118000, 135000, 108000, 145000],
      borderColor: 'rgba(54, 162, 235, 0.8)',
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      fill: true,
      tension: 0.4,
    },
  ],
};

const weeklyRevenueConfig = {
  type: 'line',
  data: weeklyRevenueData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Weekly Revenue Comparison (Year-over-Year)',
        font: { size: 18 },
      },
      legend: {
        display: true,
        position: 'top',
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return '₱' + value / 1000 + 'K';
          },
        },
      },
    },
  },
};

const weeklyRevenueChart = new Chart(
  document.getElementById('weeklyRevenueChart'),
  weeklyRevenueConfig
);
