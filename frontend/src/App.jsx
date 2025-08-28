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
import ClienteAgendamentos from './pages/cliente/Agendamentos'
import ClienteBuscarSaloes from './pages/cliente/BuscarSaloes'
import ClienteAgendar from './pages/cliente/Agendar'
import ClientePerfil from './pages/cliente/Perfil'
import SaloesPorRegiao from './pages/cliente/SaloesPorRegiao'

import ParceiroDashboard from './pages/parceiro/Dashboard'
import ParceiroAgendamentos from './pages/parceiro/Agendamentos'
import ParceiroProfissionais from './pages/parceiro/Profissionais'
import ParceiroSalao from './pages/parceiro/Salao'
import ParceiroServicos from './pages/parceiro/Servicos'
import ParceiroRelatorios from './pages/parceiro/Relatorios'

import AdminDashboard from './pages/admin/Dashboard'
import AdminSaloes from './pages/admin/Saloes'
import AdminUsuarios from './pages/admin/Usuarios'
import AdminRelatorios from './pages/admin/Relatorios'
import AdminAgendamentos from './pages/admin/Agendamentos'

// Error Pages
import NotFound from './pages/NotFound'
import Saloes from './pages/Saloes'

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
              <Route path="/cliente/buscar-saloes" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <ClienteBuscarSaloes />
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
              <Route path="/cliente/saloes-regiao" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <SaloesPorRegiao />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/cliente/saloes" element={
                <ProtectedRoute allowedRoles={['cliente']}>
                  <Layout userType="cliente">
                    <Saloes />
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
              <Route path="/parceiro/agendamentos" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroAgendamentos />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/parceiro/profissionais" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroProfissionais />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/parceiro/salao" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroSalao />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/parceiro/servicos" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroServicos />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/parceiro/relatorios" element={
                <ProtectedRoute allowedRoles={['parceiro']}>
                  <Layout userType="parceiro">
                    <ParceiroRelatorios />
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
              <Route path="/admin/saloes" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminSaloes />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/admin/usuarios" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminUsuarios />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/admin/relatorios" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminRelatorios />
                  </Layout>
                </ProtectedRoute>
              } />
              <Route path="/admin/agendamentos" element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Layout userType="admin">
                    <AdminAgendamentos />
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