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

// Serviços da API
export const apiService = {
  // Salões
  async getSaloes() {
    const response = await api.get('/saloes')
    return response.data
  },

  async getSalao(id) {
    const response = await api.get(`/saloes/${id}`)
    return response.data
  },

  async getHorariosFuncionamento(salaoId) {
    const response = await api.get(`/horarios-funcionamento/${salaoId}`)
    return response.data
  },

  // Profissionais
  async getProfissionaisBySalao(salaoId) {
    const response = await api.get(`/profissionais/salao/${salaoId}`)
    return response.data
  },

  async getProfissional(id) {
    const response = await api.get(`/profissionais/${id}`)
    return response.data
  },

  // Serviços
  async getServicosBySalao(salaoId) {
    const response = await api.get(`/servicos/salao/${salaoId}`)
    return response.data
  },

  async getServico(id) {
    const response = await api.get(`/servicos/${id}`)
    return response.data
  },

  // Agendamentos
  async getHorariosDisponiveis(profissionalId, data, salaoId) {
    const response = await api.get('/agendamentos/horarios-disponiveis', {
      params: { profissional_id: profissionalId, data, salao_id: salaoId }
    })
    return response.data
  },

  async criarAgendamento(agendamentoData) {
    const response = await api.post('/agendamentos', agendamentoData)
    return response.data
  },

  async getAgendamentosCliente(clienteId) {
    const response = await api.get(`/agendamentos/cliente/${clienteId}`)
    return response.data
  },

  async getAgendamento(id) {
    const response = await api.get(`/agendamentos/${id}`)
    return response.data
  },

  async atualizarStatusAgendamento(id, status) {
    const response = await api.put(`/agendamentos/${id}/status`, { status })
    return response.data
  },

  async cancelarAgendamento(id, dados = {}) {
    const response = await api.put(`/agendamentos/${id}/cancelar`, dados)
    return response.data
  },

  // Pagamentos
  async verificarStatusPagamento(agendamentoId) {
    const response = await api.get(`/agendamentos/${agendamentoId}/status-pagamento`)
    return response.data
  },

  async confirmarPagamento(agendamentoId) {
    const response = await api.post(`/agendamentos/${agendamentoId}/confirmar-pagamento`)
    return response.data
  },

  // Usuários
  async getUsuario(id) {
    const response = await api.get(`/usuarios/${id}`)
    return response.data
  },

  async atualizarUsuario(id, userData) {
    const response = await api.put(`/usuarios/${id}`, userData)
    return response.data
  }
}

export default api