import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const ClienteDashboard = () => {
  const { user } = useAuth()
  const { formatDate, formatCurrency } = useApp()
  const [stats, setStats] = useState({
    proximosAgendamentos: 0,
    agendamentosRealizados: 0,
    valorGasto: 0,
    saloesVisitados: 0
  })
  const [proximosAgendamentos, setProximosAgendamentos] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      setLoading(true)
      // Aqui você faria as chamadas para a API
      // Por enquanto, dados mockados
      setStats({
        proximosAgendamentos: 2,
        agendamentosRealizados: 15,
        valorGasto: 450.00,
        saloesVisitados: 3
      })
      
      setProximosAgendamentos([
        {
          id: 1,
          salao: 'Salão Beleza Total',
          servico: 'Corte + Barba',
          profissional: 'João Silva',
          data: '2025-01-15',
          hora: '14:30',
          valor: 45.00,
          status: 'confirmado'
        },
        {
          id: 2,
          salao: 'Studio Hair',
          servico: 'Corte Masculino',
          profissional: 'Maria Santos',
          data: '2025-01-20',
          hora: '16:00',
          valor: 35.00,
          status: 'pendente'
        }
      ])
    } catch (error) {
      console.error('Erro ao carregar dados do dashboard:', error)
    } finally {
      setLoading(false)
    }
  }

  const getStatusBadge = (status) => {
    const badges = {
      confirmado: 'bg-success',
      pendente: 'bg-warning',
      cancelado: 'bg-danger'
    }
    return badges[status] || 'bg-secondary'
  }

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center min-vh-75">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Carregando...</span>
        </div>
      </div>
    )
  }

  return (
    <div className="container-fluid py-4">
      {/* Cabeçalho */}
      <div className="row mb-4">
        <div className="col-12">
          <div className="d-flex justify-content-between align-items-center">
            <div>
              <h2 className="text-gradient fw-bold mb-1">Dashboard</h2>
              <p className="text-muted mb-0">
                Bem-vindo de volta, {user?.nome}! Aqui está um resumo da sua atividade.
              </p>
            </div>
            <Link to="/cliente/agendar" className="btn btn-primary btn-lg">
              <i className="fas fa-plus me-2"></i>
              Novo Agendamento
            </Link>
          </div>
        </div>
      </div>

      {/* Cards de estatísticas */}
      <div className="row mb-4">
        <div className="col-lg-3 col-md-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i className="fas fa-calendar-check text-primary fs-4"></i>
                </div>
                <div>
                  <h3 className="fw-bold mb-0">{stats.proximosAgendamentos}</h3>
                  <p className="text-muted mb-0 small">Próximos Agendamentos</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="col-lg-3 col-md-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i className="fas fa-check-circle text-success fs-4"></i>
                </div>
                <div>
                  <h3 className="fw-bold mb-0">{stats.agendamentosRealizados}</h3>
                  <p className="text-muted mb-0 small">Agendamentos Realizados</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="col-lg-3 col-md-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                  <i className="fas fa-dollar-sign text-info fs-4"></i>
                </div>
                <div>
                  <h3 className="fw-bold mb-0">{formatCurrency(stats.valorGasto)}</h3>
                  <p className="text-muted mb-0 small">Total Gasto</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="col-lg-3 col-md-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                  <i className="fas fa-store text-warning fs-4"></i>
                </div>
                <div>
                  <h3 className="fw-bold mb-0">{stats.saloesVisitados}</h3>
                  <p className="text-muted mb-0 small">Salões Visitados</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        {/* Próximos Agendamentos */}
        <div className="col-lg-8 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <div className="d-flex justify-content-between align-items-center">
                <h5 className="fw-bold mb-0">
                  <i className="fas fa-calendar-alt me-2 text-primary"></i>
                  Próximos Agendamentos
                </h5>
                <Link to="/cliente/agendamentos" className="btn btn-outline-primary btn-sm">
                  Ver Todos
                </Link>
              </div>
            </div>
            <div className="card-body">
              {proximosAgendamentos.length > 0 ? (
                <div className="row">
                  {proximosAgendamentos.map(agendamento => (
                    <div key={agendamento.id} className="col-12 mb-3">
                      <div className="border rounded p-3 hover-shadow transition-custom">
                        <div className="d-flex justify-content-between align-items-start">
                          <div className="flex-grow-1">
                            <div className="d-flex align-items-center mb-2">
                              <h6 className="fw-bold mb-0 me-2">{agendamento.salao}</h6>
                              <span className={`badge ${getStatusBadge(agendamento.status)} text-capitalize`}>
                                {agendamento.status}
                              </span>
                            </div>
                            <p className="text-muted mb-1">
                              <i className="fas fa-cut me-2"></i>
                              {agendamento.servico} - {agendamento.profissional}
                            </p>
                            <p className="text-muted mb-0">
                              <i className="fas fa-clock me-2"></i>
                              {formatDate(agendamento.data)} às {agendamento.hora}
                            </p>
                          </div>
                          <div className="text-end">
                            <div className="fw-bold text-primary fs-5">
                              {formatCurrency(agendamento.valor)}
                            </div>
                            <div className="mt-2">
                              <button className="btn btn-outline-primary btn-sm me-2">
                                <i className="fas fa-eye"></i>
                              </button>
                              <button className="btn btn-outline-secondary btn-sm">
                                <i className="fas fa-edit"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-4">
                  <i className="fas fa-calendar-times text-muted fs-1 mb-3"></i>
                  <h6 className="text-muted">Nenhum agendamento próximo</h6>
                  <p className="text-muted mb-3">Que tal agendar um novo serviço?</p>
                  <Link to="/cliente/agendar" className="btn btn-primary">
                    Agendar Agora
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Ações Rápidas */}
        <div className="col-lg-4 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-bolt me-2 text-warning"></i>
                Ações Rápidas
              </h5>
            </div>
            <div className="card-body">
              <div className="d-grid gap-3">
                <Link to="/cliente/agendar" className="btn btn-outline-primary d-flex align-items-center">
                  <i className="fas fa-plus me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Novo Agendamento</div>
                    <small className="text-muted">Agende um serviço</small>
                  </div>
                </Link>
                
                <Link to="/cliente/saloes" className="btn btn-outline-info d-flex align-items-center">
                  <i className="fas fa-search me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Encontrar Salões</div>
                    <small className="text-muted">Descubra novos locais</small>
                  </div>
                </Link>
                
                <Link to="/cliente/agendamentos" className="btn btn-outline-success d-flex align-items-center">
                  <i className="fas fa-history me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Meu Histórico</div>
                    <small className="text-muted">Ver agendamentos</small>
                  </div>
                </Link>
                
                <Link to="/cliente/perfil" className="btn btn-outline-secondary d-flex align-items-center">
                  <i className="fas fa-user-cog me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Meu Perfil</div>
                    <small className="text-muted">Editar dados</small>
                  </div>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ClienteDashboard