# Bus Booking Analytics System

## Complete BigQuery SQL Queries + Chart.js/ApexCharts Visualization

---

## 📊 **ANALYTICS MODULES**

### 1. **Smart Analytics Dashboard**

**File:** `analytics/01_smart_dashboard_queries.sql`

✅ **Metrics Included:**

- Revenue trends (daily, weekly, monthly)
- Most-used buses ranked by booking count
- Busiest booking days (day of week analysis)
- Top client types (school, company, individual)
- Upcoming maintenance alerts (mileage > 10,000 OR > 6 months)
- Customer feedback analysis (avg rating, complaints)
- Underperforming buses/days (below-average analysis)

---

### 2. **Descriptive Analytics**

**File:** `analytics/02_descriptive_analytics.sql`

✅ **What Happened?**

- Total bookings per day/week/month
- Top destinations and routes
- Monthly/yearly income with growth rates
- Completed vs cancelled vs pending bookings
- Booking summary dashboard

---

### 3. **Diagnostic Analytics**

**File:** `analytics/03_diagnostic_analytics.sql`

✅ **Why Did It Happen?**

- Cancellation reasons by client type, route, time
- Underperforming buses root cause analysis
- Underperforming days identification
- Late cancellation patterns
- Price sensitivity analysis

---

### 4. **Predictive Analytics (ARIMA+)**

**File:** `analytics/04_predictive_analytics.sql`

✅ **What Will Happen?**

- **30-day booking forecast** using BigQuery ARIMA+
- **90-day revenue forecast** (monthly/quarterly)
- Peak booking season predictions
- Demand forecast by bus type
- Route demand forecasting
- Booking probability by day of week

**Key ARIMA+ Queries:**

```sql
-- Create booking forecast model
CREATE OR REPLACE MODEL `project.dataset.bookings_forecast_model`
OPTIONS(model_type='ARIMA_PLUS', ...) AS
SELECT DATE(booking_date) as booking_day, COUNT(*) as daily_bookings
FROM `project.dataset.bookings` ...

-- Generate 30-day forecast
SELECT * FROM ML.FORECAST(MODEL `project.dataset.bookings_forecast_model`,
  STRUCT(30 AS horizon, 0.95 AS confidence_level))
```

---

### 5. **Customer Behavior Analysis**

**File:** `analytics/05_customer_behavior_analytics.sql`

✅ **Customer Insights:**

- Most preferred destinations
- Peak booking hours (hourly analysis)
- Promo code usage rates
- Booking lead time patterns
- Repeat customer behavior
- Customer journey analysis
- Trip duration preferences

---

### 6. **Marketing & Promo Analytics**

**File:** `analytics/06_marketing_promo_analytics.sql`

✅ **Marketing Metrics:**

- Promo effectiveness (ROI analysis)
- Returning vs first-time customers
- Customer segmentation (high/low spenders)
- RFM Analysis (Recency, Frequency, Monetary)
- Channel attribution
- Customer acquisition cost (CAC)
- Promo cannibalization analysis

---

### 7. **Feedback Sentiment Analysis**

**File:** `analytics/07_feedback_sentiment_analytics.sql`

✅ **Sentiment Metrics:**

- Average ratings per bus/driver
- Low-rating count (complaints)
- Keyword frequency extraction
- Rating trends over time
- Sentiment by route
- Bus type sentiment comparison
- Actionable insights for low-rated buses

---

## 📈 **VISUALIZATION CHARTS**

### **Chart.js Examples**

#### 1. Revenue Trends (`charts/revenue_trends_chart.js`)

- Daily revenue line chart
- Monthly revenue bar chart with growth
- Revenue comparison: with vs without promo
- Revenue by client type (pie chart)
- Weekly revenue trends (area chart)

#### 2. Bus Performance (`charts/bus_performance_charts.js`)

- Most-used buses (horizontal bar)
- Bus revenue performance (bubble chart)
- Underperforming buses (radar chart)
- Maintenance alerts (mixed chart)
- Fleet utilization (doughnut chart)

#### 3. Customer Feedback (`charts/customer_feedback_charts.js`)

- Average ratings per bus (horizontal bar)
- Sentiment distribution (stacked bar)
- Rating trends over time (dual-axis line)
- Keyword frequency (bar chart)
- Driver performance comparison (radar)
- Complaint rate by bus type (doughnut)

---

### **ApexCharts Examples**

#### 4. Booking Patterns (`charts/booking_patterns_apexcharts.js`)

- Busiest days (column chart)
- Seasonality heatmap (monthly patterns)
- Peak hours (radial bar)
- Booking status trends (stacked area)
- Top destinations (treemap)
- Client type distribution (gradient donut)

#### 5. Predictive Analytics (`charts/predictive_analytics_apexcharts.js`)

- 30-day booking forecast with confidence intervals
- Revenue forecast (area chart with range)
- Seasonal patterns (mixed chart)
- Demand forecast by bus type (multi-line)
- Booking probability radar chart

#### 6. Marketing Analytics (`charts/marketing_customer_charts.js`)

- Promo effectiveness comparison
- RFM segmentation (scatter plot)

---

## 🗂️ **DATA STRUCTURES**

**File:** `example_data_structures.json`

Example JSON structures for feeding chart data:

- Revenue trends
- Bus usage rankings
- Maintenance alerts
- Booking forecasts
- Customer segmentation

---

## 🚀 **IMPLEMENTATION GUIDE**

### **Step 1: Setup BigQuery**

1. Replace `project.dataset` with your BigQuery project/dataset
2. Update table names: `bookings`, `buses`, `feedback`, `drivers`
3. Ensure date fields are in DATETIME/TIMESTAMP format

### **Step 2: Run SQL Queries**

```sql
-- Test revenue query
SELECT
    DATE(booking_date) as revenue_date,
    SUM(total_amount) as daily_revenue
FROM `your_project.your_dataset.bookings`
WHERE status != 'cancelled'
GROUP BY revenue_date
ORDER BY revenue_date DESC
LIMIT 30;
```

### **Step 3: Create ARIMA+ Models**

```sql
-- Step 1: Create model (one-time)
CREATE OR REPLACE MODEL `project.dataset.bookings_forecast_model`
OPTIONS(model_type='ARIMA_PLUS', ...) AS ...

-- Step 2: Generate forecasts (run anytime)
SELECT * FROM ML.FORECAST(MODEL `project.dataset.bookings_forecast_model`, ...)
```

### **Step 4: Integrate Charts**

**HTML Setup:**

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<canvas id="dailyRevenueChart"></canvas>
<div id="bookingForecastChart"></div>
```

**JavaScript Integration:**

```javascript
// Fetch data from backend
fetch('/api/analytics/revenue-daily')
  .then((res) => res.json())
  .then((data) => {
    const chart = new Chart(ctx, {
      type: 'line',
      data: { labels: data.labels, datasets: [{ data: data.values }] },
    });
  });
```

---

## 📋 **DATABASE SCHEMA ASSUMPTIONS**

### **bookings table:**

```sql
- booking_id
- booking_date (DATETIME)
- trip_date (DATETIME)
- return_date (DATETIME)
- customer_id
- bus_id
- client_type (school/company/individual)
- origin, destination
- total_amount (FLOAT)
- discount_amount (FLOAT)
- promo_code (STRING)
- status (completed/cancelled/pending)
- cancellation_reason (STRING)
- cancelled_at (DATETIME)
```

### **buses table:**

```sql
- id, bus_name, plate_number
- bus_type, capacity
- current_mileage
- last_maintenance_date (DATETIME)
- last_maintenance_mileage
- age_years
```

### **feedback table:**

```sql
- id, booking_id, bus_id, driver_id, customer_id
- rating (1-5)
- feedback_text (STRING)
- created_at (DATETIME)
- admin_response (STRING)
- responded_at (DATETIME)
```

---

## ⚡ **PERFORMANCE TIPS**

1. **Partitioning:** Partition `bookings` table by `booking_date`
2. **Clustering:** Cluster by `status`, `client_type`, `bus_id`
3. **Materialized Views:** Cache frequently-run aggregations
4. **Scheduled Queries:** Auto-refresh forecast models daily
5. **Cost Control:** Use `LIMIT` in development, remove in production

---

## 📊 **CHART CUSTOMIZATION**

### Chart.js Color Schemes:

```javascript
const colorSchemes = {
  revenue: ['#008FFB', '#00E396'],
  status: ['#00E396', '#FF4560', '#FEB019'],
  sentiment: ['#75C192', '#FEB019', '#FF4560'],
};
```

### ApexCharts Themes:

```javascript
chart: {
  theme: { mode: 'light' },
  toolbar: { show: true }
}
```

---

## 🔄 **UPDATE FREQUENCY**

| Metric                | Update Frequency   |
| --------------------- | ------------------ |
| Revenue Trends        | Real-time / Hourly |
| Booking Forecast      | Daily (scheduled)  |
| Customer Segmentation | Weekly             |
| Maintenance Alerts    | Daily              |
| Feedback Analysis     | Real-time          |

---

## ✅ **DELIVERABLES CHECKLIST**

✅ BigQuery SQL queries for all 7 analytics modules  
✅ ARIMA+ forecasting queries (bookings + revenue)  
✅ Chart.js visualization examples (6 chart types)  
✅ ApexCharts advanced visualizations (6 chart types)  
✅ Example data structures (JSON)  
✅ Implementation guide  
✅ Performance optimization tips

---

## 🛠️ **NEXT STEPS**

1. Update `project.dataset` placeholders with your BigQuery details
2. Run test queries to validate schema compatibility
3. Create ARIMA+ models (allow 5-10 min processing time)
4. Integrate charts into your dashboard frontend
5. Set up scheduled queries for auto-refresh

---

## 📞 **SUPPORT**

- Adjust SQL queries based on your exact table schema
- Modify chart colors/styles to match your brand
- Add filters (date range, client type, bus) to queries
- Implement caching for expensive queries

**Production-ready. Lightweight. No heavy ML required.**
