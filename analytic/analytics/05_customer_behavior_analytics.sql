-- ==========================================
-- CUSTOMER BEHAVIOR ANALYSIS
-- ==========================================

-- 1. MOST PREFERRED DESTINATIONS
SELECT 
    destination,
    COUNT(*) as total_bookings,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as preference_percentage,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_trips
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY destination
ORDER BY total_bookings DESC
LIMIT 20;

-- Preferred Destinations by Client Type
SELECT 
    client_type,
    destination,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (PARTITION BY client_type), 2) as percentage_within_client
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY client_type, destination
QUALIFY ROW_NUMBER() OVER (PARTITION BY client_type ORDER BY COUNT(*) DESC) <= 5
ORDER BY client_type, booking_count DESC;

-- 2. PEAK BOOKING HOURS
SELECT 
    EXTRACT(HOUR FROM DATETIME(booking_date)) as booking_hour,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total,
    CASE 
        WHEN EXTRACT(HOUR FROM DATETIME(booking_date)) BETWEEN 6 AND 11 THEN 'Morning (6-11)'
        WHEN EXTRACT(HOUR FROM DATETIME(booking_date)) BETWEEN 12 AND 17 THEN 'Afternoon (12-17)'
        WHEN EXTRACT(HOUR FROM DATETIME(booking_date)) BETWEEN 18 AND 23 THEN 'Evening (18-23)'
        ELSE 'Night (0-5)'
    END as time_period
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY booking_hour
ORDER BY booking_hour;

-- Peak Booking Hours by Day of Week
SELECT 
    FORMAT_DATE('%A', DATE(booking_date)) as day_name,
    EXTRACT(HOUR FROM DATETIME(booking_date)) as booking_hour,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY day_name, booking_hour
ORDER BY day_name, booking_hour;

-- 3. PROMO CODE USAGE RATES
-- Overall Promo Usage
SELECT 
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
    COUNT(CASE WHEN promo_code IS NULL THEN 1 END) as non_promo_bookings,
    ROUND(COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) * 100.0 / COUNT(*), 2) as promo_usage_rate,
    SUM(CASE WHEN promo_code IS NOT NULL THEN discount_amount ELSE 0 END) as total_discount_given,
    AVG(CASE WHEN promo_code IS NOT NULL THEN discount_amount END) as avg_discount_per_promo
FROM `project.dataset.bookings`
WHERE status != 'cancelled';

-- Promo Usage by Client Type
SELECT 
    client_type,
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
    ROUND(COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) * 100.0 / COUNT(*), 2) as promo_usage_rate,
    SUM(CASE WHEN promo_code IS NOT NULL THEN discount_amount ELSE 0 END) as total_discount_given,
    AVG(total_amount) as avg_booking_value
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY client_type
ORDER BY promo_usage_rate DESC;

-- Top Promo Codes Performance
SELECT 
    promo_code,
    COUNT(*) as usage_count,
    SUM(discount_amount) as total_discount_given,
    AVG(discount_amount) as avg_discount,
    SUM(total_amount) as total_revenue_with_promo,
    AVG(total_amount) as avg_booking_value,
    COUNT(DISTINCT customer_id) as unique_customers
FROM `project.dataset.bookings`
WHERE promo_code IS NOT NULL AND status != 'cancelled'
GROUP BY promo_code
ORDER BY usage_count DESC
LIMIT 20;

-- 4. BOOKING LEAD TIME ANALYSIS
SELECT 
    DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY) as lead_time_days,
    COUNT(*) as booking_count,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total,
    CASE 
        WHEN DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY) <= 1 THEN 'Last Minute (0-1 days)'
        WHEN DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY) BETWEEN 2 AND 7 THEN 'Short Term (2-7 days)'
        WHEN DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY) BETWEEN 8 AND 30 THEN 'Medium Term (8-30 days)'
        ELSE 'Long Term (30+ days)'
    END as lead_time_category
FROM `project.dataset.bookings`
WHERE status != 'cancelled' AND trip_date >= booking_date
GROUP BY lead_time_days
ORDER BY lead_time_days;

-- Lead Time by Client Type
SELECT 
    client_type,
    AVG(DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY)) as avg_lead_time_days,
    MIN(DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY)) as min_lead_time,
    MAX(DATE_DIFF(DATE(trip_date), DATE(booking_date), DAY)) as max_lead_time,
    COUNT(*) as total_bookings
FROM `project.dataset.bookings`
WHERE status != 'cancelled' AND trip_date >= booking_date
GROUP BY client_type
ORDER BY avg_lead_time_days DESC;

-- 5. REPEAT CUSTOMER BEHAVIOR
WITH customer_booking_counts AS (
    SELECT 
        customer_id,
        COUNT(*) as total_bookings,
        SUM(total_amount) as lifetime_value,
        MIN(DATE(booking_date)) as first_booking_date,
        MAX(DATE(booking_date)) as last_booking_date,
        DATE_DIFF(MAX(DATE(booking_date)), MIN(DATE(booking_date)), DAY) as customer_lifespan_days
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY customer_id
)
SELECT 
    CASE 
        WHEN total_bookings = 1 THEN 'One-time Customer'
        WHEN total_bookings BETWEEN 2 AND 5 THEN 'Occasional (2-5 bookings)'
        WHEN total_bookings BETWEEN 6 AND 15 THEN 'Regular (6-15 bookings)'
        ELSE 'VIP (15+ bookings)'
    END as customer_segment,
    COUNT(*) as customer_count,
    AVG(total_bookings) as avg_bookings_per_customer,
    AVG(lifetime_value) as avg_lifetime_value,
    SUM(lifetime_value) as total_segment_value,
    AVG(customer_lifespan_days) as avg_customer_lifespan_days
FROM customer_booking_counts
GROUP BY customer_segment
ORDER BY 
    CASE customer_segment
        WHEN 'One-time Customer' THEN 1
        WHEN 'Occasional (2-5 bookings)' THEN 2
        WHEN 'Regular (6-15 bookings)' THEN 3
        WHEN 'VIP (15+ bookings)' THEN 4
    END;

-- 6. CUSTOMER JOURNEY ANALYSIS (First vs Latest Booking)
WITH customer_journey AS (
    SELECT 
        customer_id,
        client_type,
        ARRAY_AGG(
            STRUCT(
                booking_date,
                total_amount,
                destination,
                promo_code,
                status
            ) 
            ORDER BY booking_date
        ) as bookings_timeline
    FROM `project.dataset.bookings`
    GROUP BY customer_id, client_type
)
SELECT 
    client_type,
    COUNT(*) as total_customers,
    AVG(ARRAY_LENGTH(bookings_timeline)) as avg_bookings_per_customer,
    AVG(bookings_timeline[OFFSET(0)].total_amount) as avg_first_booking_value,
    AVG(bookings_timeline[OFFSET(ARRAY_LENGTH(bookings_timeline)-1)].total_amount) as avg_latest_booking_value,
    COUNTIF(bookings_timeline[OFFSET(0)].promo_code IS NOT NULL) as first_booking_with_promo,
    ROUND(COUNTIF(bookings_timeline[OFFSET(0)].promo_code IS NOT NULL) * 100.0 / COUNT(*), 2) as first_booking_promo_rate
FROM customer_journey
WHERE ARRAY_LENGTH(bookings_timeline) > 0
GROUP BY client_type
ORDER BY total_customers DESC;

-- 7. AVERAGE TRIP DURATION PREFERENCE
SELECT 
    DATE_DIFF(DATE(return_date), DATE(trip_date), DAY) as trip_duration_days,
    COUNT(*) as booking_count,
    AVG(total_amount) as avg_booking_value,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage_of_total,
    CASE 
        WHEN DATE_DIFF(DATE(return_date), DATE(trip_date), DAY) = 0 THEN 'Same Day'
        WHEN DATE_DIFF(DATE(return_date), DATE(trip_date), DAY) = 1 THEN '1 Day'
        WHEN DATE_DIFF(DATE(return_date), DATE(trip_date), DAY) BETWEEN 2 AND 3 THEN 'Weekend (2-3 days)'
        WHEN DATE_DIFF(DATE(return_date), DATE(trip_date), DAY) BETWEEN 4 AND 7 THEN 'Week (4-7 days)'
        ELSE 'Extended (7+ days)'
    END as trip_duration_category
FROM `project.dataset.bookings`
WHERE 
    status != 'cancelled' 
    AND return_date IS NOT NULL
    AND return_date >= trip_date
GROUP BY trip_duration_days
ORDER BY trip_duration_days;

