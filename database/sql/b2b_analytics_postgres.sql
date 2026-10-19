-- ============================================================================
-- B2B E-COMMERCE ANALYTICS & FINANCIAL REPORTING MODULE (POSTGRESQL SPECIFICATION)
-- Database: PostgreSQL 14+
-- High-throughput architecture with Partial Indexes, Window Functions & Safe Math
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. HIGH-PERFORMANCE DATABASE INDEXES (100K+ ROW SCALE)
-- ----------------------------------------------------------------------------

-- A. Gross Margin by Serial & RMA Returns Partial Index
-- Only sold units are indexed for profitability queries, keeping index compact in RAM
CREATE INDEX IF NOT EXISTS idx_product_serials_sold_order_item 
ON product_serials (order_item_id, product_id) 
WHERE status = 'sold';

-- B. Inventory Aging & Dead Stock Partial Index
-- Unsold serials indexed by warehouse intake/creation date
CREATE INDEX IF NOT EXISTS idx_product_serials_available_aging 
ON product_serials (product_id, created_at) 
WHERE status = 'available';

-- C. RMA and Hardware Defect Partial Index
CREATE INDEX IF NOT EXISTS idx_product_serials_rma_defects 
ON product_serials (product_id, status) 
WHERE status IN ('rma_pending', 'defective');

-- D. Accounts Receivable (A/R) Index on Open Invoices
CREATE INDEX IF NOT EXISTS idx_sales_orders_ar_aging 
ON sales_orders (company_id, payment_due_date, total_amount) 
WHERE status NOT IN ('paid', 'cancelled');

-- E. Sales Order Items Composite Join Index
CREATE INDEX IF NOT EXISTS idx_sales_order_items_order_prod 
ON sales_order_items (order_id, product_id);

-- F. RFQ Request Status & Submission Index
CREATE INDEX IF NOT EXISTS idx_rfq_requests_status_created 
ON rfq_requests (company_id, status, created_at);

-- G. JSONB Brand Extraction B-tree Expression Index
CREATE INDEX IF NOT EXISTS idx_products_specs_brand 
ON products USING btree ((specs->>'brand'));


-- ----------------------------------------------------------------------------
-- 2. REPORT 1: REAL GROSS MARGIN BY SERIAL NUMBER (HARDWARE PROFITABILITY)
-- ----------------------------------------------------------------------------
-- Parameters: :start_date (TIMESTAMP), :end_date (TIMESTAMP)
WITH serial_sales_ledger AS (
    SELECT 
        ps.id AS serial_id,
        ps.serial_number,
        p.id AS product_id,
        p.sku,
        p.name AS product_name,
        p.category,
        COALESCE(p.specs->>'brand', 'Generic') AS brand,
        c.id AS company_id,
        c.company_name,
        c.tier AS company_tier,
        so.id AS order_id,
        so.order_number,
        so.created_at AS order_date,
        ps.cost_price,
        soi.unit_price AS sold_price,
        (soi.unit_price - ps.cost_price) AS gross_profit,
        CASE 
            WHEN soi.unit_price > 0 THEN 
                ROUND(((soi.unit_price - ps.cost_price) / soi.unit_price) * 100, 2)
            ELSE 0.00 
        END AS margin_pct
    FROM product_serials ps
    INNER JOIN sales_order_items soi ON ps.order_item_id = soi.id
    INNER JOIN sales_orders so ON soi.order_id = so.id
    INNER JOIN products p ON ps.product_id = p.id
    INNER JOIN companies c ON so.company_id = c.id
    WHERE ps.status = 'sold'
      AND so.status NOT IN ('cancelled', 'draft')
      AND so.created_at >= :start_date 
      AND so.created_at <  :end_date
)
SELECT 
    COALESCE(company_tier, 'ALL TIERS') AS company_tier,
    COALESCE(category, 'ALL CATEGORIES') AS category,
    COALESCE(sku, 'ALL SKUS') AS sku,
    COUNT(serial_id) AS units_sold,
    ROUND(SUM(cost_price), 2) AS total_cogs,
    ROUND(SUM(sold_price), 2) AS total_revenue,
    ROUND(SUM(gross_profit), 2) AS total_gross_profit,
    ROUND(
        (SUM(gross_profit) / NULLIF(SUM(sold_price), 0)) * 100, 
        2
    ) AS aggregated_margin_pct
FROM serial_sales_ledger
GROUP BY GROUPING SETS (
    (company_tier),
    (category),
    (category, sku),
    ()
)
ORDER BY 
    company_tier NULLS LAST,
    category NULLS LAST,
    total_revenue DESC;


-- ----------------------------------------------------------------------------
-- 3. REPORT 2: AGING ACCOUNTS RECEIVABLE (A/R) FOR NET TERMS (NET 30/60)
-- ----------------------------------------------------------------------------
WITH invoice_aging AS (
    SELECT 
        so.id AS order_id,
        so.order_number,
        so.company_id,
        c.company_name,
        c.tier,
        c.payment_terms,
        c.credit_limit,
        c.credit_balance,
        so.total_amount,
        so.payment_due_date,
        CURRENT_DATE - so.payment_due_date::date AS days_overdue
    FROM sales_orders so
    INNER JOIN companies c ON so.company_id = c.id
    WHERE so.status IN ('pending_payment', 'invoiced', 'overdue', 'delivered')
      AND so.status NOT IN ('paid', 'cancelled')
),
bucketed_aging AS (
    SELECT 
        company_id,
        company_name,
        tier,
        payment_terms,
        credit_limit,
        credit_balance,
        total_amount,
        days_overdue,
        CASE WHEN days_overdue <= 0 THEN total_amount ELSE 0 END AS current_not_due,
        CASE WHEN days_overdue BETWEEN 1 AND 30 THEN total_amount ELSE 0 END AS overdue_1_30,
        CASE WHEN days_overdue BETWEEN 31 AND 60 THEN total_amount ELSE 0 END AS overdue_31_60,
        CASE WHEN days_overdue BETWEEN 61 AND 90 THEN total_amount ELSE 0 END AS overdue_61_90,
        CASE WHEN days_overdue >= 91 THEN total_amount ELSE 0 END AS overdue_91_plus
    FROM invoice_aging
)
SELECT 
    company_id,
    company_name,
    tier,
    payment_terms,
    credit_limit,
    credit_balance,
    COUNT(*) AS open_order_count,
    ROUND(SUM(total_amount), 2) AS total_ar_balance,
    ROUND(SUM(current_not_due), 2) AS bucket_current,
    ROUND(SUM(overdue_1_30), 2) AS bucket_1_30,
    ROUND(SUM(overdue_31_60), 2) AS bucket_31_60,
    ROUND(SUM(overdue_61_90), 2) AS bucket_61_90,
    ROUND(SUM(overdue_91_plus), 2) AS bucket_91_plus,
    CASE 
        WHEN SUM(overdue_91_plus) > 0 THEN 'CREDIT_HOLD'
        WHEN SUM(overdue_61_90) > 0 OR credit_balance > credit_limit THEN 'HIGH_RISK'
        WHEN SUM(overdue_31_60) > 0 THEN 'MODERATE_RISK'
        ELSE 'GOOD_STANDING'
    END AS account_risk_status
FROM bucketed_aging
GROUP BY 
    company_id, 
    company_name, 
    tier, 
    payment_terms, 
    credit_limit, 
    credit_balance
ORDER BY bucket_91_plus DESC, total_ar_balance DESC;


-- ----------------------------------------------------------------------------
-- 4. REPORT 3: STOCK AGING & DEAD INVENTORY (DEPRECIATION RISK)
-- ----------------------------------------------------------------------------
WITH inventory_aging AS (
    SELECT 
        ps.id AS serial_id,
        ps.serial_number,
        ps.product_id,
        p.sku,
        p.name AS product_name,
        p.category,
        COALESCE(p.specs->>'brand', 'Generic') AS brand,
        ps.cost_price,
        COALESCE(ps.created_at::date, CURRENT_DATE) AS intake_date,
        CURRENT_DATE - COALESCE(ps.created_at::date, CURRENT_DATE) AS shelf_days
    FROM product_serials ps
    INNER JOIN products p ON ps.product_id = p.id
    WHERE ps.status = 'available'
),
aging_matrix AS (
    SELECT 
        *,
        CASE 
            WHEN shelf_days BETWEEN 0 AND 30 THEN '0_30_HEALTHY'
            WHEN shelf_days BETWEEN 31 AND 60 THEN '31_60_WARNING'
            WHEN shelf_days BETWEEN 61 AND 90 THEN '61_90_SLOW'
            ELSE '91_PLUS_DEAD_STOCK'
        END AS age_tier
    FROM inventory_aging
)
SELECT 
    category,
    sku,
    product_name,
    COUNT(serial_id) AS total_on_hand_units,
    ROUND(SUM(cost_price), 2) AS total_tied_capital,
    COUNT(serial_id) FILTER (WHERE age_tier = '0_30_HEALTHY') AS units_0_30,
    COUNT(serial_id) FILTER (WHERE age_tier = '31_60_WARNING') AS units_31_60,
    COUNT(serial_id) FILTER (WHERE age_tier = '61_90_SLOW') AS units_61_90,
    COUNT(serial_id) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK') AS units_91_plus,
    ROUND(COALESCE(SUM(cost_price) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK'), 0), 2) AS dead_capital_risk,
    ROUND(
        (COUNT(serial_id) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK')::numeric / 
         NULLIF(COUNT(serial_id), 0)) * 100, 
        1
    ) AS dead_stock_pct
FROM aging_matrix
GROUP BY category, sku, product_name
HAVING COUNT(serial_id) > 0
ORDER BY dead_capital_risk DESC, total_tied_capital DESC;


-- ----------------------------------------------------------------------------
-- 5. REPORT 4: RFQ CONVERSION & WIN-RATE REPORT
-- ----------------------------------------------------------------------------
-- Parameters: :start_date (TIMESTAMP), :end_date (TIMESTAMP)
WITH rfq_metrics AS (
    SELECT 
        r.id AS rfq_id,
        r.rfq_number,
        r.company_id,
        c.company_name,
        c.tier AS company_tier,
        r.status,
        r.created_at AS submitted_at,
        r.closed_at,
        ROUND(EXTRACT(EPOCH FROM (r.closed_at - r.created_at)) / 3600.0, 2) AS turnaround_hours,
        so.id AS converted_order_id,
        COALESCE(so.total_amount, 0) AS converted_revenue
    FROM rfq_requests r
    INNER JOIN companies c ON r.company_id = c.id
    LEFT JOIN sales_orders so ON so.rfq_id = r.id AND so.status NOT IN ('cancelled')
    WHERE r.created_at >= :start_date 
      AND r.created_at <  :end_date
)
SELECT 
    company_tier,
    COUNT(rfq_id) AS total_rfqs_submitted,
    COUNT(rfq_id) FILTER (WHERE status = 'quoted') AS rfqs_quoted,
    COUNT(rfq_id) FILTER (WHERE status = 'accepted') AS rfqs_accepted,
    COUNT(rfq_id) FILTER (WHERE status = 'rejected') AS rfqs_rejected,
    ROUND(
        (COUNT(rfq_id) FILTER (WHERE status = 'accepted')::numeric / 
         NULLIF(COUNT(rfq_id) FILTER (WHERE status IN ('accepted', 'rejected')), 0)) * 100, 
        2
    ) AS win_rate_pct,
    ROUND(
        AVG(turnaround_hours) FILTER (WHERE turnaround_hours IS NOT NULL), 
        1
    ) AS avg_turnaround_hours,
    ROUND(SUM(converted_revenue), 2) AS total_realized_revenue
FROM rfq_metrics
GROUP BY company_tier
ORDER BY total_realized_revenue DESC;


-- ----------------------------------------------------------------------------
-- 6. REPORT 5: HARDWARE RMA & DEFECT RATE BY SKU/BRAND
-- ----------------------------------------------------------------------------
WITH brand_extracted_serials AS (
    SELECT 
        ps.id AS serial_id,
        ps.status,
        p.id AS product_id,
        p.sku,
        p.name AS product_name,
        p.category,
        COALESCE(p.specs->>'brand', 'Generic / OEM') AS brand
    FROM product_serials ps
    INNER JOIN products p ON ps.product_id = p.id
    WHERE ps.status IN ('sold', 'rma_pending', 'defective')
)
SELECT 
    brand,
    category,
    sku,
    product_name,
    COUNT(serial_id) FILTER (WHERE status = 'sold') AS units_sold,
    COUNT(serial_id) FILTER (WHERE status = 'rma_pending') AS units_rma_pending,
    COUNT(serial_id) FILTER (WHERE status = 'defective') AS units_defective,
    (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))) AS total_returns,
    ROUND(
        (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / 
         NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100, 
        2
    ) AS rma_defect_rate_pct,
    CASE 
        WHEN (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / 
              NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100 >= 4.00 
        THEN 'CRITICAL_ACTION_REQUIRED'
        WHEN (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / 
              NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100 >= 2.00 
        THEN 'ELEVATED_FAILURE_RATE'
        ELSE 'NORMAL'
    END AS quality_flag
FROM brand_extracted_serials
GROUP BY brand, category, sku, product_name
HAVING COUNT(serial_id) FILTER (WHERE status = 'sold') >= 5
ORDER BY rma_defect_rate_pct DESC, total_returns DESC;

