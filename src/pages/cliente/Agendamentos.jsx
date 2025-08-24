import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { toast } from 'react-toastify'

const Agendamentos = () => {
  const { user } = useAuth()
  const [agendamentos, setAgendamentos] = useState([])
  const [loading, setLoading] = useState(true)
  const [filterStatus, setFilterStatus] = useState('')
  const [showCancelModal, setShowCancelModal] = useState(false)
  const [agendamentoToCancel, setAgendamentoToCancel] = useState(null)
  const [cancelReason, setCancelReason] = useState('')

  // Mock data para demonstração
  useEffect(() => {
    const mockAgendamentos = [
      {
        id: 1,
        data: '2024-01-25',
        hora: '14:00',
        salao: {
          nome: 'Salão Beleza & Estilo',
          endereco: 'Av. Paulista, 1000 - Bela Vista, São Paulo'
        },
        profissional: {
          nome: 'Maria Silva',
          foto: null
        },
        servicos: [
          { nome: 'Corte Feminino', preco: 35.00, duracao: 45 },
          { nome: 'Hidratação', preco: 40.00, duracao: 60 }
        ],
        status: 'confirmado',
        valor_total: 75.00,
        observacoes: 'Corte em camadas, cabelo cacheado',
        created_at: '2024-01-20T10:30:00'
      },
      {
        id: 2,
        data: '2024-01-28',
        hora: '10:30',
        salao: {
          nome: 'Studio Hair',
          endereco: 'Rua Augusta, 500 - Consolação, São Paulo'
        },
        profissional: {
          nome: 'João Santos',
          foto: null
        },
        servicos: [
          { nome: 'Coloração', preco: 80.00, duracao: 120 }
        ],
        status: 'pendente',
        valor_total: 80.00,
        observacoes: 'Coloração loiro mel',
        created_at: '2024-01-22T15:45:00'
      },
      {
        id: 3,
        data: '2024-01-15',
        hora: '16:00',
        salao: {
          nome: 'Salão Beleza & Estilo',
          endereco: 'Av. Paulista, 1000 - Bela Vista, São Paulo'
        },
        profissional: {
          nome: 'Ana Costa',
          foto: null
        },
        servicos: [
          { nome: 'Corte Masculino', preco: 25.00, duracao: 30 }
        ],
        status: 'concluido',
        valor_total: 25.00,
        observacoes: '',
        created_at: '2024-01-10T09:15:00'
      },
      {
        id: 4,
        data: '2024-01-12',
        hora: '11:00',
        salao: {
          nome: 'Espaço Beleza',
          endereco: 'Rua da Consolação, 200 - Centro, São Paulo'
        },
        profissional: {
          nome: 'Carlos Oliveira',
          foto: null
        },
        servicos: [
          { nome: 'Barba', preco: 20.00, duracao: 30 }
        ],
        status: 'cancelado',
        valor_total: 20.00,
        observacoes: 'Cancelado pelo cliente',
        created_at: '2024-01-08T14:20:00'
      }
    ]
    
    setTimeout(() => {
      setAgendamentos(mockAgendamentos)
      setLoading(false)
    }, 1000)
  }, [])

  const statusConfig = {
    pendente: {
      label: 'Pendente',
      color: 'bg-yellow-100 text-yellow-800',
      icon: '⏳'
    },
    confirmado: {
      label: 'Confirmado',
      color: 'bg-blue-100 text-blue-800',
      icon: '✅'
    },
    concluido: {
      label: 'Concluído',
      color: 'bg-green-100 text-green-800',
      icon: '✨'
    },
    cancelado: {
      label: 'Cancelado',
      color: 'bg-red-100 text-red-800',
      icon: '❌'
    }
  }

  const filteredAgendamentos = agendamentos.filter(agendamento => {
    if (!filterStatus) return true
    return agendamento.status === filterStatus
  })

  const handleCancelAgendamento = (agendamento) => {
    setAgendamentoToCancel(agendamento)
    setShowCancelModal(true)
  }

  const confirmCancel = async () => {
    if (!cancelReason.trim()) {
      toast.error('Por favor, informe o motivo do cancelamento')
      return
    }

    try {
      // Simular cancelamento
      setAgendamentos(prev => prev.map(ag => 
        ag.id === agendamentoToCancel.id 
          ? { ...ag, status: 'cancelado', observacoes: `Cancelado: ${cancelReason}` }
          : ag
      ))
      
      toast.success('Agendamento cancelado com sucesso!')
      setShowCancelModal(false)
      setAgendamentoToCancel(null)
      setCancelReason('')
    } catch (error) {
      toast.error('Erro ao cancelar agendamento')
    }
  }

  const canCancelAgendamento = (agendamento) => {
    const agendamentoDate = new Date(`${agendamento.data}T${agendamento.hora}`)
    const now = new Date()
    const hoursUntilAppointment = (agendamentoDate - now) / (1000 * 60 * 60)
    
    return agendamento.status === 'confirmado' && hoursUntilAppointment > 24
  }

  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  const formatTime = (timeString) => {
    return timeString
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Meus Agendamentos</h1>
          <p className="text-gray-600">Acompanhe seus agendamentos e histórico</p>
        </div>
        <a
          href="/agendar"
          className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        >
          + Novo Agendamento
        </a>
      </div>

      {/* Filtros */}
      <div className="bg-white p-4 rounded-lg shadow-sm border">
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setFilterStatus('')}
            className={`px-3 py-1 rounded-full text-sm font-medium transition-colors ${
              filterStatus === '' 
                ? 'bg-blue-100 text-blue-800' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            Todos ({agendamentos.length})
          </button>
          {Object.entries(statusConfig).map(([status, config]) => {
            const count = agendamentos.filter(ag => ag.status === status).length
            return (
              <button
                key={status}
                onClick={() => setFilterStatus(status)}
                className={`px-3 py-1 rounded-full text-sm font-medium transition-colors ${
                  filterStatus === status 
                    ? config.color 
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                {config.icon} {config.label} ({count})
              </button>
            )
          })}
        </div>
      </div>

      {/* Lista de Agendamentos */}
      <div className="space-y-4">
        {filteredAgendamentos.length === 0 ? (
          <div className="bg-white p-8 rounded-lg shadow-sm border text-center">
            <div className="text-gray-400 text-4xl mb-4">📅</div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento encontrado</h3>
            <p className="text-gray-600 mb-4">
              {filterStatus 
                ? `Você não possui agendamentos com status "${statusConfig[filterStatus].label}".`
                : 'Você ainda não possui agendamentos.'
              }
            </p>
            <a
              href="/agendar"
              className="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Fazer Primeiro Agendamento
            </a>
          </div>
        ) : (
          filteredAgendamentos.map((agendamento) => (
            <div key={agendamento.id} className="bg-white p-6 rounded-lg shadow-sm border">
              <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-3">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                      statusConfig[agendamento.status].color
                    }`}>
                      {statusConfig[agendamento.status].icon} {statusConfig[agendamento.status].label}
                    </span>
                    <span className="text-sm text-gray-500">
                      Agendado em {new Date(agendamento.created_at).toLocaleDateString('pt-BR')}
                    </span>
                  </div>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900 mb-1">
                        {agendamento.salao.nome}
                      </h3>
                      <p className="text-sm text-gray-600 mb-2">{agendamento.salao.endereco}</p>
                      
                      <div className="flex items-center text-sm text-gray-700 mb-1">
                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {formatDate(agendamento.data)}
                      </div>
                      
                      <div className="flex items-center text-sm text-gray-700 mb-2">
                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {formatTime(agendamento.hora)}
                      </div>
                      
                      <div className="flex items-center text-sm text-gray-700">
                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {agendamento.profissional.nome}
                      </div>
                    </div>
                    
                    <div>
                      <h4 className="font-medium text-gray-900 mb-2">Serviços:</h4>
                      <div className="space-y-1 mb-3">
                        {agendamento.servicos.map((servico, index) => (
                          <div key={index} className="flex justify-between text-sm">
                            <span className="text-gray-700">{servico.nome}</span>
                            <span className="text-gray-900 font-medium">R$ {servico.preco.toFixed(2)}</span>
                          </div>
                        ))}
                      </div>
                      
                      <div className="border-t pt-2">
                        <div className="flex justify-between font-semibold">
                          <span>Total:</span>
                          <span className="text-blue-600">R$ {agendamento.valor_total.toFixed(2)}</span>
                        </div>
                      </div>
                      
                      {agendamento.observacoes && (
                        <div className="mt-3">
                          <h5 className="text-sm font-medium text-gray-700 mb-1">Observações:</h5>
                          <p className="text-sm text-gray-600">{agendamento.observacoes}</p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
                
                <div className="mt-4 lg:mt-0 lg:ml-6 flex flex-col space-y-2">
                  {agendamento.status === 'confirmado' && (
                    <>
                      <button className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-sm">
                        Confirmar Presença
                      </button>
                      {canCancelAgendamento(agendamento) && (
                        <button 
                          onClick={() => handleCancelAgendamento(agendamento)}
                          className="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors text-sm"
                        >
                          Cancelar
                        </button>
                      )}
                    </>
                  )}
                  
                  {agendamento.status === 'pendente' && (
                    <button 
                      onClick={() => handleCancelAgendamento(agendamento)}
                      className="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors text-sm"
                    >
                      Cancelar
                    </button>
                  )}
                  
                  {agendamento.status === 'concluido' && (
                    <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                      Avaliar Serviço
                    </button>
                  )}
                  
                  <button className="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors text-sm">
                    Ver Detalhes
                  </button>
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Modal de Cancelamento */}
      {showCancelModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                Cancelar Agendamento
              </h3>
              
              <div className="mb-4">
                <p className="text-sm text-gray-600 mb-2">
                  Tem certeza que deseja cancelar este agendamento?
                </p>
                <div className="bg-gray-50 p-3 rounded-md">
                  <p className="text-sm font-medium">{agendamentoToCancel?.salao.nome}</p>
                  <p className="text-sm text-gray-600">
                    {formatDate(agendamentoToCancel?.data)} às {agendamentoToCancel?.hora}
                  </p>
                </div>
              </div>
              
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Motivo do cancelamento *
                </label>
                <textarea
                  rows={3}
                  required
                  value={cancelReason}
                  onChange={(e) => setCancelReason(e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Informe o motivo do cancelamento..."
                />
              </div>
              
              <div className="flex justify-end space-x-3">
                <button
                  onClick={() => {
                    setShowCancelModal(false)
                    setAgendamentoToCancel(null)
                    setCancelReason('')
                  }}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                >
                  Manter Agendamento
                </button>
                <button
                  onClick={confirmCancel}
                  className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700"
                >
                  Confirmar Cancelamento
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Agendamentos