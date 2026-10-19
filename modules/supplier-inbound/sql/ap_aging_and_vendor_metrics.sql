-- ==============================================================================
-- B2B Computer Parts ERP - Supplier Analytics & Financial Reporting Queries
-- Database Engine: PostgreSQL 14+
-- Module: Supplier Inbound & Vendor Accounts Payable (A/P)
-- ==============================================================================

-- ==============================================================================
-- 1. ACCOUNTS PAYABLE (A/P) AGING REPORT
-- Buckets all outstanding supplier liabilities into standard accounting aging
-- brackets: Current (not yet due), 1-30 Days Overdue, 31-60 Days Overdue,
-- 61-90 Days Overdue, and 90+ Days Overdue.
-- ==============================================================================

WITH po_balances AS (
    SELECT
        s.id AS supplier_id,
        s.name AS supplier_name,
        s.payment_terms,
        po.id AS po_id,
        po.po_number,
        po.issue_date,
        po.payment_due_date,
        po.total_amount,
        po.status,
        -- Calculate aging days relative to payment due date
        CASE 
            WHEN po.payment_due_date >= CURRENT_DATE THEN 0
            ELSE (CURRENT_DATE - po.payment_due_date)
        END AS days_past_due
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    WHERE po.status IN ('ISSUED', 'PARTIALLY_RECEIVED', 'RECEIVED')
)
SELECT
    supplier_id,
    supplier_name,
    payment_terms,
    COUNT(po_id) AS open_po_count,
    ROUND(SUM(total_amount), 2) AS total_outstanding_ap,
    -- Current (Not yet due)
    ROUND(SUM(CASE WHEN days_past_due = 0 THEN total_amount ELSE 0 END), 2) AS current_due,
    -- 1 - 30 Days Overdue
    ROUND(SUM(CASE WHEN days_past_due BETWEEN 1 AND 30 THEN total_amount ELSE 0 END), 2) AS past_due_1_30,
    -- 31 - 60 Days Overdue
    ROUND(SUM(CASE WHEN days_past_due BETWEEN 31 AND 60 THEN total_amount ELSE 0 END), 2) AS past_due_31_60,
    -- 61 - 90 Days Overdue
    ROUND(SUM(CASE WHEN days_past_due BETWEEN 61 AND 90 THEN total_amount ELSE 0 END), 2) AS past_due_61_90,
    -- 90+ Days Severely Overdue
    ROUND(SUM(CASE WHEN days_past_due > 90 THEN total_amount ELSE 0 END), 2) AS past_due_over_90
FROM po_balances
GROUP BY supplier_id, supplier_name, payment_terms
ORDER BY past_due_over_90 DESC, total_outstanding_ap DESC;


-- ==============================================================================
-- 2. VENDOR FULFILLMENT ACCURACY & HARDWARE DEFECT RATE
-- Measures delivery accuracy (Quantity Ordered vs Quantity Received),
-- On-Time Delivery performance (Comparing fulfillment dates to expected dates),
-- and Hardware Quality / Defect Rate (Defective/RMA serials vs Total received).
-- ==============================================================================

WITH supplier_po_aggregates AS (
    SELECT
        po.supplier_id,
        COUNT(DISTINCT po.id) AS total_purchase_orders,
        -- On-time delivery rate (POs fulfilled on or prior to expected arrival date)
        COUNT(DISTINCT CASE 
            WHEN po.status = 'RECEIVED' AND po.expected_date IS NOT NULL 
                 AND po.updated_at::date <= po.expected_date 
            THEN po.id 
        END) AS on_time_pos,
        SUM(poi.quantity_ordered) AS total_units_ordered,
        SUM(poi.quantity_received) AS total_units_received
    FROM purchase_orders po
    JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
    WHERE po.status IN ('PARTIALLY_RECEIVED', 'RECEIVED')
    GROUP BY po.supplier_id
),
supplier_defect_rates AS (
    SELECT
        po.supplier_id,
        COUNT(ps.id) AS total_serials_ingested,
        COUNT(CASE WHEN ps.status = 'DEFECTIVE' OR rma.id IS NOT NULL THEN ps.id END) AS defective_serials_count
    FROM product_serials ps
    JOIN purchase_order_items poi ON ps.po_item_id = poi.id
    JOIN purchase_orders po ON poi.purchase_order_id = po.id
    LEFT JOIN supplier_rma_claims rma ON ps.id = rma.serial_id
    GROUP BY po.supplier_id
)
SELECT
    s.id AS supplier_id,
    s.name AS supplier_name,
    COALESCE(pa.total_purchase_orders, 0) AS total_completed_pos,
    COALESCE(pa.total_units_ordered, 0) AS total_units_ordered,
    COALESCE(pa.total_units_received, 0) AS total_units_received,
    -- Fulfillment Fill Rate (%)
    ROUND(
        CASE 
            WHEN COALESCE(pa.total_units_ordered, 0) = 0 THEN 0
            ELSE (COALESCE(pa.total_units_received, 0)::numeric / pa.total_units_ordered::numeric) * 100
        END, 
        2
    ) AS fill_rate_pct,
    -- On-Time Delivery Performance (%)
    ROUND(
        CASE 
            WHEN COALESCE(pa.total_purchase_orders, 0) = 0 THEN 0
            ELSE (COALESCE(pa.on_time_pos, 0)::numeric / pa.total_purchase_orders::numeric) * 100
        END, 
        2
    ) AS on_time_delivery_pct,
    -- Hardware Inbound Defect Rate (%)
    ROUND(
        CASE 
            WHEN COALESCE(sd.total_serials_ingested, 0) = 0 THEN 0
            ELSE (COALESCE(sd.defective_serials_count, 0)::numeric / sd.total_serials_ingested::numeric) * 100
        END, 
        2
    ) AS defect_rate_pct
FROM suppliers s
LEFT JOIN supplier_po_aggregates pa ON s.id = pa.supplier_id
LEFT JOIN supplier_defect_rates sd ON s.id = sd.supplier_id
ORDER BY total_units_received DESC;


-- ==============================================================================
-- 3. SUPPLIER RMA RESOLUTION EFFICIENCY & CYCLE TIME
-- Analyzes vendor turnaround speed for RMA claims:
-- Average days to close, claim disposition (replacement serial vs credit memo),
-- and total financial compensation recovered per supplier.
-- ==============================================================================

SELECT
    s.id AS supplier_id,
    s.name AS supplier_name,
    COUNT(rma.id) AS total_claims_filed,
    COUNT(CASE WHEN rma.status = 'PENDING' THEN 1 END) AS pending_claims,
    COUNT(CASE WHEN rma.status = 'RESOLVED_REPLACEMENT' THEN 1 END) AS replacement_resolutions,
    COUNT(CASE WHEN rma.status = 'RESOLVED_CREDIT' THEN 1 END) AS credit_resolutions,
    COUNT(CASE WHEN rma.status = 'REJECTED' THEN 1 END) AS rejected_claims,
    -- Average Resolution Cycle Time in Days (Closed date - Created date)
    ROUND(
        AVG(
            CASE 
                WHEN rma.closed_at IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (rma.closed_at - rma.created_at)) / 86400.0 
            END
        )::numeric, 
        1
    ) AS avg_turnaround_days,
    -- Total financial credits recovered via Credit Notes
    COALESCE(ROUND(SUM(rma.vendor_credit_amount), 2), 0.00) AS total_credits_recovered
FROM suppliers s
JOIN supplier_rma_claims rma ON s.id = rma.supplier_id
GROUP BY s.id, s.name
ORDER BY total_claims_filed DESC, avg_turnaround_days ASC;

