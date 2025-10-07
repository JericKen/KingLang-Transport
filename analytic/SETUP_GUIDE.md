# 🚀 BigQuery Setup & Configuration Guide

## Step-by-Step Instructions

### **Step 1: Get Your BigQuery Credentials**

1. **Go to Google Cloud Console:**

   - Visit: https://console.cloud.google.com/

2. **Select or Create a Project:**

   - Click the project dropdown at the top
   - Note your **Project ID** (e.g., `my-bus-company-2024`)

3. **Create a Dataset:**

   - Go to BigQuery: https://console.cloud.google.com/bigquery
   - Click "Create Dataset"
   - Choose a **Dataset ID** (e.g., `bus_analytics`)
   - Select your region
   - Click "Create Dataset"

4. **Note Your Table Names:**
   - If you have existing tables, note their exact names
   - Default expected names: `bookings`, `buses`, `feedback`, `drivers`

---

### **Step 2: Update Configuration (Choose One Method)**

#### **Method A: Automated Script (Fastest) ⚡**

1. **Edit `update_bigquery_config.js`:**

   ```javascript
   const config = {
     projectId: 'my-bus-company-2024', // ← Your Project ID
     datasetId: 'bus_analytics', // ← Your Dataset ID

     tableNames: {
       bookings: 'bookings', // ← Change if different
       buses: 'buses',
       feedback: 'feedback',
       drivers: 'drivers',
     },
   };
   ```

2. **Run the script:**

   - **Windows:** Double-click `update_bigquery_config.bat`
   - **Mac/Linux:** Run `node update_bigquery_config.js`

3. **Done!** ✅ All files updated automatically

---

#### **Method B: Manual Find & Replace (Full Control)**

1. **Open VS Code Find & Replace:**

   - Press `Ctrl + Shift + H` (Windows) or `Cmd + Shift + H` (Mac)

2. **Replace Project & Dataset:**

   ```
   Find:    project.dataset
   Replace: my-bus-company-2024.bus_analytics
   Files:   analytics/**/*.sql
   ```

3. **Click "Replace All"**

4. **Update Table Names (if different):**
   ```
   Find:    .bookings
   Replace: .my_custom_bookings_table
   Files:   analytics/**/*.sql
   ```

---

### **Step 3: Update Backend Configuration**

#### **For PHP (backend_integration_example.php):**

```php
public function __construct() {
    $this->projectId = 'my-bus-company-2024';  // ← Your Project ID
    $this->datasetId = 'bus_analytics';        // ← Your Dataset ID
    $this->bigQuery = new BigQueryClient([
        'projectId' => $this->projectId,
        'keyFilePath' => 'path/to/service-account-key.json'  // ← Update path
    ]);
}
```

---

### **Step 4: Get Service Account Credentials**

1. **Go to IAM & Admin > Service Accounts:**

   - https://console.cloud.google.com/iam-admin/serviceaccounts

2. **Create Service Account:**

   - Click "Create Service Account"
   - Name: `bus-analytics-api`
   - Click "Create and Continue"

3. **Grant Permissions:**

   - Role: `BigQuery Data Viewer`
   - Role: `BigQuery Job User`
   - Click "Continue" → "Done"

4. **Create JSON Key:**
   - Click on the service account
   - Go to "Keys" tab
   - Click "Add Key" → "Create new key"
   - Choose "JSON"
   - Download the JSON file
   - Save as `service-account-key.json` in your project

---

### **Step 5: Test Your Configuration**

#### **Test in BigQuery Console:**

```sql
-- Copy this query to BigQuery console
SELECT
    DATE(booking_date) as date,
    COUNT(*) as bookings,
    SUM(total_amount) as revenue
FROM `my-bus-company-2024.bus_analytics.bookings`  -- ← Your full path
WHERE status != 'cancelled'
GROUP BY date
ORDER BY date DESC
LIMIT 10;
```

#### **Test Expected Output:**

```
date        bookings  revenue
2025-10-03  25        450000
2025-10-02  32        580000
...
```

---

### **Step 6: Create ARIMA+ Models**

#### **Run these queries in BigQuery console:**

```sql
-- 1. Create Booking Forecast Model
CREATE OR REPLACE MODEL `my-bus-company-2024.bus_analytics.bookings_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',
    time_series_timestamp_col='booking_day',
    time_series_data_col='daily_bookings',
    auto_arima=TRUE,
    data_frequency='AUTO_FREQUENCY'
) AS
SELECT
    DATE(booking_date) as booking_day,
    COUNT(*) as daily_bookings
FROM `my-bus-company-2024.bus_analytics.bookings`
WHERE
    status != 'cancelled'
    AND DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
GROUP BY booking_day
ORDER BY booking_day;
```

⏱️ **Wait 5-10 minutes for model training...**

```sql
-- 2. Test the forecast
SELECT
    forecast_timestamp as forecast_date,
    forecast_value as predicted_bookings,
    prediction_interval_lower_bound,
    prediction_interval_upper_bound
FROM ML.FORECAST(
    MODEL `my-bus-company-2024.bus_analytics.bookings_forecast_model`,
    STRUCT(30 AS horizon, 0.95 AS confidence_level)
)
ORDER BY forecast_date;
```

---

## 🔍 **Verification Checklist**

- [ ] Project ID updated in all SQL files
- [ ] Dataset ID updated in all SQL files
- [ ] Table names match your database
- [ ] Service account JSON key downloaded
- [ ] PHP backend credentials updated
- [ ] Test query runs successfully
- [ ] ARIMA+ model created (if using forecasting)

---

## 🛠️ **Troubleshooting**

### **Error: "Table not found"**

✅ Check table name spelling in BigQuery console
✅ Verify full path: `project-id.dataset-id.table-name`

### **Error: "Access Denied"**

✅ Check service account has correct roles
✅ Verify JSON key path is correct

### **Error: "Invalid project ID"**

✅ Use Project ID (not Project Name)
✅ Find correct ID in Cloud Console project dropdown

### **ARIMA+ model fails**

✅ Ensure you have at least 30 days of historical data
✅ Check for NULL values in date/value columns
✅ Verify BigQuery ML is enabled in your project

---

## 📚 **Quick Reference**

### **Your Configuration Summary:**

```javascript
Project ID:  ____________________
Dataset ID:  ____________________
Table Names:
  - Bookings:  ____________________
  - Buses:     ____________________
  - Feedback:  ____________________
  - Drivers:   ____________________
```

### **Common Paths:**

```sql
-- Full table path format
`project-id.dataset-id.table-name`

-- Example
`my-bus-company-2024.bus_analytics.bookings`
```

---

## ✅ **Next Steps After Setup**

1. Run the SQL queries from `analytics/` folder
2. Set up scheduled queries for daily updates
3. Integrate charts into your dashboard
4. Configure backend API endpoints
5. Test the complete analytics pipeline

---

## 📞 **Need Help?**

- BigQuery Documentation: https://cloud.google.com/bigquery/docs
- BigQuery ML Guide: https://cloud.google.com/bigquery-ml/docs
- Service Accounts: https://cloud.google.com/iam/docs/service-accounts

**Remember:** Always use your actual Project ID and Dataset ID, not the placeholders!
