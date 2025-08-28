import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Agendamentos = () => {
  const { user } = useAuth()
  const { formatCurrency, formatDate, formatTime } = useApp()
  const [agendamentos, setAgendamentos] = useState([])
  const [loading, setLoading] = useState(true)
  const [filtros, setFiltros] = useState({
    status: 'todos',
    data: '',
    profissional: '',
    servico: ''
  })

  // Mock data para demonstração
  useEffect(() => {
    const mockAgendamentos = [
      {
        id: 1,
        cliente: { nome: 'João Silva', telefone: '(11) 99999-9999' },
        profissional: { nome: 'Maria Santos' },
        servico: { nome: 'Corte Masculino', preco: 25.00 },
        data: '2024-01-15',
        hora: '09:00',
        status: 'confirmado',
        observacoes: 'Cliente prefere corte baixo'
      },
      {
        id: 2,
        cliente: { nome: 'Ana Costa', telefone: '(11) 88888-8888' },
        profissional: { nome: 'Carlos Oliveira' },
        servico: { nome: 'Corte + Barba', preco: 35.00 },
        data: '2024-01-15',
        hora: '10:30',
        status: 'pendente',
        observacoes: ''
      },
      {
        id: 3,
        cliente: { nome: 'Pedro Alves', telefone: '(11) 77777-7777' },
        profissional: { nome: 'Maria Santos' },
        servico: { nome: 'Corte Masculino', preco: 25.00 },
        data: '2024-01-15',
        hora: '14:00',
        status: 'concluido',
        observacoes: ''
      }
    ]
    
    setTimeout(() => {
      setAgendamentos(mockAgendamentos)
      setLoading(false)
    }, 1000)
  }, [])

  const handleStatusChange = (agendamentoId, novoStatus) => {
    setAgendamentos(prev => 
      prev.map(agendamento => 
        agendamento.id === agendamentoId 
          ? { ...agendamento, status: novoStatus }
          : agendamento
      )
    )
    toast.success('Status do agendamento atualizado!')
  }

  const getStatusColor = (status) => {
    const colors = {
      pendente: 'bg-yellow-100 text-yellow-800',
      confirmado: 'bg-blue-100 text-blue-800',
      concluido: 'bg-green-100 text-green-800',
      cancelado: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }

  const getStatusText = (status) => {
    const texts = {
      pendente: 'Pendente',
      confirmado: 'Confirmado',
      concluido: 'Concluído',
      cancelado: 'Cancelado'
    }
    return texts[status] || status
  }

  const agendamentosFiltrados = agendamentos.filter(agendamento => {
    if (filtros.status !== 'todos' && agendamento.status !== filtros.status) return false
    if (filtros.data && agendamento.data !== filtros.data) return false
    if (filtros.profissional && !agendamento.profissional.nome.toLowerCase().includes(filtros.profissional.toLowerCase())) return false
    if (filtros.servico && !agendamento.servico.nome.toLowerCase().includes(filtros.servico.toLowerCase())) return false
    return true
  })

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
          <h1 className="text-2xl font-bold text-gray-900">Agendamentos</h1>
          <p className="text-gray-600">Gerencie todos os agendamentos do seu salão</p>
        </div>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
          Novo Agendamento
        </button>
      </div>

      {/* Filtros */}
      <div className="bg-white p-6 rounded-lg shadow-sm border">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              value={filtros.status}
              onChange={(e) => setFiltros(prev => ({ ...prev, status: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="todos">Todos</option>
              <option value="pendente">Pendente</option>
              <option value="confirmado">Confirmado</option>
              <option value="concluido">Concluído</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Data</label>
            <input
              type="date"
              value={filtros.data}
              onChange={(e) => setFiltros(prev => ({ ...prev, data: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Profissional</label>
            <input
              type="text"
              placeholder="Buscar profissional..."
              value={filtros.profissional}
              onChange={(e) => setFiltros(prev => ({ ...prev, profissional: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Serviço</label>
            <input
              type="text"
              placeholder="Buscar serviço..."
              value={filtros.servico}
              onChange={(e) => setFiltros(prev => ({ ...prev, servico: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      {/* Lista de Agendamentos */}
      <div className="bg-white rounded-lg shadow-sm border">
        <div className="px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">
            Agendamentos ({agendamentosFiltrados.length})
          </h3>
        </div>
        
        {agendamentosFiltrados.length === 0 ? (
          <div className="p-6 text-center text-gray-500">
            <p>Nenhum agendamento encontrado com os filtros aplicados.</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-200">
            {agendamentosFiltrados.map((agendamento) => (
              <div key={agendamento.id} className="p-6 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-4">
                      <div>
                        <h4 className="text-lg font-medium text-gray-900">
                          {agendamento.cliente.nome}
                        </h4>
                        <p className="text-sm text-gray-600">{agendamento.cliente.telefone}</p>
                      </div>
                      <div className="text-sm text-gray-600">
                        <p><strong>Profissional:</strong> {agendamento.profissional.nome}</p>
                        <p><strong>Serviço:</strong> {agendamento.servico.nome}</p>
                      </div>
                      <div className="text-sm text-gray-600">
                        <p><strong>Data:</strong> {formatDate(agendamento.data)}</p>
                        <p><strong>Horário:</strong> {agendamento.hora}</p>
                      </div>
                      <div className="text-sm text-gray-600">
                        <p><strong>Valor:</strong> {formatCurrency(agendamento.servico.preco)}</p>
                      </div>
                    </div>
                    {agendamento.observacoes && (
                      <div className="mt-2">
                        <p className="text-sm text-gray-600">
                          <strong>Observações:</strong> {agendamento.observacoes}
                        </p>
                      </div>
                    )}
                  </div>
                  
                  <div className="flex items-center space-x-3">
                    <span className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(agendamento.status)}`}>
                      {getStatusText(agendamento.status)}
                    </span>
                    
                    <div className="flex space-x-2">
                      {agendamento.status === 'pendente' && (
                        <>
                          <button
                            onClick={() => handleStatusChange(agendamento.id, 'confirmado')}
                            className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                          >
                            Confirmar
                          </button>
                          <button
                            onClick={() => handleStatusChange(agendamento.id, 'cancelado')}
                            className="text-red-600 hover:text-red-800 text-sm font-medium"
                          >
                            Cancelar
                          </button>
                        </>
                      )}
                      {agendamento.status === 'confirmado' && (
                        <>
                          <button
                            onClick={() => handleStatusChange(agendamento.id, 'concluido')}
                            className="text-green-600 hover:text-green-800 text-sm font-medium"
                          >
                            Concluir
                          </button>
                          <button
                            onClick={() => handleStatusChange(agendamento.id, 'cancelado')}
                            className="text-red-600 hover:text-red-800 text-sm font-medium"
                          >
                            Cancelar
                          </button>
                        </>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

export default Agendamentos