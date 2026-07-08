import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Register() {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { register } = useAuth()
  const navigate = useNavigate()

  async function handleSubmit(e) {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await register(name, email, password)
      navigate('/')
    } catch (err) {
      setError(err.message || 'Registration failed. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  // Get correct logo path for dev vs prod
  const isDev = window.location.hostname === 'localhost' && (window.location.port === '5173' || window.location.port === '5174')
  const logoPath = isDev ? '/tumsda.org/assets/img/logo.jpg' : '../assets/img/logo.jpg'

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 relative overflow-hidden">
      {/* Decorative background grid and shapes */}
      <div className="absolute top-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-brand/5 blur-[120px]" />
      <div className="absolute bottom-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-brand/5 blur-[120px]" />

      <div className="bg-white rounded-3xl shadow-2xl border border-slate-100 p-8 md:p-10 max-w-md w-full relative z-10 transition-all duration-300">
        <div className="text-center mb-8">
          <img src={logoPath} alt="TUMSDA Logo" className="w-20 h-20 mx-auto mb-4 rounded-2xl shadow-md border border-slate-100 object-cover" />
          <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight" style={{ fontFamily: "'League Spartan', sans-serif" }}>Create Account</h2>
          <p className="text-slate-500 mt-2 font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>Sign up for a new TUMSDA Admin account</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 text-red-700 rounded-2xl border border-red-200 text-sm font-medium animate-pulse" style={{ fontFamily: "'Inter', sans-serif" }}>
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="flex flex-col gap-2">
            <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>Full Name</label>
            <input
              type="text"
              className="w-full px-4 py-3.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 bg-slate-50 focus:bg-white placeholder-slate-400 font-medium"
              required
              value={name}
              onChange={e => setName(e.target.value)}
              placeholder="Elder Cephas Mukaria"
              style={{ fontFamily: "'Inter', sans-serif" }}
            />
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>Email Address</label>
            <input
              type="email"
              className="w-full px-4 py-3.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 bg-slate-50 focus:bg-white placeholder-slate-400 font-medium"
              required
              value={email}
              onChange={e => setEmail(e.target.value)}
              placeholder="email@tumsdachurch.org"
              style={{ fontFamily: "'Inter', sans-serif" }}
            />
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>Password</label>
            <input
              type="password"
              className="w-full px-4 py-3.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 bg-slate-50 focus:bg-white placeholder-slate-400 font-medium"
              required
              value={password}
              onChange={e => setPassword(e.target.value)}
              placeholder="Minimum 8 characters"
              style={{ fontFamily: "'Inter', sans-serif" }}
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full py-4 px-4 bg-brand hover:bg-brand-dark text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            style={{ fontFamily: "'Inter', sans-serif" }}
          >
            {loading ? (
              <span className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            ) : 'Register'}
          </button>
        </form>

        <div className="mt-8 text-center text-slate-500 font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          Already have an account? <Link to="/login" className="text-brand hover:text-brand-dark font-bold underline transition-colors">Login</Link>
        </div>
      </div>
    </div>
  )
}
