import { useState, useEffect } from 'react'
import { paymentsApi } from '../services/api'

export default function PaymentsList() {
  const [payments, setPayments] = useState([])
  const [error, setError] = useState('')

  useEffect(() => {
    loadPayments()
  }, [])

  async function loadPayments() {
    try {
      const data = await paymentsApi.list()
      setPayments(Array.isArray(data) ? data : [])
    } catch (err) {
      setError(err.message || 'Failed to fetch payments records.')
    }
  }

  const getStatusBadge = (status) => {
    const colors = {
      pending: 'bg-amber-100 text-amber-800 border border-amber-200',
      completed: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
      failed: 'bg-red-100 text-red-800 border border-red-200',
      cancelled: 'bg-slate-100 text-slate-700 border border-slate-200'
    }
    return (
      <span
        className={`px-3 py-1.5 rounded-full text-xs font-bold tracking-wider capitalize ${
          colors[status] || 'bg-slate-100 text-slate-700'
        }`}
        style={{ fontFamily: "'Inter', sans-serif" }}
      >
        {status}
      </span>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight" style={{ fontFamily: "'League Spartan', sans-serif" }}>Giving & Payments</h2>
      </div>

      {error && (
        <div className="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          {error}
        </div>
      )}

      <div className="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="bg-slate-900 text-white border-b border-slate-800">
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>ID</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Phone</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Donor Details</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Amount</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Purpose</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Status</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>M-Pesa Receipt</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Date</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {payments.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-6 py-8 text-center text-slate-400" style={{ fontFamily: "'Inter', sans-serif" }}>
                    No payment logs recorded yet.
                  </td>
                </tr>
              ) : (
                payments.map(p => (
                  <tr key={p.id} className="hover:bg-slate-50 transition-colors">
                    <td className="px-6 py-4 text-slate-500 font-semibold text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>{p.id}</td>
                    <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{p.phone_number}</td>
                    <td className="px-6 py-4 text-slate-600 font-medium text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>
                      {p.donor_name ? (
                        <div>
                          <div className="font-semibold text-slate-800">{p.donor_name}</div>
                          <div className="text-xs text-slate-400">{p.donor_email}</div>
                        </div>
                      ) : (
                        <span className="text-slate-400 text-xs italic">Anonymous / Guest</span>
                      )}
                    </td>
                    <td className="px-6 py-4 font-bold text-slate-900" style={{ fontFamily: "'Inter', sans-serif" }}>
                      KES {parseFloat(p.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                    </td>
                    <td className="px-6 py-4 capitalize text-slate-600 font-medium text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>
                      {p.purpose.replace('_', ' ')}
                    </td>
                    <td className="px-6 py-4">{getStatusBadge(p.status)}</td>
                    <td className="px-6 py-4 text-slate-700 font-mono font-bold text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>
                      {p.mpesa_receipt_number || '-'}
                    </td>
                    <td className="px-6 py-4 text-slate-500 text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
                      {new Date(p.created_at).toLocaleString()}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
