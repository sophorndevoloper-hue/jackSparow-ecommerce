"use server";

import { PrismaClient, PurchaseOrderStatus, SerialStatus } from "@prisma/client";
import { revalidatePath } from "next/cache";

const prisma = new PrismaClient();

export interface ScannedUnitInput {
  productId: string;
  serialNumber: string;
}

export interface ReceivePoBatchResult {
  success: boolean;
  message: string;
  poStatus?: PurchaseOrderStatus;
  insertedCount?: number;
  errors?: string[];
}

/**
 * Atomic Inbound Warehouse Receiving Server Action
 * Validates physical serials, allocates true landed costs, and updates PO state.
 */
export async function receivePoBatch(
  poId: string,
  scannedUnits: ScannedUnitInput[]
): Promise<ReceivePoBatchResult> {
  if (!poId || scannedUnits.length === 0) {
    return { success: false, message: "Invalid PO ID or empty serial batch." };
  }

  try {
    return await prisma.$transaction(async (tx) => {
      // 1. Fetch Purchase Order and line items
      const po = await tx.purchaseOrder.findUnique({
        where: { id: poId },
        include: {
          items: {
            include: { product: true },
          },
        },
      });

      if (!po) {
        throw new Error(`Purchase Order '${poId}' not found.`);
      }

      if (po.status === PurchaseOrderStatus.RECEIVED) {
        throw new Error(`Purchase Order '${po.poNumber}' is already fully received.`);
      }

      if (po.status === PurchaseOrderStatus.CANCELLED) {
        throw new Error(`Cannot receive against cancelled PO '${po.poNumber}'.`);
      }

      // 2. Validate serial uniqueness across inventory
      const candidateSerials = scannedUnits.map((u) => u.serialNumber.trim().toUpperCase());
      const duplicatesInBatch = candidateSerials.filter(
        (item, index) => candidateSerials.indexOf(item) !== index
      );

      if (duplicatesInBatch.length > 0) {
        throw new Error(`Duplicate serial numbers found in scanned batch: ${duplicatesInBatch.join(", ")}`);
      }

      const existingSerials = await tx.productSerial.findMany({
        where: { serialNumber: { in: candidateSerials } },
        select: { serialNumber: true },
      });

      if (existingSerials.length > 0) {
        const collisions = existingSerials.map((s) => s.serialNumber).join(", ");
        throw new Error(`The following serial numbers already exist in inventory: ${collisions}`);
      }

      // 3. Compute True Landed Cost Allocation
      // Landed Cost = Base Unit Cost + (Item Base Cost / Total PO Base Cost) * Total Freight/Duty
      const totalPoBaseCost = po.items.reduce(
        (sum, item) => sum + Number(item.unitCost) * item.orderedQty,
        0
      );
      const totalDutyFreight = Number(po.shippingDutyCost) || 0;

      const itemMap = new Map(po.items.map((item) => [item.productId, item]));

      // Validate quantities against PO limits
      const incomingByProduct = new Map<string, number>();
      for (const unit of scannedUnits) {
        incomingByProduct.set(
          unit.productId,
          (incomingByProduct.get(unit.productId) || 0) + 1
        );
      }

      for (const [productId, incomingCount] of incomingByProduct.entries()) {
        const lineItem = itemMap.get(productId);
        if (!lineItem) {
          throw new Error(`Product ID '${productId}' is not part of PO ${po.poNumber}.`);
        }
        const remainingAllowed = lineItem.orderedQty - lineItem.receivedQty;
        if (incomingCount > remainingAllowed) {
          throw new Error(
            `Over-receiving error for ${lineItem.product.name} (SKU: ${lineItem.product.sku}). ` +
              `Attempted: ${incomingCount}, Remaining Expected: ${remainingAllowed}.`
          );
        }
      }

      // 4. Batch insert into ProductSerial and increment receivedQty on items
      const serialDataToInsert = [];

      for (const unit of scannedUnits) {
        const lineItem = itemMap.get(unit.productId)!;
        const unitBaseCost = Number(lineItem.unitCost);

        // Apportion freight proportionately based on unit value ratio
        const apportionedDuty =
          totalPoBaseCost > 0
            ? (unitBaseCost / totalPoBaseCost) * totalDutyFreight
            : 0;
        const landedCost = Number((unitBaseCost + apportionedDuty).toFixed(2));

        serialDataToInsert.push({
          productId: unit.productId,
          poItemId: lineItem.id,
          serialNumber: unit.serialNumber.trim().toUpperCase(),
          status: SerialStatus.AVAILABLE,
          costPrice: landedCost,
          warrantyMonths: 36,
          receivedAt: new Date(),
        });
      }

      // Bulk create physical serials
      await tx.productSerial.createMany({
        data: serialDataToInsert,
      });

      // Update line items receivedQty
      for (const [productId, count] of incomingByProduct.entries()) {
        const lineItem = itemMap.get(productId)!;
        await tx.purchaseOrderItem.update({
          where: { id: lineItem.id },
          data: { receivedQty: { increment: count } },
        });
      }

      // 5. Evaluate PO Fulfillment State
      const refreshedItems = await tx.purchaseOrderItem.findMany({
        where: { poId: po.id },
        select: { orderedQty: true, receivedQty: true },
      });

      const allReceived = refreshedItems.every((i) => i.receivedQty >= i.orderedQty);
      const anyReceived = refreshedItems.some((i) => i.receivedQty > 0);

      const nextStatus: PurchaseOrderStatus = allReceived
        ? PurchaseOrderStatus.RECEIVED
        : anyReceived
        ? PurchaseOrderStatus.PARTIALLY_RECEIVED
        : po.status;

      await tx.purchaseOrder.update({
        where: { id: po.id },
        data: { status: nextStatus },
      });

      revalidatePath(`/warehouse/intake/${poId}`);
      revalidatePath("/warehouse/intake");

      return {
        success: true,
        message: `Successfully received ${scannedUnits.length} physical units into inventory.`,
        poStatus: nextStatus,
        insertedCount: scannedUnits.length,
      };
    });
  } catch (error: any) {
    return {
      success: false,
      message: error.message || "Failed to process inbound serial intake transaction.",
    };
  }
}

