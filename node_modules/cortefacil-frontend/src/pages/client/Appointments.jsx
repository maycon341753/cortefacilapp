import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Appointments = () => {
  const { user } = useAuth()
  const { showLoading, hideLoading, formatDate, formatCurrency, formatPhone } = useApp()
  const [appointments, setAppointments] = useState([])
  const [filter, setFilter] = useState('todos')
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedAppointment, setSelectedAppointment] = useState(null)
  const [showModal, setShowModal] = useState(false)

  // Mock data - substituir por chamada à API
  useEffect(() => {
    const mockAppointments = [
      {
        id: 1,
        salao: 'Salão Beleza Total',
        endereco: 'Rua das Flores, 123 - Centro',
        telefone: '11999999999',
        servico: 'Corte + Barba',
        profissional: 'João Silva',
        data: '2024-01-25',
        horario: '14:00',
        valor: 45.00,
        status: 'agendado',
        observacoes: 'Corte social, barba bem aparada'
      },
      {
        id: 2,
        salao: 'Barbearia Moderna',
        endereco: 'Av. Principal, 456 - Vila Nova',
        telefone: '11888888888',
        servico: 'Corte Degradê',
        profissional: 'Pedro Santos',
        data: '2024-01-20',
        horario: '16:30',
        valor: 35.00,
        status: 'concluido',
        observacoes: 'Degradê baixo nas laterais'
      },
      {
        id: 3,
        salao: 'Studio Hair',
        endereco: 'Rua da Moda, 789 - Jardim',
        telefone: '11777777777',
        servico: 'Corte + Sobrancelha',
        profissional: 'Carlos Lima',
        data: '2024-01-15',
        horario: '10:00',
        valor: 40.00,
        status: 'cancelado',
        observacoes: 'Cliente cancelou por motivos pessoais'
      },
      {
        id: 4,
        salao: 'Salão Beleza Total',
        endereco: 'Rua das Flores, 123 - Centro',
        telefone: '11999999999',
        servico: 'Corte Simples',
        profissional: 'Maria Oliveira',
        data: '2024-02-01',
        horario: '15:00',
        valor: 30.00,
        status: 'agendado',
        observacoes: ''
      }
    ]
    setAppointments(mockAppointments)
  }, [])

  const getStatusBadge = (status) => {
    const statusConfig = {
      agendado: { class: 'bg-primary', icon: 'calendar-check', text: 'Agendado' },
      concluido: { class: 'bg-success', icon: 'check-circle', text: 'Concluído' },
      cancelado: { class: 'bg-danger', icon: 'times-circle', text: 'Cancelado' },
      em_andamento: { class: 'bg-warning', icon: 'clock', text: 'Em Andamento' }
    }
    
    const config = statusConfig[status] || statusConfig.agendado
    return (
      <span className={`badge ${config.class} d-inline-flex align-items-center`}>
        <i className={`fas fa-${config.icon} me-1`}></i>
        {config.text}
      </span>
    )
  }

  const filteredAppointments = appointments.filter(appointment => {
    const matchesFilter = filter === 'todos' || appointment.status === filter
    const matchesSearch = searchTerm === '' || 
      appointment.salao.toLowerCase().includes(searchTerm.toLowerCase()) ||
      appointment.servico.toLowerCase().includes(searchTerm.toLowerCase()) ||
      appointment.profissional.toLowerCase().includes(searchTerm.toLowerCase())
    
    return matchesFilter && matchesSearch
  })

  const handleCancelAppointment = async (appointmentId) => {
    try {
      showLoading()
      // Aqui seria a chamada para a API
      // await appointmentService.cancel(appointmentId)
      
      setAppointments(prev => 
        prev.map(app => 
          app.id === appointmentId 
            ? { ...app, status: 'cancelado' }
            : app
        )
      )
      
      toast.success('Agendamento cancelado com sucesso!')
      setShowModal(false)
      setSelectedAppointment(null)
    } catch (error) {
      toast.error('Erro ao cancelar agendamento')
    } finally {
      hideLoading()
    }
  }

  const canCancelAppointment = (appointment) => {
    const appointmentDate = new Date(`${appointment.data}T${appointment.horario}`)
    const now = new Date()
    const hoursDiff = (appointmentDate - now) / (1000 * 60 * 60)
    
    return appointment.status === 'agendado' && hoursDiff > 2
  }

  return (
    <div className="container-fluid">
      <div className="row">
        <div className="col-12">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h1 className="h3 mb-0">Meus Agendamentos</h1>
              <p className="text-muted mb-0">Gerencie seus agendamentos</p>
            </div>
            <a href="/cliente/agendar" className="btn btn-primary">
              <i className="fas fa-plus me-2"></i>
              Novo Agendamento
            </a>
          </div>

          {/* Filtros */}
          <div className="card mb-4">
            <div className="card-body">
              <div className="row">
                <div className="col-md-6 mb-3 mb-md-0">
                  <div className="input-group">
                    <span className="input-group-text">
                      <i className="fas fa-search"></i>
                    </span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Buscar por salão, serviço ou profissional..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                    />
                  </div>
                </div>
                <div className="col-md-6">
                  <select
                    className="form-select"
                    value={filter}
                    onChange={(e) => setFilter(e.target.value)}
                  >
                    <option value="todos">Todos os Status</option>
                    <option value="agendado">Agendados</option>
                    <option value="concluido">Concluídos</option>
                    <option value="cancelado">Cancelados</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* Lista de Agendamentos */}
          {filteredAppointments.length === 0 ? (
            <div className="card">
              <div className="card-body text-center py-5">
                <i className="fas fa-calendar-times text-muted mb-3" style={{ fontSize: '3rem' }}></i>
                <h5 className="text-muted">Nenhum agendamento encontrado</h5>
                <p className="text-muted mb-4">
                  {searchTerm || filter !== 'todos' 
                    ? 'Tente ajustar os filtros de busca'
                    : 'Você ainda não possui agendamentos'
                  }
                </p>
                <a href="/cliente/agendar" className="btn btn-primary">
                  <i className="fas fa-plus me-2"></i>
                  Fazer Primeiro Agendamento
                </a>
              </div>
            </div>
          ) : (
            <div className="row">
              {filteredAppointments.map(appointment => (
                <div key={appointment.id} className="col-md-6 col-lg-4 mb-4">
                  <div className="card h-100 shadow-sm">
                    <div className="card-header d-flex justify-content-between align-items-center">
                      <h6 className="mb-0 fw-bold">{appointment.salao}</h6>
                      {getStatusBadge(appointment.status)}
                    </div>
                    <div className="card-body">
                      <div className="mb-3">
                        <div className="d-flex align-items-center mb-2">
                          <i className="fas fa-cut text-primary me-2"></i>
                          <span className="fw-semibold">{appointment.servico}</span>
                        </div>
                        <div className="d-flex align-items-center mb-2">
                          <i className="fas fa-user text-primary me-2"></i>
                          <span>{appointment.profissional}</span>
                        </div>
                        <div className="d-flex align-items-center mb-2">
                          <i className="fas fa-calendar text-primary me-2"></i>
                          <span>{formatDate(appointment.data)} às {appointment.horario}</span>
                        </div>
                        <div className="d-flex align-items-center mb-2">
                          <i className="fas fa-dollar-sign text-primary me-2"></i>
                          <span className="fw-bold text-success">{formatCurrency(appointment.valor)}</span>
                        </div>
                      </div>
                      
                      <div className="mb-3">
                        <small className="text-muted d-block">
                          <i className="fas fa-map-marker-alt me-1"></i>
                          {appointment.endereco}
                        </small>
                        <small className="text-muted d-block">
                          <i className="fas fa-phone me-1"></i>
                          {formatPhone(appointment.telefone)}
                        </small>
                      </div>
                      
                      {appointment.observacoes && (
                        <div className="mb-3">
                          <small className="text-muted">
                            <strong>Observações:</strong> {appointment.observacoes}
                          </small>
                        </div>
                      )}
                    </div>
                    <div className="card-footer bg-transparent">
                      <div className="d-flex gap-2">
                        <button 
                          className="btn btn-outline-primary btn-sm flex-fill"
                          onClick={() => {
                            setSelectedAppointment(appointment)
                            setShowModal(true)
                          }}
                        >
                          <i className="fas fa-eye me-1"></i>
                          Detalhes
                        </button>
                        
                        {canCancelAppointment(appointment) && (
                          <button 
                            className="btn btn-outline-danger btn-sm"
                            onClick={() => handleCancelAppointment(appointment.id)}
                          >
                            <i className="fas fa-times me-1"></i>
                            Cancelar
                          </button>
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

      {/* Modal de Detalhes */}
      {showModal && selectedAppointment && (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-lg">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  <i className="fas fa-calendar-check me-2"></i>
                  Detalhes do Agendamento
                </h5>
                <button 
                  type="button" 
                  className="btn-close"
                  onClick={() => {
                    setShowModal(false)
                    setSelectedAppointment(null)
                  }}
                ></button>
              </div>
              <div className="modal-body">
                <div className="row">
                  <div className="col-md-6">
                    <h6 className="fw-bold mb-3">Informações do Serviço</h6>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Salão:</label>
                      <p className="mb-1">{selectedAppointment.salao}</p>
                      <small className="text-muted">{selectedAppointment.endereco}</small>
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Serviço:</label>
                      <p className="mb-0">{selectedAppointment.servico}</p>
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Profissional:</label>
                      <p className="mb-0">{selectedAppointment.profissional}</p>
                    </div>
                  </div>
                  <div className="col-md-6">
                    <h6 className="fw-bold mb-3">Informações do Agendamento</h6>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Data e Horário:</label>
                      <p className="mb-0">{formatDate(selectedAppointment.data)} às {selectedAppointment.horario}</p>
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Valor:</label>
                      <p className="mb-0 text-success fw-bold">{formatCurrency(selectedAppointment.valor)}</p>
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Status:</label>
                      <div>{getStatusBadge(selectedAppointment.status)}</div>
                    </div>
                  </div>
                </div>
                
                {selectedAppointment.observacoes && (
                  <div className="mt-3">
                    <label className="form-label fw-semibold">Observações:</label>
                    <p className="mb-0">{selectedAppointment.observacoes}</p>
                  </div>
                )}
                
                <div className="mt-4 pt-3 border-top">
                  <h6 className="fw-bold mb-2">Contato do Salão</h6>
                  <p className="mb-1">
                    <i className="fas fa-phone text-primary me-2"></i>
                    <a href={`tel:${selectedAppointment.telefone}`} className="text-decoration-none">
                      {formatPhone(selectedAppointment.telefone)}
                    </a>
                  </p>
                  <p className="mb-0">
                    <i className="fab fa-whatsapp text-success me-2"></i>
                    <a 
                      href={`https://wa.me/55${selectedAppointment.telefone.replace(/\D/g, '')}`} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      className="text-decoration-none"
                    >
                      WhatsApp
                    </a>
                  </p>
                </div>
              </div>
              <div className="modal-footer">
                {canCancelAppointment(selectedAppointment) && (
                  <button 
                    type="button" 
                    className="btn btn-danger"
                    onClick={() => handleCancelAppointment(selectedAppointment.id)}
                  >
                    <i className="fas fa-times me-2"></i>
                    Cancelar Agendamento
                  </button>
                )}
                <button 
                  type="button" 
                  className="btn btn-secondary"
                  onClick={() => {
                    setShowModal(false)
                    setSelectedAppointment(null)
                  }}
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Appointments