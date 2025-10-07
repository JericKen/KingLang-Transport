-- ==========================================
-- FEEDBACK SENTIMENT ANALYSIS (Basic)
-- ==========================================

-- 1. AVERAGE RATINGS PER BUS
SELECT 
    fb.bus_id,
    bus.bus_name,
    bus.plate_number,
    COUNT(fb.id) as total_feedback,
    AVG(fb.rating) as avg_rating,
    MIN(fb.rating) as min_rating,
    MAX(fb.rating) as max_rating,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as positive_feedback,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as negative_feedback,
    ROUND(COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as positive_rate,
    ROUND(COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as complaint_rate
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.buses` bus ON fb.bus_id = bus.id
GROUP BY fb.bus_id, bus.bus_name, bus.plate_number
ORDER BY avg_rating DESC;

-- 2. AVERAGE RATINGS PER DRIVER
SELECT 
    fb.driver_id,
    drv.driver_name,
    COUNT(fb.id) as total_feedback,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as positive_feedback,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as negative_feedback,
    ROUND(COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as positive_rate,
    ROUND(COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as complaint_rate,
    STDDEV(fb.rating) as rating_std_dev
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.drivers` drv ON fb.driver_id = drv.id
WHERE fb.driver_id IS NOT NULL
GROUP BY fb.driver_id, drv.driver_name
ORDER BY avg_rating DESC;

-- 3. COUNT OF LOW RATINGS (Complaints)
SELECT 
    fb.bus_id,
    bus.bus_name,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as complaint_count,
    COUNT(fb.id) as total_feedback,
    ROUND(COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as complaint_rate,
    AVG(CASE WHEN fb.rating <= 2 THEN fb.rating END) as avg_complaint_rating,
    STRING_AGG(CASE WHEN fb.rating <= 2 THEN fb.feedback_text END, ' | ' LIMIT 5) as sample_complaints
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.buses` bus ON fb.bus_id = bus.id
GROUP BY fb.bus_id, bus.bus_name
HAVING COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) > 0
ORDER BY complaint_count DESC;

-- 4. RATING TRENDS OVER TIME
SELECT 
    FORMAT_DATE('%Y-%m', DATE(fb.created_at)) as month,
    COUNT(fb.id) as total_feedback,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as positive_count,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as negative_count,
    ROUND(AVG(fb.rating), 2) as monthly_avg_rating,
    ROUND(AVG(fb.rating) - LAG(AVG(fb.rating)) OVER (ORDER BY FORMAT_DATE('%Y-%m', DATE(fb.created_at))), 2) as rating_change
FROM `project.dataset.feedback` fb
GROUP BY month
ORDER BY month DESC;

-- 5. KEYWORD FREQUENCY FROM FEEDBACK TEXT
-- Extract most common words from feedback
WITH words_extracted AS (
    SELECT 
        fb.rating,
        LOWER(TRIM(word)) as keyword,
        CASE 
            WHEN fb.rating >= 4 THEN 'Positive'
            WHEN fb.rating = 3 THEN 'Neutral'
            ELSE 'Negative'
        END as sentiment
    FROM `project.dataset.feedback` fb,
    UNNEST(SPLIT(LOWER(fb.feedback_text), ' ')) as word
    WHERE 
        fb.feedback_text IS NOT NULL 
        AND LENGTH(TRIM(word)) > 3
        AND TRIM(word) NOT IN ('that', 'this', 'with', 'from', 'have', 'were', 'been', 'their', 'your', 'they')
)
SELECT 
    keyword,
    COUNT(*) as frequency,
    COUNT(CASE WHEN sentiment = 'Positive' THEN 1 END) as positive_mentions,
    COUNT(CASE WHEN sentiment = 'Negative' THEN 1 END) as negative_mentions,
    ROUND(COUNT(CASE WHEN sentiment = 'Positive' THEN 1 END) * 100.0 / COUNT(*), 2) as positive_percentage
FROM words_extracted
GROUP BY keyword
HAVING COUNT(*) >= 3
ORDER BY frequency DESC
LIMIT 30;

-- 6. SENTIMENT BY ROUTE
SELECT 
    b.origin,
    b.destination,
    CONCAT(b.origin, ' → ', b.destination) as route,
    COUNT(fb.id) as feedback_count,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as positive_count,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as negative_count,
    ROUND(COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as satisfaction_rate
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.bookings` b ON fb.booking_id = b.id
WHERE b.origin IS NOT NULL AND b.destination IS NOT NULL
GROUP BY b.origin, b.destination, route
HAVING COUNT(fb.id) >= 3
ORDER BY avg_rating DESC;

-- 7. FEEDBACK RESPONSE TIME ANALYSIS (If response tracking exists)
SELECT 
    CASE 
        WHEN fb.rating <= 2 THEN 'Complaint (Rating 1-2)'
        WHEN fb.rating = 3 THEN 'Neutral (Rating 3)'
        ELSE 'Positive (Rating 4-5)'
    END as feedback_type,
    COUNT(fb.id) as total_feedback,
    COUNT(CASE WHEN fb.admin_response IS NOT NULL THEN 1 END) as responded_count,
    ROUND(COUNT(CASE WHEN fb.admin_response IS NOT NULL THEN 1 END) * 100.0 / COUNT(fb.id), 2) as response_rate,
    AVG(DATETIME_DIFF(DATETIME(fb.responded_at), DATETIME(fb.created_at), HOUR)) as avg_response_time_hours
FROM `project.dataset.feedback` fb
GROUP BY feedback_type
ORDER BY 
    CASE feedback_type
        WHEN 'Complaint (Rating 1-2)' THEN 1
        WHEN 'Neutral (Rating 3)' THEN 2
        WHEN 'Positive (Rating 4-5)' THEN 3
    END;

-- 8. COMPARATIVE SENTIMENT: Bus Types
SELECT 
    bus.bus_type,
    COUNT(fb.id) as feedback_count,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) as positive_count,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as complaint_count,
    ROUND(COUNT(CASE WHEN fb.rating >= 4 THEN 1 END) * 100.0 / COUNT(fb.id), 2) as satisfaction_rate,
    ROUND(AVG(fb.rating), 2) as rounded_avg_rating
FROM `project.dataset.feedback` fb
LEFT JOIN `project.dataset.buses` bus ON fb.bus_id = bus.id
WHERE bus.bus_type IS NOT NULL
GROUP BY bus.bus_type
ORDER BY avg_rating DESC;

-- 9. REPEAT FEEDBACK CUSTOMERS (Customer Satisfaction Journey)
WITH customer_feedback_journey AS (
    SELECT 
        fb.customer_id,
        COUNT(fb.id) as feedback_count,
        AVG(fb.rating) as avg_rating,
        MIN(fb.rating) as lowest_rating,
        MAX(fb.rating) as highest_rating,
        ARRAY_AGG(fb.rating ORDER BY fb.created_at) as rating_timeline
    FROM `project.dataset.feedback` fb
    GROUP BY fb.customer_id
    HAVING COUNT(fb.id) >= 2
)
SELECT 
    CASE 
        WHEN avg_rating >= 4 THEN 'Consistently Satisfied'
        WHEN avg_rating >= 3 THEN 'Moderately Satisfied'
        WHEN highest_rating - lowest_rating >= 3 THEN 'Inconsistent Experience'
        ELSE 'Consistently Dissatisfied'
    END as customer_satisfaction_segment,
    COUNT(*) as customer_count,
    AVG(feedback_count) as avg_feedback_per_customer,
    AVG(avg_rating) as segment_avg_rating
FROM customer_feedback_journey
GROUP BY customer_satisfaction_segment
ORDER BY segment_avg_rating DESC;

-- 10. ACTIONABLE INSIGHTS: Low-Rated Buses Needing Attention
SELECT 
    bus.id as bus_id,
    bus.bus_name,
    bus.plate_number,
    bus.age_years,
    COUNT(fb.id) as feedback_count,
    AVG(fb.rating) as avg_rating,
    COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as complaint_count,
    STRING_AGG(DISTINCT CASE WHEN fb.rating <= 2 THEN fb.feedback_text END, ' | ' LIMIT 3) as recent_complaints,
    DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) as days_since_maintenance,
    CASE 
        WHEN AVG(fb.rating) < 2.5 AND COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) >= 5 THEN 'CRITICAL: Immediate Action Required'
        WHEN AVG(fb.rating) < 3.0 THEN 'WARNING: Needs Attention'
        ELSE 'Monitor'
    END as action_priority
FROM `project.dataset.buses` bus
LEFT JOIN `project.dataset.feedback` fb ON bus.id = fb.bus_id
GROUP BY bus.id, bus.bus_name, bus.plate_number, bus.age_years, bus.last_maintenance_date
HAVING AVG(fb.rating) < 3.5 OR COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) >= 3
ORDER BY avg_rating ASC, complaint_count DESC;

