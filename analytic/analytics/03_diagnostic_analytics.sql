-- ==========================================
-- DIAGNOSTIC ANALYTICS (Why Did It Happen?)
-- ==========================================

-- 1. CANCELLATION REASONS BY CLIENT TYPE
SELECT 
    client_type,
    cancellation_reason,
    COUNT(*) as cancellation_count,
    SUM(total_amount) as lost_revenue,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (PARTITION BY client_type), 2) as percentage_within_client_type,
    AVG(total_amount) as avg_cancelled_booking_value
FROM `project.dataset.bookings`
WHERE status = 'cancelled' AND cancellation_reason IS NOT NULL
GROUP BY client_type, cancellation_reason
ORDER BY client_type, cancellation_count DESC;

-- Overall Cancellation Reasons
SELECT 
    cancellation_reason,
    COUNT(*) as cancellation_count,
    SUM(total_amount) as lost_revenue,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total,
    AVG(DATETIME_DIFF(DATETIME(cancelled_at), DATETIME(booking_date), HOUR)) as avg_hours_before_cancellation
FROM `project.dataset.bookings`
WHERE status = 'cancelled' AND cancellation_reason IS NOT NULL
GROUP BY cancellation_reason
ORDER BY cancellation_count DESC;

-- 2. CANCELLATION PATTERNS BY ROUTE
SELECT 
    origin,
    destination,
    CONCAT(origin, ' → ', destination) as route,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as lost_revenue
FROM `project.dataset.bookings`
GROUP BY origin, destination, route
HAVING COUNT(*) >= 5  -- Only routes with at least 5 bookings
ORDER BY cancellation_rate DESC
LIMIT 20;

-- 3. CANCELLATION PATTERNS BY TIME
-- By Day of Week
SELECT 
    FORMAT_DATE('%A', DATE(booking_date)) as day_of_week,
    EXTRACT(DAYOFWEEK FROM DATE(booking_date)) as day_number,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as lost_revenue
FROM `project.dataset.bookings`
GROUP BY day_of_week, day_number
ORDER BY day_number;

-- By Month
SELECT 
    FORMAT_DATE('%Y-%m', DATE(booking_date)) as month,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as lost_revenue
FROM `project.dataset.bookings`
GROUP BY month
ORDER BY month DESC;

-- 4. UNDERPERFORMING BUSES - ROOT CAUSE ANALYSIS
WITH bus_metrics AS (
    SELECT 
        b.bus_id,
        bus.bus_name,
        bus.bus_type,
        bus.age_years,
        COUNT(*) as total_bookings,
        SUM(b.total_amount) as total_revenue,
        AVG(b.total_amount) as avg_booking_value,
        COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_count,
        ROUND(COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
        AVG(fb.rating) as avg_rating,
        COUNT(fb.id) as feedback_count
    FROM `project.dataset.bookings` b
    LEFT JOIN `project.dataset.buses` bus ON b.bus_id = bus.id
    LEFT JOIN `project.dataset.feedback` fb ON b.bus_id = fb.bus_id
    GROUP BY b.bus_id, bus.bus_name, bus.bus_type, bus.age_years
),
performance_benchmark AS (
    SELECT 
        AVG(total_bookings) as avg_bookings,
        AVG(total_revenue) as avg_revenue,
        AVG(cancellation_rate) as avg_cancellation_rate,
        AVG(avg_rating) as avg_rating_benchmark
    FROM bus_metrics
)
SELECT 
    bm.*,
    pb.avg_bookings as benchmark_bookings,
    pb.avg_revenue as benchmark_revenue,
    pb.avg_cancellation_rate as benchmark_cancellation_rate,
    pb.avg_rating_benchmark,
    CASE 
        WHEN bm.total_bookings < pb.avg_bookings * 0.7 THEN 'Low Bookings'
        WHEN bm.cancellation_rate > pb.avg_cancellation_rate * 1.3 THEN 'High Cancellations'
        WHEN bm.avg_rating < pb.avg_rating_benchmark - 0.5 THEN 'Poor Ratings'
        WHEN bm.total_revenue < pb.avg_revenue * 0.7 THEN 'Low Revenue'
        ELSE 'Acceptable'
    END as underperformance_reason
FROM bus_metrics bm
CROSS JOIN performance_benchmark pb
WHERE 
    bm.total_bookings < pb.avg_bookings * 0.7
    OR bm.cancellation_rate > pb.avg_cancellation_rate * 1.3
    OR bm.avg_rating < pb.avg_rating_benchmark - 0.5
    OR bm.total_revenue < pb.avg_revenue * 0.7
ORDER BY bm.total_bookings ASC;

-- 5. UNDERPERFORMING DAYS - ROOT CAUSE ANALYSIS
WITH daily_metrics AS (
    SELECT 
        DATE(booking_date) as booking_day,
        FORMAT_DATE('%A', DATE(booking_date)) as day_name,
        EXTRACT(DAYOFWEEK FROM DATE(booking_date)) as day_number,
        COUNT(*) as total_bookings,
        SUM(total_amount) as total_revenue,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
        ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
        COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
        AVG(total_amount) as avg_booking_value
    FROM `project.dataset.bookings`
    GROUP BY booking_day, day_name, day_number
),
day_benchmark AS (
    SELECT 
        day_number,
        AVG(total_bookings) as avg_bookings_for_day,
        AVG(total_revenue) as avg_revenue_for_day
    FROM daily_metrics
    GROUP BY day_number
)
SELECT 
    dm.booking_day,
    dm.day_name,
    dm.total_bookings,
    dm.total_revenue,
    dm.cancellation_rate,
    dm.promo_bookings,
    dm.avg_booking_value,
    db.avg_bookings_for_day as benchmark_bookings,
    db.avg_revenue_for_day as benchmark_revenue,
    dm.total_bookings - db.avg_bookings_for_day as bookings_variance,
    CASE 
        WHEN dm.total_bookings < db.avg_bookings_for_day * 0.6 THEN 'Significantly Below Average'
        WHEN dm.cancellation_rate > 20 THEN 'High Cancellation Rate'
        WHEN dm.promo_bookings = 0 AND dm.total_bookings < db.avg_bookings_for_day THEN 'No Promo Activity'
        ELSE 'Other Factors'
    END as underperformance_reason
FROM daily_metrics dm
LEFT JOIN day_benchmark db ON dm.day_number = db.day_number
WHERE dm.total_bookings < db.avg_bookings_for_day * 0.7
ORDER BY dm.booking_day DESC
LIMIT 30;

-- 6. LATE CANCELLATION ANALYSIS (Last-Minute Cancellations)
SELECT 
    client_type,
    cancellation_reason,
    COUNT(*) as late_cancellation_count,
    SUM(total_amount) as lost_revenue,
    AVG(DATETIME_DIFF(DATETIME(trip_date), DATETIME(cancelled_at), HOUR)) as avg_hours_before_trip,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total
FROM `project.dataset.bookings`
WHERE 
    status = 'cancelled' 
    AND DATETIME_DIFF(DATETIME(trip_date), DATETIME(cancelled_at), HOUR) <= 24
GROUP BY client_type, cancellation_reason
ORDER BY late_cancellation_count DESC;

-- 7. PRICE SENSITIVITY ANALYSIS
WITH price_segments AS (
    SELECT 
        *,
        CASE 
            WHEN total_amount < 500 THEN 'Budget (<500)'
            WHEN total_amount BETWEEN 500 AND 1500 THEN 'Mid-Range (500-1500)'
            WHEN total_amount BETWEEN 1500 AND 3000 THEN 'Premium (1500-3000)'
            ELSE 'Luxury (>3000)'
        END as price_segment
    FROM `project.dataset.bookings`
)
SELECT 
    price_segment,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate,
    AVG(total_amount) as avg_price,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as lost_revenue
FROM price_segments
GROUP BY price_segment
ORDER BY 
    CASE price_segment
        WHEN 'Budget (<500)' THEN 1
        WHEN 'Mid-Range (500-1500)' THEN 2
        WHEN 'Premium (1500-3000)' THEN 3
        WHEN 'Luxury (>3000)' THEN 4
    END;

