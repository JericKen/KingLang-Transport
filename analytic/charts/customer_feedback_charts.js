// ==========================================
// CUSTOMER FEEDBACK & SENTIMENT CHARTS (Chart.js)
// ==========================================

// 1. AVERAGE RATINGS PER BUS (Horizontal Bar Chart)
const busRatingsData = {
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
      label: 'Average Rating',
      data: [4.7, 4.5, 4.3, 4.2, 3.9, 3.7, 3.5, 3.2],
      backgroundColor: function (context) {
        const value = context.parsed.x;
        if (value >= 4.5) return 'rgba(75, 192, 192, 0.8)';
        if (value >= 4.0) return 'rgba(54, 162, 235, 0.8)';
        if (value >= 3.5) return 'rgba(255, 206, 86, 0.8)';
        return 'rgba(255, 99, 132, 0.8)';
      },
      borderColor: function (context) {
        const value = context.parsed.x;
        if (value >= 4.5) return 'rgb(75, 192, 192)';
        if (value >= 4.0) return 'rgb(54, 162, 235)';
        if (value >= 3.5) return 'rgb(255, 206, 86)';
        return 'rgb(255, 99, 132)';
      },
      borderWidth: 1,
    },
  ],
};

const busRatingsConfig = {
  type: 'bar',
  data: busRatingsData,
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Average Customer Ratings per Bus',
        font: { size: 18 },
      },
      legend: {
        display: false,
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            return 'Rating: ' + context.parsed.x.toFixed(2) + ' / 5.0';
          },
        },
      },
    },
    scales: {
      x: {
        min: 0,
        max: 5,
        ticks: {
          stepSize: 0.5,
        },
        title: {
          display: true,
          text: 'Rating (out of 5)',
        },
      },
    },
  },
};

const busRatingsChart = new Chart(
  document.getElementById('busRatingsChart'),
  busRatingsConfig
);

// 2. FEEDBACK SENTIMENT DISTRIBUTION (Stacked Bar Chart)
const sentimentDistributionData = {
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
      label: 'Positive (4-5)',
      data: [85, 78, 72, 68, 55, 48, 42, 35],
      backgroundColor: 'rgba(75, 192, 192, 0.8)',
      borderColor: 'rgb(75, 192, 192)',
      borderWidth: 1,
    },
    {
      label: 'Neutral (3)',
      data: [10, 15, 18, 22, 25, 28, 30, 32],
      backgroundColor: 'rgba(255, 206, 86, 0.8)',
      borderColor: 'rgb(255, 206, 86)',
      borderWidth: 1,
    },
    {
      label: 'Negative (1-2)',
      data: [5, 7, 10, 10, 20, 24, 28, 33],
      backgroundColor: 'rgba(255, 99, 132, 0.8)',
      borderColor: 'rgb(255, 99, 132)',
      borderWidth: 1,
    },
  ],
};

const sentimentDistributionConfig = {
  type: 'bar',
  data: sentimentDistributionData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Feedback Sentiment Distribution by Bus',
        font: { size: 18 },
      },
      legend: {
        position: 'top',
      },
      tooltip: {
        mode: 'index',
        intersect: false,
        callbacks: {
          footer: function (tooltipItems) {
            let total = 0;
            tooltipItems.forEach(function (tooltipItem) {
              total += tooltipItem.parsed.y;
            });
            return 'Total Feedback: ' + total;
          },
        },
      },
    },
    scales: {
      x: {
        stacked: true,
      },
      y: {
        stacked: true,
        title: {
          display: true,
          text: 'Number of Feedback',
        },
      },
    },
  },
};

const sentimentDistributionChart = new Chart(
  document.getElementById('sentimentDistributionChart'),
  sentimentDistributionConfig
);

// 3. RATING TRENDS OVER TIME (Line Chart)
const ratingTrendsData = {
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
      label: 'Average Rating',
      data: [4.2, 4.3, 4.1, 4.4, 4.5, 4.3, 4.6, 4.5, 4.4, 4.7, 4.6, 4.8],
      borderColor: 'rgb(75, 192, 192)',
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      tension: 0.4,
      fill: true,
      yAxisID: 'y',
    },
    {
      label: 'Total Feedback Count',
      data: [85, 92, 78, 105, 118, 125, 132, 128, 142, 155, 148, 165],
      borderColor: 'rgb(54, 162, 235)',
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      tension: 0.4,
      fill: false,
      yAxisID: 'y1',
    },
  ],
};

const ratingTrendsConfig = {
  type: 'line',
  data: ratingTrendsData,
  options: {
    responsive: true,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      title: {
        display: true,
        text: 'Rating Trends Over Time',
        font: { size: 18 },
      },
      legend: {
        position: 'top',
      },
    },
    scales: {
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        min: 0,
        max: 5,
        title: {
          display: true,
          text: 'Average Rating',
        },
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        title: {
          display: true,
          text: 'Feedback Count',
        },
        grid: {
          drawOnChartArea: false,
        },
      },
    },
  },
};

const ratingTrendsChart = new Chart(
  document.getElementById('ratingTrendsChart'),
  ratingTrendsConfig
);

// 4. KEYWORD FREQUENCY (Word Cloud Style Bar Chart)
const keywordFrequencyData = {
  labels: [
    'comfortable',
    'clean',
    'professional',
    'punctual',
    'friendly',
    'safe',
    'smooth',
    'spacious',
    'airconditioned',
    'excellent',
    'dirty',
    'late',
    'rude',
    'uncomfortable',
    'broken',
  ],
  datasets: [
    {
      label: 'Frequency',
      data: [245, 198, 165, 152, 138, 125, 108, 95, 88, 75, 45, 38, 32, 28, 22],
      backgroundColor: function (context) {
        const negativeKeywords = [
          'dirty',
          'late',
          'rude',
          'uncomfortable',
          'broken',
        ];
        const label = context.chart.data.labels[context.dataIndex];
        return negativeKeywords.includes(label)
          ? 'rgba(255, 99, 132, 0.8)'
          : 'rgba(75, 192, 192, 0.8)';
      },
      borderColor: function (context) {
        const negativeKeywords = [
          'dirty',
          'late',
          'rude',
          'uncomfortable',
          'broken',
        ];
        const label = context.chart.data.labels[context.dataIndex];
        return negativeKeywords.includes(label)
          ? 'rgb(255, 99, 132)'
          : 'rgb(75, 192, 192)';
      },
      borderWidth: 1,
    },
  ],
};

const keywordFrequencyConfig = {
  type: 'bar',
  data: keywordFrequencyData,
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Most Common Feedback Keywords',
        font: { size: 18 },
      },
      legend: {
        display: false,
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            return 'Mentioned: ' + context.parsed.x + ' times';
          },
        },
      },
    },
    scales: {
      x: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Frequency',
        },
      },
    },
  },
};

const keywordFrequencyChart = new Chart(
  document.getElementById('keywordFrequencyChart'),
  keywordFrequencyConfig
);

// 5. DRIVER RATINGS COMPARISON (Radar Chart)
const driverRatingsData = {
  labels: [
    'Professionalism',
    'Punctuality',
    'Safety',
    'Friendliness',
    'Communication',
  ],
  datasets: [
    {
      label: 'Driver A (Top Rated)',
      data: [4.8, 4.9, 4.7, 4.8, 4.6],
      fill: true,
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgb(75, 192, 192)',
      pointBackgroundColor: 'rgb(75, 192, 192)',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: 'rgb(75, 192, 192)',
    },
    {
      label: 'Driver F (Needs Improvement)',
      data: [3.5, 3.2, 3.8, 3.3, 3.4],
      fill: true,
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgb(255, 99, 132)',
      pointBackgroundColor: 'rgb(255, 99, 132)',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: 'rgb(255, 99, 132)',
    },
  ],
};

const driverRatingsConfig = {
  type: 'radar',
  data: driverRatingsData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Driver Performance Comparison',
        font: { size: 18 },
      },
      legend: {
        position: 'top',
      },
    },
    scales: {
      r: {
        beginAtZero: true,
        max: 5,
        ticks: {
          stepSize: 1,
        },
      },
    },
  },
};

const driverRatingsChart = new Chart(
  document.getElementById('driverRatingsChart'),
  driverRatingsConfig
);

// 6. COMPLAINT RATE BY BUS TYPE (Doughnut Chart)
const complaintRateData = {
  labels: ['Standard Bus', 'Luxury Bus', 'Mini Bus', 'Double Decker', 'Coach'],
  datasets: [
    {
      data: [45, 12, 28, 8, 15],
      backgroundColor: [
        'rgba(255, 99, 132, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(153, 102, 255, 0.8)',
      ],
      borderColor: [
        'rgb(255, 99, 132)',
        'rgb(75, 192, 192)',
        'rgb(255, 206, 86)',
        'rgb(54, 162, 235)',
        'rgb(153, 102, 255)',
      ],
      borderWidth: 2,
    },
  ],
};

const complaintRateConfig = {
  type: 'doughnut',
  data: complaintRateData,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Complaint Distribution by Bus Type',
        font: { size: 18 },
      },
      legend: {
        position: 'right',
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
              ' complaints (' +
              percentage +
              '%)'
            );
          },
        },
      },
    },
  },
};

const complaintRateChart = new Chart(
  document.getElementById('complaintRateChart'),
  complaintRateConfig
);
