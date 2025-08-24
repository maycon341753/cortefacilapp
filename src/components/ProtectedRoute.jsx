import React from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { useApp } from '../contexts/AppContext'

const ProtectedRoute = ({ children, allowedRoles = [] }) => {
  const { user, isAuthenticated, loading } = useAuth()
  const { showLoading, hideLoading } = useApp()
  const location = useLocation()

  // Mostrar loading enquanto verifica autenticação
  if (loading) {
    return (
      <div className="loading-container">
        <div className="loading-spinner">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Carregando...</span>
          </div>
          <div className="mt-3">
            <p>Verificando autenticação...</p>
          </div>
        </div>
      </div>
    )
  }

  // Se não está autenticado, redirecionar para login
  if (!isAuthenticated || !user) {
    return (
      <Navigate 
        to="/login" 
        state={{ from: location.pathname }} 
        replace 
      />
    )
  }

  // Se há roles específicas permitidas, verificar se o usuário tem permissão
  if (allowedRoles.length > 0 && !allowedRoles.includes(user.tipo_usuario)) {
    // Redirecionar para a página apropriada baseada no tipo de usuário
    const redirectPath = getRedirectPathByUserType(user.tipo_usuario)
    return (
      <Navigate 
        to={redirectPath} 
        replace 
      />
    )
  }

  // Se passou por todas as verificações, renderizar o componente
  return children
}

// Função auxiliar para determinar o caminho de redirecionamento baseado no tipo de usuário
const getRedirectPathByUserType = (userType) => {
  switch (userType) {
    case 'admin':
      return '/admin/dashboard'
    case 'parceiro':
      return '/parceiro/dashboard'
    case 'cliente':
      return '/cliente/dashboard'
    default:
      return '/'
  }
}

export default ProtectedRoute