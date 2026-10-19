"use server";

import { PrismaClient, SerialStatus, SupplierRmaStatus } from "@prisma/client";
import { revalidatePath } from "next/cache";

const prisma = new PrismaClient();

export interface CreateRmaClaimInput {
  supplierId: string;
  serialId: string;
  faultDescription: string;
}

/**
 * File a Vendor RMA Claim for a defective hardware unit.
 */
export async function createSupplierRmaClaim(input: CreateRmaClaimInput) {
  try {
    return await prisma.$transaction(async (tx) => {
      const serial = await tx.productSerial.findUnique({
        where: { id: input.serialId },
      });

      if (!serial) {
        throw new Error(`Serial ID '${input.serialId}' not found.`);
      }

      // Transition serial status to RMA_PENDING
      await tx.productSerial.update({
        where: { id: serial.id },
        data: { status: SerialStatus.RMA_PENDING },
      });

      const claimNumber = `VRMA-${new Date().getFullYear()}-${Math.floor(100000 + Math.random() * 900000)}`;

      const claim = await tx.supplierRmaClaim.create({
        data: {
          claimNumber,
          supplierId: input.supplierId,
          serialId: serial.id,
          faultDescription: input.faultDescription,
          status: SupplierRmaStatus.PENDING,
        },
      });

      revalidatePath("/warehouse/rma");
      return { success: true, claim };
    });
  } catch (error: any) {
    return { success: false, message: error.message };
  }
}

/**
 * Resolve a Vendor RMA Claim by accepting a replacement serial number.
 */
export async function resolveWithReplacementSerial(
  claimId: string,
  newSerialNumber: string
) {
  try {
    return await prisma.$transaction(async (tx) => {
      const claim = await tx.supplierRmaClaim.findUnique({
        where: { id: claimId },
        include: { defectiveSerial: true },
      });

      if (!claim) {
        throw new Error("RMA Claim not found.");
      }

      const cleanNewSerial = newSerialNumber.trim().toUpperCase();

      // Check if new serial exists
      const existing = await tx.productSerial.findUnique({
        where: { serialNumber: cleanNewSerial },
      });

      if (existing) {
        throw new Error(`Replacement serial '${cleanNewSerial}' is already in the database.`);
      }

      // Create replacement serial unit into inventory
      const replacementSerial = await tx.productSerial.create({
        data: {
          productId: claim.defectiveSerial.productId,
          serialNumber: cleanNewSerial,
          status: SerialStatus.AVAILABLE,
          costPrice: claim.defectiveSerial.costPrice, // Maintain original landed cost basis
          warrantyMonths: 36,
          receivedAt: new Date(),
        },
      });

      // Mark original defective serial as DEFECTIVE
      await tx.productSerial.update({
        where: { id: claim.serialId },
        data: { status: SerialStatus.DEFECTIVE },
      });

      // Close claim with replacement reference
      await tx.supplierRmaClaim.update({
        where: { id: claimId },
        data: {
          status: SupplierRmaStatus.RESOLVED_REPLACEMENT,
          replacementSerialId: replacementSerial.id,
          closedAt: new Date(),
        },
      });

      revalidatePath("/warehouse/rma");
      return { success: true, message: `Claim resolved with replacement serial '${cleanNewSerial}'.` };
    });
  } catch (error: any) {
    return { success: false, message: error.message };
  }
}

/**
 * Resolve a Vendor RMA Claim by applying a financial Credit Note.
 */
export async function resolveWithCreditNote(
  claimId: string,
  creditAmount: number
) {
  try {
    return await prisma.$transaction(async (tx) => {
      const claim = await tx.supplierRmaClaim.findUnique({
        where: { id: claimId },
      });

      if (!claim) {
        throw new Error("RMA Claim not found.");
      }

      // Mark defective serial as DEFECTIVE
      await tx.productSerial.update({
        where: { id: claim.serialId },
        data: { status: SerialStatus.DEFECTIVE },
      });

      // Close claim with credit note amount
      await tx.supplierRmaClaim.update({
        where: { id: claimId },
        data: {
          status: SupplierRmaStatus.RESOLVED_CREDIT,
          vendorCreditAmount: creditAmount,
          closedAt: new Date(),
        },
      });

      revalidatePath("/warehouse/rma");
      return { success: true, message: `Claim resolved with vendor credit of $${creditAmount.toFixed(2)}.` };
    });
  } catch (error: any) {
    return { success: false, message: error.message };
  }
}

