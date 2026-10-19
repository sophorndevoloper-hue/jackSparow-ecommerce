"use client";

import React, { useState } from "react";
import { Building2, Plus, Mail, Phone, Clock, DollarSign, Search, ShieldCheck } from "lucide-react";

export interface SupplierListItem {
  id: string;
  companyName: string;
  contactName: string | null;
  email: string;
  phone: string | null;
  paymentTerms: "PREPAID" | "NET_15" | "NET_30" | "NET_60";
  currency: string;
  openPoCount: number;
  totalApBalance: number;
}

interface SupplierDirectoryProps {
  initialSuppliers: SupplierListItem[];
}

export default function SupplierDirectory({ initialSuppliers }: SupplierDirectoryProps) {
  const [suppliers, setSuppliers] = useState<SupplierListItem[]>(initialSuppliers);
  const [search, setSearch] = useState("");
  const [isModalOpen, setIsModalOpen] = useState(false);

  // Modal Form State
  const [formCompany, setFormCompany] = useState("");
  const [formContact, setFormContact] = useState("");
  const [formEmail, setFormEmail] = useState("");
  const [formPhone, setFormPhone] = useState("");
  const [formTerms, setFormTerms] = useState<"PREPAID" | "NET_15" | "NET_30" | "NET_60">("NET_30");

  const filtered = suppliers.filter(
    (s) =>
      s.companyName.toLowerCase().includes(search.toLowerCase()) ||
      s.email.toLowerCase().includes(search.toLowerCase()) ||
      (s.contactName && s.contactName.toLowerCase().includes(search.toLowerCase()))
  );

  const handleCreateSupplier = (e: React.FormEvent) => {
    e.preventDefault();
    const newSupplier: SupplierListItem = {
      id: `supp-${Date.now()}`,
      companyName: formCompany,
      contactName: formContact || null,
      email: formEmail,
      phone: formPhone || null,
      paymentTerms: formTerms,
      currency: "USD",
      openPoCount: 0,
      totalApBalance: 0,
    };

    setSuppliers([newSupplier, ...suppliers]);
    setIsModalOpen(false);
    setFormCompany("");
    setFormContact("");
    setFormEmail("");
    setFormPhone("");
  };

  return (
    <div className="max-w-7xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-lg border shadow-sm">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <Building2 className="w-6 h-6 text-indigo-600" /> Supplier Directory &amp; Net Terms
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            Manage vendor payment terms, credit lines, open purchase orders, and Accounts Payable (A/P).
          </p>
        </div>
        <button
          onClick={() => setIsModalOpen(true)}
          className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-md shadow-sm transition"
        >
          <Plus className="w-4 h-4" /> Add New Supplier
        </button>
      </div>

      {/* Filter Bar */}
      <div className="flex items-center bg-white p-4 rounded-lg border shadow-sm">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 absolute left-3 top-3 text-gray-400" />
          <input
            type="text"
            placeholder="Search suppliers by name, contact, or email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-9 pr-4 py-2 border rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
        </div>
      </div>

      {/* Supplier Table */}
      <div className="bg-white rounded-lg border shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b">
              <tr>
                <th className="px-6 py-3">Company &amp; Contact</th>
                <th className="px-6 py-3">Contact Info</th>
                <th className="px-6 py-3 text-center">Payment Terms</th>
                <th className="px-6 py-3 text-center">Open POs</th>
                <th className="px-6 py-3 text-right">Outstanding A/P</th>
                <th className="px-6 py-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {filtered.map((s) => (
                <tr key={s.id} className="hover:bg-gray-50 transition">
                  <td className="px-6 py-4">
                    <div className="font-semibold text-gray-900">{s.companyName}</div>
                    <div className="text-xs text-gray-500">{s.contactName || "No contact specified"}</div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-1.5 text-gray-600">
                      <Mail className="w-3.5 h-3.5" /> {s.email}
                    </div>
                    {s.phone && (
                      <div className="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                        <Phone className="w-3.5 h-3.5" /> {s.phone}
                      </div>
                    )}
                  </td>
                  <td className="px-6 py-4 text-center">
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                      <Clock className="w-3 h-3" /> {s.paymentTerms.replace("_", " ")}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center font-mono font-medium text-gray-700">
                    {s.openPoCount}
                  </td>
                  <td className="px-6 py-4 text-right font-mono font-semibold text-gray-900">
                    ${s.totalApBalance.toLocaleString("en-US", { minimumFractionDigits: 2 })}
                  </td>
                  <td className="px-6 py-4 text-center">
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                      <ShieldCheck className="w-3 h-3" /> Active
                    </span>
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-6 py-8 text-center text-gray-400">
                    No suppliers match your search criteria.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add Supplier Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="bg-white rounded-lg max-w-lg w-full p-6 space-y-4 shadow-xl">
            <h2 className="text-lg font-bold text-gray-900 border-b pb-2">Register Vendor / Supplier</h2>
            <form onSubmit={handleCreateSupplier} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Company Name</label>
                <input
                  type="text"
                  required
                  value={formCompany}
                  onChange={(e) => setFormCompany(e.target.value)}
                  placeholder="e.g. Asus Global Distribution"
                  className="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Contact Person</label>
                  <input
                    type="text"
                    value={formContact}
                    onChange={(e) => setFormContact(e.target.value)}
                    placeholder="e.g. Sarah Connor"
                    className="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                  <input
                    type="email"
                    required
                    value={formEmail}
                    onChange={(e) => setFormEmail(e.target.value)}
                    placeholder="orders@vendor.com"
                    className="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Phone</label>
                  <input
                    type="text"
                    value={formPhone}
                    onChange={(e) => setFormPhone(e.target.value)}
                    placeholder="+1 800 555 0199"
                    className="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Payment Terms</label>
                  <select
                    value={formTerms}
                    onChange={(e: any) => setFormTerms(e.target.value)}
                    className="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                  >
                    <option value="PREPAID">Prepaid</option>
                    <option value="NET_15">Net 15 Days</option>
                    <option value="NET_30">Net 30 Days</option>
                    <option value="NET_60">Net 60 Days</option>
                  </select>
                </div>
              </div>
              <div className="flex justify-end gap-2 pt-4 border-t">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 border rounded-md text-sm text-gray-600 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700"
                >
                  Save Supplier
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

