import React from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { ToastContainer } from 'react-toastify'

// Contexts
import { AuthProvider } from './contexts/AuthContext'
import { AppProvider } from './contexts/AppContext'

// Components
import ProtectedRoute from './components/ProtectedRoute'
import Layout from './components/Layout'

// Pages
import Home from './pages/Home'
import Login from './pages/auth/Login'
import Register from './pages/auth/Register'

// Dashboard Pages
import ClienteDashboard from './pages/cliente/Dashboard'
import ClienteAgendamentos from './pages/client/Appointments'
import ClienteAgendar from './pages/client/BookAppointment'
import ClientePerfil from './pages/client/Profile'

import ParceiroDashboard from './pages/parceiro/Dashboard'

import AdminDashboard from './pages/admin/Dashboard'

// Error Pages
import NotFound from './pages/NotFound'

function App() {
  return (
    <AppProvider>
      <AuthProvider>
        <Router>
          <div className="App">
            <Routes>
              {/* Rotas Públicas */}
              <Route path="/" element={<Home />} />
              <Route path="/login" element={<Login />} />
              <Route path="/register" element={<Register />} />
              
              {/* Rotas do Cliente */}
              <Route path="/cliente" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClienteDashboard />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/cliente/dashboard" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClienteDashboard />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/cliente/agendamentos" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClienteAgendamentos />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/cliente/agendar" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClienteAgendar />
                  </Layout>
                </ProtectedRoute>
              } />

              <Route path="/cliente/perfil" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClientePerfil />
                  </Layout>
                </ProtectedRoute>
              } />
              
              {/* Rotas do Parceiro */}
              <Route path="/parceiro" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroDashboard />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/parceiro/dashboard" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroDashboard />
                  </Layout>
                </ProtectedRoute>
              } />

              
              {/* Rotas do Admin */}
              <Route path="/admin" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminDashboard />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/admin/dashboard" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminDashboard />
                  </Layout>
                </ProtectedRoute>
              } />

              
              {/* Página 404 */}
              <Route path="/404" element={<NotFound />} />
              <Route path="*" element={<Navigate to="/404" replace />} />
            </Routes>
            
            {/* Toast Notifications */}
            <ToastContainer
              position="top-right"
              autoClose={5000}
              hideProgressBar={false}
              newestOnTop={false}
              closeOnClick
              rtl={false}
              pauseOnFocusLoss
              draggable
              pauseOnHover
              theme="light"
            />
          </div>
        </Router>
      </AuthProvider>
    </AppProvider>
  )
}

export default App