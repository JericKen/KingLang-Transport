-- ==========================================
-- PREDICTIVE ANALYTICS (What Will Happen?)
-- ==========================================

-- 1. FORECAST BOOKINGS (30 DAYS) USING ARIMA+
-- Step 1: Create training data (historical daily bookings)
CREATE OR REPLACE MODEL `project.dataset.bookings_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',
    time_series_timestamp_col='booking_day',
    time_series_data_col='daily_bookings',
    auto_arima=TRUE,
    data_frequency='AUTO_FREQUENCY',
    decompose_time_series=TRUE,
    holiday_region='PH'
) AS
SELECT 
    DATE(booking_date) as booking_day,
    COUNT(*) as daily_bookings
FROM `project.dataset.bookings`
WHERE 
    status != 'cancelled'
    AND DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
GROUP BY booking_day
ORDER BY booking_day;

-- Step 2: Generate 30-day forecast
SELECT
    forecast_timestamp as forecast_date,
    forecast_value as predicted_bookings,
    standard_error,
    confidence_level,
    prediction_interval_lower_bound as lower_bound,
    prediction_interval_upper_bound as upper_bound,
    confidence_interval_lower_bound,
    confidence_interval_upper_bound
FROM
    ML.FORECAST(MODEL `project.dataset.bookings_forecast_model`,
        STRUCT(30 AS horizon, 0.95 AS confidence_level)
    )
ORDER BY forecast_date;

-- 2. FORECAST INCOME FOR NEXT MONTH/QUARTER
-- Step 1: Create income forecast model
CREATE OR REPLACE MODEL `project.dataset.income_forecast_model`
OPTIONS(
    model_type='ARIMA_PLUS',
    time_series_timestamp_col='revenue_day',
    time_series_data_col='daily_revenue',
    auto_arima=TRUE,
    data_frequency='AUTO_FREQUENCY',
    decompose_time_series=TRUE
) AS
SELECT 
    DATE(booking_date) as revenue_day,
    SUM(total_amount) as daily_revenue
FROM `project.dataset.bookings`
WHERE 
    status != 'cancelled'
    AND DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
GROUP BY revenue_day
ORDER BY revenue_day;

-- Step 2: Generate income forecast (90 days for quarterly)
SELECT
    forecast_timestamp as forecast_date,
    forecast_value as predicted_revenue,
    standard_error,
    prediction_interval_lower_bound as lower_revenue_bound,
    prediction_interval_upper_bound as upper_revenue_bound
FROM
    ML.FORECAST(MODEL `project.dataset.income_forecast_model`,
        STRUCT(90 AS horizon, 0.95 AS confidence_level)
    )
ORDER BY forecast_date;

-- Step 3: Aggregate forecast to monthly/quarterly summary
WITH daily_forecast AS (
    SELECT
        forecast_timestamp as forecast_date,
        forecast_value as predicted_revenue
    FROM ML.FORECAST(MODEL `project.dataset.income_forecast_model`,
        STRUCT(90 AS horizon, 0.95 AS confidence_level))
)
SELECT 
    FORMAT_DATE('%Y-%m', forecast_date) as forecast_month,
    SUM(predicted_revenue) as predicted_monthly_revenue,
    COUNT(*) as days_in_month,
    AVG(predicted_revenue) as avg_daily_revenue
FROM daily_forecast
GROUP BY forecast_month
ORDER BY forecast_month;

-- Quarterly Forecast
WITH daily_forecast AS (
    SELECT
        forecast_timestamp as forecast_date,
        forecast_value as predicted_revenue
    FROM ML.FORECAST(MODEL `project.dataset.income_forecast_model`,
        STRUCT(90 AS horizon, 0.95 AS confidence_level))
)
SELECT 
    CONCAT('Q', CAST(EXTRACT(QUARTER FROM forecast_date) AS STRING), '-', CAST(EXTRACT(YEAR FROM forecast_date) AS STRING)) as quarter,
    SUM(predicted_revenue) as predicted_quarterly_revenue,
    COUNT(*) as days_in_quarter,
    AVG(predicted_revenue) as avg_daily_revenue
FROM daily_forecast
GROUP BY quarter
ORDER BY quarter;

-- 3. PEAK BOOKING SEASONS (Holidays, School Trip Months)
-- Historical Seasonal Patterns
SELECT 
    EXTRACT(MONTH FROM DATE(booking_date)) as month_number,
    FORMAT_DATE('%B', DATE(booking_date)) as month_name,
    COUNT(*) as total_bookings,
    SUM(total_amount) as total_revenue,
    AVG(COUNT(*)) OVER () as avg_monthly_bookings,
    ROUND((COUNT(*) - AVG(COUNT(*)) OVER ()) * 100.0 / AVG(COUNT(*)) OVER (), 2) as variance_percentage,
    CASE 
        WHEN COUNT(*) > AVG(COUNT(*)) OVER () * 1.2 THEN 'Peak Season'
        WHEN COUNT(*) < AVG(COUNT(*)) OVER () * 0.8 THEN 'Low Season'
        ELSE 'Normal Season'
    END as season_category
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY month_number, month_name
ORDER BY month_number;

-- School Trip Months Analysis (typically March-May, September-October)
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    client_type,
    COUNT(*) as booking_count,
    SUM(total_amount) as revenue,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (PARTITION BY FORMAT_DATE('%Y-%m', DATE(booking_date))), 2) as percentage_of_month
FROM `project.dataset.bookings`
WHERE 
    status != 'cancelled'
    AND client_type = 'school'
GROUP BY month, client_type
ORDER BY month DESC;

-- Holiday Season Identification (based on booking spikes)
WITH daily_bookings AS (
    SELECT 
        DATE(booking_date) as booking_day,
        COUNT(*) as daily_count,
        SUM(total_amount) as daily_revenue,
        AVG(COUNT(*)) OVER (ORDER BY DATE(booking_date) ROWS BETWEEN 7 PRECEDING AND 7 FOLLOWING) as moving_avg
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY booking_day
)
SELECT 
    booking_day,
    FORMAT_DATE('%A, %B %d', booking_day) as date_formatted,
    daily_count,
    daily_revenue,
    ROUND(moving_avg, 2) as seven_day_avg,
    ROUND((daily_count - moving_avg) * 100.0 / moving_avg, 2) as spike_percentage
FROM daily_bookings
WHERE daily_count > moving_avg * 1.5  -- 50% above moving average
ORDER BY booking_day DESC
LIMIT 50;

-- 4. SEASONAL DECOMPOSITION FOR TREND ANALYSIS
-- Evaluate ARIMA model components
SELECT
    *
FROM
    ML.ARIMA_EVALUATE(MODEL `project.dataset.bookings_forecast_model`);

-- Extract seasonality and trend components
SELECT
    *
FROM
    ML.EXPLAIN_FORECAST(MODEL `project.dataset.bookings_forecast_model`,
        STRUCT(30 AS horizon, 0.95 AS confidence_level)
    )
ORDER BY time_series_timestamp;

-- 5. DEMAND FORECASTING BY BUS TYPE
-- Create model for each bus type
CREATE OR REPLACE MODEL `project.dataset.demand_by_bus_type_model`
OPTIONS(
    model_type='ARIMA_PLUS',
    time_series_timestamp_col='booking_day',
    time_series_data_col='booking_count',
    time_series_id_col='bus_type',
    auto_arima=TRUE,
    data_frequency='AUTO_FREQUENCY'
) AS
SELECT 
    DATE(b.booking_date) as booking_day,
    bus.bus_type,
    COUNT(*) as booking_count
FROM `project.dataset.bookings` b
LEFT JOIN `project.dataset.buses` bus ON b.bus_id = bus.id
WHERE 
    b.status != 'cancelled'
    AND DATE(b.booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
    AND bus.bus_type IS NOT NULL
GROUP BY booking_day, bus.bus_type
ORDER BY bus.bus_type, booking_day;

-- Forecast by bus type
SELECT
    forecast_timestamp as forecast_date,
    bus_type,
    forecast_value as predicted_bookings,
    prediction_interval_lower_bound as lower_bound,
    prediction_interval_upper_bound as upper_bound
FROM
    ML.FORECAST(MODEL `project.dataset.demand_by_bus_type_model`,
        STRUCT(30 AS horizon, 0.95 AS confidence_level)
    )
ORDER BY bus_type, forecast_date;

-- 6. ROUTE DEMAND FORECAST (Top Routes)
-- Identify top routes for forecasting
WITH top_routes AS (
    SELECT 
        CONCAT(origin, ' → ', destination) as route,
        COUNT(*) as total_bookings
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY route
    ORDER BY total_bookings DESC
    LIMIT 10
)
SELECT 
    DATE(b.booking_date) as booking_day,
    CONCAT(b.origin, ' → ', b.destination) as route,
    COUNT(*) as booking_count,
    SUM(b.total_amount) as daily_revenue
FROM `project.dataset.bookings` b
INNER JOIN top_routes tr ON CONCAT(b.origin, ' → ', b.destination) = tr.route
WHERE 
    b.status != 'cancelled'
    AND DATE(b.booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 180 DAY)
GROUP BY booking_day, route
ORDER BY route, booking_day;

-- 7. BOOKING PROBABILITY BY DAY OF WEEK
SELECT 
    EXTRACT(DAYOFWEEK FROM DATE(booking_date)) as day_number,
    FORMAT_DATE('%A', DATE(booking_date)) as day_name,
    COUNT(*) as historical_bookings,
    AVG(total_amount) as avg_revenue,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as probability_percentage,
    ROUND(AVG(COUNT(*)) OVER (), 2) as expected_daily_avg
FROM `project.dataset.bookings`
WHERE 
    status != 'cancelled'
    AND DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
GROUP BY day_number, day_name
ORDER BY day_number;

