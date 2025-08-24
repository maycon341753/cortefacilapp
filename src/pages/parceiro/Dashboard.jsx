import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const ParceiroDashboard = () => {
  const { user } = useAuth()
  const { formatDate, formatCurrency } = useApp()
  const [stats, setStats] = useState({
    agendamentosHoje: 0,
    receitaHoje: 0,
    agendamentosMes: 0,
    receitaMes: 0,
    avaliacaoMedia: 0,
    clientesAtivos: 0
  })
  const [agendamentosHoje, setAgendamentosHoje] = useState([])
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
        agendamentosHoje: 8,
        receitaHoje: 420.00,
        agendamentosMes: 156,
        receitaMes: 7850.00,
        avaliacaoMedia: 4.8,
        clientesAtivos: 89
      })
      
      setAgendamentosHoje([
        {
          id: 1,
          cliente: 'João Silva',
          servico: 'Corte + Barba',
          profissional: 'Carlos Santos',
          hora: '09:00',
          valor: 45.00,
          status: 'confirmado',
          telefone: '(11) 99999-9999'
        },
        {
          id: 2,
          cliente: 'Maria Oliveira',
          servico: 'Corte Feminino',
          profissional: 'Ana Costa',
          hora: '10:30',
          valor: 60.00,
          status: 'em_andamento',
          telefone: '(11) 88888-8888'
        },
        {
          id: 3,
          cliente: 'Pedro Santos',
          servico: 'Barba',
          profissional: 'Carlos Santos',
          hora: '14:00',
          valor: 25.00,
          status: 'pendente',
          telefone: '(11) 77777-7777'
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
      em_andamento: 'bg-info',
      concluido: 'bg-primary',
      cancelado: 'bg-danger'
    }
    return badges[status] || 'bg-secondary'
  }

  const getStatusText = (status) => {
    const texts = {
      confirmado: 'Confirmado',
      pendente: 'Pendente',
      em_andamento: 'Em Andamento',
      concluido: 'Concluído',
      cancelado: 'Cancelado'
    }
    return texts[status] || status
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
              <h2 className="text-gradient fw-bold mb-1">Dashboard do Parceiro</h2>
              <p className="text-muted mb-0">
                Bem-vindo, {user?.nome}! Gerencie seu salão e acompanhe o desempenho.
              </p>
            </div>
            <div className="d-flex gap-2">
              <Link to="/parceiro/agendamentos/novo" className="btn btn-primary">
                <i className="fas fa-plus me-2"></i>
                Novo Agendamento
              </Link>
              <Link to="/parceiro/relatorios" className="btn btn-outline-primary">
                <i className="fas fa-chart-bar me-2"></i>
                Relatórios
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Cards de estatísticas */}
      <div className="row mb-4">
        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-primary bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-calendar-day text-primary fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{stats.agendamentosHoje}</h3>
              <p className="text-muted mb-0 small">Hoje</p>
            </div>
          </div>
        </div>

        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-success bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-dollar-sign text-success fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{formatCurrency(stats.receitaHoje)}</h3>
              <p className="text-muted mb-0 small">Receita Hoje</p>
            </div>
          </div>
        </div>

        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-info bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-calendar-check text-info fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{stats.agendamentosMes}</h3>
              <p className="text-muted mb-0 small">Este Mês</p>
            </div>
          </div>
        </div>

        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-warning bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-chart-line text-warning fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{formatCurrency(stats.receitaMes)}</h3>
              <p className="text-muted mb-0 small">Receita Mês</p>
            </div>
          </div>
        </div>

        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-danger bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-star text-danger fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{stats.avaliacaoMedia}</h3>
              <p className="text-muted mb-0 small">Avaliação</p>
            </div>
          </div>
        </div>

        <div className="col-lg-2 col-md-4 col-sm-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body text-center">
              <div className="bg-secondary bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style={{ width: '60px', height: '60px' }}>
                <i className="fas fa-users text-secondary fs-4"></i>
              </div>
              <h3 className="fw-bold mb-1">{stats.clientesAtivos}</h3>
              <p className="text-muted mb-0 small">Clientes</p>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        {/* Agendamentos de Hoje */}
        <div className="col-lg-8 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <div className="d-flex justify-content-between align-items-center">
                <h5 className="fw-bold mb-0">
                  <i className="fas fa-calendar-day me-2 text-primary"></i>
                  Agendamentos de Hoje
                </h5>
                <Link to="/parceiro/agendamentos" className="btn btn-outline-primary btn-sm">
                  Ver Todos
                </Link>
              </div>
            </div>
            <div className="card-body">
              {agendamentosHoje.length > 0 ? (
                <div className="table-responsive">
                  <table className="table table-hover">
                    <thead>
                      <tr>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Profissional</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      {agendamentosHoje.map(agendamento => (
                        <tr key={agendamento.id}>
                          <td className="fw-medium">{agendamento.hora}</td>
                          <td>
                            <div>
                              <div className="fw-medium">{agendamento.cliente}</div>
                              <small className="text-muted">{agendamento.telefone}</small>
                            </div>
                          </td>
                          <td>{agendamento.servico}</td>
                          <td>{agendamento.profissional}</td>
                          <td className="fw-bold text-success">{formatCurrency(agendamento.valor)}</td>
                          <td>
                            <span className={`badge ${getStatusBadge(agendamento.status)}`}>
                              {getStatusText(agendamento.status)}
                            </span>
                          </td>
                          <td>
                            <div className="btn-group btn-group-sm">
                              <button className="btn btn-outline-primary" title="Ver detalhes">
                                <i className="fas fa-eye"></i>
                              </button>
                              <button className="btn btn-outline-success" title="Editar">
                                <i className="fas fa-edit"></i>
                              </button>
                              <button className="btn btn-outline-info" title="WhatsApp">
                                <i className="fab fa-whatsapp"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="text-center py-4">
                  <i className="fas fa-calendar-times text-muted fs-1 mb-3"></i>
                  <h6 className="text-muted">Nenhum agendamento para hoje</h6>
                  <p className="text-muted mb-3">Que tal promover seus serviços?</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Ações Rápidas */}
        <div className="col-lg-4 mb-4">
          <div className="card border-0 shadow-sm mb-4">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-bolt me-2 text-warning"></i>
                Ações Rápidas
              </h5>
            </div>
            <div className="card-body">
              <div className="d-grid gap-3">
                <Link to="/parceiro/agendamentos/novo" className="btn btn-outline-primary d-flex align-items-center">
                  <i className="fas fa-plus me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Novo Agendamento</div>
                    <small className="text-muted">Agendar para cliente</small>
                  </div>
                </Link>
                
                <Link to="/parceiro/profissionais" className="btn btn-outline-info d-flex align-items-center">
                  <i className="fas fa-users me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Gerenciar Equipe</div>
                    <small className="text-muted">Profissionais</small>
                  </div>
                </Link>
                
                <Link to="/parceiro/servicos" className="btn btn-outline-success d-flex align-items-center">
                  <i className="fas fa-cut me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Meus Serviços</div>
                    <small className="text-muted">Catálogo</small>
                  </div>
                </Link>
                
                <Link to="/parceiro/salao" className="btn btn-outline-warning d-flex align-items-center">
                  <i className="fas fa-store me-3"></i>
                  <div className="text-start">
                    <div className="fw-medium">Meu Salão</div>
                    <small className="text-muted">Configurações</small>
                  </div>
                </Link>
              </div>
            </div>
          </div>

          {/* Resumo Rápido */}
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-chart-pie me-2 text-info"></i>
                Resumo Rápido
              </h5>
            </div>
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center mb-3">
                <span className="text-muted">Taxa de Ocupação</span>
                <span className="fw-bold">85%</span>
              </div>
              <div className="progress mb-3" style={{ height: '8px' }}>
                <div className="progress-bar bg-success" style={{ width: '85%' }}></div>
              </div>
              
              <div className="d-flex justify-content-between align-items-center mb-3">
                <span className="text-muted">Próximo Agendamento</span>
                <span className="fw-bold">09:00</span>
              </div>
              
              <div className="d-flex justify-content-between align-items-center">
                <span className="text-muted">Meta Mensal</span>
                <span className="fw-bold text-success">78%</span>
              </div>
              <div className="progress" style={{ height: '8px' }}>
                <div className="progress-bar bg-warning" style={{ width: '78%' }}></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ParceiroDashboard