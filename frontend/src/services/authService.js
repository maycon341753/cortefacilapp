import axios from 'axios'
import Cookies from 'js-cookie'

// Configuração base da API
const API_BASE_URL = import.meta.env.VITE_API_URL || 
  (process.env.NODE_ENV === 'production' 
    ? window.location.origin + '/api'
    : 'http://localhost:3001/api')

// Criar instância do axios
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor para adicionar token nas requisições
api.interceptors.request.use(
  (config) => {
    const token = Cookies.get('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para tratar respostas
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado ou inválido
      Cookies.remove('auth_token')
      localStorage.removeItem('user_data')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

const authService = {
  // Login
  async login(email, password) {
    try {
      const response = await api.post('/auth/login', {
        email,
        password
      })
      
      return response.data
    } catch (error) {
      console.error('Erro no login:', error)
      throw error
    }
  },

  // Registro
  async register(userData) {
    try {
      const response = await api.post('/auth/register', userData)
      
      return response.data
    } catch (error) {
      console.error('Erro no registro:', error)
      throw error
    }
  },

  // Logout
  async logout() {
    try {
      await api.post('/auth/logout')
      
      // Limpar dados locais
      Cookies.remove('auth_token')
      localStorage.removeItem('user_data')
      
      return { success: true }
    } catch (error) {
      console.error('Erro no logout:', error)
      // Mesmo com erro, limpar dados locais
      Cookies.remove('auth_token')
      localStorage.removeItem('user_data')
      return { success: true }
    }
  },

  // Validar token
  async validateToken(token) {
    try {
      const response = await api.get('/auth/verify-token')
      
      return response.data.success === true
    } catch (error) {
      console.error('Erro na validação do token:', error)
      return false
    }
  },

  // Obter dados do usuário atual
  async getCurrentUser() {
    try {
      const response = await api.get('/auth/me')
      
      return response.data
    } catch (error) {
      console.error('Erro ao obter dados do usuário:', error)
      throw error
    }
  },

  // Atualizar perfil
  async updateProfile(userData) {
    try {
      const response = await api.put('/auth/profile', userData)
      
      return response.data
    } catch (error) {
      console.error('Erro ao atualizar perfil:', error)
      throw error
    }
  },

  // Alterar senha
  async changePassword(currentPassword, newPassword) {
    try {
      const response = await api.post('/auth/change-password', {
        current_password: currentPassword,
        new_password: newPassword
      })
      
      return response.data
    } catch (error) {
      console.error('Erro ao alterar senha:', error)
      throw error
    }
  },

  // Recuperar senha
  async forgotPassword(email) {
    try {
      const response = await api.post('/auth/forgot-password', {
        email
      })
      
      return response.data
    } catch (error) {
      console.error('Erro ao solicitar recuperação de senha:', error)
      throw error
    }
  },

  // Redefinir senha com token
  async resetPassword(token, newPassword) {
    try {
      const response = await api.post('/auth/reset-password', {
        token,
        newPassword
      })
      
      return response.data
    } catch (error) {
      console.error('Erro ao redefinir senha:', error)
      throw error
    }
  },

  // Verificar se email existe
  async checkEmailExists(email) {
    try {
      const response = await api.post('/auth/check-email', {
        email
      })
      
      return response.data
    } catch (error) {
      console.error('Erro ao verificar email:', error)
      throw error
    }
  },

  // Verificar se CPF existe
  async checkCPFExists(cpf) {
    try {
      const response = await api.post('/auth/check-cpf', {
        cpf
      })
      
      return response.data
    } catch (error) {
      console.error('Erro ao verificar CPF:', error)
      throw error
    }
  }
}

export default authService