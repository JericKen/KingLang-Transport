// ==========================================
// BOOKING PATTERNS CHARTS (ApexCharts)
// ==========================================

// 1. BUSIEST BOOKING DAYS - DAY OF WEEK (Column Chart)
const busyDaysOptions = {
  series: [
    {
      name: 'Bookings',
      data: [85, 92, 88, 110, 125, 145, 98],
    },
    {
      name: 'Revenue',
      data: [170000, 184000, 176000, 220000, 250000, 290000, 196000],
    },
  ],
  chart: {
    type: 'bar',
    height: 400,
    toolbar: {
      show: true,
    },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      borderRadius: 5,
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent'],
  },
  xaxis: {
    categories: [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday',
    ],
    title: {
      text: 'Day of Week',
    },
  },
  yaxis: [
    {
      title: {
        text: 'Number of Bookings',
      },
      labels: {
        formatter: function (val) {
          return val.toFixed(0);
        },
      },
    },
    {
      opposite: true,
      title: {
        text: 'Revenue (₱)',
      },
      labels: {
        formatter: function (val) {
          return '₱' + (val / 1000).toFixed(0) + 'K';
        },
      },
    },
  ],
  fill: {
    opacity: 1,
  },
  colors: ['#008FFB', '#00E396'],
  title: {
    text: 'Busiest Booking Days (by Day of Week)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  tooltip: {
    y: [
      {
        formatter: function (val) {
          return val + ' bookings';
        },
      },
      {
        formatter: function (val) {
          return '₱' + val.toLocaleString();
        },
      },
    ],
  },
};

const busyDaysChart = new ApexCharts(
  document.querySelector('#busyDaysChart'),
  busyDaysOptions
);
busyDaysChart.render();

// 2. SEASONALITY ANALYSIS - MONTHLY PATTERNS (Heatmap)
const seasonalityOptions = {
  series: [
    {
      name: '2023',
      data: [65, 72, 88, 95, 110, 85, 90, 105, 125, 92, 78, 120],
    },
    {
      name: '2024',
      data: [75, 82, 98, 105, 120, 95, 100, 115, 135, 102, 88, 135],
    },
    {
      name: '2025',
      data: [85, 92, 108, 115, 130, 105, 110, 125, 145, 112, 98, 145],
    },
  ],
  chart: {
    height: 350,
    type: 'heatmap',
    toolbar: {
      show: true,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#fff'],
    },
  },
  colors: ['#008FFB'],
  title: {
    text: 'Booking Seasonality Heatmap (Monthly Patterns)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  xaxis: {
    categories: [
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
    title: {
      text: 'Month',
    },
  },
  yaxis: {
    title: {
      text: 'Year',
    },
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + ' bookings';
      },
    },
  },
};

const seasonalityChart = new ApexCharts(
  document.querySelector('#seasonalityChart'),
  seasonalityOptions
);
seasonalityChart.render();

// 3. PEAK BOOKING HOURS (Radial Bar Chart)
const peakHoursOptions = {
  series: [85, 72, 45, 28, 15, 8],
  chart: {
    height: 390,
    type: 'radialBar',
  },
  plotOptions: {
    radialBar: {
      offsetY: 0,
      startAngle: 0,
      endAngle: 270,
      hollow: {
        margin: 5,
        size: '30%',
        background: 'transparent',
        image: undefined,
      },
      dataLabels: {
        name: {
          show: true,
        },
        value: {
          show: true,
          formatter: function (val) {
            return parseInt(val) + '%';
          },
        },
      },
    },
  },
  colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5', '#775DD0', '#546E7A'],
  labels: [
    'Morning (6-11)',
    'Afternoon (12-17)',
    'Evening (18-23)',
    'Night (0-5)',
    'Early Morning (4-6)',
    'Late Night (23-2)',
  ],
  legend: {
    show: true,
    floating: true,
    fontSize: '14px',
    position: 'left',
    offsetX: 0,
    offsetY: 15,
    labels: {
      useSeriesColors: true,
    },
    markers: {
      size: 0,
    },
    formatter: function (seriesName, opts) {
      return seriesName + ':  ' + opts.w.globals.series[opts.seriesIndex];
    },
    itemMargin: {
      vertical: 3,
    },
  },
  title: {
    text: 'Peak Booking Hours Distribution',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  responsive: [
    {
      breakpoint: 480,
      options: {
        legend: {
          show: false,
        },
      },
    },
  ],
};

const peakHoursChart = new ApexCharts(
  document.querySelector('#peakHoursChart'),
  peakHoursOptions
);
peakHoursChart.render();

// 4. COMPLETED VS CANCELLED BOOKINGS (Stacked Area Chart)
const bookingStatusOptions = {
  series: [
    {
      name: 'Completed',
      data: [120, 135, 142, 158, 165, 178, 185, 192, 188, 195, 202, 210],
    },
    {
      name: 'Cancelled',
      data: [15, 18, 12, 22, 18, 25, 20, 28, 22, 30, 25, 32],
    },
    {
      name: 'Pending',
      data: [8, 12, 10, 15, 12, 18, 14, 20, 16, 22, 18, 24],
    },
  ],
  chart: {
    type: 'area',
    height: 400,
    stacked: true,
    toolbar: {
      show: true,
    },
  },
  colors: ['#00E396', '#FF4560', '#FEB019'],
  dataLabels: {
    enabled: false,
  },
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  fill: {
    type: 'gradient',
    gradient: {
      opacityFrom: 0.6,
      opacityTo: 0.1,
    },
  },
  legend: {
    position: 'top',
    horizontalAlign: 'left',
  },
  xaxis: {
    categories: [
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
    title: {
      text: 'Month',
    },
  },
  yaxis: {
    title: {
      text: 'Number of Bookings',
    },
  },
  title: {
    text: 'Booking Status Trends (Completed vs Cancelled vs Pending)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  tooltip: {
    shared: true,
    intersect: false,
  },
};

const bookingStatusChart = new ApexCharts(
  document.querySelector('#bookingStatusChart'),
  bookingStatusOptions
);
bookingStatusChart.render();

// 5. TOP DESTINATIONS (TreeMap)
const topDestinationsOptions = {
  series: [
    {
      data: [
        { x: 'Manila', y: 245 },
        { x: 'Baguio', y: 198 },
        { x: 'Tagaytay', y: 165 },
        { x: 'Batangas', y: 142 },
        { x: 'Subic', y: 128 },
        { x: 'Laguna', y: 115 },
        { x: 'Pangasinan', y: 98 },
        { x: 'Zambales', y: 85 },
        { x: 'Cavite', y: 72 },
        { x: 'Bulacan', y: 65 },
      ],
    },
  ],
  chart: {
    height: 400,
    type: 'treemap',
    toolbar: {
      show: true,
    },
  },
  title: {
    text: 'Top Destinations (by Booking Count)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  colors: [
    '#008FFB',
    '#00E396',
    '#FEB019',
    '#FF4560',
    '#775DD0',
    '#546E7A',
    '#26a69a',
    '#D10CE8',
    '#FF6178',
    '#1E88E5',
  ],
  plotOptions: {
    treemap: {
      distributed: true,
      enableShades: false,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      fontSize: '14px',
      fontWeight: 'bold',
    },
    formatter: function (text, op) {
      return [text, op.value + ' bookings'];
    },
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + ' bookings';
      },
    },
  },
};

const topDestinationsChart = new ApexCharts(
  document.querySelector('#topDestinationsChart'),
  topDestinationsOptions
);
topDestinationsChart.render();

// 6. CLIENT TYPE DISTRIBUTION (Donut Chart with Gradient)
const clientTypeOptions = {
  series: [245, 398, 156, 89, 112],
  chart: {
    type: 'donut',
    height: 400,
  },
  labels: ['School', 'Company', 'Individual', 'Government', 'Tour Group'],
  colors: ['#FF4560', '#008FFB', '#00E396', '#FEB019', '#775DD0'],
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'dark',
      type: 'horizontal',
      shadeIntensity: 0.5,
      gradientToColors: ['#FF6178', '#1E88E5', '#26a69a', '#FFD54F', '#9575CD'],
      inverseColors: true,
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100],
    },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Bookings',
            fontSize: '16px',
            fontWeight: 600,
            formatter: function (w) {
              return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
            },
          },
        },
      },
    },
  },
  dataLabels: {
    enabled: true,
    formatter: function (val) {
      return val.toFixed(1) + '%';
    },
  },
  legend: {
    position: 'bottom',
    fontSize: '14px',
  },
  title: {
    text: 'Bookings by Client Type',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + ' bookings';
      },
    },
  },
};

const clientTypeChart = new ApexCharts(
  document.querySelector('#clientTypeChart'),
  clientTypeOptions
);
clientTypeChart.render();
