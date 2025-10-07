-- ==========================================
-- MARKETING & PROMO ANALYTICS
-- ==========================================

-- 1. PROMO EFFECTIVENESS (Compare With vs Without Promo)
-- Revenue & Booking Comparison
SELECT 
    CASE WHEN promo_code IS NOT NULL THEN 'With Promo' ELSE 'Without Promo' END as promo_status,
    COUNT(*) as booking_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    SUM(CASE WHEN promo_code IS NOT NULL THEN discount_amount ELSE 0 END) as total_discount,
    AVG(CASE WHEN promo_code IS NOT NULL THEN discount_amount END) as avg_discount,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as booking_percentage
FROM `project.dataset.bookings`
WHERE status != 'cancelled'
GROUP BY promo_status;

-- ROI Analysis per Promo Code
SELECT 
    promo_code,
    COUNT(*) as total_usage,
    SUM(discount_amount) as total_discount_cost,
    SUM(total_amount) as total_revenue_generated,
    SUM(total_amount) + SUM(discount_amount) as gross_revenue_before_discount,
    ROUND((SUM(total_amount) - SUM(discount_amount)) / NULLIF(SUM(discount_amount), 0), 2) as roi_ratio,
    AVG(total_amount) as avg_booking_value,
    COUNT(DISTINCT customer_id) as unique_customers_acquired
FROM `project.dataset.bookings`
WHERE promo_code IS NOT NULL AND status != 'cancelled'
GROUP BY promo_code
ORDER BY total_revenue_generated DESC;

-- Promo Impact on Conversion Rate
WITH promo_metrics AS (
    SELECT 
        promo_code,
        COUNT(*) as total_bookings,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
        ROUND(COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 2) as completion_rate,
        ROUND(COUNT(CASE WHEN status = 'cancelled' THEN 1 END) * 100.0 / COUNT(*), 2) as cancellation_rate
    FROM `project.dataset.bookings`
    WHERE promo_code IS NOT NULL
    GROUP BY promo_code
)
SELECT 
    promo_code,
    total_bookings,
    completed_bookings,
    cancelled_bookings,
    completion_rate,
    cancellation_rate,
    CASE 
        WHEN completion_rate >= 90 THEN 'Excellent'
        WHEN completion_rate >= 80 THEN 'Good'
        WHEN completion_rate >= 70 THEN 'Average'
        ELSE 'Poor'
    END as performance_rating
FROM promo_metrics
ORDER BY completion_rate DESC;

-- 2. RETURNING VS FIRST-TIME CUSTOMERS
WITH customer_classification AS (
    SELECT 
        customer_id,
        booking_date,
        total_amount,
        promo_code,
        status,
        ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY booking_date) as booking_sequence
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
)
SELECT 
    CASE WHEN booking_sequence = 1 THEN 'First-Time Customer' ELSE 'Returning Customer' END as customer_type,
    COUNT(*) as booking_count,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
    ROUND(COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) * 100.0 / COUNT(*), 2) as promo_usage_rate
FROM customer_classification
GROUP BY customer_type
ORDER BY customer_type;

-- Customer Retention Rate
WITH customer_bookings AS (
    SELECT 
        customer_id,
        MIN(DATE(booking_date)) as first_booking_date,
        MAX(DATE(booking_date)) as last_booking_date,
        COUNT(*) as total_bookings,
        SUM(total_amount) as lifetime_value
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY customer_id
),
cohort_analysis AS (
    SELECT 
        FORMAT_DATE('%Y-%m', first_booking_date) as cohort_month,
        COUNT(*) as cohort_size,
        COUNT(CASE WHEN total_bookings > 1 THEN 1 END) as retained_customers,
        ROUND(COUNT(CASE WHEN total_bookings > 1 THEN 1 END) * 100.0 / COUNT(*), 2) as retention_rate,
        AVG(lifetime_value) as avg_lifetime_value
    FROM customer_bookings
    GROUP BY cohort_month
)
SELECT * FROM cohort_analysis
ORDER BY cohort_month DESC;

-- 3. CUSTOMER SEGMENTATION: HIGH SPENDERS VS LOW SPENDERS
WITH customer_spending AS (
    SELECT 
        customer_id,
        client_type,
        COUNT(*) as total_bookings,
        SUM(total_amount) as total_spent,
        AVG(total_amount) as avg_booking_value,
        MAX(DATE(booking_date)) as last_booking_date,
        DATE_DIFF(CURRENT_DATE(), MAX(DATE(booking_date)), DAY) as days_since_last_booking
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY customer_id, client_type
),
spending_percentiles AS (
    SELECT 
        APPROX_QUANTILES(total_spent, 100) as spending_quantiles
    FROM customer_spending
)
SELECT 
    CASE 
        WHEN cs.total_spent >= sq.spending_quantiles[OFFSET(90)] THEN 'VIP High Spender (Top 10%)'
        WHEN cs.total_spent >= sq.spending_quantiles[OFFSET(70)] THEN 'High Spender (Top 30%)'
        WHEN cs.total_spent >= sq.spending_quantiles[OFFSET(40)] THEN 'Medium Spender (Middle 30%)'
        ELSE 'Low Spender (Bottom 40%)'
    END as spending_segment,
    COUNT(*) as customer_count,
    AVG(cs.total_spent) as avg_total_spent,
    AVG(cs.total_bookings) as avg_bookings,
    AVG(cs.avg_booking_value) as avg_booking_value,
    SUM(cs.total_spent) as total_segment_revenue,
    ROUND(SUM(cs.total_spent) * 100.0 / SUM(SUM(cs.total_spent)) OVER (), 2) as revenue_percentage,
    AVG(cs.days_since_last_booking) as avg_days_since_last_booking
FROM customer_spending cs
CROSS JOIN spending_percentiles sq
GROUP BY spending_segment
ORDER BY 
    CASE spending_segment
        WHEN 'VIP High Spender (Top 10%)' THEN 1
        WHEN 'High Spender (Top 30%)' THEN 2
        WHEN 'Medium Spender (Middle 30%)' THEN 3
        WHEN 'Low Spender (Bottom 40%)' THEN 4
    END;

-- 4. RFM ANALYSIS (Recency, Frequency, Monetary)
WITH rfm_base AS (
    SELECT 
        customer_id,
        MAX(DATE(booking_date)) as last_booking_date,
        COUNT(*) as frequency,
        SUM(total_amount) as monetary_value,
        DATE_DIFF(CURRENT_DATE(), MAX(DATE(booking_date)), DAY) as recency_days
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY customer_id
),
rfm_scores AS (
    SELECT 
        customer_id,
        recency_days,
        frequency,
        monetary_value,
        NTILE(5) OVER (ORDER BY recency_days ASC) as recency_score,  -- Lower recency = better
        NTILE(5) OVER (ORDER BY frequency DESC) as frequency_score,
        NTILE(5) OVER (ORDER BY monetary_value DESC) as monetary_score
    FROM rfm_base
),
rfm_segments AS (
    SELECT 
        *,
        (recency_score + frequency_score + monetary_score) / 3.0 as rfm_score,
        CONCAT(CAST(recency_score AS STRING), CAST(frequency_score AS STRING), CAST(monetary_score AS STRING)) as rfm_code
    FROM rfm_scores
)
SELECT 
    CASE 
        WHEN rfm_score >= 4.5 THEN 'Champions'
        WHEN rfm_score >= 4.0 THEN 'Loyal Customers'
        WHEN rfm_score >= 3.5 AND recency_score >= 4 THEN 'Potential Loyalists'
        WHEN rfm_score >= 3.0 AND recency_score <= 2 THEN 'At Risk'
        WHEN rfm_score >= 2.5 THEN 'Need Attention'
        WHEN recency_score <= 2 AND frequency_score <= 2 THEN 'Lost Customers'
        ELSE 'Hibernating'
    END as customer_segment,
    COUNT(*) as customer_count,
    AVG(monetary_value) as avg_monetary_value,
    AVG(frequency) as avg_frequency,
    AVG(recency_days) as avg_recency_days,
    SUM(monetary_value) as total_segment_value,
    ROUND(SUM(monetary_value) * 100.0 / SUM(SUM(monetary_value)) OVER (), 2) as revenue_percentage
FROM rfm_segments
GROUP BY customer_segment
ORDER BY total_segment_value DESC;

-- 5. CHANNEL ATTRIBUTION (If marketing_channel field exists)
SELECT 
    marketing_channel,
    COUNT(*) as total_bookings,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(total_amount) / NULLIF(COUNT(DISTINCT customer_id), 0) as revenue_per_customer,
    COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
    ROUND(COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) * 100.0 / COUNT(*), 2) as promo_usage_rate
FROM `project.dataset.bookings`
WHERE status != 'cancelled' AND marketing_channel IS NOT NULL
GROUP BY marketing_channel
ORDER BY total_revenue DESC;

-- 6. CUSTOMER ACQUISITION COST ANALYSIS (CAC)
-- Assuming marketing_cost table exists
WITH monthly_metrics AS (
    SELECT 
        FORMAT_DATE('%Y-%m', DATE(b.booking_date)) as month,
        COUNT(DISTINCT b.customer_id) as new_customers,
        SUM(b.total_amount) as total_revenue,
        COALESCE(mc.total_marketing_spend, 0) as marketing_spend
    FROM `project.dataset.bookings` b
    LEFT JOIN `project.dataset.marketing_costs` mc ON FORMAT_DATE('%Y-%m', DATE(b.booking_date)) = mc.month
    WHERE b.status != 'cancelled'
    GROUP BY month, mc.total_marketing_spend
)
SELECT 
    month,
    new_customers,
    total_revenue,
    marketing_spend,
    ROUND(marketing_spend / NULLIF(new_customers, 0), 2) as customer_acquisition_cost,
    ROUND(total_revenue / NULLIF(new_customers, 0), 2) as revenue_per_customer,
    ROUND((total_revenue - marketing_spend) / NULLIF(marketing_spend, 0), 2) as marketing_roi
FROM monthly_metrics
ORDER BY month DESC;

-- 7. PROMO CODE CANNIBALIZATION ANALYSIS
-- Compare customers who would have booked anyway vs those attracted by promo
WITH customer_promo_behavior AS (
    SELECT 
        customer_id,
        COUNT(*) as total_bookings,
        COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) as promo_bookings,
        COUNT(CASE WHEN promo_code IS NULL THEN 1 END) as non_promo_bookings,
        ROUND(COUNT(CASE WHEN promo_code IS NOT NULL THEN 1 END) * 100.0 / COUNT(*), 2) as promo_dependency_rate
    FROM `project.dataset.bookings`
    WHERE status != 'cancelled'
    GROUP BY customer_id
)
SELECT 
    CASE 
        WHEN promo_dependency_rate = 100 THEN 'Promo-Only Customers'
        WHEN promo_dependency_rate >= 75 THEN 'High Promo Dependency'
        WHEN promo_dependency_rate >= 50 THEN 'Moderate Promo Dependency'
        WHEN promo_dependency_rate >= 25 THEN 'Low Promo Dependency'
        ELSE 'Promo-Independent'
    END as customer_segment,
    COUNT(*) as customer_count,
    AVG(total_bookings) as avg_bookings,
    AVG(promo_bookings) as avg_promo_bookings,
    ROUND(AVG(promo_dependency_rate), 2) as avg_dependency_rate
FROM customer_promo_behavior
GROUP BY customer_segment
ORDER BY 
    CASE customer_segment
        WHEN 'Promo-Only Customers' THEN 1
        WHEN 'High Promo Dependency' THEN 2
        WHEN 'Moderate Promo Dependency' THEN 3
        WHEN 'Low Promo Dependency' THEN 4
        WHEN 'Promo-Independent' THEN 5
    END;

