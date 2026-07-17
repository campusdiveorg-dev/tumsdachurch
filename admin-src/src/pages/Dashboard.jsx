import { useAuth } from '../context/AuthContext'

export default function Dashboard() {
  const { user } = useAuth()
  return (
    <div className="text-center p-8 bg-white rounded-2xl shadow-lg border border-slate-100">
      <h1 className="text-3xl font-bold mb-4" style={{ fontFamily: "'League Spartan', sans-serif", background: 'linear-gradient(135deg, var(--brand), var(--brand-dark))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>Welcome, {user?.name}!</h1>
      <p className="text-lg text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>Use the sidebar navigation to manage content, members, and payments.</p>
    </div>
  )
}
