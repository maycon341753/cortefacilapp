import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const AdminUsuarios = () => {
  const { user } = useAuth()
  const { formatDate } = useApp()
  const [usuarios, setUsuarios] = useState([])
  const [loading, setLoading] = useState(true)
  const [filtros, setFiltros] = useState({
    tipo: 'todos',
    status: 'todos',
    busca: ''
  })
  const [showModal, setShowModal] = useState(false)
  const [usuarioSelecionado, setUsuarioSelecionado] = useState(null)
  const [acao, setAcao] = useState('') // 'ativar', 'desativar', 'resetar_senha'

  useEffect(() => {
    loadUsuarios()
  }, [])

  const loadUsuarios = async () => {
    try {
      setLoading(true)
      // Mock data para demonstração
      const mockUsuarios = [
        {
          id: 1,
          nome: 'João Silva',
          email: 'joao@email.com',
          telefone: '(11) 99999-9999',
          tipo_usuario: 'cliente',
          status: 'ativo',
          data_cadastro: '2024-01-10',
          ultimo_acesso: '2024-01-14',
          total_agendamentos: 15,
          valor_gasto: 750.00
        },
        {
          id: 2,
          nome: 'Maria Santos',
          email: 'maria@salaobela.com',
          telefone: '(11) 88888-8888',
          tipo_usuario: 'parceiro',
          status: 'ativo',
          data_cadastro: '2024-01-05',
          ultimo_acesso: '2024-01-14',
          salao: 'Salão Beleza Total',
          total_agendamentos: 156,
          receita_gerada: 7850.00
        },
        {
          id: 3,
          nome: 'Ana Costa',
          email: 'ana@email.com',
          telefone: '(11) 77777-7777',
          tipo_usuario: 'cliente',
          status: 'inativo',
          data_cadastro: '2024-01-08',
          ultimo_acesso: '2024-01-12',
          total_agendamentos: 3,
          valor_gasto: 150.00
        },
        {
          id: 4,
          nome: 'Carlos Oliveira',
          email: 'carlos@barbeariamoderna.com',
          telefone: '(11) 66666-6666',
          tipo_usuario: 'parceiro',
          status: 'suspenso',
          data_cadastro: '2024-01-03',
          ultimo_acesso: '2024-01-10',
          salao: 'Barbearia Moderna',
          total_agendamentos: 89,
          receita_gerada: 4450.00
        },
        {
          id: 5,
          nome: 'Admin Sistema',
          email: 'admin@cortefacil.com',
          telefone: '(11) 55555-5555',
          tipo_usuario: 'admin',
          status: 'ativo',
          data_cadastro: '2024-01-01',
          ultimo_acesso: '2024-01-14'
        }
      ]
      
      setUsuarios(mockUsuarios)
    } catch (error) {
      console.error('Erro ao carregar usuários:', error)
      toast.error('Erro ao carregar usuários')
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

  const usuariosFiltrados = usuarios.filter(usuario => {
    const matchTipo = filtros.tipo === 'todos' || usuario.tipo_usuario === filtros.tipo
    const matchStatus = filtros.status === 'todos' || usuario.status === filtros.status
    const matchBusca = !filtros.busca || 
      usuario.nome.toLowerCase().includes(filtros.busca.toLowerCase()) ||
      usuario.email.toLowerCase().includes(filtros.busca.toLowerCase())
    
    return matchTipo && matchStatus && matchBusca
  })

  const handleAcaoUsuario = (usuario, acao) => {
    setUsuarioSelecionado(usuario)
    setAcao(acao)
    setShowModal(true)
  }

  const confirmarAcao = async () => {
    try {
      // Aqui você faria a chamada para a API
      console.log(`${acao} usuário:`, usuarioSelecionado.id)
      
      // Atualizar status do usuário localmente
      setUsuarios(prev => prev.map(usuario => {
        if (usuario.id === usuarioSelecionado.id) {
          if (acao === 'ativar') {
            return { ...usuario, status: 'ativo' }
          } else if (acao === 'desativar') {
            return { ...usuario, status: 'inativo' }
          } else if (acao === 'suspender') {
            return { ...usuario, status: 'suspenso' }
          }
        }
        return usuario
      }))
      
      let mensagem = ''
      if (acao === 'ativar') mensagem = 'Usuário ativado com sucesso!'
      else if (acao === 'desativar') mensagem = 'Usuário desativado com sucesso!'
      else if (acao === 'suspender') mensagem = 'Usuário suspenso com sucesso!'
      else if (acao === 'resetar_senha') mensagem = 'Nova senha enviada por email!'
      
      toast.success(mensagem)
      setShowModal(false)
    } catch (error) {
      console.error('Erro ao executar ação:', error)
      toast.error('Erro ao executar ação')
    }
  }

  const getStatusBadge = (status) => {
    const badges = {
      ativo: 'bg-success',
      inativo: 'bg-secondary',
      suspenso: 'bg-danger'
    }
    return badges[status] || 'bg-secondary'
  }

  const getStatusText = (status) => {
    const texts = {
      ativo: 'Ativo',
      inativo: 'Inativo',
      suspenso: 'Suspenso'
    }
    return texts[status] || status
  }

  const getTipoText = (tipo) => {
    const texts = {
      cliente: 'Cliente',
      parceiro: 'Parceiro',
      admin: 'Administrador'
    }
    return texts[tipo] || tipo
  }

  const getTipoBadge = (tipo) => {
    const badges = {
      cliente: 'bg-primary',
      parceiro: 'bg-info',
      admin: 'bg-warning'
    }
    return badges[tipo] || 'bg-secondary'
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
                  <i className="fas fa-users me-2"></i>
                  Gerenciar Usuários
                </h5>
                <span className="badge bg-light text-dark">
                  {usuariosFiltrados.length} usuários
                </span>
              </div>
            </div>
            
            <div className="card-body">
              {/* Filtros */}
              <div className="row mb-4">
                <div className="col-md-3">
                  <label className="form-label">Tipo de Usuário</label>
                  <select 
                    className="form-select"
                    value={filtros.tipo}
                    onChange={(e) => handleFiltroChange('tipo', e.target.value)}
                  >
                    <option value="todos">Todos os Tipos</option>
                    <option value="cliente">Cliente</option>
                    <option value="parceiro">Parceiro</option>
                    <option value="admin">Administrador</option>
                  </select>
                </div>
                <div className="col-md-3">
                  <label className="form-label">Status</label>
                  <select 
                    className="form-select"
                    value={filtros.status}
                    onChange={(e) => handleFiltroChange('status', e.target.value)}
                  >
                    <option value="todos">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="suspenso">Suspenso</option>
                  </select>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Buscar</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Buscar por nome ou email"
                    value={filtros.busca}
                    onChange={(e) => handleFiltroChange('busca', e.target.value)}
                  />
                </div>
              </div>

              {/* Lista de Usuários */}
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead className="table-light">
                    <tr>
                      <th>Usuário</th>
                      <th>Tipo</th>
                      <th>Status</th>
                      <th>Cadastro</th>
                      <th>Último Acesso</th>
                      <th>Estatísticas</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {usuariosFiltrados.map(usuario => (
                      <tr key={usuario.id}>
                        <td>
                          <div>
                            <strong>{usuario.nome}</strong>
                            <br />
                            <small className="text-muted">{usuario.email}</small>
                            <br />
                            <small className="text-muted">{usuario.telefone}</small>
                            {usuario.salao && (
                              <>
                                <br />
                                <small className="text-info">Salão: {usuario.salao}</small>
                              </>
                            )}
                          </div>
                        </td>
                        <td>
                          <span className={`badge ${getTipoBadge(usuario.tipo_usuario)}`}>
                            {getTipoText(usuario.tipo_usuario)}
                          </span>
                        </td>
                        <td>
                          <span className={`badge ${getStatusBadge(usuario.status)}`}>
                            {getStatusText(usuario.status)}
                          </span>
                        </td>
                        <td>
                          <small>{formatDate(usuario.data_cadastro)}</small>
                        </td>
                        <td>
                          <small>{formatDate(usuario.ultimo_acesso)}</small>
                        </td>
                        <td>
                          <div>
                            {usuario.tipo_usuario === 'cliente' && (
                              <>
                                <small><strong>{usuario.total_agendamentos}</strong> agendamentos</small>
                                <br />
                                <small><strong>R$ {usuario.valor_gasto?.toFixed(2)}</strong> gasto</small>
                              </>
                            )}
                            {usuario.tipo_usuario === 'parceiro' && (
                              <>
                                <small><strong>{usuario.total_agendamentos}</strong> agendamentos</small>
                                <br />
                                <small><strong>R$ {usuario.receita_gerada?.toFixed(2)}</strong> receita</small>
                              </>
                            )}
                            {usuario.tipo_usuario === 'admin' && (
                              <small className="text-muted">Administrador</small>
                            )}
                          </div>
                        </td>
                        <td>
                          <div className="btn-group-vertical btn-group-sm">
                            {usuario.status === 'ativo' && usuario.tipo_usuario !== 'admin' && (
                              <>
                                <button
                                  className="btn btn-warning btn-sm mb-1"
                                  onClick={() => handleAcaoUsuario(usuario, 'suspender')}
                                >
                                  <i className="fas fa-pause"></i> Suspender
                                </button>
                                <button
                                  className="btn btn-secondary btn-sm mb-1"
                                  onClick={() => handleAcaoUsuario(usuario, 'desativar')}
                                >
                                  <i className="fas fa-ban"></i> Desativar
                                </button>
                              </>
                            )}
                            {(usuario.status === 'inativo' || usuario.status === 'suspenso') && (
                              <button
                                className="btn btn-success btn-sm mb-1"
                                onClick={() => handleAcaoUsuario(usuario, 'ativar')}
                              >
                                <i className="fas fa-check"></i> Ativar
                              </button>
                            )}
                            <button
                              className="btn btn-info btn-sm mb-1"
                              onClick={() => handleAcaoUsuario(usuario, 'resetar_senha')}
                            >
                              <i className="fas fa-key"></i> Reset Senha
                            </button>
                            <button className="btn btn-outline-primary btn-sm">
                              <i className="fas fa-eye"></i> Detalhes
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {usuariosFiltrados.length === 0 && (
                <div className="text-center py-4">
                  <i className="fas fa-search fa-3x text-muted mb-3"></i>
                  <p className="text-muted">Nenhum usuário encontrado com os filtros aplicados.</p>
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
                  Confirmar {acao === 'ativar' ? 'Ativação' : 
                           acao === 'desativar' ? 'Desativação' : 
                           acao === 'suspender' ? 'Suspensão' : 'Reset de Senha'}
                </h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => setShowModal(false)}
                ></button>
              </div>
              <div className="modal-body">
                <p>
                  Tem certeza que deseja {acao === 'ativar' ? 'ativar' : 
                                        acao === 'desativar' ? 'desativar' : 
                                        acao === 'suspender' ? 'suspender' : 'resetar a senha de'} o usuário <strong>{usuarioSelecionado?.nome}</strong>?
                </p>
                {acao === 'ativar' && (
                  <div className="alert alert-success">
                    <i className="fas fa-check-circle me-2"></i>
                    O usuário será ativado e poderá acessar o sistema normalmente.
                  </div>
                )}
                {acao === 'desativar' && (
                  <div className="alert alert-warning">
                    <i className="fas fa-exclamation-triangle me-2"></i>
                    O usuário será desativado e não poderá acessar o sistema.
                  </div>
                )}
                {acao === 'suspender' && (
                  <div className="alert alert-danger">
                    <i className="fas fa-ban me-2"></i>
                    O usuário será suspenso temporariamente.
                  </div>
                )}
                {acao === 'resetar_senha' && (
                  <div className="alert alert-info">
                    <i className="fas fa-key me-2"></i>
                    Uma nova senha será gerada e enviada por email para o usuário.
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
                    acao === 'ativar' ? 'btn-success' : 
                    acao === 'desativar' ? 'btn-warning' : 
                    acao === 'suspender' ? 'btn-danger' : 'btn-info'
                  }`}
                  onClick={confirmarAcao}
                >
                  Confirmar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default AdminUsuarios