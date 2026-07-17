import { useState, useEffect } from 'react'
import { usersApi } from '../services/api'

export default function Members() {
  const [members, setMembers] = useState([])
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')

  useEffect(() => {
    loadMembers()
  }, [])

  async function loadMembers() {
    try {
      const data = await usersApi.list()
      setMembers(Array.isArray(data) ? data : [])
    } catch (err) {
      setError(err.message || 'Failed to fetch members.')
    }
  }

  async function toggleRole(member) {
    const newRole = member.role === 'admin' ? 'member' : 'admin'
    if (window.confirm(`Are you sure you want to change role of ${member.name} to ${newRole}?`)) {
      setError('')
      setSuccess('')
      try {
        await usersApi.update(member.id, { role: newRole })
        setSuccess(`Successfully changed ${member.name}'s role to ${newRole}.`)
        loadMembers()
      } catch (err) {
        setError(err.message || 'Failed to update role.')
      }
    }
  }

  async function deactivate(member) {
    if (window.confirm(`Are you sure you want to deactivate ${member.name}?`)) {
      setError('')
      setSuccess('')
      try {
        await usersApi.deactivate(member.id)
        setSuccess(`Successfully deactivated user ${member.name}.`)
        loadMembers()
      } catch (err) {
        setError(err.message || 'Failed to deactivate member.')
      }
    }
  }

  async function activate(member) {
    if (window.confirm(`Are you sure you want to reactivate ${member.name}?`)) {
      setError('')
      setSuccess('')
      try {
        await usersApi.update(member.id, { is_active: 1 })
        setSuccess(`Successfully reactivated user ${member.name}.`)
        loadMembers()
      } catch (err) {
        setError(err.message || 'Failed to reactivate member.')
      }
    }
  }

  async function deleteMember(member) {
    if (window.confirm(`Are you sure you want to PERMANENTLY delete user ${member.name}? This action cannot be undone and will unset their id from all audit logs and payment records.`)) {
      setError('')
      setSuccess('')
      try {
        await usersApi.delete(member.id)
        setSuccess(`Successfully deleted user ${member.name}.`)
        loadMembers()
      } catch (err) {
        setError(err.message || 'Failed to delete member.')
      }
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight" style={{ fontFamily: "'League Spartan', sans-serif" }}>Member Accounts</h2>
      </div>

      {error && (
        <div className="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          {error}
        </div>
      )}

      {success && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          {success}
        </div>
      )}

      <div className="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="bg-slate-900 text-white border-b border-slate-800">
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>ID</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Name</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Email Address</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>System Role</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Active Status</th>
                <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {members.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-6 py-8 text-center text-slate-400" style={{ fontFamily: "'Inter', sans-serif" }}>
                    No members found.
                  </td>
                </tr>
              ) : (
                members.map(m => (
                  <tr key={m.id} className="hover:bg-slate-50 transition-colors">
                    <td className="px-6 py-4 text-slate-500 font-semibold text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>{m.id}</td>
                    <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{m.name}</td>
                    <td className="px-6 py-4 text-slate-600 font-medium text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>{m.email}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`px-3 py-1.5 rounded-full text-xs font-bold tracking-wider ${
                          m.role === 'admin' ? 'bg-brand/10 text-brand' : 'bg-slate-100 text-slate-700'
                        }`}
                        style={{ fontFamily: "'Inter', sans-serif" }}
                      >
                        {m.role}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`px-3 py-1.5 rounded-full text-xs font-bold tracking-wider ${
                          m.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'
                        }`}
                        style={{ fontFamily: "'Inter', sans-serif" }}
                      >
                        {m.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex gap-2">
                        {m.is_active ? (
                          <>
                            <button
                              className="px-3 py-1.5 bg-slate-100 hover:bg-brand hover:text-white text-slate-700 text-xs font-bold rounded-lg transition-all duration-200"
                              style={{ fontFamily: "'Inter', sans-serif" }}
                              onClick={() => toggleRole(m)}
                            >
                              Toggle Role
                            </button>
                            <button
                              className="px-3 py-1.5 bg-slate-100 hover:bg-amber-600 hover:text-white text-slate-700 text-xs font-bold rounded-lg transition-all duration-200"
                              style={{ fontFamily: "'Inter', sans-serif" }}
                              onClick={() => deactivate(m)}
                            >
                              Deactivate
                            </button>
                          </>
                        ) : (
                          <button
                            className="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-xs font-bold rounded-lg transition-all duration-200"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                            onClick={() => activate(m)}
                          >
                            Activate
                          </button>
                        )}
                        <button
                          className="px-3 py-1.5 bg-red-50 hover:bg-red-600 hover:text-white text-red-600 text-xs font-bold rounded-lg transition-all duration-200"
                          style={{ fontFamily: "'Inter', sans-serif" }}
                          onClick={() => deleteMember(m)}
                        >
                          Delete
                        </button>
                      </div>
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
