"use client";

import React, { useState, useRef, useEffect, useTransition } from "react";
import { receivePoBatch, ScannedUnitInput } from "../actions/receivePoBatch";
import { 
  Barcode, 
  CheckCircle2, 
  AlertCircle, 
  Trash2, 
  ArrowRight, 
  Loader2, 
  Boxes 
} from "lucide-react";

interface PoLineItem {
  id: string;
  productId: string;
  productName: string;
  productSku: string;
  orderedQty: number;
  receivedQty: number;
  unitCost: number;
}

interface WarehouseIntakeProps {
  purchaseOrder: {
    id: string;
    poNumber: string;
    supplierName: string;
    status: string;
    shippingDutyCost: number;
    items: PoLineItem[];
  };
}

export default function WarehouseIntake({ purchaseOrder }: WarehouseIntakeProps) {
  const [selectedProductId, setSelectedProductId] = useState<string>(
    purchaseOrder.items[0]?.productId || ""
  );
  const [currentBarcode, setCurrentBarcode] = useState<string>("");
  const [scanQueue, setScanQueue] = useState<ScannedUnitInput[]>([]);
  const [statusFeedback, setStatusFeedback] = useState<{ type: "success" | "error"; msg: string } | null>(null);
  const [isPending, startTransition] = useTransition();

  const scannerInputRef = useRef<HTMLInputElement>(null);

  // Keep scanner input focused at all times for hardware scanners
  useEffect(() => {
    scannerInputRef.current?.focus();
  }, [scanQueue, isPending]);

  // Real-time calculation of remaining quantities per product
  const getRemainingQty = (productId: string) => {
    const item = purchaseOrder.items.find((i) => i.productId === productId);
    if (!item) return 0;
    const inQueueCount = scanQueue.filter((q) => q.productId === productId).length;
    return Math.max(0, item.orderedQty - item.receivedQty - inQueueCount);
  };

  const handleBarcodeSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const barcode = currentBarcode.trim().toUpperCase();

    if (!barcode) return;

    // Check duplicate in current scan queue
    if (scanQueue.some((q) => q.serialNumber === barcode)) {
      setStatusFeedback({ type: "error", msg: `Serial '${barcode}' is already in the current scanning batch.` });
      setCurrentBarcode("");
      return;
    }

    // Check capacity against line item
    const remaining = getRemainingQty(selectedProductId);
    if (remaining <= 0) {
      setStatusFeedback({ type: "error", msg: "All expected units for this item have already been scanned." });
      setCurrentBarcode("");
      return;
    }

    // Add to batch queue
    setScanQueue((prev) => [{ productId: selectedProductId, serialNumber: barcode }, ...prev]);
    setStatusFeedback({ type: "success", msg: `Serial '${barcode}' added to batch.` });
    setCurrentBarcode("");
  };

  const removeItemFromQueue = (index: number) => {
    setScanQueue((prev) => prev.filter((_, idx) => idx !== index));
  };

  const handleCommitBatch = () => {
    if (scanQueue.length === 0) return;

    startTransition(async () => {
      const result = await receivePoBatch(purchaseOrder.id, scanQueue);
      if (result.success) {
        setScanQueue([]);
        setStatusFeedback({ type: "success", msg: result.message });
      } else {
        setStatusFeedback({ type: "error", msg: result.message });
      }
    });
  };

  return (
    <div className="max-w-7xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-lg border shadow-sm">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-mono font-bold text-gray-900">PO #{purchaseOrder.poNumber}</h1>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
              {purchaseOrder.status}
            </span>
          </div>
          <p className="text-sm text-gray-500 mt-1">
            Supplier: <strong className="text-gray-700">{purchaseOrder.supplierName}</strong> &bull; Landed Duty: ${purchaseOrder.shippingDutyCost.toFixed(2)}
          </p>
        </div>
        <button
          onClick={handleCommitBatch}
          disabled={scanQueue.length === 0 || isPending}
          className="mt-4 md:mt-0 flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-medium px-5 py-2.5 rounded-md shadow-sm transition"
        >
          {isPending ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
          Commit Batch ({scanQueue.length} Serials)
        </button>
      </div>

      {/* Notifications */}
      {statusFeedback && (
        <div
          className={`flex items-center gap-3 p-4 rounded-md text-sm ${
            statusFeedback.type === "success"
              ? "bg-emerald-50 text-emerald-800 border border-emerald-200"
              : "bg-red-50 text-red-800 border border-red-200"
          }`}
        >
          {statusFeedback.type === "success" ? (
            <CheckCircle2 className="w-5 h-5 flex-shrink-0 text-emerald-600" />
          ) : (
            <AlertCircle className="w-5 h-5 flex-shrink-0 text-red-600" />
          )}
          <span>{statusFeedback.msg}</span>
        </div>
      )}

      {/* Barcode Scanner Workstation Section */}
      <div className="bg-white border-2 border-indigo-500/20 bg-indigo-50/10 p-6 rounded-lg shadow-sm">
        <h2 className="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <Barcode className="w-5 h-5 text-indigo-600" /> High-Speed Hardware Barcode Scanner
        </h2>
        <form onSubmit={handleBarcodeSubmit} className="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div className="md:col-span-5">
            <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
              Active Receiving SKU
            </label>
            <select
              value={selectedProductId}
              onChange={(e) => setSelectedProductId(e.target.value)}
              className="w-full bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
              {purchaseOrder.items.map((item) => (
                <option key={item.productId} value={item.productId}>
                  {item.productSku} &bull; {item.productName} ({item.receivedQty}/{item.orderedQty})
                </option>
              ))}
            </select>
          </div>

          <div className="md:col-span-5">
            <label className="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
              Scan Barcode (Auto-Presses Enter)
            </label>
            <input
              ref={scannerInputRef}
              type="text"
              value={currentBarcode}
              onChange={(e) => setCurrentBarcode(e.target.value)}
              placeholder="Scan or enter serial number..."
              className="w-full bg-white border border-gray-300 rounded-md px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>

          <div className="md:col-span-2 flex items-end">
            <button
              type="submit"
              className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-md transition text-sm flex items-center justify-center gap-1"
            >
              Push <ArrowRight className="w-4 h-4" />
            </button>
          </div>
        </form>
      </div>

      {/* Main Grid: PO Items Progress vs Batch Queue */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Column: PO Items Progress */}
        <div className="lg:col-span-7 bg-white p-6 rounded-lg border shadow-sm space-y-4">
          <h3 className="font-semibold text-gray-900 flex items-center gap-2 border-b pb-3">
            <Boxes className="w-5 h-5 text-gray-600" /> Purchase Order Progress
          </h3>
          <div className="space-y-4">
            {purchaseOrder.items.map((item) => {
              const inQueue = scanQueue.filter((q) => q.productId === item.productId).length;
              const totalProcessed = item.receivedQty + inQueue;
              const progressPct = Math.min(100, Math.round((totalProcessed / item.orderedQty) * 100));

              return (
                <div key={item.id} className="p-4 border rounded-md space-y-2 bg-gray-50/50">
                  <div className="flex justify-between items-start">
                    <div>
                      <div className="font-semibold text-gray-900">{item.productName}</div>
                      <div className="text-xs font-mono text-gray-500">SKU: {item.productSku}</div>
                    </div>
                    <div className="text-right">
                      <span className="font-mono text-sm font-bold text-gray-800">
                        {totalProcessed} / {item.orderedQty}
                      </span>
                      {inQueue > 0 && (
                        <span className="text-xs text-indigo-600 block font-medium">
                          (+{inQueue} in batch)
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Progress Bar */}
                  <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div
                      className={`h-full transition-all duration-300 ${
                        progressPct >= 100 ? "bg-emerald-500" : "bg-indigo-600"
                      }`}
                      style={{ width: `${progressPct}%` }}
                    />
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Right Column: Uncommitted Scanned Queue */}
        <div className="lg:col-span-5 bg-white p-6 rounded-lg border shadow-sm space-y-4">
          <div className="flex justify-between items-center border-b pb-3">
            <h3 className="font-semibold text-gray-900">Uncommitted Serials ({scanQueue.length})</h3>
            {scanQueue.length > 0 && (
              <button
                onClick={() => setScanQueue([])}
                className="text-xs text-red-600 hover:text-red-800 font-medium"
              >
                Clear Queue
              </button>
            )}
          </div>

          {scanQueue.length === 0 ? (
            <div className="py-12 text-center text-gray-400 text-sm">
              No serials scanned yet. Point scanner at hardware barcodes to begin intake.
            </div>
          ) : (
            <div className="divide-y max-h-[420px] overflow-y-auto">
              {scanQueue.map((item, idx) => {
                const product = purchaseOrder.items.find((p) => p.productId === item.productId);
                return (
                  <div key={idx} className="py-2.5 flex justify-between items-center">
                    <div>
                      <div className="font-mono text-sm font-semibold text-gray-800">{item.serialNumber}</div>
                      <div className="text-xs text-gray-500">{product?.productSku}</div>
                    </div>
                    <button
                      onClick={() => removeItemFromQueue(idx)}
                      className="text-gray-400 hover:text-red-600 p-1 transition"
                      title="Remove serial"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

