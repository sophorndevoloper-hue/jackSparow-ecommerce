"use client";

import React, { useState, useTransition } from "react";
import {
  AlertTriangle,
  CheckCircle2,
  Clock,
  DollarSign,
  FileText,
  Plus,
  RefreshCw,
  Search,
  ShieldAlert,
  SlidersHorizontal,
  X,
  XCircle,
} from "lucide-react";
import {
  createSupplierRmaClaim,
  resolveWithCreditNote,
  resolveWithReplacementSerial,
} from "../actions/supplierRmaActions";

export interface SupplierRmaItem {
  id: string;
  claimNumber: string;
  supplierId: string;
  supplierName: string;
  serialId: string;
  serialNumber: string;
  productName: string;
  productSku: string;
  costPrice: number;
  faultDescription: string;
  status: "PENDING" | "APPROVED_BY_VENDOR" | "RESOLVED_REPLACEMENT" | "RESOLVED_CREDIT" | "REJECTED";
  replacementSerialId?: string | null;
  replacementSerialNumber?: string | null;
  vendorCreditAmount?: number | null;
  createdAt: string;
  closedAt?: string | null;
}

interface VendorRmaDashboardProps {
  initialClaims: SupplierRmaItem[];
  suppliersList?: { id: string; name: string }[];
}

export default function VendorRmaDashboard({
  initialClaims,
  suppliersList = [],
}: VendorRmaDashboardProps) {
  const [claims, setClaims] = useState<SupplierRmaItem[]>(initialClaims);
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("ALL");
  const [isPending, startTransition] = useTransition();

  // Modals state
  const [activeClaimForReplacement, setActiveClaimForReplacement] = useState<SupplierRmaItem | null>(null);
  const [activeClaimForCredit, setActiveClaimForCredit] = useState<SupplierRmaItem | null>(null);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);

  // Form states
  const [replacementSerialInput, setReplacementSerialInput] = useState("");
  const [creditAmountInput, setCreditAmountInput] = useState("");
  const [newClaimSupplierId, setNewClaimSupplierId] = useState("");
  const [newClaimSerialId, setNewClaimSerialId] = useState("");
  const [newClaimFault, setNewClaimFault] = useState("");
  const [actionFeedback, setActionFeedback] = useState<{ type: "success" | "error"; message: string } | null>(null);

  // Calculations
  const totalPending = claims.filter((c) => c.status === "PENDING" || c.status === "APPROVED_BY_VENDOR").length;
  const totalValueAtRisk = claims
    .filter((c) => c.status === "PENDING" || c.status === "APPROVED_BY_VENDOR")
    .reduce((acc, curr) => acc + (curr.costPrice || 0), 0);
  const totalCreditResolved = claims
    .filter((c) => c.status === "RESOLVED_CREDIT")
    .reduce((acc, curr) => acc + (curr.vendorCreditAmount || 0), 0);
  const totalReplacementResolved = claims.filter((c) => c.status === "RESOLVED_REPLACEMENT").length;

  // Filtered claims
  const filteredClaims = claims.filter((claim) => {
    const matchesSearch =
      claim.claimNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
      claim.supplierName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      claim.serialNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
      claim.productName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      claim.productSku.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesStatus = statusFilter === "ALL" || claim.status === statusFilter;

    return matchesSearch && matchesStatus;
  });

  const handleReplacementSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeClaimForReplacement || !replacementSerialInput.trim()) return;

    setActionFeedback(null);
    startTransition(async () => {
      const res = await resolveWithReplacementSerial(activeClaimForReplacement.id, replacementSerialInput.trim());
      if (res.success) {
        setClaims((prev) =>
          prev.map((item) =>
            item.id === activeClaimForReplacement.id
              ? {
                  ...item,
                  status: "RESOLVED_REPLACEMENT",
                  replacementSerialNumber: replacementSerialInput.trim().toUpperCase(),
                  closedAt: new Date().toISOString(),
                }
              : item
          )
        );
        setActiveClaimForReplacement(null);
        setReplacementSerialInput("");
        setActionFeedback({ type: "success", message: res.message || "Replacement recorded successfully!" });
      } else {
        setActionFeedback({ type: "error", message: res.message || "Failed to resolve replacement serial." });
      }
    });
  };

  const handleCreditSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const amount = parseFloat(creditAmountInput);
    if (!activeClaimForCredit || isNaN(amount) || amount <= 0) return;

    setActionFeedback(null);
    startTransition(async () => {
      const res = await resolveWithCreditNote(activeClaimForCredit.id, amount);
      if (res.success) {
        setClaims((prev) =>
          prev.map((item) =>
            item.id === activeClaimForCredit.id
              ? {
                  ...item,
                  status: "RESOLVED_CREDIT",
                  vendorCreditAmount: amount,
                  closedAt: new Date().toISOString(),
                }
              : item
          )
        );
        setActiveClaimForCredit(null);
        setCreditAmountInput("");
        setActionFeedback({ type: "success", message: res.message || "Credit note recorded successfully!" });
      } else {
        setActionFeedback({ type: "error", message: res.message || "Failed to record credit note." });
      }
    });
  };

  const handleCreateClaim = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newClaimSupplierId || !newClaimSerialId || !newClaimFault.trim()) return;

    setActionFeedback(null);
    startTransition(async () => {
      const res = await createSupplierRmaClaim({
        supplierId: newClaimSupplierId,
        serialId: newClaimSerialId,
        faultDescription: newClaimFault.trim(),
      });

      if (res.success && res.claim) {
        const supplierObj = suppliersList.find((s) => s.id === newClaimSupplierId);
        const newClaimItem: SupplierRmaItem = {
          id: res.claim.id,
          claimNumber: res.claim.claimNumber,
          supplierId: res.claim.supplierId,
          supplierName: supplierObj ? supplierObj.name : "Vendor",
          serialId: res.claim.serialId,
          serialNumber: "SN-PENDING",
          productName: "Hardware Serial Item",
          productSku: "HW-ITEM",
          costPrice: 0,
          faultDescription: res.claim.faultDescription,
          status: "PENDING",
          createdAt: new Date().toISOString(),
        };

        setClaims((prev) => [newClaimItem, ...prev]);
        setIsCreateModalOpen(false);
        setNewClaimSupplierId("");
        setNewClaimSerialId("");
        setNewClaimFault("");
        setActionFeedback({ type: "success", message: `Claim ${res.claim.claimNumber} initiated successfully!` });
      } else {
        setActionFeedback({ type: "error", message: res.message || "Failed to create RMA claim." });
      }
    });
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
            <ShieldAlert className="w-7 h-7 text-indigo-600" />
            Supplier RMA & Defective Unit Management
          </h1>
          <p className="text-sm text-slate-500 mt-1">
            Track hardware defects, supplier replacements, warranty claims, and vendor credit memo balance.
          </p>
        </div>

        <button
          onClick={() => setIsCreateModalOpen(true)}
          className="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition"
        >
          <Plus className="w-4 h-4" />
          File Vendor RMA Claim
        </button>
      </div>

      {/* Global Action Feedback Alert */}
      {actionFeedback && (
        <div
          className={`p-4 rounded-lg flex items-center justify-between text-sm ${
            actionFeedback.type === "success"
              ? "bg-emerald-50 text-emerald-800 border border-emerald-200"
              : "bg-rose-50 text-rose-800 border border-rose-200"
          }`}
        >
          <div className="flex items-center gap-2">
            {actionFeedback.type === "success" ? (
              <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
            ) : (
              <AlertTriangle className="w-5 h-5 text-rose-600 shrink-0" />
            )}
            <span>{actionFeedback.message}</span>
          </div>
          <button
            onClick={() => setActionFeedback(null)}
            className="text-slate-400 hover:text-slate-600"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Metric Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Claims</span>
            <div className="p-2 bg-amber-50 text-amber-600 rounded-lg">
              <Clock className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <span className="text-2xl font-bold text-slate-900">{totalPending}</span>
            <p className="text-xs text-slate-500 mt-0.5">Awaiting replacement or credit</p>
          </div>
        </div>

        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Value at Risk</span>
            <div className="p-2 bg-rose-50 text-rose-600 rounded-lg">
              <AlertTriangle className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <span className="text-2xl font-bold text-slate-900">
              ${totalValueAtRisk.toLocaleString("en-US", { minimumFractionDigits: 2 })}
            </span>
            <p className="text-xs text-slate-500 mt-0.5">Original inventory cost basis</p>
          </div>
        </div>

        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Credit Recovered</span>
            <div className="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
              <DollarSign className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <span className="text-2xl font-bold text-slate-900">
              ${totalCreditResolved.toLocaleString("en-US", { minimumFractionDigits: 2 })}
            </span>
            <p className="text-xs text-slate-500 mt-0.5">Total credit memo adjustments</p>
          </div>
        </div>

        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Units Replaced</span>
            <div className="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <RefreshCw className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <span className="text-2xl font-bold text-slate-900">{totalReplacementResolved}</span>
            <p className="text-xs text-slate-500 mt-0.5">New replacement serials returned</p>
          </div>
        </div>
      </div>

      {/* Filters & Search */}
      <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search claim #, supplier, serial number, product..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div className="flex items-center gap-2">
          <SlidersHorizontal className="w-4 h-4 text-slate-400" />
          <span className="text-xs font-medium text-slate-600">Status:</span>
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="text-sm border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          >
            <option value="ALL">All Statuses</option>
            <option value="PENDING">Pending Approval</option>
            <option value="APPROVED_BY_VENDOR">Vendor Approved</option>
            <option value="RESOLVED_REPLACEMENT">Resolved (Replacement)</option>
            <option value="RESOLVED_CREDIT">Resolved (Credit Note)</option>
            <option value="REJECTED">Rejected</option>
          </select>
        </div>
      </div>

      {/* Claims Table */}
      <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600">
            <thead className="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
              <tr>
                <th className="px-5 py-3">Claim Info</th>
                <th className="px-5 py-3">Supplier</th>
                <th className="px-5 py-3">Defective Serial / SKU</th>
                <th className="px-5 py-3">Cost Basis</th>
                <th className="px-5 py-3">Fault / Diagnostics</th>
                <th className="px-5 py-3">Status</th>
                <th className="px-5 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {filteredClaims.length === 0 ? (
                <tr>
                  <td colSpan={7} className="px-5 py-10 text-center text-slate-400">
                    No RMA claims found matching your criteria.
                  </td>
                </tr>
              ) : (
                filteredClaims.map((claim) => {
                  const isPendingStatus = claim.status === "PENDING" || claim.status === "APPROVED_BY_VENDOR";

                  return (
                    <tr key={claim.id} className="hover:bg-slate-50 transition">
                      <td className="px-5 py-4">
                        <div className="font-semibold text-slate-900">{claim.claimNumber}</div>
                        <div className="text-xs text-slate-400">
                          {new Date(claim.createdAt).toLocaleDateString()}
                        </div>
                      </td>

                      <td className="px-5 py-4 font-medium text-slate-800">
                        {claim.supplierName}
                      </td>

                      <td className="px-5 py-4">
                        <div className="font-mono text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded w-fit font-medium">
                          {claim.serialNumber}
                        </div>
                        <div className="text-xs text-slate-500 mt-1">
                          {claim.productName} ({claim.productSku})
                        </div>
                      </td>

                      <td className="px-5 py-4 font-mono font-medium text-slate-900">
                        ${claim.costPrice.toFixed(2)}
                      </td>

                      <td className="px-5 py-4 max-w-xs text-xs text-slate-600 truncate" title={claim.faultDescription}>
                        {claim.faultDescription}
                      </td>

                      <td className="px-5 py-4">
                        {claim.status === "PENDING" && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            <Clock className="w-3.5 h-3.5" /> Pending
                          </span>
                        )}
                        {claim.status === "APPROVED_BY_VENDOR" && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                            <CheckCircle2 className="w-3.5 h-3.5" /> Approved
                          </span>
                        )}
                        {claim.status === "RESOLVED_REPLACEMENT" && (
                          <div className="space-y-0.5">
                            <span className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                              <RefreshCw className="w-3.5 h-3.5" /> Replaced
                            </span>
                            {claim.replacementSerialNumber && (
                              <div className="text-[11px] font-mono text-slate-500">
                                New: {claim.replacementSerialNumber}
                              </div>
                            )}
                          </div>
                        )}
                        {claim.status === "RESOLVED_CREDIT" && (
                          <div className="space-y-0.5">
                            <span className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                              <DollarSign className="w-3.5 h-3.5" /> Credit Note
                            </span>
                            <div className="text-[11px] font-mono font-medium text-slate-700">
                              +${claim.vendorCreditAmount?.toFixed(2)}
                            </div>
                          </div>
                        )}
                        {claim.status === "REJECTED" && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                            <XCircle className="w-3.5 h-3.5" /> Rejected
                          </span>
                        )}
                      </td>

                      <td className="px-5 py-4 text-right">
                        {isPendingStatus ? (
                          <div className="inline-flex items-center gap-2">
                            <button
                              onClick={() => {
                                setActiveClaimForReplacement(claim);
                                setReplacementSerialInput("");
                              }}
                              className="px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md transition"
                            >
                              Receive New Serial
                            </button>
                            <button
                              onClick={() => {
                                setActiveClaimForCredit(claim);
                                setCreditAmountInput(claim.costPrice.toString());
                              }}
                              className="px-2.5 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md transition"
                            >
                              Apply Credit Note
                            </button>
                          </div>
                        ) : (
                          <span className="text-xs text-slate-400 italic">Resolved</span>
                        )}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal: Resolve with Replacement Serial */}
      {activeClaimForReplacement && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 animate-in fade-in">
            <div className="flex items-center justify-between pb-4 border-b border-slate-100">
              <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                <RefreshCw className="w-5 h-5 text-blue-600" />
                Intake Replacement Serial
              </h3>
              <button
                onClick={() => setActiveClaimForReplacement(null)}
                className="text-slate-400 hover:text-slate-600"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleReplacementSubmit} className="mt-4 space-y-4">
              <div className="bg-slate-50 p-3 rounded-lg text-xs space-y-1 border border-slate-200">
                <div className="text-slate-500">Claim: <span className="font-semibold text-slate-800">{activeClaimForReplacement.claimNumber}</span></div>
                <div className="text-slate-500">Defective Unit: <span className="font-mono font-medium text-rose-700">{activeClaimForReplacement.serialNumber}</span></div>
                <div className="text-slate-500">Carried Landed Cost Basis: <span className="font-semibold text-slate-800">${activeClaimForReplacement.costPrice.toFixed(2)}</span></div>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-700 mb-1">
                  New Hardware Serial Number *
                </label>
                <input
                  type="text"
                  required
                  placeholder="Scan or enter new serial number..."
                  value={replacementSerialInput}
                  onChange={(e) => setReplacementSerialInput(e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none"
                  autoFocus
                />
                <p className="text-[11px] text-slate-500 mt-1">
                  The replacement unit will be immediately registered into warehouse inventory as AVAILABLE with 36-month warranty.
                </p>
              </div>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setActiveClaimForReplacement(null)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isPending || !replacementSerialInput.trim()}
                  className="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50"
                >
                  {isPending ? "Validating..." : "Confirm Replacement Serial"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: Resolve with Credit Note */}
      {activeClaimForCredit && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 animate-in fade-in">
            <div className="flex items-center justify-between pb-4 border-b border-slate-100">
              <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                <DollarSign className="w-5 h-5 text-indigo-600" />
                Apply Vendor Credit Note
              </h3>
              <button
                onClick={() => setActiveClaimForCredit(null)}
                className="text-slate-400 hover:text-slate-600"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreditSubmit} className="mt-4 space-y-4">
              <div className="bg-slate-50 p-3 rounded-lg text-xs space-y-1 border border-slate-200">
                <div className="text-slate-500">Claim: <span className="font-semibold text-slate-800">{activeClaimForCredit.claimNumber}</span></div>
                <div className="text-slate-500">Defective Unit: <span className="font-mono text-rose-700">{activeClaimForCredit.serialNumber}</span></div>
                <div className="text-slate-500">Default Cost Refund: <span className="font-semibold text-slate-800">${activeClaimForCredit.costPrice.toFixed(2)}</span></div>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-700 mb-1">
                  Credit Note Dollar Amount ($) *
                </label>
                <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                  <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                    placeholder="0.00"
                    value={creditAmountInput}
                    onChange={(e) => setCreditAmountInput(e.target.value)}
                    className="w-full pl-7 pr-3 py-2 text-sm border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    autoFocus
                  />
                </div>
                <p className="text-[11px] text-slate-500 mt-1">
                  The defective serial will be retired, and the credit note credited against the vendor’s open A/P balance.
                </p>
              </div>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setActiveClaimForCredit(null)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isPending || !creditAmountInput}
                  className="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-50"
                >
                  {isPending ? "Applying..." : "Post Vendor Credit Memo"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: Create Vendor RMA Claim */}
      {isCreateModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200 animate-in fade-in">
            <div className="flex items-center justify-between pb-4 border-b border-slate-100">
              <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                <FileText className="w-5 h-5 text-indigo-600" />
                Initiate New Vendor RMA
              </h3>
              <button
                onClick={() => setIsCreateModalOpen(false)}
                className="text-slate-400 hover:text-slate-600"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreateClaim} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium text-slate-700 mb-1">
                  Supplier / Vendor *
                </label>
                <select
                  required
                  value={newClaimSupplierId}
                  onChange={(e) => setNewClaimSupplierId(e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="">-- Select Vendor --</option>
                  {suppliersList.map((sup) => (
                    <option key={sup.id} value={sup.id}>
                      {sup.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-700 mb-1">
                  Defective Serial ID (UUID) *
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. c1b37e0c-99a2-4a7b-..."
                  value={newClaimSerialId}
                  onChange={(e) => setNewClaimSerialId(e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-700 mb-1">
                  Diagnostic Failure Report / Reason *
                </label>
                <textarea
                  rows={3}
                  required
                  placeholder="Detailed failure description (e.g. DOA, memory artifacts, blown VRM)..."
                  value={newClaimFault}
                  onChange={(e) => setNewClaimFault(e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsCreateModalOpen(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isPending || !newClaimSupplierId || !newClaimSerialId || !newClaimFault.trim()}
                  className="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-50"
                >
                  {isPending ? "Submitting..." : "Submit RMA Claim"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

