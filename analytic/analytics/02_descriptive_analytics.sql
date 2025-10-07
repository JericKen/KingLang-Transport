-- ==========================================
-- DESCRIPTIVE ANALYTICS (What Happened?)
-- ==========================================

-- 1. TOTAL BOOKINGS PER DAY/WEEK/MONTH
-- Daily Bookings
SELECT 
    DATE(booking_date) as booking_day,
    FORMAT_DATE('%A', DATE(booking_date)) as day_name,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings
FROM `project.dataset.bookings`
GROUP BY booking_day, day_name
ORDER BY booking_day DESC;

-- Weekly Bookings
SELECT 
    DATE_TRUNC(DATE(booking_date), WEEK) as week_start,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 2) as completion_rate
FROM `project.dataset.bookings`
GROUP BY week_start
ORDER BY week_start DESC;

-- Monthly Bookings
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
    ROUND(COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 2) as completion_rate
FROM `project.dataset.bookings`
GROUP BY month
ORDER BY month DESC;

-- 2. TOP DESTINATIONS
SELECT 
    destination,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_trips,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY destination
ORDER BY booking_count DESC
LIMIT 20;

-- Top Routes (Origin to Destination)
SELECT 
    origin,
    destination,
    CONCAT(origin, ' → ', destination) as route,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY origin, destination, route
ORDER BY booking_count DESC
LIMIT 15;

-- 3. MONTHLY/YEARLY INCOME
-- Monthly Income with Growth
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    SUM(total_amount) as monthly_income,
    COUNT(*) as total_bookings,
    AVG(total_amount) as avg_booking_value,
    SUM(total_amount) - LAG(SUM(total_amount)) OVER (ORDER BY FORMAT_DATE('%Y-%m', DATE(booking_date))) as income_change,
    ROUND((SUM(total_amount) - LAG(SUM(total_amount)) OVER (ORDER BY FORMAT_DATE('%Y-%m', DATE(booking_date)))) * 100.0 / 
          NULLIF(LAG(SUM(total_amount)) OVER (ORDER BY FORMAT_DATE('%Y-%m', DATE(booking_date))), 0), 2) as growth_percentage
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY month
ORDER BY month DESC;

-- Yearly Income
SELECT 
    EXTRACT(YEAR FROM DATE(booking_date)) as year,
    SUM(total_amount) as yearly_income,
    COUNT(*) as total_bookings,
    AVG(total_amount) as avg_booking_value,
    SUM(total_amount) - LAG(SUM(total_amount)) OVER (ORDER BY EXTRACT(YEAR FROM DATE(booking_date))) as income_change,
    ROUND((SUM(total_amount) - LAG(SUM(total_amount)) OVER (ORDER BY EXTRACT(YEAR FROM DATE(booking_date)))) * 100.0 / 
          NULLIF(LAG(SUM(total_amount)) OVER (ORDER BY EXTRACT(YEAR FROM DATE(booking_date))), 0), 2) as growth_percentage
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY year
ORDER BY year DESC;

-- 4. COMPLETED VS CANCELLED BOOKINGS
-- Overall Status Distribution
SELECT 
    status,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
GROUP BY status
ORDER BY booking_count DESC;

-- Status Trends Over Time
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    ROUND(COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 2) as completion_rate
FROM `project.dataset.bookings`
GROUP BY month
ORDER BY month DESC;

-- Cancellation Rate by Client Type
SELECT 
    client_type,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as lost_revenue
FROM `project.dataset.bookings`
GROUP BY client_type
ORDER BY cancellation_rate DESC;

-- 5. BOOKING SUMMARY (Comprehensive Overview)
SELECT 
    COUNT(*) as total_bookings,
    COUNT(DISTINCT customer_id) as unique_customers,
    COUNT(DISTINCT bus_id) as buses_used,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    MIN(total_amount) as min_booking_value,
    MAX(total_amount) as max_booking_value,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 2) as completion_rate
FROM `project.dataset.bookings`
WHERE DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY);

