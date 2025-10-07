// ==========================================
// PREDICTIVE ANALYTICS CHARTS (ApexCharts)
// ==========================================

// 1. BOOKING FORECAST (30 DAYS) - Line Chart with Prediction Interval
const bookingForecastOptions = {
  series: [
    {
      name: 'Historical Bookings',
      type: 'line',
      data: [
        { x: '2025-09-01', y: 85 },
        { x: '2025-09-02', y: 92 },
        { x: '2025-09-03', y: 88 },
        { x: '2025-09-04', y: 95 },
        { x: '2025-09-05', y: 102 },
        { x: '2025-09-06', y: 110 },
        { x: '2025-09-07', y: 98 },
        { x: '2025-09-08', y: 105 },
        { x: '2025-09-09', y: 112 },
        { x: '2025-09-10', y: 108 },
      ],
    },
    {
      name: 'Forecasted Bookings',
      type: 'line',
      data: [
        { x: '2025-09-10', y: 108 },
        { x: '2025-09-11', y: 115 },
        { x: '2025-09-12', y: 118 },
        { x: '2025-09-13', y: 122 },
        { x: '2025-09-14', y: 125 },
        { x: '2025-09-15', y: 130 },
        { x: '2025-09-16', y: 135 },
        { x: '2025-09-17', y: 128 },
        { x: '2025-09-18', y: 132 },
        { x: '2025-09-19', y: 138 },
        { x: '2025-09-20', y: 142 },
      ],
    },
    {
      name: 'Upper Bound (95% CI)',
      type: 'line',
      data: [
        { x: '2025-09-10', y: 108 },
        { x: '2025-09-11', y: 125 },
        { x: '2025-09-12', y: 132 },
        { x: '2025-09-13', y: 138 },
        { x: '2025-09-14', y: 142 },
        { x: '2025-09-15', y: 148 },
        { x: '2025-09-16', y: 155 },
        { x: '2025-09-17', y: 145 },
        { x: '2025-09-18', y: 150 },
        { x: '2025-09-19', y: 158 },
        { x: '2025-09-20', y: 165 },
      ],
    },
    {
      name: 'Lower Bound (95% CI)',
      type: 'line',
      data: [
        { x: '2025-09-10', y: 108 },
        { x: '2025-09-11', y: 105 },
        { x: '2025-09-12', y: 104 },
        { x: '2025-09-13', y: 106 },
        { x: '2025-09-14', y: 108 },
        { x: '2025-09-15', y: 112 },
        { x: '2025-09-16', y: 115 },
        { x: '2025-09-17', y: 111 },
        { x: '2025-09-18', y: 114 },
        { x: '2025-09-19', y: 118 },
        { x: '2025-09-20', y: 119 },
      ],
    },
  ],
  chart: {
    height: 450,
    type: 'line',
    toolbar: {
      show: true,
    },
    zoom: {
      enabled: true,
    },
  },
  colors: ['#008FFB', '#00E396', '#FEB019', '#FEB019'],
  stroke: {
    width: [3, 3, 1, 1],
    curve: 'smooth',
    dashArray: [0, 5, 3, 3],
  },
  fill: {
    type: 'solid',
    opacity: [1, 1, 0.3, 0.3],
  },
  markers: {
    size: [5, 5, 0, 0],
    hover: {
      size: 7,
    },
  },
  xaxis: {
    type: 'datetime',
    title: {
      text: 'Date',
    },
  },
  yaxis: {
    title: {
      text: 'Number of Bookings',
    },
    labels: {
      formatter: function (val) {
        return val.toFixed(0);
      },
    },
  },
  title: {
    text: '30-Day Booking Forecast (ARIMA+)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  legend: {
    position: 'top',
    horizontalAlign: 'center',
  },
  annotations: {
    xaxis: [
      {
        x: new Date('2025-09-10').getTime(),
        borderColor: '#775DD0',
        label: {
          borderColor: '#775DD0',
          style: {
            color: '#fff',
            background: '#775DD0',
          },
          text: 'Forecast Start',
        },
      },
    ],
  },
  tooltip: {
    shared: true,
    intersect: false,
    x: {
      format: 'dd MMM yyyy',
    },
  },
};

const bookingForecastChart = new ApexCharts(
  document.querySelector('#bookingForecastChart'),
  bookingForecastOptions
);
bookingForecastChart.render();

// 2. REVENUE FORECAST (90 DAYS) - Area Chart with Range
const revenueForecastOptions = {
  series: [
    {
      name: 'Historical Revenue',
      data: [
        [new Date('2025-07-01').getTime(), 850000],
        [new Date('2025-07-15').getTime(), 920000],
        [new Date('2025-08-01').getTime(), 980000],
        [new Date('2025-08-15').getTime(), 1050000],
        [new Date('2025-09-01').getTime(), 1120000],
        [new Date('2025-09-15').getTime(), 1180000],
        [new Date('2025-09-30').getTime(), 1250000],
      ],
    },
    {
      name: 'Forecasted Revenue',
      data: [
        [new Date('2025-09-30').getTime(), 1250000],
        [new Date('2025-10-15').getTime(), 1320000],
        [new Date('2025-10-31').getTime(), 1450000],
        [new Date('2025-11-15').getTime(), 1580000],
        [new Date('2025-11-30').getTime(), 1680000],
        [new Date('2025-12-15').getTime(), 1820000],
        [new Date('2025-12-31').getTime(), 1950000],
      ],
    },
  ],
  chart: {
    type: 'area',
    height: 450,
    toolbar: {
      show: true,
    },
    zoom: {
      enabled: true,
    },
  },
  colors: ['#008FFB', '#00E396'],
  dataLabels: {
    enabled: false,
  },
  stroke: {
    curve: 'smooth',
    width: [3, 3],
    dashArray: [0, 5],
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.7,
      opacityTo: 0.2,
    },
  },
  xaxis: {
    type: 'datetime',
    title: {
      text: 'Date',
    },
  },
  yaxis: {
    title: {
      text: 'Revenue (₱)',
    },
    labels: {
      formatter: function (val) {
        return '₱' + (val / 1000000).toFixed(1) + 'M';
      },
    },
  },
  title: {
    text: 'Revenue Forecast (Next 90 Days)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  legend: {
    position: 'top',
  },
  annotations: {
    xaxis: [
      {
        x: new Date('2025-09-30').getTime(),
        borderColor: '#775DD0',
        label: {
          borderColor: '#775DD0',
          style: {
            color: '#fff',
            background: '#775DD0',
          },
          text: 'Forecast Start',
        },
      },
    ],
  },
  tooltip: {
    x: {
      format: 'dd MMM yyyy',
    },
    y: {
      formatter: function (val) {
        return '₱' + val.toLocaleString();
      },
    },
  },
};

const revenueForecastChart = new ApexCharts(
  document.querySelector('#revenueForecastChart'),
  revenueForecastOptions
);
revenueForecastChart.render();

// 3. SEASONAL BOOKING PATTERNS (Mixed Chart)
const seasonalPatternsOptions = {
  series: [
    {
      name: 'Bookings',
      type: 'column',
      data: [65, 72, 88, 95, 110, 85, 90, 105, 125, 92, 78, 120],
    },
    {
      name: 'Trend Line',
      type: 'line',
      data: [70, 75, 80, 85, 90, 88, 92, 96, 100, 95, 90, 105],
    },
    {
      name: 'School Trips Peak',
      type: 'area',
      data: [10, 15, 35, 40, 45, 8, 5, 12, 42, 15, 8, 15],
    },
  ],
  chart: {
    height: 400,
    type: 'line',
    toolbar: {
      show: true,
    },
  },
  stroke: {
    width: [0, 4, 2],
    curve: 'smooth',
  },
  plotOptions: {
    bar: {
      columnWidth: '50%',
    },
  },
  fill: {
    opacity: [0.85, 1, 0.25],
    gradient: {
      inverseColors: false,
      shade: 'light',
      type: 'vertical',
      opacityFrom: 0.85,
      opacityTo: 0.55,
      stops: [0, 100, 100, 100],
    },
  },
  colors: ['#008FFB', '#00E396', '#FEB019'],
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
  markers: {
    size: 0,
  },
  xaxis: {
    title: {
      text: 'Month',
    },
  },
  yaxis: {
    title: {
      text: 'Number of Bookings',
    },
    min: 0,
  },
  title: {
    text: 'Seasonal Booking Patterns & Peak Periods',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  legend: {
    position: 'top',
    horizontalAlign: 'center',
  },
  tooltip: {
    shared: true,
    intersect: false,
  },
};

const seasonalPatternsChart = new ApexCharts(
  document.querySelector('#seasonalPatternsChart'),
  seasonalPatternsOptions
);
seasonalPatternsChart.render();

// 4. DEMAND FORECAST BY BUS TYPE (Multi-line Chart)
const demandByBusTypeOptions = {
  series: [
    {
      name: 'Standard Bus',
      data: [45, 48, 52, 55, 58, 62, 65, 68, 72, 75],
    },
    {
      name: 'Luxury Bus',
      data: [25, 28, 30, 32, 35, 38, 40, 42, 45, 48],
    },
    {
      name: 'Mini Bus',
      data: [30, 32, 35, 38, 40, 42, 45, 48, 50, 52],
    },
    {
      name: 'Double Decker',
      data: [15, 17, 18, 20, 22, 25, 27, 30, 32, 35],
    },
  ],
  chart: {
    height: 400,
    type: 'line',
    toolbar: {
      show: true,
    },
    zoom: {
      enabled: true,
    },
  },
  colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560'],
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: 3,
    curve: 'smooth',
  },
  markers: {
    size: 4,
    hover: {
      size: 6,
    },
  },
  xaxis: {
    categories: [
      'Day 1',
      'Day 5',
      'Day 10',
      'Day 15',
      'Day 20',
      'Day 25',
      'Day 30',
      'Day 35',
      'Day 40',
      'Day 45',
    ],
    title: {
      text: 'Forecast Period',
    },
  },
  yaxis: {
    title: {
      text: 'Expected Bookings',
    },
  },
  title: {
    text: 'Demand Forecast by Bus Type (45 Days)',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  legend: {
    position: 'top',
  },
  grid: {
    borderColor: '#e7e7e7',
    row: {
      colors: ['#f3f3f3', 'transparent'],
      opacity: 0.5,
    },
  },
  tooltip: {
    shared: true,
    intersect: false,
  },
};

const demandByBusTypeChart = new ApexCharts(
  document.querySelector('#demandByBusTypeChart'),
  demandByBusTypeOptions
);
demandByBusTypeChart.render();

// 5. BOOKING PROBABILITY BY DAY OF WEEK (Radar Chart)
const bookingProbabilityOptions = {
  series: [
    {
      name: 'Booking Probability (%)',
      data: [12.5, 13.8, 14.2, 16.5, 18.9, 21.2, 15.3],
    },
  ],
  chart: {
    height: 400,
    type: 'radar',
    toolbar: {
      show: true,
    },
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
  },
  fill: {
    opacity: 0.2,
    colors: ['#008FFB'],
  },
  stroke: {
    show: true,
    width: 3,
    colors: ['#008FFB'],
    dashArray: 0,
  },
  markers: {
    size: 5,
    colors: ['#008FFB'],
    strokeColor: '#fff',
    strokeWidth: 2,
  },
  title: {
    text: 'Booking Probability by Day of Week',
    align: 'center',
    style: {
      fontSize: '18px',
      fontWeight: 'bold',
    },
  },
  yaxis: {
    show: true,
    tickAmount: 5,
    labels: {
      formatter: function (val) {
        return val.toFixed(1) + '%';
      },
    },
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val.toFixed(1) + '% probability';
      },
    },
  },
};

const bookingProbabilityChart = new ApexCharts(
  document.querySelector('#bookingProbabilityChart'),
  bookingProbabilityOptions
);
bookingProbabilityChart.render();
