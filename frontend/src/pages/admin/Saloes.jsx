import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const AdminSaloes = () => {
  const { user } = useAuth()
  const { formatCurrency, formatDate } = useApp()
  const [saloes, setSaloes] = useState([])
  const [loading, setLoading] = useState(true)
  const [filtros, setFiltros] = useState({
    status: 'todos',
    cidade: '',
    busca: ''
  })
  const [showModal, setShowModal] = useState(false)
  const [salaoSelecionado, setSalaoSelecionado] = useState(null)
  const [acao, setAcao] = useState('') // 'aprovar', 'rejeitar', 'suspender'

  useEffect(() => {
    loadSaloes()
  }, [])

  const loadSaloes = async () => {
    try {
      setLoading(true)
      // Mock data para demonstração
      const mockSaloes = [
        {
          id: 1,
          nome: 'Salão Beleza Total',
          proprietario: 'João Silva',
          email: 'joao@belezatotal.com',
          telefone: '(11) 99999-9999',
          endereco: 'Rua das Flores, 123 - Centro',
          cidade: 'São Paulo',
          status: 'ativo',
          dataRegistro: '2024-01-10',
          totalAgendamentos: 156,
          receitaTotal: 7850.00,
          avaliacao: 4.8,
          documentos: {
            cnpj: '12.345.678/0001-90',
            alvara: 'Enviado',
            contrato: 'Assinado'
          }
        },
        {
          id: 2,
          nome: 'Studio Hair Premium',
          proprietario: 'Maria Santos',
          email: 'maria@studiohair.com',
          telefone: '(11) 88888-8888',
          endereco: 'Av. Paulista, 456 - Bela Vista',
          cidade: 'São Paulo',
          status: 'pendente',
          dataRegistro: '2024-01-12',
          totalAgendamentos: 0,
          receitaTotal: 0,
          avaliacao: 0,
          documentos: {
            cnpj: '98.765.432/0001-10',
            alvara: 'Pendente',
            contrato: 'Pendente'
          }
        },
        {
          id: 3,
          nome: 'Barbearia Moderna',
          proprietario: 'Carlos Oliveira',
          email: 'carlos@barberiamoderna.com',
          telefone: '(11) 77777-7777',
          endereco: 'Rua Augusta, 789 - Consolação',
          cidade: 'São Paulo',
          status: 'suspenso',
          dataRegistro: '2024-01-05',
          totalAgendamentos: 89,
          receitaTotal: 4450.00,
          avaliacao: 4.2,
          documentos: {
            cnpj: '11.222.333/0001-44',
            alvara: 'Vencido',
            contrato: 'Assinado'
          }
        }
      ]
      
      setSaloes(mockSaloes)
    } catch (error) {
      console.error('Erro ao carregar salões:', error)
      toast.error('Erro ao carregar salões')
    } finally {
      setLoading(false)
    }
  }

  const handleFiltroChange = (campo, valor) => {
    setFiltros(prev => ({
      ...prev,
      [campo]: valor
    }))
  }

  const saloesFiltrados = saloes.filter(salao => {
    const matchStatus = filtros.status === 'todos' || salao.status === filtros.status
    const matchCidade = !filtros.cidade || salao.cidade.toLowerCase().includes(filtros.cidade.toLowerCase())
    const matchBusca = !filtros.busca || 
      salao.nome.toLowerCase().includes(filtros.busca.toLowerCase()) ||
      salao.proprietario.toLowerCase().includes(filtros.busca.toLowerCase())
    
    return matchStatus && matchCidade && matchBusca
  })

  const handleAcaoSalao = (salao, acao) => {
    setSalaoSelecionado(salao)
    setAcao(acao)
    setShowModal(true)
  }

  const confirmarAcao = async () => {
    try {
      // Aqui você faria a chamada para a API
      console.log(`${acao} salão:`, salaoSelecionado.id)
      
      // Atualizar status do salão localmente
      setSaloes(prev => prev.map(salao => {
        if (salao.id === salaoSelecionado.id) {
          let novoStatus = salao.status
          if (acao === 'aprovar') novoStatus = 'ativo'
          else if (acao === 'rejeitar') novoStatus = 'rejeitado'
          else if (acao === 'suspender') novoStatus = 'suspenso'
          
          return { ...salao, status: novoStatus }
        }
        return salao
      }))
      
      toast.success(`Salão ${acao === 'aprovar' ? 'aprovado' : acao === 'rejeitar' ? 'rejeitado' : 'suspenso'} com sucesso!`)
      setShowModal(false)
    } catch (error) {
      console.error('Erro ao executar ação:', error)
      toast.error('Erro ao executar ação')
    }
  }

  const getStatusBadge = (status) => {
    const badges = {
      ativo: 'bg-success',
      pendente: 'bg-warning',
      suspenso: 'bg-danger',
      rejeitado: 'bg-secondary'
    }
    return badges[status] || 'bg-secondary'
  }

  const getStatusText = (status) => {
    const texts = {
      ativo: 'Ativo',
      pendente: 'Pendente',
      suspenso: 'Suspenso',
      rejeitado: 'Rejeitado'
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
      <div className="row">
        <div className="col-12">
          <div className="card shadow-sm">
            <div className="card-header bg-primary text-white">
              <div className="d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-store me-2"></i>
                  Gerenciar Salões
                </h5>
                <span className="badge bg-light text-dark">
                  {saloesFiltrados.length} salões
                </span>
              </div>
            </div>
            
            <div className="card-body">
              {/* Filtros */}
              <div className="row mb-4">
                <div className="col-md-3">
                  <label className="form-label">Status</label>
                  <select 
                    className="form-select"
                    value={filtros.status}
                    onChange={(e) => handleFiltroChange('status', e.target.value)}
                  >
                    <option value="todos">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="pendente">Pendente</option>
                    <option value="suspenso">Suspenso</option>
                    <option value="rejeitado">Rejeitado</option>
                  </select>
                </div>
                <div className="col-md-3">
                  <label className="form-label">Cidade</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Filtrar por cidade"
                    value={filtros.cidade}
                    onChange={(e) => handleFiltroChange('cidade', e.target.value)}
                  />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Buscar</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Buscar por nome ou proprietário"
                    value={filtros.busca}
                    onChange={(e) => handleFiltroChange('busca', e.target.value)}
                  />
                </div>
              </div>

              {/* Lista de Salões */}
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead className="table-light">
                    <tr>
                      <th>Salão</th>
                      <th>Proprietário</th>
                      <th>Contato</th>
                      <th>Status</th>
                      <th>Estatísticas</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {saloesFiltrados.map(salao => (
                      <tr key={salao.id}>
                        <td>
                          <div>
                            <strong>{salao.nome}</strong>
                            <br />
                            <small className="text-muted">{salao.endereco}</small>
                            <br />
                            <small className="text-muted">{salao.cidade}</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <strong>{salao.proprietario}</strong>
                            <br />
                            <small className="text-muted">CNPJ: {salao.documentos.cnpj}</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <small>{salao.email}</small>
                            <br />
                            <small>{salao.telefone}</small>
                          </div>
                        </td>
                        <td>
                          <span className={`badge ${getStatusBadge(salao.status)}`}>
                            {getStatusText(salao.status)}
                          </span>
                          <br />
                          <small className="text-muted">
                            Desde {formatDate(salao.dataRegistro)}
                          </small>
                        </td>
                        <td>
                          <div>
                            <small><strong>{salao.totalAgendamentos}</strong> agendamentos</small>
                            <br />
                            <small><strong>{formatCurrency(salao.receitaTotal)}</strong> receita</small>
                            <br />
                            <small><strong>{salao.avaliacao}</strong> ⭐ avaliação</small>
                          </div>
                        </td>
                        <td>
                          <div className="btn-group-vertical btn-group-sm">
                            {salao.status === 'pendente' && (
                              <>
                                <button
                                  className="btn btn-success btn-sm mb-1"
                                  onClick={() => handleAcaoSalao(salao, 'aprovar')}
                                >
                                  <i className="fas fa-check"></i> Aprovar
                                </button>
                                <button
                                  className="btn btn-danger btn-sm mb-1"
                                  onClick={() => handleAcaoSalao(salao, 'rejeitar')}
                                >
                                  <i className="fas fa-times"></i> Rejeitar
                                </button>
                              </>
                            )}
                            {salao.status === 'ativo' && (
                              <button
                                className="btn btn-warning btn-sm mb-1"
                                onClick={() => handleAcaoSalao(salao, 'suspender')}
                              >
                                <i className="fas fa-pause"></i> Suspender
                              </button>
                            )}
                            {salao.status === 'suspenso' && (
                              <button
                                className="btn btn-success btn-sm mb-1"
                                onClick={() => handleAcaoSalao(salao, 'aprovar')}
                              >
                                <i className="fas fa-play"></i> Reativar
                              </button>
                            )}
                            <button className="btn btn-info btn-sm">
                              <i className="fas fa-eye"></i> Detalhes
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {saloesFiltrados.length === 0 && (
                <div className="text-center py-4">
                  <i className="fas fa-search fa-3x text-muted mb-3"></i>
                  <p className="text-muted">Nenhum salão encontrado com os filtros aplicados.</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Modal de Confirmação */}
      {showModal && (
        <div className="modal fade show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  Confirmar {acao === 'aprovar' ? 'Aprovação' : acao === 'rejeitar' ? 'Rejeição' : 'Suspensão'}
                </h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => setShowModal(false)}
                ></button>
              </div>
              <div className="modal-body">
                <p>
                  Tem certeza que deseja {acao === 'aprovar' ? 'aprovar' : acao === 'rejeitar' ? 'rejeitar' : 'suspender'} o salão <strong>{salaoSelecionado?.nome}</strong>?
                </p>
                {acao === 'aprovar' && (
                  <div className="alert alert-info">
                    <i className="fas fa-info-circle me-2"></i>
                    O salão será ativado e poderá receber agendamentos.
                  </div>
                )}
                {acao === 'rejeitar' && (
                  <div className="alert alert-warning">
                    <i className="fas fa-exclamation-triangle me-2"></i>
                    O salão será rejeitado e não poderá operar na plataforma.
                  </div>
                )}
                {acao === 'suspender' && (
                  <div className="alert alert-danger">
                    <i className="fas fa-ban me-2"></i>
                    O salão será suspenso temporariamente.
                  </div>
                )}
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setShowModal(false)}
                >
                  Cancelar
                </button>
                <button 
                  type="button" 
                  className={`btn ${
                    acao === 'aprovar' ? 'btn-success' : 
                    acao === 'rejeitar' ? 'btn-danger' : 'btn-warning'
                  }`}
                  onClick={confirmarAcao}
                >
                  Confirmar {acao === 'aprovar' ? 'Aprovação' : acao === 'rejeitar' ? 'Rejeição' : 'Suspensão'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default AdminSaloes