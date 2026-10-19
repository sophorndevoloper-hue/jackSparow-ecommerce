# Supplier Management & Inbound Receiving Module

A standalone, production-ready Supplier Management and Inbound Warehouse Receiving module for B2B computer parts e-commerce and ERP systems.

---

## 🚀 Key Features

1. **Strict Serial-Driven Inventory Intake**:
   - Hardware intake occurs strictly at the individual serial number level against official Supplier Purchase Orders (PO).
   - Real-time duplicate barcode detection across both existing database records and current intake queue batches.
   - Strict over-receiving safeguards preventing warehouse staff from scanning more items than authorized on PO line items.

2. **Accurate GAAP Landed Cost Calculation**:
   - Automatically calculates and attributes landed freight and customs duty to each physical hardware unit:
     $$\text{Duty/Freight Ratio} = \frac{\text{Unit Cost}}{\sum (\text{Line Item Cost} \times \text{Ordered Qty})}$$
     $$\text{Serial Landed Cost Basis} = \text{Unit Cost} + (\text{Duty/Freight Ratio} \times \text{Total Shipping/Duty})$$
   - Ensures precise inventory valuation on the balance sheet and accurate margins upon eventual B2B resale.

3. **Vendor Net Terms & Accounts Payable (A/P)**:
   - Full support for `PREPAID`, `NET_15`, `NET_30`, and `NET_60` payment terms.
   - Tracks automated payment due dates and A/P aging buckets.

4. **Defective Unit & Supplier RMA Lifecycle**:
   - Inbound or post-test defective serials are isolated into `RMA_PENDING` and `DEFECTIVE` statuses.
   - Dual resolution pathways:
     - **Replacement Serial Intake**: Seamlessly intakes replacement hardware serials into active stock carrying forward the original cost basis.
     - **Vendor Credit Memo**: Directly records financial credit notes against supplier open liabilities.

5. **Production SQL Analytics**:
   - A/P Aging Breakdown (`Current`, `1-30`, `31-60`, `61-90`, `90+` days overdue).
   - Supplier Delivery Accuracy & Inbound Defect Rate.
   - Vendor RMA Resolution Cycle Time and Turnaround Speed.

---

## 📂 Architecture & Directory Structure

```
modules/supplier-inbound/
├── prisma/
│   └── schema.prisma                # PostgreSQL models, enums & composite indexes
├── sql/
│   └── ap_aging_and_vendor_metrics.sql # Raw PostgreSQL performance-indexed reports
├── src/
│   ├── actions/
│   │   ├── receivePoBatch.ts        # Atomic intake action with landed cost apportionment
│   │   └── supplierRmaActions.ts    # Vendor RMA filing & dual resolution actions
│   ├── components/
│   │   ├── WarehouseIntake.tsx      # Barcode scanning warehouse workstation UI
│   │   ├── SupplierDirectory.tsx    # Vendor directory & A/P terms management
│   │   └── VendorRmaDashboard.tsx   # Supplier RMA claims & resolution modals
│   └── lib/
│       └── supplierAnalytics.ts     # Typed Prisma queries for ERP dashboard reporting
├── package.json
└── README.md
```

---

## 🛠️ Data Model Overview (`schema.prisma`)

- **`Supplier`**: Holds vendor profile, address, contact, default `PaymentTerms`, and open A/P balance.
- **`PurchaseOrder`**: Official supplier purchase order with `poNumber`, `issueDate`, `paymentDueDate`, `totalDutyAndFreight`, `totalAmount`, and status transitions (`DRAFT` → `ISSUED` → `PARTIALLY_RECEIVED` → `RECEIVED` → `CLOSED`).
- **`PurchaseOrderItem`**: Line items tracking `quantityOrdered`, `quantityReceived`, and `unitCost`.
- **`ProductSerial`**: The core physical unit tracker, storing unique `serialNumber`, `costPrice` (inclusive of landed cost), `status` (`AVAILABLE`, `RESERVED`, `SOLD`, `RMA_PENDING`, `DEFECTIVE`), and 36-month warranty.
- **`SupplierRmaClaim`**: Tracks defective serial returns to suppliers, resolution status (`PENDING`, `APPROVED_BY_VENDOR`, `RESOLVED_REPLACEMENT`, `RESOLVED_CREDIT`), replacement serial links, and credit memo amounts.

---

## 💡 Quick Integration Guide

### 1. Database Migration
```bash
npx prisma migrate dev --schema=./prisma/schema.prisma
```

### 2. Using the Warehouse Intake Component
```tsx
import WarehouseIntake from "@/modules/supplier-inbound/src/components/WarehouseIntake";

export default function ReceivingStationPage({ poData }) {
  return <WarehouseIntake purchaseOrder={poData} />;
}
```

### 3. Executing Inbound Receiving Programmatically
```typescript
import { receivePoBatch } from "@/modules/supplier-inbound/src/actions/receivePoBatch";

const result = await receivePoBatch({
  purchaseOrderId: "po-uuid-here",
  scannedItems: [
    { poItemId: "item-1-uuid", serialNumber: "SN-RTX4090-001" },
    { poItemId: "item-1-uuid", serialNumber: "SN-RTX4090-002" },
  ]
});
```

