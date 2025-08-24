import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const BookAppointment = () => {
  const { user } = useAuth()
  const { setLoading, formatCurrency, formatPhone } = useApp()
  const [step, setStep] = useState(1)
  const [salons, setSalons] = useState([])
  const [selectedSalon, setSelectedSalon] = useState(null)
  const [services, setServices] = useState([])
  const [selectedService, setSelectedService] = useState(null)
  const [professionals, setProfessionals] = useState([])
  const [selectedProfessional, setSelectedProfessional] = useState(null)
  const [availableDates, setAvailableDates] = useState([])
  const [selectedDate, setSelectedDate] = useState('')
  const [availableTimes, setAvailableTimes] = useState([])
  const [selectedTime, setSelectedTime] = useState('')
  const [observations, setObservations] = useState('')
  const [searchTerm, setSearchTerm] = useState('')

  // Mock data - substituir por chamadas à API
  useEffect(() => {
    const mockSalons = [
      {
        id: 1,
        nome: 'Salão Beleza Total',
        endereco: 'Rua das Flores, 123 - Centro',
        telefone: '11999999999',
        avaliacao: 4.8,
        distancia: '0.5 km',
        foto: '/api/placeholder/300/200',
        horario_funcionamento: 'Seg-Sex: 8h-18h | Sáb: 8h-16h'
      },
      {
        id: 2,
        nome: 'Barbearia Moderna',
        endereco: 'Av. Principal, 456 - Vila Nova',
        telefone: '11888888888',
        avaliacao: 4.6,
        distancia: '1.2 km',
        foto: '/api/placeholder/300/200',
        horario_funcionamento: 'Seg-Sáb: 9h-19h'
      },
      {
        id: 3,
        nome: 'Studio Hair',
        endereco: 'Rua da Moda, 789 - Jardim',
        telefone: '11777777777',
        avaliacao: 4.9,
        distancia: '2.1 km',
        foto: '/api/placeholder/300/200',
        horario_funcionamento: 'Ter-Sáb: 9h-18h'
      }
    ]
    setSalons(mockSalons)
  }, [])

  useEffect(() => {
    if (selectedSalon) {
      const mockServices = [
        { id: 1, nome: 'Corte Simples', preco: 30.00, duracao: 30 },
        { id: 2, nome: 'Corte + Barba', preco: 45.00, duracao: 45 },
        { id: 3, nome: 'Corte Degradê', preco: 35.00, duracao: 40 },
        { id: 4, nome: 'Corte + Sobrancelha', preco: 40.00, duracao: 35 },
        { id: 5, nome: 'Barba Completa', preco: 25.00, duracao: 25 }
      ]
      setServices(mockServices)
    }
  }, [selectedSalon])

  useEffect(() => {
    if (selectedService) {
      const mockProfessionals = [
        { id: 1, nome: 'João Silva', especialidade: 'Cortes Masculinos', avaliacao: 4.9 },
        { id: 2, nome: 'Pedro Santos', especialidade: 'Barbeiro', avaliacao: 4.7 },
        { id: 3, nome: 'Carlos Lima', especialidade: 'Cortes e Barba', avaliacao: 4.8 }
      ]
      setProfessionals(mockProfessionals)
    }
  }, [selectedService])

  useEffect(() => {
    if (selectedProfessional) {
      // Gerar próximos 7 dias úteis
      const dates = []
      const today = new Date()
      let currentDate = new Date(today)
      
      while (dates.length < 7) {
        currentDate.setDate(currentDate.getDate() + 1)
        const dayOfWeek = currentDate.getDay()
        
        // Pular domingos (0) - ajustar conforme horário do salão
        if (dayOfWeek !== 0) {
          dates.push(new Date(currentDate))
        }
      }
      
      setAvailableDates(dates)
    }
  }, [selectedProfessional])

  useEffect(() => {
    if (selectedDate) {
      // Gerar horários disponíveis
      const times = [
        '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
        '11:00', '11:30', '14:00', '14:30', '15:00', '15:30',
        '16:00', '16:30', '17:00', '17:30'
      ]
      setAvailableTimes(times)
    }
  }, [selectedDate])

  const filteredSalons = salons.filter(salon =>
    salon.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
    salon.endereco.toLowerCase().includes(searchTerm.toLowerCase())
  )

  const handleSalonSelect = (salon) => {
    setSelectedSalon(salon)
    setStep(2)
  }

  const handleServiceSelect = (service) => {
    setSelectedService(service)
    setStep(3)
  }

  const handleProfessionalSelect = (professional) => {
    setSelectedProfessional(professional)
    setStep(4)
  }

  const handleDateSelect = (date) => {
    setSelectedDate(date.toISOString().split('T')[0])
    setStep(5)
  }

  const handleTimeSelect = (time) => {
    setSelectedTime(time)
    setStep(6)
  }

  const handleConfirmBooking = async () => {
    try {
      setLoading(true)
      
      const bookingData = {
        salao_id: selectedSalon.id,
        servico_id: selectedService.id,
        profissional_id: selectedProfessional.id,
        data: selectedDate,
        horario: selectedTime,
        observacoes: observations
      }
      
      // Aqui seria a chamada para a API
      // await appointmentService.create(bookingData)
      
      toast.success('Agendamento realizado com sucesso!')
      
      // Resetar formulário
      setStep(1)
      setSelectedSalon(null)
      setSelectedService(null)
      setSelectedProfessional(null)
      setSelectedDate('')
      setSelectedTime('')
      setObservations('')
      
    } catch (error) {
      toast.error('Erro ao realizar agendamento')
    } finally {
      setLoading(false)
    }
  }

  const formatDate = (date) => {
    return date.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  const renderStepIndicator = () => {
    const steps = [
      { number: 1, title: 'Salão', icon: 'store' },
      { number: 2, title: 'Serviço', icon: 'cut' },
      { number: 3, title: 'Profissional', icon: 'user' },
      { number: 4, title: 'Data', icon: 'calendar' },
      { number: 5, title: 'Horário', icon: 'clock' },
      { number: 6, title: 'Confirmação', icon: 'check' }
    ]

    return (
      <div className="row mb-4">
        <div className="col-12">
          <div className="d-flex justify-content-between align-items-center">
            {steps.map((stepItem, index) => (
              <div key={stepItem.number} className="d-flex flex-column align-items-center">
                <div className={`rounded-circle d-flex align-items-center justify-content-center mb-2 ${
                  step >= stepItem.number ? 'bg-primary text-white' : 'bg-light text-muted'
                }`} style={{ width: '40px', height: '40px' }}>
                  <i className={`fas fa-${stepItem.icon}`}></i>
                </div>
                <small className={step >= stepItem.number ? 'text-primary fw-bold' : 'text-muted'}>
                  {stepItem.title}
                </small>
                {index < steps.length - 1 && (
                  <div className={`position-absolute border-top ${
                    step > stepItem.number ? 'border-primary' : 'border-light'
                  }`} style={{ 
                    width: 'calc(100% / 6)', 
                    top: '20px', 
                    left: `${(index + 1) * (100/6)}%`,
                    zIndex: -1
                  }}></div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="container-fluid">
      <div className="row">
        <div className="col-12">
          <div className="mb-4">
            <h1 className="h3 mb-0">Agendar Serviço</h1>
            <p className="text-muted mb-0">Escolha o salão, serviço e horário desejado</p>
          </div>

          {renderStepIndicator()}

          {/* Passo 1: Escolher Salão */}
          {step === 1 && (
            <div className="card">
              <div className="card-header">
                <h5 className="mb-0">
                  <i className="fas fa-store me-2"></i>
                  Escolha o Salão
                </h5>
              </div>
              <div className="card-body">
                <div className="mb-4">
                  <div className="input-group">
                    <span className="input-group-text">
                      <i className="fas fa-search"></i>
                    </span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Buscar salão por nome ou localização..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                    />
                  </div>
                </div>

                <div className="row">
                  {filteredSalons.map(salon => (
                    <div key={salon.id} className="col-md-6 col-lg-4 mb-4">
                      <div className="card h-100 shadow-sm salon-card" style={{ cursor: 'pointer' }}
                           onClick={() => handleSalonSelect(salon)}>
                        <img src={salon.foto} className="card-img-top" alt={salon.nome} style={{ height: '200px', objectFit: 'cover' }} />
                        <div className="card-body">
                          <h6 className="card-title fw-bold">{salon.nome}</h6>
                          <p className="card-text text-muted small mb-2">
                            <i className="fas fa-map-marker-alt me-1"></i>
                            {salon.endereco}
                          </p>
                          <p className="card-text text-muted small mb-2">
                            <i className="fas fa-phone me-1"></i>
                            {formatPhone(salon.telefone)}
                          </p>
                          <div className="d-flex justify-content-between align-items-center">
                            <div>
                              <span className="text-warning">
                                {[...Array(5)].map((_, i) => (
                                  <i key={i} className={`fas fa-star ${
                                    i < Math.floor(salon.avaliacao) ? '' : 'text-muted'
                                  }`}></i>
                                ))}
                              </span>
                              <small className="text-muted ms-1">({salon.avaliacao})</small>
                            </div>
                            <small className="text-primary">{salon.distancia}</small>
                          </div>
                          <small className="text-muted d-block mt-2">
                            <i className="fas fa-clock me-1"></i>
                            {salon.horario_funcionamento}
                          </small>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Passo 2: Escolher Serviço */}
          {step === 2 && (
            <div className="card">
              <div className="card-header d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-cut me-2"></i>
                  Escolha o Serviço
                </h5>
                <button className="btn btn-outline-secondary btn-sm" onClick={() => setStep(1)}>
                  <i className="fas fa-arrow-left me-1"></i>
                  Voltar
                </button>
              </div>
              <div className="card-body">
                <div className="mb-3">
                  <strong>Salão selecionado:</strong> {selectedSalon?.nome}
                </div>
                
                <div className="row">
                  {services.map(service => (
                    <div key={service.id} className="col-md-6 col-lg-4 mb-3">
                      <div className="card h-100 shadow-sm service-card" style={{ cursor: 'pointer' }}
                           onClick={() => handleServiceSelect(service)}>
                        <div className="card-body">
                          <h6 className="card-title fw-bold">{service.nome}</h6>
                          <p className="card-text text-success fw-bold mb-2">
                            {formatCurrency(service.preco)}
                          </p>
                          <p className="card-text text-muted small">
                            <i className="fas fa-clock me-1"></i>
                            Duração: {service.duracao} minutos
                          </p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Passo 3: Escolher Profissional */}
          {step === 3 && (
            <div className="card">
              <div className="card-header d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-user me-2"></i>
                  Escolha o Profissional
                </h5>
                <button className="btn btn-outline-secondary btn-sm" onClick={() => setStep(2)}>
                  <i className="fas fa-arrow-left me-1"></i>
                  Voltar
                </button>
              </div>
              <div className="card-body">
                <div className="mb-3">
                  <strong>Serviço selecionado:</strong> {selectedService?.nome} - {formatCurrency(selectedService?.preco)}
                </div>
                
                <div className="row">
                  {professionals.map(professional => (
                    <div key={professional.id} className="col-md-6 col-lg-4 mb-3">
                      <div className="card h-100 shadow-sm professional-card" style={{ cursor: 'pointer' }}
                           onClick={() => handleProfessionalSelect(professional)}>
                        <div className="card-body text-center">
                          <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                               style={{ width: '60px', height: '60px' }}>
                            <i className="fas fa-user text-white" style={{ fontSize: '1.5rem' }}></i>
                          </div>
                          <h6 className="card-title fw-bold">{professional.nome}</h6>
                          <p className="card-text text-muted small mb-2">{professional.especialidade}</p>
                          <div className="text-warning">
                            {[...Array(5)].map((_, i) => (
                              <i key={i} className={`fas fa-star ${
                                i < Math.floor(professional.avaliacao) ? '' : 'text-muted'
                              }`}></i>
                            ))}
                            <small className="text-muted ms-1">({professional.avaliacao})</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Passo 4: Escolher Data */}
          {step === 4 && (
            <div className="card">
              <div className="card-header d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-calendar me-2"></i>
                  Escolha a Data
                </h5>
                <button className="btn btn-outline-secondary btn-sm" onClick={() => setStep(3)}>
                  <i className="fas fa-arrow-left me-1"></i>
                  Voltar
                </button>
              </div>
              <div className="card-body">
                <div className="mb-3">
                  <strong>Profissional selecionado:</strong> {selectedProfessional?.nome}
                </div>
                
                <div className="row">
                  {availableDates.map((date, index) => (
                    <div key={index} className="col-md-6 col-lg-3 mb-3">
                      <div className="card shadow-sm date-card" style={{ cursor: 'pointer' }}
                           onClick={() => handleDateSelect(date)}>
                        <div className="card-body text-center">
                          <div className="fw-bold text-primary mb-1">
                            {date.toLocaleDateString('pt-BR', { weekday: 'short' }).toUpperCase()}
                          </div>
                          <div className="h4 mb-1">{date.getDate()}</div>
                          <div className="small text-muted">
                            {date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Passo 5: Escolher Horário */}
          {step === 5 && (
            <div className="card">
              <div className="card-header d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-clock me-2"></i>
                  Escolha o Horário
                </h5>
                <button className="btn btn-outline-secondary btn-sm" onClick={() => setStep(4)}>
                  <i className="fas fa-arrow-left me-1"></i>
                  Voltar
                </button>
              </div>
              <div className="card-body">
                <div className="mb-3">
                  <strong>Data selecionada:</strong> {formatDate(new Date(selectedDate + 'T00:00:00'))}
                </div>
                
                <div className="row">
                  {availableTimes.map(time => (
                    <div key={time} className="col-6 col-md-4 col-lg-3 mb-3">
                      <button 
                        className="btn btn-outline-primary w-100 time-btn"
                        onClick={() => handleTimeSelect(time)}
                      >
                        {time}
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Passo 6: Confirmação */}
          {step === 6 && (
            <div className="card">
              <div className="card-header d-flex justify-content-between align-items-center">
                <h5 className="mb-0">
                  <i className="fas fa-check me-2"></i>
                  Confirmar Agendamento
                </h5>
                <button className="btn btn-outline-secondary btn-sm" onClick={() => setStep(5)}>
                  <i className="fas fa-arrow-left me-1"></i>
                  Voltar
                </button>
              </div>
              <div className="card-body">
                <div className="row">
                  <div className="col-md-8">
                    <h6 className="fw-bold mb-3">Resumo do Agendamento</h6>
                    
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Salão:</label>
                      <p className="mb-1">{selectedSalon?.nome}</p>
                      <small className="text-muted">{selectedSalon?.endereco}</small>
                    </div>
                    
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Serviço:</label>
                      <p className="mb-0">{selectedService?.nome}</p>
                    </div>
                    
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Profissional:</label>
                      <p className="mb-0">{selectedProfessional?.nome}</p>
                    </div>
                    
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Data e Horário:</label>
                      <p className="mb-0">{formatDate(new Date(selectedDate + 'T00:00:00'))} às {selectedTime}</p>
                    </div>
                    
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Valor:</label>
                      <p className="mb-0 text-success fw-bold h5">{formatCurrency(selectedService?.preco)}</p>
                    </div>
                    
                    <div className="mb-4">
                      <label htmlFor="observations" className="form-label fw-semibold">Observações (opcional):</label>
                      <textarea
                        id="observations"
                        className="form-control"
                        rows="3"
                        placeholder="Alguma observação especial sobre o serviço..."
                        value={observations}
                        onChange={(e) => setObservations(e.target.value)}
                      ></textarea>
                    </div>
                    
                    <div className="d-flex gap-3">
                      <button 
                        className="btn btn-primary btn-lg"
                        onClick={handleConfirmBooking}
                      >
                        <i className="fas fa-check me-2"></i>
                        Confirmar Agendamento
                      </button>
                      <button 
                        className="btn btn-outline-secondary btn-lg"
                        onClick={() => setStep(1)}
                      >
                        <i className="fas fa-times me-2"></i>
                        Cancelar
                      </button>
                    </div>
                  </div>
                  
                  <div className="col-md-4">
                    <div className="card bg-light">
                      <div className="card-body">
                        <h6 className="fw-bold mb-3">
                          <i className="fas fa-info-circle me-2"></i>
                          Informações Importantes
                        </h6>
                        <ul className="list-unstyled small">
                          <li className="mb-2">
                            <i className="fas fa-clock text-primary me-2"></i>
                            Chegue com 10 minutos de antecedência
                          </li>
                          <li className="mb-2">
                            <i className="fas fa-calendar-times text-warning me-2"></i>
                            Cancelamentos devem ser feitos com 2h de antecedência
                          </li>
                          <li className="mb-2">
                            <i className="fas fa-credit-card text-success me-2"></i>
                            Pagamento no local (dinheiro, cartão ou PIX)
                          </li>
                          <li className="mb-0">
                            <i className="fas fa-phone text-info me-2"></i>
                            Em caso de dúvidas, entre em contato com o salão
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default BookAppointment