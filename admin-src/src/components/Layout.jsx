import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Layout({ children }) {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [isSidebarOpen, setIsSidebarOpen] = useState(false)

  const menuItems = [
    { path: '/', label: 'Dashboard', icon: 'fa-chart-pie' },
    { path: '/content/departments', label: 'Departments', icon: 'fa-sitemap' },
    { path: '/content/ministries', label: 'Ministries', icon: 'fa-handshake' },
    { path: '/content/leadership', label: 'Leadership', icon: 'fa-users-cog' },
    { path: '/content/sermons', label: 'Sermons', icon: 'fa-video' },
    { path: '/content/events', label: 'Events Calendar', icon: 'fa-calendar-alt' },
    { path: '/content/weekly_meetings', label: 'Weekly Meetings', icon: 'fa-clock' },
    { path: '/content/resources', label: 'Resources', icon: 'fa-folder-open' },
    { path: '/content/missions', label: 'Missions', icon: 'fa-globe-africa' },
    { path: '/content/announcements', label: 'Notice Board', icon: 'fa-bullhorn' },
    { path: '/content/word_of_the_day', label: 'Word of the Day', icon: 'fa-bible' },
    ...(user?.role === 'admin' ? [{ path: '/members', label: 'Members', icon: 'fa-users' }] : []),
    { path: '/payments', label: 'Payments History', icon: 'fa-wallet' },
  ]

  function isActive(path) {
    return location.pathname === path
  }

  async function handleLogout() {
    try {
      await logout()
      navigate('/login')
    } catch (err) {
      console.error('Logout failed:', err)
    }
  }

  // Get correct logo path for dev vs prod
  const isDev = window.location.hostname === 'localhost' && (window.location.port === '5173' || window.location.port === '5174')
  const logoPath = isDev ? '/tumsdachurch.org/assets/img/logo.jpg' : '../assets/img/logo.jpg'

  return (
    <div className="flex h-screen bg-slate-50 overflow-hidden relative">
      {/* Sidebar Backdrop Overlay on Mobile */}
      {isSidebarOpen && (
        <div
          className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 md:hidden transition-opacity duration-300"
          onClick={() => setIsSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed md:relative top-0 bottom-0 left-0 z-50 w-72 bg-slate-900 flex flex-col justify-between flex-shrink-0 border-r border-slate-800 shadow-2xl transition-transform duration-300 ease-in-out ${
          isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        }`}
      >
        {/* Sidebar Header */}
        <div className="p-6 border-b border-slate-800 bg-slate-950/30 flex items-center justify-between">
          <Link to="/" className="flex items-center gap-3 group" onClick={() => setIsSidebarOpen(false)}>
            <img src={logoPath} alt="TUMSDA Logo" className="h-11 w-auto max-w-[60px] object-contain bg-white rounded-xl shadow-md border border-slate-800 p-0.5 group-hover:scale-105 transition-transform" />
            <div className="flex flex-col">
              <span className="text-white font-extrabold text-lg tracking-wide" style={{ fontFamily: "'League Spartan', sans-serif" }}>TUMSDA Admin</span>
              <span className="text-slate-500 text-xs font-semibold" style={{ fontFamily: "'Inter', sans-serif" }}>Church Portal</span>
            </div>
          </Link>
          <button
            onClick={() => setIsSidebarOpen(false)}
            className="md:hidden text-slate-400 hover:text-white p-1 hover:bg-slate-800 rounded-lg transition-colors"
          >
            <i className="fas fa-times text-lg" />
          </button>
        </div>

        {/* Menu Items */}
        <nav className="flex-1 p-4 space-y-1.5 overflow-y-auto custom-scrollbar">
          {menuItems.map((item) => (
            <Link
              key={item.path}
              to={item.path}
              onClick={() => setIsSidebarOpen(false)}
              className={`flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 group ${
                isActive(item.path)
                  ? 'bg-brand text-white font-semibold shadow-lg shadow-brand/20'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
              }`}
              style={{ fontFamily: "'Inter', sans-serif" }}
            >
              <i className={`fas ${item.icon} w-5 text-center text-sm ${isActive(item.path) ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'}`} />
              <span className="text-sm">{item.label}</span>
            </Link>
          ))}
        </nav>

        {/* Logout and User Block */}
        <div className="p-4 border-t border-slate-800 bg-slate-950/20">
          <div className="flex items-center gap-3 px-3 py-2.5 mb-4 bg-slate-800/40 rounded-xl">
            <div className="w-9 h-9 bg-brand/20 text-brand rounded-lg flex items-center justify-center font-bold text-sm">
              {user?.name?.charAt(0)?.toUpperCase()}
            </div>
            <div className="flex flex-col truncate">
              <span className="text-slate-200 font-semibold text-xs truncate" style={{ fontFamily: "'Inter', sans-serif" }}>{user?.name}</span>
              <span className="text-slate-500 text-[10px] uppercase font-bold" style={{ fontFamily: "'Inter', sans-serif" }}>{user?.role}</span>
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-red-600/90 text-slate-300 hover:text-white rounded-xl transition-all duration-200 font-bold text-sm shadow-inner"
            style={{ fontFamily: "'Inter', sans-serif" }}
          >
            <i className="fas fa-sign-out-alt text-xs" />
            <span>Logout</span>
          </button>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top Header */}
        <header className="bg-white border-b border-slate-200 px-4 md:px-8 py-4 md:py-5 flex items-center justify-between shadow-sm relative z-20">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsSidebarOpen(true)}
              className="md:hidden p-2 -ml-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors flex-shrink-0"
            >
              <i className="fas fa-bars text-xl" />
            </button>
            <h1 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight" style={{ fontFamily: "'League Spartan', sans-serif" }}>
              Control Panel
            </h1>
          </div>
          <div className="flex items-center gap-4">
            <span className="text-xs md:text-sm font-semibold text-slate-500 bg-slate-100 px-3 py-1 md:px-3.5 md:py-1.5 rounded-full capitalize" style={{ fontFamily: "'Inter', sans-serif" }}>
              Role: <strong className="text-slate-800 font-bold">{user?.role}</strong>
            </span>
          </div>
        </header>

        {/* Page Body */}
        <main className="flex-1 overflow-y-auto p-4 md:p-8 relative z-10 custom-scrollbar">
          <div className="max-w-7xl mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  )
}
