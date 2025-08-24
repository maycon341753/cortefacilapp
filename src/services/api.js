import axios from 'axios'
import Cookies from 'js-cookie'

// Configuração base da API
const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? window.location.origin 
  : 'http://localhost:3001'

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

// Serviços da API
export const apiService = {
  // Salões
  async getSaloes() {
    const response = await api.get('/api/saloes')
    return response.data
  },

  async getSalao(id) {
    const response = await api.get(`/api/saloes/${id}`)
    return response.data
  },

  // Profissionais
  async getProfissionaisBySalao(salaoId) {
    const response = await api.get(`/api/profissionais/salao/${salaoId}`)
    return response.data
  },

  async getProfissional(id) {
    const response = await api.get(`/api/profissionais/${id}`)
    return response.data
  },

  // Serviços
  async getServicosBySalao(salaoId) {
    const response = await api.get(`/api/servicos/salao/${salaoId}`)
    return response.data
  },

  async getServico(id) {
    const response = await api.get(`/api/servicos/${id}`)
    return response.data
  },

  // Agendamentos
  async getHorariosDisponiveis(profissionalId, data) {
    const response = await api.get('/api/agendamentos/horarios-disponiveis', {
      params: { profissional_id: profissionalId, data }
    })
    return response.data
  },

  async criarAgendamento(agendamentoData) {
    const response = await api.post('/api/agendamentos', agendamentoData)
    return response.data
  },

  async getAgendamentosCliente(clienteId) {
    const response = await api.get(`/api/agendamentos/cliente/${clienteId}`)
    return response.data
  },

  async getAgendamento(id) {
    const response = await api.get(`/api/agendamentos/${id}`)
    return response.data
  },

  async atualizarStatusAgendamento(id, status) {
    const response = await api.put(`/api/agendamentos/${id}/status`, { status })
    return response.data
  },

  async cancelarAgendamento(id) {
    const response = await api.delete(`/api/agendamentos/${id}`)
    return response.data
  },

  // Usuários
  async getUsuario(id) {
    const response = await api.get(`/api/usuarios/${id}`)
    return response.data
  },

  async atualizarUsuario(id, userData) {
    const response = await api.put(`/api/usuarios/${id}`, userData)
    return response.data
  }
}

export default api