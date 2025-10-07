# 🔍 Where ARIMA+ is Used in the Code

## 📍 **3 Main Locations**

---

## **1️⃣ BigQuery SQL Queries** (Training & Forecasting)

### **File:** `analytics/04_predictive_analytics.sql`

#### **Location 1: Create ARIMA+ Model for Booking Forecasts**

```sql
-- Lines 7-25: Create the model
CREATE OR REPLACE MODEL `project.dataset.bookings_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',           ← ARIMA+ HERE!
    time_series_timestamp_col='booking_day',
    time_series_data_col='daily_bookings',
    auto_arima=TRUE,
    data_frequency='AUTO_FREQUENCY',
    decompose_time_series=TRUE,
    holiday_region='US'
) AS
SELECT
    DATE(booking_date) as booking_day,
    COUNT(*) as daily_bookings
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY booking_day;
```

#### **Location 2: Use ARIMA+ Model to Generate Forecasts**

```sql
-- Lines 28-41: Run forecasting
SELECT
    forecast_timestamp as forecast_date,
    forecast_value as predicted_bookings,
    prediction_interval_lower_bound as lower_bound,
    prediction_interval_upper_bound as upper_bound
FROM ML.FORECAST(                      ← ARIMA+ FORECAST HERE!
    MODEL `project.dataset.bookings_forecast_model`,
    STRUCT(30 AS horizon, 0.95 AS confidence_level)
);
```

#### **Location 3: Create ARIMA+ Model for Revenue Forecasts**

```sql
-- Lines 45-62: Income forecast model
CREATE OR REPLACE MODEL `project.dataset.income_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',           ← ARIMA+ HERE!
    time_series_timestamp_col='revenue_day',
    time_series_data_col='daily_revenue',
    auto_arima=TRUE
) AS
SELECT
    DATE(booking_date) as revenue_day,
    SUM(total_amount) as daily_revenue
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY revenue_day;
```

#### **Location 4: Generate Revenue Forecasts**

```sql
-- Lines 65-75: Revenue forecasting
SELECT
    forecast_timestamp as forecast_date,
    forecast_value as predicted_revenue,
    prediction_interval_lower_bound,
    prediction_interval_upper_bound
FROM ML.FORECAST(                      ← ARIMA+ FORECAST HERE!
    MODEL `project.dataset.income_forecast_model`,
    STRUCT(90 AS horizon, 0.95 AS confidence_level)
);
```

---

## **2️⃣ PHP Backend** (Fetch ARIMA+ Results)

### **File:** `backend_integration_example.php`

#### **Location 5: PHP Function to Get ARIMA+ Forecasts**

```php
// Lines 64-80: Fetch booking forecasts from ARIMA+ model
public function getBookingForecast($horizon = 30) {
    $query = "
        SELECT
            forecast_timestamp as forecast_date,
            forecast_value as predicted_bookings,
            prediction_interval_lower_bound as lower_bound,
            prediction_interval_upper_bound as upper_bound
        FROM ML.FORECAST(                   ← ARIMA+ USED HERE!
            MODEL `{$this->projectId}.{$this->datasetId}.bookings_forecast_model`,
            STRUCT({$horizon} AS horizon, 0.95 AS confidence_level)
        )
        ORDER BY forecast_date
    ";

    return $this->executeQuery($query);
}
```

**What this does:**

- Calls the pre-trained ARIMA+ model
- Gets predictions for next 30 days
- Returns forecast with confidence intervals
- Used by the dashboard to display predictions

---

## **3️⃣ Dashboard (Display Results)**

### **File:** `dashboard_html_example.html`

#### **Location 6: Fetch Forecast Data from API**

```javascript
// Lines 210-213: Load forecast data
const forecastRes = await fetch('api/analytics.php?endpoint=forecast-bookings');
const forecastData = await forecastRes.json();
console.log('Forecast data:', forecastData);
// This data comes from ARIMA+ model via PHP backend
```

#### **Location 7: Display ARIMA+ Predictions in Chart**

```javascript
// Lines 337-449: Create forecast chart
new Chart(forecastCtx, {
  type: 'line',
  data: {
    labels: forecastLabels, // Future dates from ARIMA+
    datasets: [
      {
        label: '📈 Predicted Bookings',
        data: forecastValues, // ARIMA+ predictions
      },
      {
        label: '📊 Upper Confidence Bound',
        data: upperBounds, // ARIMA+ upper bound
      },
      {
        label: '📊 Lower Confidence Bound',
        data: lowerBounds, // ARIMA+ lower bound
      },
    ],
  },
  options: {
    title: '🔮 Future Booking Predictions (Next 10 Days)',
    // Shows ARIMA+ forecasts visually
  },
});
```

---

## 🔄 **Complete Data Flow**

```
1. BigQuery (analytics/04_predictive_analytics.sql)
   ↓
   CREATE MODEL with ARIMA_PLUS
   ↓
   ML.FORECAST() generates predictions

2. PHP Backend (backend_integration_example.php)
   ↓
   getBookingForecast() calls ML.FORECAST()
   ↓
   Returns JSON data

3. API (api/analytics.php)
   ↓
   Endpoint: ?endpoint=forecast-bookings
   ↓
   Returns forecast data to frontend

4. Dashboard (dashboard_html_example.html)
   ↓
   Fetches forecast data
   ↓
   Creates Chart.js visualization
   ↓
   Displays ARIMA+ predictions to user
```

---

## 📊 **What Each File Does**

| File                              | What it Does                  | ARIMA+ Usage                                                       |
| --------------------------------- | ----------------------------- | ------------------------------------------------------------------ |
| `04_predictive_analytics.sql`     | **Creates** ARIMA+ models     | ✅ Trains models<br>✅ Generates forecasts                         |
| `backend_integration_example.php` | **Fetches** ARIMA+ results    | ✅ Calls ML.FORECAST()<br>✅ Returns predictions                   |
| `api/analytics.php`               | **Provides** data to frontend | ⚠️ Currently uses sample data<br>✅ Will use ARIMA+ when connected |
| `dashboard_html_example.html`     | **Displays** predictions      | ✅ Shows ARIMA+ results in charts                                  |

---

## 🎯 **How to Use ARIMA+ in Your System**

### **Step 1: Train the Model (Run Once)**

```sql
-- Run in BigQuery Console
CREATE OR REPLACE MODEL `bigquery-analytics-470015.bus_booking_analytics.bookings_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',
    time_series_timestamp_col='booking_day',
    time_series_data_col='daily_bookings',
    auto_arima=TRUE
) AS
SELECT
    DATE(booking_date) as booking_day,
    COUNT(*) as daily_bookings
FROM `bigquery-analytics-470015.bus_booking_analytics.bookings`
WHERE status != 'cancelled'
GROUP BY booking_day;
```

### **Step 2: Get Predictions (Anytime)**

```sql
-- Run this to get forecasts
SELECT * FROM ML.FORECAST(
    MODEL `bigquery-analytics-470015.bus_booking_analytics.bookings_forecast_model`,
    STRUCT(30 AS horizon, 0.95 AS confidence_level)
);
```

### **Step 3: Use in Dashboard**

```php
// In your analytics API
$analytics = new BusBookingAnalytics();
$forecasts = $analytics->getBookingForecast(30);
echo json_encode($forecasts);
```

---

## 🔍 **Quick Find Guide**

### **To Create ARIMA+ Model:**

- **File:** `analytics/04_predictive_analytics.sql`
- **Lines:** 7-25 (booking model), 45-62 (revenue model)
- **Keyword:** `model_type='ARIMA_PLUS'`

### **To Generate Forecasts:**

- **File:** `analytics/04_predictive_analytics.sql`
- **Lines:** 28-41 (booking forecast), 65-75 (revenue forecast)
- **Keyword:** `ML.FORECAST(MODEL ...)`

### **To Fetch in PHP:**

- **File:** `backend_integration_example.php`
- **Lines:** 64-80
- **Function:** `getBookingForecast()`

### **To Display in Dashboard:**

- **File:** `dashboard_html_example.html`
- **Lines:** 210-213 (fetch), 337-449 (chart)
- **Chart:** Forecast chart with confidence intervals

---

## 💡 **Search Commands**

### **Find all ARIMA+ usage:**

```bash
# In terminal/command prompt
grep -r "ARIMA" .
grep -r "ML.FORECAST" .
grep -r "forecast_model" .
```

### **In VS Code:**

Press `Ctrl+Shift+F` and search for:

- `ARIMA_PLUS`
- `ML.FORECAST`
- `bookings_forecast_model`

---

## 📝 **Summary**

### **ARIMA+ is used in 3 stages:**

1. **Training** (SQL)

   - Create model from historical data
   - Files: `04_predictive_analytics.sql`

2. **Prediction** (SQL + PHP)

   - Generate forecasts using trained model
   - Files: `04_predictive_analytics.sql`, `backend_integration_example.php`

3. **Visualization** (JavaScript)
   - Display predictions in charts
   - Files: `dashboard_html_example.html`

**All ARIMA+ magic happens in BigQuery - the code just calls it and displays the results!** 🎯
