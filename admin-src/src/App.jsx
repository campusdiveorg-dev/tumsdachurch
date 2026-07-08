import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './context/AuthContext'
import ProtectedRoute from './components/ProtectedRoute'
import Layout from './components/Layout'
import Login from './pages/Login'
import Register from './pages/Register'
import Dashboard from './pages/Dashboard'
import ContentPage from './pages/ContentPage'
import Members from './pages/Members'
import PaymentsList from './pages/PaymentsList'

export default function App() {
  const { user, loading } = useAuth()

  if (loading) return <div className="container py-4"><p>Loading...</p></div>

  return (
    <Routes>
      <Route path="/login" element={user ? <Navigate to="/" replace /> : <Login />} />
      <Route path="/register" element={user ? <Navigate to="/" replace /> : <Register />} />
      <Route path="/" element={
        <ProtectedRoute>
          <Layout><Dashboard /></Layout>
        </ProtectedRoute>
      } />
      <Route path="/content/:type" element={
        <ProtectedRoute>
          <Layout><ContentPage /></Layout>
        </ProtectedRoute>
      } />
      <Route path="/members" element={
        <ProtectedRoute requireAdmin>
          <Layout><Members /></Layout>
        </ProtectedRoute>
      } />
      <Route path="/payments" element={
        <ProtectedRoute>
          <Layout><PaymentsList /></Layout>
        </ProtectedRoute>
      } />
    </Routes>
  )
}
