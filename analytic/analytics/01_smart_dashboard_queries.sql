-- ==========================================
-- SMART ANALYTICS DASHBOARD QUERIES
-- ==========================================

-- 1. REVENUE TRENDS (Daily, Weekly, Monthly)
-- Daily Revenue
SELECT 
    DATE(booking_date) as revenue_date,
    COUNT(*) as total_bookings,
    SUM(total_amount) as daily_revenue,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY revenue_date
ORDER BY revenue_date DESC;

-- Weekly Revenue
SELECT 
    DATE_TRUNC(DATE(booking_date), WEEK) as week_start,
    COUNT(*) as total_bookings,
    SUM(total_amount) as weekly_revenue,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY week_start
ORDER BY week_start DESC;

-- Monthly Revenue
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    COUNT(*) as total_bookings,
    SUM(total_amount) as monthly_revenue,
    AVG(total_amount) as avg_booking_value,
    SUM(total_amount) - LAG(SUM(total_amount)) OVER (ORDER BY FORMAT_DATE('%Y-%m', DATE(booking_date))) as revenue_change
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY month
ORDER BY month DESC;

-- 2. MOST-USED BUSES (Ranked by Booking Count)
SELECT 
    b.bus_id,
    bus.bus_name,
    bus.plate_number,
    bus.capacity,
    COUNT(*) as booking_count,
    SUM(b.total_amount) as total_revenue,
    AVG(b.total_amount) as avg_revenue_per_booking,
    RANK() OVER (ORDER BY COUNT(*) DESC) as usage_rank
FROM `project.dataset.bookings` b
LEFT JOIN `project.dataset.buses` bus ON b.bus_id = bus.id
WHERE b.status != 'cancelled'
GROUP BY b.bus_id, bus.bus_name, bus.plate_number, bus.capacity
ORDER BY booking_count DESC;

-- 3. BUSIEST BOOKING DAYS
-- By Day of Week
SELECT 
    FORMAT_DATE('%A', DATE(booking_date)) as day_of_week,
    EXTRACT(DAYOFWEEK FROM DATE(booking_date)) as day_number,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY day_of_week, day_number
ORDER BY day_number;

-- Seasonality Analysis (Monthly Patterns)
SELECT 
    EXTRACT(MONTH FROM DATE(booking_date)) as month_number,
    FORMAT_DATE('%B', DATE(booking_date)) as month_name,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(COUNT(*)) OVER () as avg_bookings_per_month,
    COUNT(*) - AVG(COUNT(*)) OVER () as variance_from_avg
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY month_number, month_name
ORDER BY month_number;

-- 4. TOP CLIENT TYPES
SELECT 
    client_type,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY client_type
ORDER BY booking_count DESC;

-- 5. UPCOMING MAINTENANCE ALERTS
SELECT 
    bus.id,
    bus.bus_name,
    bus.plate_number,
    bus.current_mileage,
    bus.last_maintenance_date,
    bus.last_maintenance_mileage,
    bus.current_mileage - bus.last_maintenance_mileage as mileage_since_maintenance,
    DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) as days_since_maintenance,
    CASE 
        WHEN bus.current_mileage - bus.last_maintenance_mileage > 10000 THEN 'URGENT: Mileage Exceeded'
        WHEN DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 180 THEN 'URGENT: Time Exceeded'
        WHEN bus.current_mileage - bus.last_maintenance_mileage > 8000 THEN 'WARNING: Approaching Mileage Limit'
        WHEN DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 150 THEN 'WARNING: Approaching Time Limit'
        ELSE 'OK'
    END as maintenance_status
FROM `project.dataset.buses` bus
WHERE 
    bus.current_mileage - bus.last_maintenance_mileage > 8000
    OR DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 150
ORDER BY 
    CASE 
        WHEN bus.current_mileage - bus.last_maintenance_mileage > 10000 THEN 1
        WHEN DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 180 THEN 1
        ELSE 2
    END,
    bus.current_mileage - bus.last_maintenance_mileage DESC;

-- 6. CUSTOMER FEEDBACK ANALYSIS
-- Average Ratings
SELECT 
    fb.bus_id,
    bus.bus_name,
    COUNT(*) as feedback_count,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as low_rating_count,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as high_rating_count,
    ROUND(COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) * 100.0 / COUNT(*), 2) as low_rating_percentage
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.buses` bus ON fb.bus_id = bus.id
GROUP BY fb.bus_id, bus.bus_name
ORDER BY avg_rating DESC;

-- Most Common Feedback (Keyword Frequency - if feedback_text exists)
SELECT 
    LOWER(TRIM(word)) as keyword,
    COUNT(*) as frequency
FROM `project.dataset.feedback`,
UNNEST(SPLIT(feedback_text, ' ')) as word
WHERE LENGTH(TRIM(word)) > 3
GROUP BY keyword
ORDER BY frequency DESC
LIMIT 20;

-- 7. UNDERPERFORMING BUSES/DAYS
-- Underperforming Buses
WITH bus_performance AS (
    SELECT 
        b.bus_id,
        bus.bus_name,
        COUNT(*) as booking_count,
        SUM(b.total_amount) as total_revenue,
        AVG(COUNT(*)) OVER () as avg_bookings,
        AVG(SUM(b.total_amount)) OVER () as avg_revenue
    FROM `project.dataset.bookings` b
    LEFT JOIN `project.dataset.buses` bus ON b.bus_id = bus.id
    WHERE b.status != 'cancelled'
    GROUP BY b.bus_id, bus.bus_name
)
SELECT 
    bus_id,
    bus_name,
    booking_count,
    total_revenue,
    ROUND(avg_bookings, 2) as avg_bookings_benchmark,
    ROUND(avg_revenue, 2) as avg_revenue_benchmark,
    booking_count - avg_bookings as bookings_vs_avg,
    total_revenue - avg_revenue as revenue_vs_avg
FROM bus_performance
WHERE booking_count < avg_bookings OR total_revenue < avg_revenue
ORDER BY booking_count ASC;

-- Underperforming Days
WITH day_performance AS (
    SELECT 
        DATE(booking_date) as booking_day,
        COUNT(*) as booking_count,
        SUM(total_amount) as daily_revenue,
        AVG(COUNT(*)) OVER () as avg_daily_bookings,
        AVG(SUM(total_amount)) OVER () as avg_daily_revenue
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY booking_day
)
SELECT 
    booking_day,
    FORMAT_DATE('%A', booking_day) as day_name,
    booking_count,
    daily_revenue,
    ROUND(avg_daily_bookings, 2) as avg_bookings_benchmark,
    ROUND(avg_daily_revenue, 2) as avg_revenue_benchmark,
    booking_count - avg_daily_bookings as bookings_vs_avg,
    daily_revenue - avg_daily_revenue as revenue_vs_avg
FROM day_performance
WHERE booking_count < avg_daily_bookings * 0.7
ORDER BY booking_count ASC
LIMIT 30;

