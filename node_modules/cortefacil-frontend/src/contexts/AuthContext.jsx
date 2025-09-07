import React, { createContext, useContext, useState, useEffect } from 'react'
import { toast } from 'react-toastify'
import authService from '../services/authService'
import Cookies from 'js-cookie'

const AuthContext = createContext({})

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth deve ser usado dentro de um AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const [isAuthenticated, setIsAuthenticated] = useState(false)

  // Verificar se há usuário logado ao inicializar
  useEffect(() => {
    checkAuthStatus()
  }, [])

  const checkAuthStatus = async () => {
    try {
      setLoading(true)
      const token = Cookies.get('auth_token')
      const userData = localStorage.getItem('user_data')
      
      if (token && userData) {
        const parsedUser = JSON.parse(userData)
        
        // Verificar se o token ainda é válido
        const isValid = await authService.validateToken(token)
        
        if (isValid) {
          setUser(parsedUser)
          setIsAuthenticated(true)
        } else {
          // Token inválido, limpar dados
          logout()
        }
      }
    } catch (error) {
      console.error('Erro ao verificar status de autenticação:', error)
      logout()
    } finally {
      setLoading(false)
    }
  }

  const login = async (email, password) => {
    try {
      setLoading(true)
      const response = await authService.login(email, password)
      
      if (response.success) {
        const { user: userData, token } = response.data
        
        // Salvar dados do usuário
        setUser(userData)
        setIsAuthenticated(true)
        
        // Salvar no localStorage e cookies
        localStorage.setItem('user_data', JSON.stringify(userData))
        Cookies.set('auth_token', token, { expires: 7 }) // 7 dias
        
        toast.success('Login realizado com sucesso!')
        return { success: true, user: userData }
      } else {
        toast.error(response.message || 'Erro ao fazer login')
        return { success: false, message: response.message }
      }
    } catch (error) {
      console.error('Erro no login:', error)
      const message = error.response?.data?.message || 'Erro interno do servidor'
      toast.error(message)
      return { success: false, message }
    } finally {
      setLoading(false)
    }
  }

  const register = async (userData) => {
    try {
      setLoading(true)
      const response = await authService.register(userData)
      
      if (response.success && response.data) {
        // Fazer login automático após registro bem-sucedido
        const { token, user } = response.data
        
        // Salvar token nos cookies
        Cookies.set('auth_token', token, { expires: 7 })
        
        // Salvar dados do usuário
        setUser(user)
        setIsAuthenticated(true)
        localStorage.setItem('user_data', JSON.stringify(user))
        
        toast.success('Cadastro realizado com sucesso! Bem-vindo(a)!')
        return { success: true, user }
      } else {
        toast.error(response.message || 'Erro ao fazer cadastro')
        return { success: false, message: response.message }
      }
    } catch (error) {
      console.error('Erro no cadastro:', error)
      const message = error.response?.data?.message || 'Erro interno do servidor'
      toast.error(message)
      return { success: false, message }
    } finally {
      setLoading(false)
    }
  }

  const logout = () => {
    setUser(null)
    setIsAuthenticated(false)
    localStorage.removeItem('user_data')
    Cookies.remove('auth_token')
    toast.info('Logout realizado com sucesso!')
  }

  const updateUser = (updatedUserData) => {
    setUser(updatedUserData)
    localStorage.setItem('user_data', JSON.stringify(updatedUserData))
  }

  const hasRole = (role) => {
    return user && user.tipo_usuario === role
  }

  const hasAnyRole = (roles) => {
    return user && roles.includes(user.tipo_usuario)
  }

  const getRedirectPath = (userType) => {
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

  const value = {
    user,
    loading,
    isAuthenticated,
    login,
    register,
    logout,
    updateUser,
    hasRole,
    hasAnyRole,
    getRedirectPath,
    checkAuthStatus
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}

export default AuthContext