/**
 * B2B E-Commerce Platform Analytics & Financial Aggregation Engine
 * Optimized for PostgreSQL connection pools (pg / pg-promise)
 */

import { Pool, QueryResult } from 'pg';

export interface DateFilterRange {
  startDate: string; // ISO 8601 (YYYY-MM-DD or YYYY-MM-DDTHH:mm:ssZ)
  endDate: string;
}

export interface GrossMarginMetric {
  companyTier: string;
  category: string;
  sku: string;
  unitsSold: number;
  totalCogs: number;
  totalRevenue: number;
  totalGrossProfit: number;
  marginPct: number;
}

export interface AccountsReceivableAgingRow {
  companyId: number;
  companyName: string;
  tier: string;
  paymentTerms: string;
  creditLimit: number;
  creditBalance: number;
  openOrderCount: number;
  totalArBalance: number;
  bucketCurrent: number;
  bucket1To30: number;
  bucket31To60: number;
  bucket61To90: number;
  bucket91Plus: number;
  accountRiskStatus: 'GOOD_STANDING' | 'MODERATE_RISK' | 'HIGH_RISK' | 'CREDIT_HOLD';
}

export interface StockAgingItem {
  category: string;
  sku: string;
  productName: string;
  totalOnHandUnits: number;
  totalTiedCapital: number;
  units0To30: number;
  units31To60: number;
  units61To90: number;
  units91Plus: number;
  deadCapitalRisk: number;
  deadStockPct: number;
}

export interface RfqPerformanceMetric {
  companyTier: string;
  totalRfqsSubmitted: number;
  rfqsQuoted: number;
  rfqsAccepted: number;
  rfqsRejected: number;
  winRatePct: number;
  avgTurnaroundHours: number;
  totalRealizedRevenue: number;
}

export interface RmaDefectMetric {
  brand: string;
  category: string;
  sku: string;
  productName: string;
  unitsSold: number;
  unitsRmaPending: number;
  unitsDefective: number;
  totalReturns: number;
  rmaDefectRatePct: number;
  qualityFlag: 'NORMAL' | 'ELEVATED_FAILURE_RATE' | 'CRITICAL_ACTION_REQUIRED';
}

export interface ExecutiveDashboardPayload {
  status: 'success';
  meta: {
    generatedAt: string;
    currency: string;
    dateRange: DateFilterRange;
  };
  data: {
    financialSummary: {
      totalRevenue: number;
      totalGrossProfit: number;
      overallMarginPct: number;
      totalArOutstanding: number;
      totalOverdue91Plus: number;
      accountsOnCreditHold: number;
    };
    inventorySummary: {
      totalOnHandUnits: number;
      totalValuation: number;
      deadCapitalRisk: number;
      deadStockRatioPct: number;
    };
    rfqPipeline: {
      overallWinRatePct: number;
      avgQuoteTurnaroundHours: number;
      totalPipelineValue: number;
      totalRealizedRevenue: number;
    };
    qualityOverview: {
      totalReturns: number;
      overallDefectRatePct: number;
      criticalFlagCount: number;
    };
    reports: {
      grossMarginByTier: GrossMarginMetric[];
      agingReceivables: AccountsReceivableAgingRow[];
      deadStockInventory: StockAgingItem[];
      rfqMetrics: RfqPerformanceMetric[];
      rmaDefectRates: RmaDefectMetric[];
    };
  };
}

export class B2BAnalyticsService {
  constructor(private readonly pool: Pool) {}

  /**
   * 1. Real Gross Margin by Serial Number (Hardware Profitability)
   */
  async getGrossMarginReport(range: DateFilterRange): Promise<GrossMarginMetric[]> {
    const sql = `
      WITH serial_sales_ledger AS (
          SELECT 
              ps.id AS serial_id,
              p.sku,
              p.category,
              c.tier AS company_tier,
              ps.cost_price,
              soi.unit_price AS sold_price,
              (soi.unit_price - ps.cost_price) AS gross_profit
          FROM product_serials ps
          INNER JOIN sales_order_items soi ON ps.order_item_id = soi.id
          INNER JOIN sales_orders so ON soi.order_id = so.id
          INNER JOIN products p ON ps.product_id = p.id
          INNER JOIN companies c ON so.company_id = c.id
          WHERE ps.status = 'sold'
            AND so.status NOT IN ('cancelled', 'draft')
            AND so.created_at >= $1 AND so.created_at < $2
      )
      SELECT 
          COALESCE(company_tier, 'ALL TIERS') AS "companyTier",
          COALESCE(category, 'ALL CATEGORIES') AS "category",
          COALESCE(sku, 'ALL SKUS') AS "sku",
          COUNT(serial_id)::int AS "unitsSold",
          ROUND(SUM(cost_price), 2)::float AS "totalCogs",
          ROUND(SUM(sold_price), 2)::float AS "totalRevenue",
          ROUND(SUM(gross_profit), 2)::float AS "totalGrossProfit",
          ROUND((SUM(gross_profit) / NULLIF(SUM(sold_price), 0)) * 100, 2)::float AS "marginPct"
      FROM serial_sales_ledger
      GROUP BY GROUPING SETS ((company_tier), (category), (category, sku), ())
      ORDER BY "companyTier" NULLS LAST, "totalRevenue" DESC;
    `;

    const res: QueryResult<GrossMarginMetric> = await this.pool.query(sql, [range.startDate, range.endDate]);
    return res.rows;
  }

  /**
   * 2. Aging Accounts Receivable (A/R) for Net Terms (Net 30/60)
   */
  async getAgingAccountsReceivable(): Promise<AccountsReceivableAgingRow[]> {
    const sql = `
      WITH invoice_aging AS (
          SELECT 
              so.company_id,
              c.company_name,
              c.tier,
              c.payment_terms,
              c.credit_limit,
              c.credit_balance,
              so.total_amount,
              (CURRENT_DATE - so.payment_due_date::date) AS days_overdue
          FROM sales_orders so
          INNER JOIN companies c ON so.company_id = c.id
          WHERE so.status IN ('pending_payment', 'invoiced', 'overdue', 'delivered')
            AND so.status NOT IN ('paid', 'cancelled')
      )
      SELECT 
          company_id AS "companyId",
          company_name AS "companyName",
          tier,
          payment_terms AS "paymentTerms",
          credit_limit::float AS "creditLimit",
          credit_balance::float AS "creditBalance",
          COUNT(*)::int AS "openOrderCount",
          ROUND(SUM(total_amount), 2)::float AS "totalArBalance",
          ROUND(SUM(CASE WHEN days_overdue <= 0 THEN total_amount ELSE 0 END), 2)::float AS "bucketCurrent",
          ROUND(SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN total_amount ELSE 0 END), 2)::float AS "bucket1To30",
          ROUND(SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN total_amount ELSE 0 END), 2)::float AS "bucket31To60",
          ROUND(SUM(CASE WHEN days_overdue BETWEEN 61 AND 90 THEN total_amount ELSE 0 END), 2)::float AS "bucket61To90",
          ROUND(SUM(CASE WHEN days_overdue >= 91 THEN total_amount ELSE 0 END), 2)::float AS "bucket91Plus",
          CASE 
              WHEN SUM(CASE WHEN days_overdue >= 91 THEN total_amount ELSE 0 END) > 0 THEN 'CREDIT_HOLD'
              WHEN SUM(CASE WHEN days_overdue BETWEEN 61 AND 90 THEN total_amount ELSE 0 END) > 0 OR credit_balance > credit_limit THEN 'HIGH_RISK'
              WHEN SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN total_amount ELSE 0 END) > 0 THEN 'MODERATE_RISK'
              ELSE 'GOOD_STANDING'
          END AS "accountRiskStatus"
      FROM invoice_aging
      GROUP BY company_id, company_name, tier, payment_terms, credit_limit, credit_balance
      ORDER BY "bucket91Plus" DESC, "totalArBalance" DESC;
    `;

    const res: QueryResult<AccountsReceivableAgingRow> = await this.pool.query(sql);
    return res.rows;
  }

  /**
   * 3. Stock Aging & Dead Inventory (Depreciation Risk)
   */
  async getStockAgingReport(): Promise<StockAgingItem[]> {
    const sql = `
      WITH inventory_aging AS (
          SELECT 
              ps.id AS serial_id,
              p.sku,
              p.name AS product_name,
              p.category,
              ps.cost_price,
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
          product_name AS "productName",
          COUNT(serial_id)::int AS "totalOnHandUnits",
          ROUND(SUM(cost_price), 2)::float AS "totalTiedCapital",
          COUNT(serial_id) FILTER (WHERE age_tier = '0_30_HEALTHY')::int AS "units0To30",
          COUNT(serial_id) FILTER (WHERE age_tier = '31_60_WARNING')::int AS "units31To60",
          COUNT(serial_id) FILTER (WHERE age_tier = '61_90_SLOW')::int AS "units61To90",
          COUNT(serial_id) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK')::int AS "units91Plus",
          ROUND(COALESCE(SUM(cost_price) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK'), 0), 2)::float AS "deadCapitalRisk",
          ROUND((COUNT(serial_id) FILTER (WHERE age_tier = '91_PLUS_DEAD_STOCK')::numeric / NULLIF(COUNT(serial_id), 0)) * 100, 1)::float AS "deadStockPct"
      FROM aging_matrix
      GROUP BY category, sku, product_name
      HAVING COUNT(serial_id) > 0
      ORDER BY "deadCapitalRisk" DESC, "totalTiedCapital" DESC;
    `;

    const res: QueryResult<StockAgingItem> = await this.pool.query(sql);
    return res.rows;
  }

  /**
   * 4. RFQ Conversion & Win-Rate Report
   */
  async getRfqWinRateReport(range: DateFilterRange): Promise<RfqPerformanceMetric[]> {
    const sql = `
      WITH rfq_metrics AS (
          SELECT 
              r.id AS rfq_id,
              c.tier AS company_tier,
              r.status,
              ROUND(EXTRACT(EPOCH FROM (r.closed_at - r.created_at)) / 3600.0, 2) AS turnaround_hours,
              COALESCE(so.total_amount, 0) AS converted_revenue
          FROM rfq_requests r
          INNER JOIN companies c ON r.company_id = c.id
          LEFT JOIN sales_orders so ON so.rfq_id = r.id AND so.status NOT IN ('cancelled')
          WHERE r.created_at >= $1 AND r.created_at < $2
      )
      SELECT 
          company_tier AS "companyTier",
          COUNT(rfq_id)::int AS "totalRfqsSubmitted",
          COUNT(rfq_id) FILTER (WHERE status = 'quoted')::int AS "rfqsQuoted",
          COUNT(rfq_id) FILTER (WHERE status = 'accepted')::int AS "rfqsAccepted",
          COUNT(rfq_id) FILTER (WHERE status = 'rejected')::int AS "rfqsRejected",
          ROUND((COUNT(rfq_id) FILTER (WHERE status = 'accepted')::numeric / NULLIF(COUNT(rfq_id) FILTER (WHERE status IN ('accepted', 'rejected')), 0)) * 100, 2)::float AS "winRatePct",
          ROUND(AVG(turnaround_hours) FILTER (WHERE turnaround_hours IS NOT NULL), 1)::float AS "avgTurnaroundHours",
          ROUND(SUM(converted_revenue), 2)::float AS "totalRealizedRevenue"
      FROM rfq_metrics
      GROUP BY company_tier
      ORDER BY "totalRealizedRevenue" DESC;
    `;

    const res: QueryResult<RfqPerformanceMetric> = await this.pool.query(sql, [range.startDate, range.endDate]);
    return res.rows;
  }

  /**
   * 5. Hardware RMA & Defect Rate by SKU/Brand
   */
  async getRmaDefectRateReport(): Promise<RmaDefectMetric[]> {
    const sql = `
      WITH brand_extracted_serials AS (
          SELECT 
              ps.id AS serial_id,
              ps.status,
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
          product_name AS "productName",
          COUNT(serial_id) FILTER (WHERE status = 'sold')::int AS "unitsSold",
          COUNT(serial_id) FILTER (WHERE status = 'rma_pending')::int AS "unitsRmaPending",
          COUNT(serial_id) FILTER (WHERE status = 'defective')::int AS "unitsDefective",
          (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective')))::int AS "totalReturns",
          ROUND((COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100, 2)::float AS "rmaDefectRatePct",
          CASE 
              WHEN (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100 >= 4.00 THEN 'CRITICAL_ACTION_REQUIRED'
              WHEN (COUNT(serial_id) FILTER (WHERE status IN ('rma_pending', 'defective'))::numeric / NULLIF(COUNT(serial_id) FILTER (WHERE status = 'sold'), 0)) * 100 >= 2.00 THEN 'ELEVATED_FAILURE_RATE'
              ELSE 'NORMAL'
          END AS "qualityFlag"
      FROM brand_extracted_serials
      GROUP BY brand, category, sku, product_name
      HAVING COUNT(serial_id) FILTER (WHERE status = 'sold') >= 5
      ORDER BY "rmaDefectRatePct" DESC, "totalReturns" DESC;
    `;

    const res: QueryResult<RmaDefectMetric> = await this.pool.query(sql);
    return res.rows;
  }
}

