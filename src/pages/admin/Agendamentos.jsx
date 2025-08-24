import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const AdminAgendamentos = () => {
  const { user } = useAuth()
  const { formatCurrency, formatDate, formatTime } = useApp()
  const [loading, setLoading] = useState(true)
  const [agendamentos, setAgendamentos] = useState([])
  const [filtros, setFiltros] = useState({
    status: '',
    salao: '',
    cliente: '',
    dataInicio: '',
    dataFim: '',
    servico: ''
  })
  const [paginacao, setPaginacao] = useState({
    pagina: 1,
    itensPorPagina: 20,
    total: 0
  })
  const [modalDetalhes, setModalDetalhes] = useState({
    show: false,
    agendamento: null
  })
  const [modalAcao, setModalAcao] = useState({
    show: false,
    agendamento: null,
    acao: '',
    motivo: ''
  })

  useEffect(() => {
    loadAgendamentos()
  }, [filtros, paginacao.pagina])

  const loadAgendamentos = async () => {
    try {
      setLoading(true)
      
      // Mock data para demonstração
      const mockAgendamentos = [
        {
          id: 1,
          cliente: {
            id: 101,
            nome: 'João Silva',
            email: 'joao@email.com',
            telefone: '(11) 99999-9999'
          },
          salao: {
            id: 201,
            nome: 'Salão Beleza Total',
            endereco: 'Rua das Flores, 123'
          },
          servico: {
            id: 301,
            nome: 'Corte Masculino',
            preco: 25.00,
            duracao: 30
          },
          data: '2024-01-15',
          hora: '14:30',
          status: 'confirmado',
          valor: 25.00,
          comissao: 2.50,
          formaPagamento: 'cartao',
          observacoes: 'Cliente preferencial',
          criadoEm: '2024-01-10T10:30:00',
          atualizadoEm: '2024-01-12T15:45:00'
        },
        {
          id: 2,
          cliente: {
            id: 102,
            nome: 'Maria Santos',
            email: 'maria@email.com',
            telefone: '(11) 88888-8888'
          },
          salao: {
            id: 202,
            nome: 'Studio Hair Premium',
            endereco: 'Av. Principal, 456'
          },
          servico: {
            id: 302,
            nome: 'Corte + Escova',
            preco: 45.00,
            duracao: 60
          },
          data: '2024-01-15',
          hora: '16:00',
          status: 'pendente',
          valor: 45.00,
          comissao: 4.50,
          formaPagamento: 'dinheiro',
          observacoes: '',
          criadoEm: '2024-01-14T09:15:00',
          atualizadoEm: '2024-01-14T09:15:00'
        },
        {
          id: 3,
          cliente: {
            id: 103,
            nome: 'Pedro Costa',
            email: 'pedro@email.com',
            telefone: '(11) 77777-7777'
          },
          salao: {
            id: 203,
            nome: 'Barbearia Moderna',
            endereco: 'Rua do Comércio, 789'
          },
          servico: {
            id: 303,
            nome: 'Corte + Barba',
            preco: 35.00,
            duracao: 45
          },
          data: '2024-01-14',
          hora: '10:00',
          status: 'concluido',
          valor: 35.00,
          comissao: 3.50,
          formaPagamento: 'pix',
          observacoes: 'Primeira vez no salão',
          criadoEm: '2024-01-12T14:20:00',
          atualizadoEm: '2024-01-14T11:30:00'
        },
        {
          id: 4,
          cliente: {
            id: 104,
            nome: 'Ana Oliveira',
            email: 'ana@email.com',
            telefone: '(11) 66666-6666'
          },
          salao: {
            id: 204,
            nome: 'Espaço Feminino',
            endereco: 'Rua da Beleza, 321'
          },
          servico: {
            id: 304,
            nome: 'Coloração',
            preco: 80.00,
            duracao: 120
          },
          data: '2024-01-13',
          hora: '09:00',
          status: 'cancelado',
          valor: 80.00,
          comissao: 8.00,
          formaPagamento: 'cartao',
          observacoes: 'Cancelado pelo cliente',
          motivoCancelamento: 'Imprevisto pessoal',
          criadoEm: '2024-01-10T16:45:00',
          atualizadoEm: '2024-01-13T08:30:00'
        },
        {
          id: 5,
          cliente: {
            id: 105,
            nome: 'Carlos Ferreira',
            email: 'carlos@email.com',
            telefone: '(11) 55555-5555'
          },
          salao: {
            id: 205,
            nome: 'Corte & Estilo',
            endereco: 'Av. Central, 654'
          },
          servico: {
            id: 305,
            nome: 'Corte Social',
            preco: 30.00,
            duracao: 40
          },
          data: '2024-01-16',
          hora: '11:30',
          status: 'confirmado',
          valor: 30.00,
          comissao: 3.00,
          formaPagamento: 'cartao',
          observacoes: 'Cliente VIP',
          criadoEm: '2024-01-15T13:10:00',
          atualizadoEm: '2024-01-15T13:10:00'
        }
      ]
      
      setAgendamentos(mockAgendamentos)
      setPaginacao(prev => ({ ...prev, total: mockAgendamentos.length }))
      
    } catch (error) {
      console.error('Erro ao carregar agendamentos:', error)
      toast.error('Erro ao carregar agendamentos')
    } finally {
      setLoading(false)
    }
  }

  const handleFiltroChange = (campo, valor) => {
    setFiltros(prev => ({
      ...prev,
      [campo]: valor
    }))
    setPaginacao(prev => ({ ...prev, pagina: 1 }))
  }

  const getStatusBadge = (status) => {
    const badges = {
      pendente: 'bg-warning',
      confirmado: 'bg-primary',
      concluido: 'bg-success',
      cancelado: 'bg-danger',
      reagendado: 'bg-info'
    }
    
    const labels = {
      pendente: 'Pendente',
      confirmado: 'Confirmado',
      concluido: 'Concluído',
      cancelado: 'Cancelado',
      reagendado: 'Reagendado'
    }
    
    return (
      <span className={`badge ${badges[status] || 'bg-secondary'}`}>
        {labels[status] || status}
      </span>
    )
  }

  const handleVerDetalhes = (agendamento) => {
    setModalDetalhes({
      show: true,
      agendamento
    })
  }

  const handleAcao = (agendamento, acao) => {
    setModalAcao({
      show: true,
      agendamento,
      acao,
      motivo: ''
    })
  }

  const executarAcao = async () => {
    try {
      const { agendamento, acao, motivo } = modalAcao
      
      // Aqui você faria a chamada real para a API
      console.log(`Executando ação ${acao} no agendamento ${agendamento.id}`, { motivo })
      
      // Atualizar o status localmente
      setAgendamentos(prev => prev.map(item => 
        item.id === agendamento.id 
          ? { ...item, status: acao, motivoCancelamento: motivo }
          : item
      ))
      
      toast.success(`Agendamento ${acao} com sucesso!`)
      setModalAcao({ show: false, agendamento: null, acao: '', motivo: '' })
      
    } catch (error) {
      console.error('Erro ao executar ação:', error)
      toast.error('Erro ao executar ação')
    }
  }

  const exportarAgendamentos = () => {
    // Aqui você implementaria a exportação real
    toast.success('Agendamentos exportados com sucesso!')
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
                  <i className="fas fa-calendar-check me-2"></i>
                  Gerenciar Agendamentos
                </h5>
                <button 
                  className="btn btn-light btn-sm"
                  onClick={exportarAgendamentos}
                >
                  <i className="fas fa-download me-1"></i>
                  Exportar
                </button>
              </div>
            </div>
            <div className="card-body">
              {/* Filtros */}
              <div className="row">
                <div className="col-md-2">
                  <label className="form-label">Status</label>
                  <select
                    className="form-select form-select-sm"
                    value={filtros.status}
                    onChange={(e) => handleFiltroChange('status', e.target.value)}
                  >
                    <option value="">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                  </select>
                </div>
                <div className="col-md-2">
                  <label className="form-label">Data Início</label>
                  <input
                    type="date"
                    className="form-control form-control-sm"
                    value={filtros.dataInicio}
                    onChange={(e) => handleFiltroChange('dataInicio', e.target.value)}
                  />
                </div>
                <div className="col-md-2">
                  <label className="form-label">Data Fim</label>
                  <input
                    type="date"
                    className="form-control form-control-sm"
                    value={filtros.dataFim}
                    onChange={(e) => handleFiltroChange('dataFim', e.target.value)}
                  />
                </div>
                <div className="col-md-3">
                  <label className="form-label">Cliente</label>
                  <input
                    type="text"
                    className="form-control form-control-sm"
                    placeholder="Nome do cliente..."
                    value={filtros.cliente}
                    onChange={(e) => handleFiltroChange('cliente', e.target.value)}
                  />
                </div>
                <div className="col-md-3">
                  <label className="form-label">Salão</label>
                  <input
                    type="text"
                    className="form-control form-control-sm"
                    placeholder="Nome do salão..."
                    value={filtros.salao}
                    onChange={(e) => handleFiltroChange('salao', e.target.value)}
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Estatísticas Rápidas */}
      <div className="row mb-4">
        <div className="col-md-3">
          <div className="card border-primary">
            <div className="card-body text-center">
              <i className="fas fa-clock fa-2x text-primary mb-2"></i>
              <h5 className="text-primary">{agendamentos.filter(a => a.status === 'pendente').length}</h5>
              <small className="text-muted">Pendentes</small>
            </div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="card border-info">
            <div className="card-body text-center">
              <i className="fas fa-check-circle fa-2x text-info mb-2"></i>
              <h5 className="text-info">{agendamentos.filter(a => a.status === 'confirmado').length}</h5>
              <small className="text-muted">Confirmados</small>
            </div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="card border-success">
            <div className="card-body text-center">
              <i className="fas fa-thumbs-up fa-2x text-success mb-2"></i>
              <h5 className="text-success">{agendamentos.filter(a => a.status === 'concluido').length}</h5>
              <small className="text-muted">Concluídos</small>
            </div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="card border-danger">
            <div className="card-body text-center">
              <i className="fas fa-times-circle fa-2x text-danger mb-2"></i>
              <h5 className="text-danger">{agendamentos.filter(a => a.status === 'cancelado').length}</h5>
              <small className="text-muted">Cancelados</small>
            </div>
          </div>
        </div>
      </div>

      {/* Lista de Agendamentos */}
      <div className="row">
        <div className="col-12">
          <div className="card">
            <div className="card-header">
              <h6 className="mb-0">Lista de Agendamentos ({agendamentos.length})</h6>
            </div>
            <div className="card-body p-0">
              <div className="table-responsive">
                <table className="table table-hover mb-0">
                  <thead className="table-light">
                    <tr>
                      <th>ID</th>
                      <th>Cliente</th>
                      <th>Salão</th>
                      <th>Serviço</th>
                      <th>Data/Hora</th>
                      <th>Status</th>
                      <th>Valor</th>
                      <th>Comissão</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {agendamentos.map((agendamento) => (
                      <tr key={agendamento.id}>
                        <td>
                          <span className="badge bg-light text-dark">#{agendamento.id}</span>
                        </td>
                        <td>
                          <div>
                            <strong>{agendamento.cliente.nome}</strong>
                            <br />
                            <small className="text-muted">{agendamento.cliente.telefone}</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <strong>{agendamento.salao.nome}</strong>
                            <br />
                            <small className="text-muted">{agendamento.salao.endereco}</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <strong>{agendamento.servico.nome}</strong>
                            <br />
                            <small className="text-muted">{agendamento.servico.duracao}min</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <strong>{formatDate(agendamento.data)}</strong>
                            <br />
                            <small className="text-muted">{agendamento.hora}</small>
                          </div>
                        </td>
                        <td>{getStatusBadge(agendamento.status)}</td>
                        <td>
                          <strong>{formatCurrency(agendamento.valor)}</strong>
                        </td>
                        <td>
                          <span className="text-success">{formatCurrency(agendamento.comissao)}</span>
                        </td>
                        <td>
                          <div className="btn-group btn-group-sm">
                            <button
                              className="btn btn-outline-primary"
                              onClick={() => handleVerDetalhes(agendamento)}
                              title="Ver detalhes"
                            >
                              <i className="fas fa-eye"></i>
                            </button>
                            {agendamento.status === 'pendente' && (
                              <>
                                <button
                                  className="btn btn-outline-success"
                                  onClick={() => handleAcao(agendamento, 'confirmado')}
                                  title="Confirmar"
                                >
                                  <i className="fas fa-check"></i>
                                </button>
                                <button
                                  className="btn btn-outline-danger"
                                  onClick={() => handleAcao(agendamento, 'cancelado')}
                                  title="Cancelar"
                                >
                                  <i className="fas fa-times"></i>
                                </button>
                              </>
                            )}
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
      </div>

      {/* Modal Detalhes */}
      {modalDetalhes.show && (
        <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-lg">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  <i className="fas fa-info-circle me-2"></i>
                  Detalhes do Agendamento #{modalDetalhes.agendamento?.id}
                </h5>
                <button 
                  type="button" 
                  className="btn-close"
                  onClick={() => setModalDetalhes({ show: false, agendamento: null })}
                ></button>
              </div>
              <div className="modal-body">
                {modalDetalhes.agendamento && (
                  <div className="row">
                    <div className="col-md-6">
                      <h6>Informações do Cliente</h6>
                      <p><strong>Nome:</strong> {modalDetalhes.agendamento.cliente.nome}</p>
                      <p><strong>Email:</strong> {modalDetalhes.agendamento.cliente.email}</p>
                      <p><strong>Telefone:</strong> {modalDetalhes.agendamento.cliente.telefone}</p>
                      
                      <h6 className="mt-3">Informações do Salão</h6>
                      <p><strong>Nome:</strong> {modalDetalhes.agendamento.salao.nome}</p>
                      <p><strong>Endereço:</strong> {modalDetalhes.agendamento.salao.endereco}</p>
                    </div>
                    <div className="col-md-6">
                      <h6>Informações do Serviço</h6>
                      <p><strong>Serviço:</strong> {modalDetalhes.agendamento.servico.nome}</p>
                      <p><strong>Duração:</strong> {modalDetalhes.agendamento.servico.duracao} minutos</p>
                      <p><strong>Preço:</strong> {formatCurrency(modalDetalhes.agendamento.servico.preco)}</p>
                      
                      <h6 className="mt-3">Informações do Agendamento</h6>
                      <p><strong>Data:</strong> {formatDate(modalDetalhes.agendamento.data)}</p>
                      <p><strong>Hora:</strong> {modalDetalhes.agendamento.hora}</p>
                      <p><strong>Status:</strong> {getStatusBadge(modalDetalhes.agendamento.status)}</p>
                      <p><strong>Forma de Pagamento:</strong> {modalDetalhes.agendamento.formaPagamento}</p>
                      <p><strong>Valor Total:</strong> {formatCurrency(modalDetalhes.agendamento.valor)}</p>
                      <p><strong>Comissão:</strong> {formatCurrency(modalDetalhes.agendamento.comissao)}</p>
                    </div>
                    {modalDetalhes.agendamento.observacoes && (
                      <div className="col-12 mt-3">
                        <h6>Observações</h6>
                        <p>{modalDetalhes.agendamento.observacoes}</p>
                      </div>
                    )}
                    {modalDetalhes.agendamento.motivoCancelamento && (
                      <div className="col-12 mt-3">
                        <h6>Motivo do Cancelamento</h6>
                        <p className="text-danger">{modalDetalhes.agendamento.motivoCancelamento}</p>
                      </div>
                    )}
                  </div>
                )}
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary"
                  onClick={() => setModalDetalhes({ show: false, agendamento: null })}
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modal Ação */}
      {modalAcao.show && (
        <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  <i className="fas fa-exclamation-triangle me-2"></i>
                  {modalAcao.acao === 'confirmado' ? 'Confirmar' : 'Cancelar'} Agendamento
                </h5>
                <button 
                  type="button" 
                  className="btn-close"
                  onClick={() => setModalAcao({ show: false, agendamento: null, acao: '', motivo: '' })}
                ></button>
              </div>
              <div className="modal-body">
                <p>
                  Tem certeza que deseja {modalAcao.acao === 'confirmado' ? 'confirmar' : 'cancelar'} o agendamento 
                  <strong>#{modalAcao.agendamento?.id}</strong> de <strong>{modalAcao.agendamento?.cliente.nome}</strong>?
                </p>
                {modalAcao.acao === 'cancelado' && (
                  <div className="mb-3">
                    <label className="form-label">Motivo do cancelamento:</label>
                    <textarea
                      className="form-control"
                      rows="3"
                      value={modalAcao.motivo}
                      onChange={(e) => setModalAcao(prev => ({ ...prev, motivo: e.target.value }))}
                      placeholder="Descreva o motivo do cancelamento..."
                    ></textarea>
                  </div>
                )}
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary"
                  onClick={() => setModalAcao({ show: false, agendamento: null, acao: '', motivo: '' })}
                >
                  Cancelar
                </button>
                <button 
                  type="button" 
                  className={`btn ${modalAcao.acao === 'confirmado' ? 'btn-success' : 'btn-danger'}`}
                  onClick={executarAcao}
                >
                  {modalAcao.acao === 'confirmado' ? 'Confirmar' : 'Cancelar'} Agendamento
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default AdminAgendamentos