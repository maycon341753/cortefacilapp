import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const AdminRelatorios = () => {
  const { user } = useAuth()
  const { formatCurrency, formatDate } = useApp()
  const [loading, setLoading] = useState(true)
  const [periodo, setPeriodo] = useState({
    inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    fim: new Date().toISOString().split('T')[0]
  })
  const [relatorios, setRelatorios] = useState({
    financeiro: {
      receitaTotal: 0,
      comissaoPlataforma: 0,
      receitaLiquida: 0,
      transacoes: 0
    },
    agendamentos: {
      total: 0,
      confirmados: 0,
      cancelados: 0,
      concluidos: 0,
      taxaCancelamento: 0
    },
    usuarios: {
      totalClientes: 0,
      novosClientes: 0,
      totalParceiros: 0,
      novosParceiros: 0,
      clientesAtivos: 0
    },
    saloes: {
      totalSaloes: 0,
      saloesAtivos: 0,
      saloesPendentes: 0,
      saloesSuspensos: 0
    }
  })
  const [chartData, setChartData] = useState({
    receitaMensal: [],
    agendamentosDiarios: [],
    topSaloes: [],
    servicosPopulares: []
  })

  useEffect(() => {
    loadRelatorios()
  }, [periodo])

  const loadRelatorios = async () => {
    try {
      setLoading(true)
      
      // Mock data para demonstração
      const mockRelatorios = {
        financeiro: {
          receitaTotal: 89750.50,
          comissaoPlataforma: 8975.05, // 10%
          receitaLiquida: 80775.45,
          transacoes: 1247
        },
        agendamentos: {
          total: 1247,
          confirmados: 856,
          cancelados: 156,
          concluidos: 1091,
          taxaCancelamento: 12.5
        },
        usuarios: {
          totalClientes: 2847,
          novosClientes: 124,
          totalParceiros: 156,
          novosParceiros: 8,
          clientesAtivos: 1892
        },
        saloes: {
          totalSaloes: 156,
          saloesAtivos: 142,
          saloesPendentes: 8,
          saloesSuspensos: 6
        }
      }
      
      const mockChartData = {
        receitaMensal: [
          { mes: 'Jan', receita: 45230.50, agendamentos: 567 },
          { mes: 'Fev', receita: 52180.75, agendamentos: 634 },
          { mes: 'Mar', receita: 48920.30, agendamentos: 598 },
          { mes: 'Abr', receita: 56340.80, agendamentos: 689 },
          { mes: 'Mai', receita: 61250.40, agendamentos: 745 },
          { mes: 'Jun', receita: 58970.25, agendamentos: 712 }
        ],
        agendamentosDiarios: [
          { dia: '01', agendamentos: 45 },
          { dia: '02', agendamentos: 52 },
          { dia: '03', agendamentos: 38 },
          { dia: '04', agendamentos: 61 },
          { dia: '05', agendamentos: 47 },
          { dia: '06', agendamentos: 55 },
          { dia: '07', agendamentos: 42 }
        ],
        topSaloes: [
          { nome: 'Salão Beleza Total', receita: 12450.00, agendamentos: 156 },
          { nome: 'Studio Hair Premium', receita: 10890.50, agendamentos: 134 },
          { nome: 'Barbearia Moderna', receita: 9750.25, agendamentos: 128 },
          { nome: 'Espaço Feminino', receita: 8920.75, agendamentos: 112 },
          { nome: 'Corte & Estilo', receita: 7850.30, agendamentos: 98 }
        ],
        servicosPopulares: [
          { servico: 'Corte Masculino', quantidade: 456, receita: 11400.00 },
          { servico: 'Corte + Barba', quantidade: 234, receita: 8190.00 },
          { servico: 'Corte Feminino', quantidade: 189, receita: 9450.00 },
          { servico: 'Escova', quantidade: 167, receita: 5010.00 },
          { servico: 'Coloração', quantidade: 89, receita: 7120.00 }
        ]
      }
      
      setRelatorios(mockRelatorios)
      setChartData(mockChartData)
      
    } catch (error) {
      console.error('Erro ao carregar relatórios:', error)
      toast.error('Erro ao carregar relatórios')
    } finally {
      setLoading(false)
    }
  }

  const handlePeriodoChange = (campo, valor) => {
    setPeriodo(prev => ({
      ...prev,
      [campo]: valor
    }))
  }

  const exportarRelatorio = (tipo) => {
    // Aqui você implementaria a exportação real
    toast.success(`Relatório ${tipo} exportado com sucesso!`)
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
      {/* Header */}
      <div className="row mb-4">
        <div className="col-12">
          <div className="card shadow-sm">
            <div className="card-header bg-primary text-white">
              <div className="d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-chart-bar me-2"></i>
                  Relatórios Administrativos
                </h5>
                <div className="d-flex gap-2">
                  <button 
                    className="btn btn-light btn-sm"
                    onClick={() => exportarRelatorio('completo')}
                  >
                    <i className="fas fa-download me-1"></i>
                    Exportar
                  </button>
                </div>
              </div>
            </div>
            <div className="card-body">
              <div className="row">
                <div className="col-md-4">
                  <label className="form-label">Data Início</label>
                  <input
                    type="date"
                    className="form-control"
                    value={periodo.inicio}
                    onChange={(e) => handlePeriodoChange('inicio', e.target.value)}
                  />
                </div>
                <div className="col-md-4">
                  <label className="form-label">Data Fim</label>
                  <input
                    type="date"
                    className="form-control"
                    value={periodo.fim}
                    onChange={(e) => handlePeriodoChange('fim', e.target.value)}
                  />
                </div>
                <div className="col-md-4 d-flex align-items-end">
                  <button 
                    className="btn btn-primary w-100"
                    onClick={loadRelatorios}
                  >
                    <i className="fas fa-search me-1"></i>
                    Atualizar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Cards de Resumo */}
      <div className="row mb-4">
        {/* Financeiro */}
        <div className="col-md-3 mb-3">
          <div className="card h-100 border-success">
            <div className="card-header bg-success text-white">
              <h6 className="mb-0">
                <i className="fas fa-dollar-sign me-2"></i>
                Financeiro
              </h6>
            </div>
            <div className="card-body">
              <div className="mb-2">
                <small className="text-muted">Receita Total</small>
                <div className="h5 text-success">{formatCurrency(relatorios.financeiro.receitaTotal)}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Comissão (10%)</small>
                <div className="h6">{formatCurrency(relatorios.financeiro.comissaoPlataforma)}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Receita Líquida</small>
                <div className="h6">{formatCurrency(relatorios.financeiro.receitaLiquida)}</div>
              </div>
              <div>
                <small className="text-muted">Transações</small>
                <div className="h6">{relatorios.financeiro.transacoes}</div>
              </div>
            </div>
          </div>
        </div>

        {/* Agendamentos */}
        <div className="col-md-3 mb-3">
          <div className="card h-100 border-primary">
            <div className="card-header bg-primary text-white">
              <h6 className="mb-0">
                <i className="fas fa-calendar-check me-2"></i>
                Agendamentos
              </h6>
            </div>
            <div className="card-body">
              <div className="mb-2">
                <small className="text-muted">Total</small>
                <div className="h5 text-primary">{relatorios.agendamentos.total}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Confirmados</small>
                <div className="h6 text-success">{relatorios.agendamentos.confirmados}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Cancelados</small>
                <div className="h6 text-danger">{relatorios.agendamentos.cancelados}</div>
              </div>
              <div>
                <small className="text-muted">Taxa Cancelamento</small>
                <div className="h6">{relatorios.agendamentos.taxaCancelamento}%</div>
              </div>
            </div>
          </div>
        </div>

        {/* Usuários */}
        <div className="col-md-3 mb-3">
          <div className="card h-100 border-info">
            <div className="card-header bg-info text-white">
              <h6 className="mb-0">
                <i className="fas fa-users me-2"></i>
                Usuários
              </h6>
            </div>
            <div className="card-body">
              <div className="mb-2">
                <small className="text-muted">Total Clientes</small>
                <div className="h5 text-info">{relatorios.usuarios.totalClientes}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Novos Clientes</small>
                <div className="h6 text-success">+{relatorios.usuarios.novosClientes}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Total Parceiros</small>
                <div className="h6">{relatorios.usuarios.totalParceiros}</div>
              </div>
              <div>
                <small className="text-muted">Clientes Ativos</small>
                <div className="h6">{relatorios.usuarios.clientesAtivos}</div>
              </div>
            </div>
          </div>
        </div>

        {/* Salões */}
        <div className="col-md-3 mb-3">
          <div className="card h-100 border-warning">
            <div className="card-header bg-warning text-dark">
              <h6 className="mb-0">
                <i className="fas fa-store me-2"></i>
                Salões
              </h6>
            </div>
            <div className="card-body">
              <div className="mb-2">
                <small className="text-muted">Total Salões</small>
                <div className="h5 text-warning">{relatorios.saloes.totalSaloes}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Ativos</small>
                <div className="h6 text-success">{relatorios.saloes.saloesAtivos}</div>
              </div>
              <div className="mb-2">
                <small className="text-muted">Pendentes</small>
                <div className="h6 text-warning">{relatorios.saloes.saloesPendentes}</div>
              </div>
              <div>
                <small className="text-muted">Suspensos</small>
                <div className="h6 text-danger">{relatorios.saloes.saloesSuspensos}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Gráficos e Tabelas */}
      <div className="row">
        {/* Receita Mensal */}
        <div className="col-md-6 mb-4">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">
                <i className="fas fa-chart-line me-2"></i>
                Receita Mensal
              </h6>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table table-sm">
                  <thead>
                    <tr>
                      <th>Mês</th>
                      <th>Receita</th>
                      <th>Agendamentos</th>
                    </tr>
                  </thead>
                  <tbody>
                    {chartData.receitaMensal.map((item, index) => (
                      <tr key={index}>
                        <td>{item.mes}</td>
                        <td>{formatCurrency(item.receita)}</td>
                        <td>{item.agendamentos}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {/* Top Salões */}
        <div className="col-md-6 mb-4">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">
                <i className="fas fa-trophy me-2"></i>
                Top 5 Salões
              </h6>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table table-sm">
                  <thead>
                    <tr>
                      <th>Salão</th>
                      <th>Receita</th>
                      <th>Agendamentos</th>
                    </tr>
                  </thead>
                  <tbody>
                    {chartData.topSaloes.map((salao, index) => (
                      <tr key={index}>
                        <td>
                          <span className="badge bg-primary me-2">{index + 1}º</span>
                          {salao.nome}
                        </td>
                        <td>{formatCurrency(salao.receita)}</td>
                        <td>{salao.agendamentos}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {/* Serviços Populares */}
        <div className="col-md-6 mb-4">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">
                <i className="fas fa-star me-2"></i>
                Serviços Mais Populares
              </h6>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table table-sm">
                  <thead>
                    <tr>
                      <th>Serviço</th>
                      <th>Quantidade</th>
                      <th>Receita</th>
                    </tr>
                  </thead>
                  <tbody>
                    {chartData.servicosPopulares.map((servico, index) => (
                      <tr key={index}>
                        <td>{servico.servico}</td>
                        <td>{servico.quantidade}</td>
                        <td>{formatCurrency(servico.receita)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {/* Agendamentos Diários */}
        <div className="col-md-6 mb-4">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">
                <i className="fas fa-calendar-day me-2"></i>
                Agendamentos dos Últimos 7 Dias
              </h6>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table table-sm">
                  <thead>
                    <tr>
                      <th>Dia</th>
                      <th>Agendamentos</th>
                      <th>Progresso</th>
                    </tr>
                  </thead>
                  <tbody>
                    {chartData.agendamentosDiarios.map((item, index) => {
                      const maxAgendamentos = Math.max(...chartData.agendamentosDiarios.map(d => d.agendamentos))
                      const porcentagem = (item.agendamentos / maxAgendamentos) * 100
                      
                      return (
                        <tr key={index}>
                          <td>Dia {item.dia}</td>
                          <td>{item.agendamentos}</td>
                          <td>
                            <div className="progress" style={{ height: '8px' }}>
                              <div 
                                className="progress-bar bg-primary" 
                                style={{ width: `${porcentagem}%` }}
                              ></div>
                            </div>
                          </td>
                        </tr>
                      )
                    })}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Botões de Exportação */}
      <div className="row">
        <div className="col-12">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">
                <i className="fas fa-download me-2"></i>
                Exportar Relatórios
              </h6>
            </div>
            <div className="card-body">
              <div className="row">
                <div className="col-md-3 mb-2">
                  <button 
                    className="btn btn-success w-100"
                    onClick={() => exportarRelatorio('financeiro')}
                  >
                    <i className="fas fa-file-excel me-1"></i>
                    Relatório Financeiro
                  </button>
                </div>
                <div className="col-md-3 mb-2">
                  <button 
                    className="btn btn-primary w-100"
                    onClick={() => exportarRelatorio('agendamentos')}
                  >
                    <i className="fas fa-file-pdf me-1"></i>
                    Relatório Agendamentos
                  </button>
                </div>
                <div className="col-md-3 mb-2">
                  <button 
                    className="btn btn-info w-100"
                    onClick={() => exportarRelatorio('usuarios')}
                  >
                    <i className="fas fa-file-csv me-1"></i>
                    Relatório Usuários
                  </button>
                </div>
                <div className="col-md-3 mb-2">
                  <button 
                    className="btn btn-warning w-100"
                    onClick={() => exportarRelatorio('saloes')}
                  >
                    <i className="fas fa-file-alt me-1"></i>
                    Relatório Salões
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default AdminRelatorios