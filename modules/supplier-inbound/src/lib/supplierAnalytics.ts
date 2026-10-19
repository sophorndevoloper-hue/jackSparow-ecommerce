import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

export interface ApAgingBucket {
  supplierId: string;
  supplierName: string;
  paymentTerms: string;
  openPoCount: number;
  totalOutstandingAp: number;
  currentDue: number;
  pastDue1to30: number;
  pastDue31to60: number;
  pastDue61to90: number;
  pastDueOver90: number;
}

export interface VendorPerformanceMetric {
  supplierId: string;
  supplierName: string;
  totalCompletedPos: number;
  totalUnitsOrdered: number;
  totalUnitsReceived: number;
  fillRatePct: number;
  onTimeDeliveryPct: number;
  defectRatePct: number;
}

export interface SupplierRmaMetric {
  supplierId: string;
  supplierName: string;
  totalClaimsFiled: number;
  pendingClaims: number;
  replacementResolutions: number;
  creditResolutions: number;
  rejectedClaims: number;
  avgTurnaroundDays: number | null;
  totalCreditsRecovered: number;
}

/**
 * Fetch Accounts Payable (A/P) Aging breakdown across all vendors.
 */
export async function getAccountsPayableAging(): Promise<ApAgingBucket[]> {
  const rawRows: any[] = await prisma.$queryRaw`
    WITH po_balances AS (
      SELECT
        s.id AS supplier_id,
        s.name AS supplier_name,
        s.payment_terms::text AS payment_terms,
        po.id AS po_id,
        po.po_number,
        po.issue_date,
        po.payment_due_date,
        po.total_amount,
        po.status::text AS status,
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
      COUNT(po_id)::int AS open_po_count,
      ROUND(SUM(total_amount)::numeric, 2)::float AS total_outstanding_ap,
      ROUND(SUM(CASE WHEN days_past_due = 0 THEN total_amount ELSE 0 END)::numeric, 2)::float AS current_due,
      ROUND(SUM(CASE WHEN days_past_due BETWEEN 1 AND 30 THEN total_amount ELSE 0 END)::numeric, 2)::float AS past_due_1_30,
      ROUND(SUM(CASE WHEN days_past_due BETWEEN 31 AND 60 THEN total_amount ELSE 0 END)::numeric, 2)::float AS past_due_31_60,
      ROUND(SUM(CASE WHEN days_past_due BETWEEN 61 AND 90 THEN total_amount ELSE 0 END)::numeric, 2)::float AS past_due_61_90,
      ROUND(SUM(CASE WHEN days_past_due > 90 THEN total_amount ELSE 0 END)::numeric, 2)::float AS past_due_over_90
    FROM po_balances
    GROUP BY supplier_id, supplier_name, payment_terms
    ORDER BY past_due_over_90 DESC, total_outstanding_ap DESC;
  `;

  return rawRows.map((row) => ({
    supplierId: row.supplier_id,
    supplierName: row.supplier_name,
    paymentTerms: row.payment_terms,
    openPoCount: Number(row.open_po_count),
    totalOutstandingAp: Number(row.total_outstanding_ap),
    currentDue: Number(row.current_due),
    pastDue1to30: Number(row.past_due_1_30),
    pastDue31to60: Number(row.past_due_31_60),
    pastDue61to90: Number(row.past_due_61_90),
    pastDueOver90: Number(row.past_due_over_90),
  }));
}

/**
 * Fetch Vendor Fulfillment and Quality performance metrics.
 */
export async function getVendorPerformanceMetrics(): Promise<VendorPerformanceMetric[]> {
  const rawRows: any[] = await prisma.$queryRaw`
    WITH supplier_po_aggregates AS (
      SELECT
        po.supplier_id,
        COUNT(DISTINCT po.id) AS total_purchase_orders,
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
      COALESCE(pa.total_purchase_orders, 0)::int AS total_completed_pos,
      COALESCE(pa.total_units_ordered, 0)::int AS total_units_ordered,
      COALESCE(pa.total_units_received, 0)::int AS total_units_received,
      ROUND(
        CASE 
          WHEN COALESCE(pa.total_units_ordered, 0) = 0 THEN 0
          ELSE (COALESCE(pa.total_units_received, 0)::numeric / pa.total_units_ordered::numeric) * 100
        END, 
        2
      )::float AS fill_rate_pct,
      ROUND(
        CASE 
          WHEN COALESCE(pa.total_purchase_orders, 0) = 0 THEN 0
          ELSE (COALESCE(pa.on_time_pos, 0)::numeric / pa.total_purchase_orders::numeric) * 100
        END, 
        2
      )::float AS on_time_delivery_pct,
      ROUND(
        CASE 
          WHEN COALESCE(sd.total_serials_ingested, 0) = 0 THEN 0
          ELSE (COALESCE(sd.defective_serials_count, 0)::numeric / sd.total_serials_ingested::numeric) * 100
        END, 
        2
      )::float AS defect_rate_pct
    FROM suppliers s
    LEFT JOIN supplier_po_aggregates pa ON s.id = pa.supplier_id
    LEFT JOIN supplier_defect_rates sd ON s.id = sd.supplier_id
    ORDER BY total_units_received DESC;
  `;

  return rawRows.map((row) => ({
    supplierId: row.supplier_id,
    supplierName: row.supplier_name,
    totalCompletedPos: Number(row.total_completed_pos),
    totalUnitsOrdered: Number(row.total_units_ordered),
    totalUnitsReceived: Number(row.total_units_received),
    fillRatePct: Number(row.fill_rate_pct),
    onTimeDeliveryPct: Number(row.on_time_delivery_pct),
    defectRatePct: Number(row.defect_rate_pct),
  }));
}

/**
 * Fetch Supplier RMA efficiency and cycle turnaround metrics.
 */
export async function getSupplierRmaMetrics(): Promise<SupplierRmaMetric[]> {
  const rawRows: any[] = await prisma.$queryRaw`
    SELECT
      s.id AS supplier_id,
      s.name AS supplier_name,
      COUNT(rma.id)::int AS total_claims_filed,
      COUNT(CASE WHEN rma.status = 'PENDING' THEN 1 END)::int AS pending_claims,
      COUNT(CASE WHEN rma.status = 'RESOLVED_REPLACEMENT' THEN 1 END)::int AS replacement_resolutions,
      COUNT(CASE WHEN rma.status = 'RESOLVED_CREDIT' THEN 1 END)::int AS credit_resolutions,
      COUNT(CASE WHEN rma.status = 'REJECTED' THEN 1 END)::int AS rejected_claims,
      ROUND(
        AVG(
          CASE 
            WHEN rma.closed_at IS NOT NULL 
            THEN EXTRACT(EPOCH FROM (rma.closed_at - rma.created_at)) / 86400.0 
          END
        )::numeric, 
        1
      )::float AS avg_turnaround_days,
      COALESCE(ROUND(SUM(rma.vendor_credit_amount)::numeric, 2), 0.00)::float AS total_credits_recovered
    FROM suppliers s
    JOIN supplier_rma_claims rma ON s.id = rma.supplier_id
    GROUP BY s.id, s.name
    ORDER BY total_claims_filed DESC;
  `;

  return rawRows.map((row) => ({
    supplierId: row.supplier_id,
    supplierName: row.supplier_name,
    totalClaimsFiled: Number(row.total_claims_filed),
    pendingClaims: Number(row.pending_claims),
    replacementResolutions: Number(row.replacement_resolutions),
    creditResolutions: Number(row.credit_resolutions),
    rejectedClaims: Number(row.rejected_claims),
    avgTurnaroundDays: row.avg_turnaround_days !== null ? Number(row.avg_turnaround_days) : null,
    totalCreditsRecovered: Number(row.total_credits_recovered),
  }));
}

