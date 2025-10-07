# 🚀 Integration Guide - Add Analytics to Your Existing Booking System

## 📋 **Overview**

This guide will help you integrate the analytics dashboard into your existing bus booking system with **real data**.

---

## 🎯 **What You Need**

### **From Your Existing System:**

- ✅ Database with booking data (MySQL, PostgreSQL, etc.)
- ✅ Table structure for: bookings, buses, customers, feedback
- ✅ PHP backend (or any language that can query your DB)

### **What We'll Add:**

- ✅ Analytics dashboard (already built)
- ✅ API endpoints to fetch your real data
- ✅ BigQuery integration (optional, for ML forecasting)

---

## 📊 **Integration Options**

### **Option 1: Quick Integration (Use Your Existing Database)**

**Best for:** Getting started quickly, no BigQuery needed  
**Time:** 1-2 hours  
**Features:** All analytics except ARIMA+ forecasting

### **Option 2: Full Integration (BigQuery + ML Forecasting)**

**Best for:** Production-ready with predictions  
**Time:** 4-6 hours  
**Features:** Everything including ML forecasting

---

## 🔧 **Option 1: Quick Integration (Your Database)**

### **Step 1: Copy Files to Your System**

```bash
# Copy these files to your project:
📁 Your_Booking_System/
├── 📁 analytics/
│   └── dashboard.php              ← Main dashboard page
├── 📁 api/
│   └── analytics_api.php          ← API endpoints
├── 📁 assets/
│   ├── 📁 css/
│   │   └── dashboard.css
│   └── 📁 js/
│       └── charts.js
```

### **Step 2: Create Analytics API (Connect to Your DB)**

Create `api/analytics_api.php`:

```php
<?php
// Connect to YOUR existing database
require_once __DIR__ . '/../config/database.php'; // Your DB config

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get your existing DB connection
// Example: $conn = mysqli_connect($host, $user, $pass, $dbname);
// Or use your existing connection method

$endpoint = $_GET['endpoint'] ?? 'revenue-daily';

switch ($endpoint) {
    case 'revenue-daily':
        // Query YOUR bookings table
        $query = "
            SELECT
                DATE(booking_date) as revenue_date,
                COUNT(*) as booking_count,
                SUM(total_amount) as daily_revenue
            FROM bookings
            WHERE status != 'cancelled'
              AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(booking_date)
            ORDER BY revenue_date DESC
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'buses-most-used':
        $query = "
            SELECT
                b.bus_id,
                b.bus_name,
                COUNT(bk.id) as booking_count,
                SUM(bk.total_amount) as total_revenue
            FROM buses b
            LEFT JOIN bookings bk ON b.bus_id = bk.bus_id
            WHERE bk.status != 'cancelled'
            GROUP BY b.bus_id, b.bus_name
            ORDER BY booking_count DESC
            LIMIT 10
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'busiest-days':
        $query = "
            SELECT
                DAYNAME(booking_date) as day_of_week,
                COUNT(*) as booking_count,
                AVG(total_amount) as avg_revenue
            FROM bookings
            WHERE status != 'cancelled'
              AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY DAYNAME(booking_date), DAYOFWEEK(booking_date)
            ORDER BY DAYOFWEEK(booking_date)
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'destinations-top':
        $query = "
            SELECT
                destination,
                COUNT(*) as booking_count,
                SUM(total_amount) as total_revenue
            FROM bookings
            WHERE status != 'cancelled'
            GROUP BY destination
            ORDER BY booking_count DESC
            LIMIT 10
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'maintenance-alerts':
        $query = "
            SELECT
                bus_id,
                bus_name,
                current_mileage - COALESCE(last_maintenance_mileage, 0) as mileage_since_maintenance,
                DATEDIFF(CURDATE(), COALESCE(last_maintenance_date, '2000-01-01')) as days_since_maintenance,
                CASE
                    WHEN (current_mileage - COALESCE(last_maintenance_mileage, 0)) > 10000
                         OR DATEDIFF(CURDATE(), COALESCE(last_maintenance_date, '2000-01-01')) > 180
                    THEN 'URGENT'
                    WHEN (current_mileage - COALESCE(last_maintenance_mileage, 0)) > 8000
                         OR DATEDIFF(CURDATE(), COALESCE(last_maintenance_date, '2000-01-01')) > 150
                    THEN 'WARNING'
                    ELSE 'OK'
                END as status
            FROM buses
            HAVING status IN ('URGENT', 'WARNING')
            ORDER BY
                CASE status
                    WHEN 'URGENT' THEN 1
                    WHEN 'WARNING' THEN 2
                END
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'bus-ratings':
        $query = "
            SELECT
                b.bus_name,
                AVG(f.rating) as avg_rating,
                COUNT(f.id) as total_ratings
            FROM buses b
            LEFT JOIN feedback f ON b.bus_id = f.bus_id
            GROUP BY b.bus_id, b.bus_name
            HAVING total_ratings > 0
            ORDER BY avg_rating DESC
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'feedback-keywords':
        // Simple keyword extraction from feedback
        $query = "
            SELECT
                LOWER(comment) as comment
            FROM feedback
            WHERE comment IS NOT NULL
              AND comment != ''
        ";
        $result = mysqli_query($conn, $query);

        // Count keyword frequencies
        $keywords = [];
        $commonWords = ['clean', 'comfortable', 'on-time', 'late', 'friendly',
                       'professional', 'safe', 'dirty', 'rude', 'spacious'];

        while ($row = mysqli_fetch_assoc($result)) {
            foreach ($commonWords as $word) {
                if (stripos($row['comment'], $word) !== false) {
                    $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                }
            }
        }

        arsort($keywords);
        $data = [];
        foreach (array_slice($keywords, 0, 10) as $keyword => $frequency) {
            $data[] = ['keyword' => ucfirst($keyword), 'frequency' => $frequency];
        }
        echo json_encode($data);
        break;

    case 'monthly-income':
        $query = "
            SELECT
                DATE_FORMAT(booking_date, '%Y-%m') as month,
                SUM(total_amount) as total_income,
                COUNT(*) as booking_count
            FROM bookings
            WHERE status != 'cancelled'
              AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
            ORDER BY month DESC
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    case 'booking-status':
        $query = "
            SELECT
                status,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM bookings), 1) as percentage
            FROM bookings
            GROUP BY status
            ORDER BY count DESC
        ";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        break;

    default:
        echo json_encode(['error' => 'Invalid endpoint']);
}

mysqli_close($conn);
?>
```

### **Step 3: Create Dashboard Page**

Create `analytics/dashboard.php`:

```php
<?php
// Include your existing auth/session checks
session_start();
require_once __DIR__ . '/../includes/auth.php'; // Your auth system

// Check if user is admin (adjust based on your system)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - <?php echo SITE_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card { margin-bottom: 20px; }
        .kpi-card { text-align: center; padding: 20px; }
        .kpi-value { font-size: 2.5rem; font-weight: bold; }
        .kpi-label { color: #6c757d; }
    </style>
</head>
<body>
    <!-- Include your existing navigation -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <h1>📊 Analytics Dashboard</h1>

        <!-- Copy the dashboard HTML from dashboard_html_example.html here -->
        <!-- Just paste the content inside the <body> tag -->

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card kpi-card">
                    <div class="kpi-value" id="totalRevenue">₱0</div>
                    <div class="kpi-label">Total Revenue</div>
                </div>
            </div>
            <!-- ... more KPI cards ... -->
        </div>

        <!-- Charts (copy from dashboard_html_example.html) -->
        <!-- ... -->

    </div>

    <script>
        // Copy the JavaScript from dashboard_html_example.html
        // Update API URL to point to your analytics_api.php

        async function loadDashboardData() {
            try {
                // Update this URL to match your project structure
                const baseURL = '/api/analytics_api.php';

                const revenueRes = await fetch(`${baseURL}?endpoint=revenue-daily`);
                const revenueData = await revenueRes.json();

                // ... rest of the code from dashboard_html_example.html
            } catch (error) {
                console.error('Error:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>
</html>
```

### **Step 4: Add Menu Link**

In your existing navigation/menu file:

```php
<!-- Add to your admin menu -->
<?php if ($_SESSION['user_role'] === 'admin'): ?>
    <li class="nav-item">
        <a class="nav-link" href="/analytics/dashboard.php">
            📊 Analytics
        </a>
    </li>
<?php endif; ?>
```

### **Step 5: Test the Integration**

1. **Test API endpoints:**

   ```
   http://yoursite.com/api/analytics_api.php?endpoint=revenue-daily
   http://yoursite.com/api/analytics_api.php?endpoint=buses-most-used
   ```

2. **Open dashboard:**

   ```
   http://yoursite.com/analytics/dashboard.php
   ```

3. **Check browser console** for any errors

---

## 🚀 **Option 2: Full Integration (with BigQuery)**

### **Step 1: Export Your Data to BigQuery**

```php
// create: scripts/export_to_bigquery.php

<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Google\Cloud\BigQuery\BigQueryClient;

$bigQuery = new BigQueryClient([
    'projectId' => 'bigquery-analytics-470015',
    'keyFilePath' => __DIR__ . '/../storage/google-credentials.json'
]);

$dataset = $bigQuery->dataset('bus_booking_analytics');

// Export bookings table
$query = "SELECT * FROM bookings";
$result = mysqli_query($conn, $query);

$table = $dataset->table('bookings');
$rows = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = ['data' => $row];
}

// Insert into BigQuery
$table->insertRows($rows);

echo "✅ Exported " . count($rows) . " bookings to BigQuery\n";
?>
```

### **Step 2: Set Up Automated Sync**

Create a cron job to sync data daily:

```bash
# Add to crontab: crontab -e
0 2 * * * php /path/to/your/project/scripts/export_to_bigquery.php
```

### **Step 3: Use BigQuery for Analytics**

Update your `analytics_api.php` to use BigQuery for complex queries:

```php
case 'forecast-bookings':
    require_once __DIR__ . '/../vendor/autoload.php';

    $bigQuery = new \Google\Cloud\BigQuery\BigQueryClient([
        'projectId' => 'bigquery-analytics-470015',
        'keyFilePath' => __DIR__ . '/../storage/google-credentials.json'
    ]);

    $query = "
        SELECT
            forecast_timestamp as forecast_date,
            forecast_value as predicted_bookings,
            prediction_interval_lower_bound as lower_bound,
            prediction_interval_upper_bound as upper_bound
        FROM
            ML.FORECAST(MODEL `bigquery-analytics-470015.bus_booking_analytics.bookings_forecast_model`,
                STRUCT(30 AS horizon, 0.95 AS confidence_level)
            )
        ORDER BY forecast_date
    ";

    $queryJobConfig = $bigQuery->query($query);
    $queryResults = $bigQuery->runQuery($queryJobConfig);

    $forecasts = [];
    foreach ($queryResults as $row) {
        $forecasts[] = [
            'forecast_date' => $row['forecast_date']->format('Y-m-d'),
            'predicted_bookings' => round($row['predicted_bookings']),
            'lower_bound' => round($row['lower_bound']),
            'upper_bound' => round($row['upper_bound'])
        ];
    }

    echo json_encode($forecasts);
    break;
```

---

## 📋 **Checklist for Integration**

### **Quick Integration (Your DB):**

- [ ] Copy dashboard files to your project
- [ ] Create `analytics_api.php` with your DB connection
- [ ] Update SQL queries to match your table structure
- [ ] Create dashboard page with your auth system
- [ ] Add menu link to analytics
- [ ] Test all endpoints
- [ ] Test dashboard displays

### **Full Integration (BigQuery):**

- [ ] Install Google Cloud BigQuery PHP library
- [ ] Create export script
- [ ] Export existing data to BigQuery
- [ ] Set up cron job for daily sync
- [ ] Create ARIMA+ model in BigQuery
- [ ] Update API to use BigQuery for forecasting
- [ ] Test predictions

---

## 🔑 **Important: Table Structure**

Make sure your database has these tables (or adjust queries):

### **Required Tables:**

```sql
-- Bookings table
bookings (
    id, booking_date, total_amount, status,
    bus_id, destination, customer_id
)

-- Buses table
buses (
    bus_id, bus_name, current_mileage,
    last_maintenance_date, last_maintenance_mileage
)

-- Feedback table (optional)
feedback (
    id, bus_id, rating, comment, created_at
)
```

If your table names or columns are different, update the SQL queries accordingly!

---

## 🆘 **Need Help?**

### **Common Issues:**

1. **SQL errors?** → Adjust table/column names in queries
2. **No data showing?** → Check API endpoints return JSON
3. **Charts not rendering?** → Open browser console (F12)
4. **Permission denied?** → Check user role/auth system

### **Quick Debug:**

```php
// Add to top of analytics_api.php for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
var_dump($conn);

// Test query results
var_dump(mysqli_fetch_all($result, MYSQLI_ASSOC));
```

---

## 🎯 **Next Steps**

1. **Choose integration option** (Quick or Full)
2. **Follow the steps** for your chosen option
3. **Test with your real data**
4. **Customize colors/branding** to match your system
5. **Add more analytics** as needed

**I can help you with any step! Just share your table structure and I'll customize the queries for you.** 🚀

---

**Ready to integrate? Which option do you want to start with?** 💪
