import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const AdminDashboard = () => {
  const { user } = useAuth()
  const { formatDate, formatCurrency } = useApp()
  const [loading, setLoading] = useState(false)
  const [stats, setStats] = useState({
    totalSaloes: 0,
    totalClientes: 0,
    totalAgendamentos: 0,
    receitaTotal: 0,
    agendamentosHoje: 0,
    novosSaloes: 0,
    novosClientes: 0,
    taxaCancelamento: 0
  })
  const [recentActivity, setRecentActivity] = useState([])
  const [topSaloes, setTopSaloes] = useState([])
  const [chartData, setChartData] = useState({
    agendamentosPorMes: [],
    receitaPorMes: [],
    saloesAtivos: []
  })

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    setLoading(true)
    
    try {
      // Simular carregamento de dados
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Mock data das estatísticas
      const mockStats = {
        totalSaloes: 156,
        totalClientes: 2847,
        totalAgendamentos: 12543,
        receitaTotal: 89750.50,
        agendamentosHoje: 47,
        novosSaloes: 8,
        novosClientes: 124,
        taxaCancelamento: 12.5
      }
      
      // Atualizar estatísticas
      setStats(mockStats)
      
      // Mock data das atividades recentes
      const mockActivity = [
        {
          id: 1,
          tipo: 'novo_salao',
          descricao: 'Novo salão cadastrado: "Beleza Premium"',
          timestamp: new Date(Date.now() - 1000 * 60 * 30), // 30 min atrás
          icon: '🏪'
        },
        {
          id: 2,
          tipo: 'novo_salao',
          descricao: 'Salão Beleza Total foi aprovado',
          tempo: '15 minutos atrás',
          icon: 'fas fa-store',
          color: 'info'
        },
        {
          id: 3,
          tipo: 'agendamento',
          descricao: '25 novos agendamentos hoje',
          tempo: '1 hora atrás',
          icon: 'fas fa-calendar-check',
          color: 'primary'
        },
        {
          id: 4,
          tipo: 'pagamento',
          descricao: 'Pagamento de R$ 450,00 processado',
          tempo: '2 horas atrás',
          icon: 'fas fa-dollar-sign',
          color: 'warning'
        }
      ];
      
      // Atualizar atividades recentes
      setRecentActivity(mockActivity)
      
      setTopSaloes([
        {
          id: 1,
          nome: 'Salão Beleza Total',
          agendamentos: 45,
          receita: 2250.00,
          avaliacao: 4.9
        },
        {
          id: 2,
          nome: 'Studio Hair',
          agendamentos: 38,
          receita: 1890.00,
          avaliacao: 4.8
        },
        {
          id: 3,
          nome: 'Barbearia Moderna',
          agendamentos: 32,
          receita: 1600.00,
          avaliacao: 4.7
        }
      ])
    } catch (error) {
      console.error('Erro ao carregar dados do dashboard:', error)
    } finally {
      setLoading(false)
    }
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
              <h2 className="text-gradient fw-bold mb-1">Dashboard Administrativo</h2>
              <p className="text-muted mb-0">
                Bem-vindo, {user?.nome}! Monitore e gerencie toda a plataforma CorteFácil.
              </p>
            </div>
            <div className="d-flex gap-2">
              <Link to="/admin/relatorios" className="btn btn-primary">
                <i className="fas fa-chart-line me-2"></i>
                Relatórios
              </Link>
              <Link to="/admin/configuracoes" className="btn btn-outline-primary">
                <i className="fas fa-cog me-2"></i>
                Configurações
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Cards de estatísticas principais */}
      <div className="row mb-4">
        <div className="col-lg-3 col-md-6 mb-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i className="fas fa-users text-primary fs-4"></i>
                </div>
                <div className="flex-grow-1">
                  <h3 className="fw-bold mb-0">{stats.totalUsuarios.toLocaleString()}</h3>
                  <p className="text-muted mb-1 small">Total de Usuários</p>
                  <small className="text-success">
                    <i className="fas fa-arrow-up me-1"></i>
                    +{stats.novosUsuarios} este mês
                  </small>
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
                  <i className="fas fa-store text-info fs-4"></i>
                </div>
                <div className="flex-grow-1">
                  <h3 className="fw-bold mb-0">{stats.totalSaloes}</h3>
                  <p className="text-muted mb-1 small">Salões Ativos</p>
                  <small className="text-success">
                    <i className="fas fa-arrow-up me-1"></i>
                    +{stats.novosSaloes} este mês
                  </small>
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
                  <i className="fas fa-calendar-check text-success fs-4"></i>
                </div>
                <div className="flex-grow-1">
                  <h3 className="fw-bold mb-0">{stats.agendamentosHoje}</h3>
                  <p className="text-muted mb-1 small">Agendamentos Hoje</p>
                  <small className="text-info">
                    {stats.agendamentosMes.toLocaleString()} este mês
                  </small>
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
                  <i className="fas fa-dollar-sign text-warning fs-4"></i>
                </div>
                <div className="flex-grow-1">
                  <h3 className="fw-bold mb-0">{formatCurrency(stats.receitaTotal)}</h3>
                  <p className="text-muted mb-1 small">Receita Total</p>
                  <small className="text-success">
                    {formatCurrency(stats.receitaMes)} este mês
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        {/* Gráfico de Performance */}
        <div className="col-lg-8 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <div className="d-flex justify-content-between align-items-center">
                <h5 className="fw-bold mb-0">
                  <i className="fas fa-chart-area me-2 text-primary"></i>
                  Performance da Plataforma
                </h5>
                <div className="btn-group btn-group-sm">
                  <button className="btn btn-outline-primary active">7 dias</button>
                  <button className="btn btn-outline-primary">30 dias</button>
                  <button className="btn btn-outline-primary">90 dias</button>
                </div>
              </div>
            </div>
            <div className="card-body">
              {/* Aqui você integraria um gráfico real como Chart.js */}
              <div className="row text-center">
                <div className="col-md-3 mb-3">
                  <div className="border rounded p-3">
                    <i className="fas fa-users text-primary fs-2 mb-2"></i>
                    <h6 className="fw-bold">Novos Usuários</h6>
                    <div className="d-flex justify-content-center align-items-center">
                      <span className="fs-4 fw-bold me-2">156</span>
                      <small className="text-success">
                        <i className="fas fa-arrow-up"></i> 12%
                      </small>
                    </div>
                  </div>
                </div>
                <div className="col-md-3 mb-3">
                  <div className="border rounded p-3">
                    <i className="fas fa-calendar-check text-info fs-2 mb-2"></i>
                    <h6 className="fw-bold">Agendamentos</h6>
                    <div className="d-flex justify-content-center align-items-center">
                      <span className="fs-4 fw-bold me-2">1,234</span>
                      <small className="text-success">
                        <i className="fas fa-arrow-up"></i> 8%
                      </small>
                    </div>
                  </div>
                </div>
                <div className="col-md-3 mb-3">
                  <div className="border rounded p-3">
                    <i className="fas fa-dollar-sign text-success fs-2 mb-2"></i>
                    <h6 className="fw-bold">Receita</h6>
                    <div className="d-flex justify-content-center align-items-center">
                      <span className="fs-4 fw-bold me-2">R$ 45K</span>
                      <small className="text-success">
                        <i className="fas fa-arrow-up"></i> 15%
                      </small>
                    </div>
                  </div>
                </div>
                <div className="col-md-3 mb-3">
                  <div className="border rounded p-3">
                    <i className="fas fa-star text-warning fs-2 mb-2"></i>
                    <h6 className="fw-bold">Satisfação</h6>
                    <div className="d-flex justify-content-center align-items-center">
                      <span className="fs-4 fw-bold me-2">4.8</span>
                      <small className="text-success">
                        <i className="fas fa-arrow-up"></i> 0.2
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Atividade Recente */}
        <div className="col-lg-4 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-bell me-2 text-warning"></i>
                Atividade Recente
              </h5>
            </div>
            <div className="card-body">
              <div className="timeline">
                {recentActivity.map(activity => (
                  <div key={activity.id} className="d-flex mb-3">
                    <div className={`bg-${activity.color} bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0`} style={{ width: '40px', height: '40px' }}>
                      <i className={`${activity.icon} text-${activity.color} small`}></i>
                    </div>
                    <div className="flex-grow-1">
                      <p className="mb-1 small">{activity.descricao}</p>
                      <small className="text-muted">{activity.tempo}</small>
                    </div>
                  </div>
                ))}
              </div>
              <div className="text-center mt-3">
                <Link to="/admin/atividades" className="btn btn-outline-primary btn-sm">
                  Ver Todas as Atividades
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        {/* Top Salões */}
        <div className="col-lg-6 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-trophy me-2 text-warning"></i>
                Top Salões do Mês
              </h5>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead>
                    <tr>
                      <th>Posição</th>
                      <th>Salão</th>
                      <th>Agendamentos</th>
                      <th>Receita</th>
                      <th>Avaliação</th>
                    </tr>
                  </thead>
                  <tbody>
                    {topSaloes.map((salao, index) => (
                      <tr key={salao.id}>
                        <td>
                          <div className="d-flex align-items-center">
                            {index === 0 && <i className="fas fa-crown text-warning me-2"></i>}
                            {index === 1 && <i className="fas fa-medal text-secondary me-2"></i>}
                            {index === 2 && <i className="fas fa-award text-warning me-2"></i>}
                            <span className="fw-bold">#{index + 1}</span>
                          </div>
                        </td>
                        <td className="fw-medium">{salao.nome}</td>
                        <td>{salao.agendamentos}</td>
                        <td className="text-success fw-bold">{formatCurrency(salao.receita)}</td>
                        <td>
                          <div className="d-flex align-items-center">
                            <i className="fas fa-star text-warning me-1"></i>
                            <span>{salao.avaliacao}</span>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {/* Ações Rápidas */}
        <div className="col-lg-6 mb-4">
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white border-0 py-3">
              <h5 className="fw-bold mb-0">
                <i className="fas fa-bolt me-2 text-primary"></i>
                Ações Rápidas
              </h5>
            </div>
            <div className="card-body">
              <div className="row">
                <div className="col-md-6 mb-3">
                  <Link to="/admin/usuarios" className="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                    <i className="fas fa-users fs-2 mb-2"></i>
                    <span className="fw-medium">Gerenciar Usuários</span>
                    <small className="text-muted">Clientes e Parceiros</small>
                  </Link>
                </div>
                <div className="col-md-6 mb-3">
                  <Link to="/admin/saloes" className="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                    <i className="fas fa-store fs-2 mb-2"></i>
                    <span className="fw-medium">Gerenciar Salões</span>
                    <small className="text-muted">Aprovar e monitorar</small>
                  </Link>
                </div>
                <div className="col-md-6 mb-3">
                  <Link to="/admin/agendamentos" className="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                    <i className="fas fa-calendar-check fs-2 mb-2"></i>
                    <span className="fw-medium">Agendamentos</span>
                    <small className="text-muted">Monitorar sistema</small>
                  </Link>
                </div>
                <div className="col-md-6 mb-3">
                  <Link to="/admin/relatorios" className="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                    <i className="fas fa-chart-bar fs-2 mb-2"></i>
                    <span className="fw-medium">Relatórios</span>
                    <small className="text-muted">Análises detalhadas</small>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default AdminDashboard