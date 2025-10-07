// ==========================================
// MARKETING & CUSTOMER ANALYTICS CHARTS
// ==========================================

// 1. PROMO EFFECTIVENESS COMPARISON (Chart.js)
const promoEffectivenessData = {
  labels: ['SUMMER25', 'SCHOOL15', 'CORPORATE20', 'WEEKEND10', 'FLASH30'],
  datasets: [
    {
      label: 'Total Usage',
      data: [245, 198, 165, 142, 88],
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      yAxisID: 'y',
    },
    {
      label: 'ROI Ratio',
      data: [3.5, 4.2, 3.8, 2.9, 2.1],
      type: 'line',
      borderColor: 'rgb(255, 99, 132)',
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      yAxisID: 'y1',
    },
  ],
};

const promoEffectivenessChart = new Chart(
  document.getElementById('promoEffectivenessChart'),
  {
    type: 'bar',
    data: promoEffectivenessData,
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Promo Code Effectiveness',
          font: { size: 18 },
        },
      },
      scales: {
        y: {
          type: 'linear',
          position: 'left',
          title: { display: true, text: 'Usage Count' },
        },
        y1: {
          type: 'linear',
          position: 'right',
          title: { display: true, text: 'ROI Ratio' },
          grid: { drawOnChartArea: false },
        },
      },
    },
  }
);

// 2. RFM SEGMENTATION (ApexCharts)
const rfmOptions = {
  series: [
    { name: 'Champions', data: [28, 450000] },
    { name: 'Loyal', data: [65, 380000] },
    { name: 'At Risk', data: [42, 220000] },
    { name: 'Lost', data: [38, 150000] },
  ],
  chart: { height: 400, type: 'scatter', zoom: { enabled: true } },
  xaxis: { title: { text: 'Customer Count' } },
  yaxis: { title: { text: 'Total Revenue (₱)' } },
  title: {
    text: 'Customer Segmentation (RFM Analysis)',
    align: 'center',
    style: { fontSize: '18px' },
  },
};

new ApexCharts(document.querySelector('#rfmChart'), rfmOptions).render();
