import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { apiService } from '../../services/api';
import QRCode from 'qrcode';
import './Agendar.css';

const Agendar = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [loading, setLoading] = useState(false);
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    salao_id: '',
    profissional_id: '',
    servico_id: '',
    data: '',
    horario: '',
    observacoes: ''
  });

  const [saloes, setSaloes] = useState([]);
  const [profissionais, setProfissionais] = useState([]);
  const [servicos, setServicos] = useState([]);
  const [horarios, setHorarios] = useState([]);
  const [busca, setBusca] = useState('');
  const [saloesFiltrados, setSaloesFiltrados] = useState([]);
  const [salaoSelecionado, setSalaoSelecionado] = useState(null);
  const [horariosFuncionamento, setHorariosFuncionamento] = useState([]);
  
  // Estados para pagamento
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [qrCodeUrl, setQrCodeUrl] = useState('');
  const [paymentStatus, setPaymentStatus] = useState('pending'); // pending, processing, confirmed, failed
  const [agendamentoId, setAgendamentoId] = useState(null);
  const [currentAgendamentoData, setCurrentAgendamentoData] = useState(null);
  const [paymentCheckInterval, setPaymentCheckInterval] = useState(null);

  useEffect(() => {
    console.log('🚀 Componente Agendar montado, carregando salões...')
    carregarSaloes()
  }, []);

  const carregarSaloes = async () => {
    try {
      console.log('🔄 Iniciando carregamento de salões...');
      setLoading(true);
      const response = await apiService.getSaloes();
      console.log('✅ Resposta da API de salões:', response);
      
      if (response.success && Array.isArray(response.data)) {
        console.log('📋 Salões encontrados:', response.data.length);
        setSaloes(response.data);
        setSaloesFiltrados(response.data);
      } else {
        console.error('❌ Formato de resposta inválido:', response);
        setSaloes([]);
        setSaloesFiltrados([]);
      }
    } catch (error) {
      console.error('💥 Erro ao carregar salões:', error);
      console.error('Detalhes do erro:', error.response?.data || error.message);
      setSaloes([]);
      setSaloesFiltrados([]);
    } finally {
      setLoading(false);
      console.log('🏁 Carregamento de salões finalizado');
    }
  };

  const filtrarSaloes = (termo) => {
    setBusca(termo);
    if (!termo) {
      setSaloesFiltrados(saloes);
    } else {
      const filtrados = saloes.filter(salao => 
        salao.nome.toLowerCase().includes(termo.toLowerCase()) ||
        salao.endereco?.cidade?.toLowerCase().includes(termo.toLowerCase())
      );
      setSaloesFiltrados(filtrados);
    }
  };

  const carregarHorariosFuncionamento = async (salaoId) => {
    try {
      // Como não temos endpoint específico, vamos usar dados padrão
      const horariosPadrao = [
        { dia: 'Segunda-feira', abertura: '08:00', fechamento: '18:00' },
        { dia: 'Terça-feira', abertura: '08:00', fechamento: '18:00' },
        { dia: 'Quarta-feira', abertura: '08:00', fechamento: '18:00' },
        { dia: 'Quinta-feira', abertura: '08:00', fechamento: '18:00' },
        { dia: 'Sexta-feira', abertura: '08:00', fechamento: '18:00' },
        { dia: 'Sábado', abertura: '08:00', fechamento: '16:00' }
      ];
      setHorariosFuncionamento(horariosPadrao);
    } catch (error) {
      console.error('Erro ao carregar horários de funcionamento:', error);
    }
  };

  const selecionarSalao = async (salao) => {
    setFormData(prev => ({ ...prev, salao_id: salao.id, salao_nome: salao.nome }));
    setSalaoSelecionado(salao);
    await carregarHorariosFuncionamento(salao.id);
    try {
      const response = await apiService.getProfissionaisBySalao(salao.id);
      const profissionaisData = Array.isArray(response.data) ? response.data : [];
      setProfissionais(profissionaisData);
      setStep(2);
    } catch (error) {
      console.error('Erro ao carregar profissionais:', error);
      setProfissionais([]);
    }
  };

  const selecionarProfissional = async (profissional) => {
    setFormData(prev => ({ ...prev, profissional_id: profissional.id }));
    try {
      const response = await apiService.getServicosBySalao(formData.salao_id);
      const servicosData = Array.isArray(response.data) ? response.data : [];
      setServicos(servicosData);
      setStep(3);
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
      setServicos([]);
    }
  };

  const selecionarServico = (servico) => {
    setFormData(prev => ({ ...prev, servico_id: servico.id }));
    // Não avança automaticamente, permite que o usuário veja a seleção
  };

  const handleDataChange = async (data) => {
    setFormData(prev => ({ ...prev, data, horario: '' }));
    setHorarios([]);
    
    if (data && formData.profissional_id && formData.salao_id) {
      try {
        const response = await apiService.getHorariosDisponiveis(formData.profissional_id, data, formData.salao_id);
        console.log('Resposta da API:', response); // Debug
        if (response.success && response.todos_horarios) {
          // Usar todos os horários com status de disponibilidade
          setHorarios(response.todos_horarios);
        } else if (response.success && Array.isArray(response.data)) {
          // Fallback para compatibilidade com versão antiga
          setHorarios(response.data);
        } else {
          console.log('Nenhum horário encontrado na resposta:', response);
          setHorarios([]);
        }
      } catch (error) {
        console.error('Erro ao carregar horários:', error);
        setHorarios([]);
      }
    }
  };

  const selecionarHorario = (horario) => {
    setFormData(prev => ({ ...prev, horario }));
    // Não avança automaticamente, permite que o usuário veja a seleção
  };

  const confirmarAgendamento = async () => {
    setLoading(true);
    try {
      console.log('📋 Dados do formulário antes de enviar:', formData);
      console.log('👤 Usuário logado:', user);
      
      // Validar dados obrigatórios
      if (!formData.salao_id || !formData.profissional_id || !formData.servico_id || !formData.data || !formData.horario) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
      }
      
      // Preparar dados do agendamento (não criar ainda)
      const dadosAgendamento = {
        id_cliente: user?.id,
        id_salao: formData.salao_id,
        id_profissional: formData.profissional_id,
        data: formData.data,
        hora: formData.horario,
        observacoes: formData.observacoes
      };
      
      console.log('📤 Dados preparados para agendamento:', dadosAgendamento);
      
      // Gerar ID temporário para o pagamento
      const tempPaymentId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
      
      // Armazenar dados temporariamente
      setCurrentAgendamentoData(dadosAgendamento);
      setAgendamentoId(tempPaymentId);
      
      console.log('🎯 ID temporário gerado:', tempPaymentId);
      
      // Gerar QR Code para pagamento
      await gerarQRCodePagamento(tempPaymentId);
      
      // Mostrar modal de pagamento ANTES de criar o agendamento
      setShowPaymentModal(true);
      
      // Iniciar verificação de pagamento (que criará o agendamento quando confirmado)
      await iniciarVerificacaoPagamentoEAgendamento(tempPaymentId, dadosAgendamento);
      
    } catch (error) {
      console.error('❌ Erro ao iniciar processo:', error);
      console.error('📊 Detalhes do erro:', error.response?.data || error.message);
      alert(`Erro ao iniciar processo: ${error.response?.data?.message || error.message}`);
    } finally {
      setLoading(false);
    }
  };

  const getMinDate = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
  };

  const voltarEtapa = () => {
    if (step > 1) {
      setStep(step - 1);
    }
  };

  // Função para gerar QR Code de pagamento
  const gerarQRCodePagamento = async (tempPaymentId) => {
    try {
      // Taxa de agendamento fixa
      const pixData = {
        chave: 'contato@cortefacil.com',
        valor: '1.29',
        descricao: `Taxa Agendamento #${tempPaymentId}`,
        agendamento_id: tempPaymentId
      };
      
      // Gerar string PIX (simulada)
      const pixString = `00020126580014BR.GOV.BCB.PIX0136${pixData.chave}0208${pixData.descricao}5204000053039865802BR5925CORTE FACIL LTDA6009SAO PAULO62070503***6304`;
      
      // Gerar QR Code
      const qrCodeDataUrl = await QRCode.toDataURL(pixString, {
        width: 256,
        margin: 2,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      });
      
      setQrCodeUrl(qrCodeDataUrl);
    } catch (error) {
      console.error('Erro ao gerar QR Code:', error);
    }
  };

  // Função para verificar status do pagamento e criar agendamento quando confirmado
  const iniciarVerificacaoPagamentoEAgendamento = async (tempPaymentId, dadosAgendamento) => {
    try {
      // Primeiro, criar o agendamento para obter um ID real
      console.log('📝 Criando agendamento para verificação de pagamento...');
      const response = await apiService.criarAgendamento(dadosAgendamento);
      
      if (response.success && response.data) {
        const agendamentoRealId = response.data.id;
        setAgendamentoId(agendamentoRealId);
        console.log('🎯 Agendamento criado com ID:', agendamentoRealId);
        
        // Agora iniciar a verificação periódica do pagamento
        const interval = setInterval(async () => {
          try {
            console.log('🔍 Verificando status do pagamento para agendamento:', agendamentoRealId);
            
            // Verificar o status real do pagamento via API
            const paymentResponse = await apiService.verificarStatusPagamento(agendamentoRealId);
            
            console.log('📥 Resposta da verificação de pagamento:', paymentResponse);
            
            if (paymentResponse && paymentResponse.success && paymentResponse.data) {
              const statusPagamento = paymentResponse.data.status;
              console.log('📊 Status do pagamento:', statusPagamento);
              
              setPaymentStatus(statusPagamento);
              
              if (statusPagamento === 'confirmado') {
                console.log('✅ Pagamento confirmado pelo banco! Finalizando processo...');
                
                // Parar verificação
                clearInterval(interval);
                setPaymentCheckInterval(null);
                
                // Fechar modal e redirecionar
                setTimeout(() => {
                  setShowPaymentModal(false);
                  navigate('/cliente/agendamentos');
                }, 2000);
              } else if (statusPagamento === 'failed' || statusPagamento === 'cancelled') {
                console.log('❌ Pagamento falhou ou foi cancelado');
                setPaymentStatus('failed');
                clearInterval(interval);
                setPaymentCheckInterval(null);
              } else {
                console.log('⏳ Aguardando confirmação do pagamento pelo banco...');
              }
            } else {
              console.warn('⚠️ Resposta inválida da API de pagamento:', paymentResponse);
            }
            
          } catch (error) {
            console.error('❌ Erro ao verificar pagamento:', error);
            console.error('📊 Detalhes do erro:', error.response?.data || error.message);
            
            // Se houver muitos erros consecutivos, parar a verificação
            if (error.response?.status === 404 || error.response?.status === 403) {
              console.log('🛑 Parando verificação devido a erro crítico');
              clearInterval(interval);
              setPaymentCheckInterval(null);
              setPaymentStatus('failed');
            }
          }
        }, 3000); // Verificar a cada 3 segundos
        
        setPaymentCheckInterval(interval);
        
        // Limpar intervalo após 10 minutos
        setTimeout(() => {
          if (interval) {
            clearInterval(interval);
            setPaymentCheckInterval(null);
            console.log('⏰ Timeout da verificação de pagamento');
            setPaymentStatus('failed');
          }
        }, 600000);
        
      } else {
        throw new Error('Erro ao criar agendamento');
      }
    } catch (error) {
      console.error('❌ Erro ao iniciar processo de pagamento:', error);
      setPaymentStatus('failed');
    }
  };

  // Limpar interval ao desmontar componente
  useEffect(() => {
    return () => {
      if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
      }
    };
  }, [paymentCheckInterval]);

  return (
    <div className="agendar-container">
      <div className="step-indicator">
        <div className={`step ${step >= 1 ? 'active' : ''}`}>1</div>
        <div className={`step ${step >= 2 ? 'active' : ''}`}>2</div>
        <div className={`step ${step >= 3 ? 'active' : ''}`}>3</div>
        <div className={`step ${step >= 4 ? 'active' : ''}`}>4</div>
      </div>

      <div className="agendar-content">
        {step === 1 && (
          <div className="step-content">
            <h2>Escolha o Salão</h2>
            
            <div className="search-container mb-4">
              <input
                type="text"
                className="form-control"
                placeholder="Buscar por nome do salão ou cidade..."
                value={busca}
                onChange={(e) => filtrarSaloes(e.target.value)}
              />
            </div>

            {loading ? (
              <div className="loading">Carregando salões...</div>
            ) : saloesFiltrados.length === 0 ? (
              <div className="no-results">
                <p>Nenhum salão encontrado.</p>
                {busca && <p>Tente ajustar sua busca.</p>}
              </div>
            ) : (
              <div className="saloes-grid">
                {saloesFiltrados.map(salao => (
                  <div key={salao.id} className="salao-card" onClick={() => selecionarSalao(salao)}>
                    <h3>{salao.nome}</h3>
                    <p>{salao.endereco}</p>
                    <p className="telefone">{salao.telefone}</p>
                    <div className="rating">
                      <span>★★★★☆</span>
                      <span>(4.5)</span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {step === 2 && (
          <div className="step-content">
            <h2>Escolha o Profissional</h2>
            <button className="btn-voltar" onClick={voltarEtapa}>← Voltar</button>
            
            {profissionais.length === 0 ? (
              <div className="no-results">
                <p>Nenhum profissional encontrado para este salão.</p>
                <p>Você pode prosseguir sem selecionar um profissional específico.</p>
                <div className="step-actions">
                  <button className="btn btn-primary" onClick={() => setStep(3)}>
                    Próximo →
                  </button>
                </div>
              </div>
            ) : (
              <div className="profissionais-grid">
                {profissionais.map(profissional => (
                  <div key={profissional.id} className="profissional-card" onClick={() => selecionarProfissional(profissional)}>
                    <h3>{profissional.nome}</h3>
                    <p>{profissional.especialidade}</p>
                  </div>
                ))}
                <div className="step-actions">
                  <button className="btn btn-secondary" onClick={() => setStep(3)}>
                    Pular Seleção →
                  </button>
                </div>
              </div>
            )}
          </div>
        )}

        {step === 3 && (
          <div className="step-content">
            <div className="container-fluid">
              <div className="row">
                <div className="col-12">
                  <h2 className="text-center mb-4">Escolha o Serviço</h2>
                  <button className="btn btn-outline-secondary mb-4" onClick={voltarEtapa}>
                    <i className="fas fa-arrow-left me-2"></i>Voltar
                  </button>
                </div>
              </div>
              
              {servicos.length === 0 ? (
                <div className="row justify-content-center">
                  <div className="col-md-6">
                    <div className="alert alert-info text-center">
                      <h5>Nenhum serviço encontrado</h5>
                      <p className="mb-3">Nenhum serviço foi encontrado para este salão.</p>
                      <p className="mb-4">Você pode prosseguir sem selecionar um serviço específico.</p>
                      <button className="btn btn-primary btn-lg" onClick={() => setStep(4)}>
                        Próximo <i className="fas fa-arrow-right ms-2"></i>
                      </button>
                    </div>
                  </div>
                </div>
              ) : (
                <div>
                  <div className="row">
                    {servicos.map(servico => (
                      <div key={servico.id} className="col-lg-4 col-md-6 mb-4">
                        <div 
                          className={`card h-100 servico-card ${
                            formData.servico_id === servico.id ? 'border-primary bg-light' : ''
                          }`}
                          onClick={() => selecionarServico(servico)}
                          style={{ cursor: 'pointer', transition: 'all 0.3s ease' }}
                        >
                          <div className="card-body d-flex flex-column">
                            <h5 className="card-title text-primary">{servico.nome}</h5>
                            <p className="card-text text-muted flex-grow-1">{servico.descricao}</p>
                            <div className="mt-auto">
                              <div className="d-flex justify-content-between align-items-center">
                                <span className="h5 text-success mb-0">
                                  R$ {parseFloat(servico.preco || 0).toFixed(2)}
                                </span>
                                <span className="badge bg-secondary">
                                  {servico.duracao || 30} min
                                </span>
                              </div>
                              {formData.servico_id === servico.id && (
                                <div className="text-center mt-2">
                                  <i className="fas fa-check-circle text-primary"></i>
                                  <small className="text-primary ms-1">Selecionado</small>
                                </div>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  
                  <div className="row mt-4">
                    <div className="col-12 text-center">
                      <div className="d-flex justify-content-center gap-3">
                        {formData.servico_id && (
                          <button className="btn btn-success btn-lg" onClick={() => setStep(4)}>
                            Continuar com Serviço Selecionado <i className="fas fa-arrow-right ms-2"></i>
                          </button>
                        )}
                        <button className="btn btn-outline-secondary" onClick={() => setStep(4)}>
                          Pular Seleção
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {step === 4 && (
          <div className="step-content">
            <div className="container">
              <div className="row justify-content-center">
                <div className="col-md-8">
                  <h2 className="text-center mb-4">
                    <i className="fas fa-calendar-alt me-2"></i>
                    Escolha a Data e Horário
                  </h2>
                  
                  <button className="btn btn-outline-secondary mb-4" onClick={voltarEtapa}>
                    <i className="fas fa-arrow-left me-2"></i>Voltar
                  </button>
                  
                  <div className="alert alert-info mb-4">
                    <i className="fas fa-info-circle me-2"></i>
                    <strong>Informações:</strong>
                    <ul className="mb-0 mt-2">
                      <li>Cada serviço tem duração de 30 minutos</li>
                      <li>Selecione uma data e depois escolha o horário disponível</li>
                      <li>Os horários respeitam o funcionamento do salão</li>
                    </ul>
                  </div>



                  <div className="card mb-4">
                    <div className="card-header">
                      <h5 className="mb-0">
                        <i className="fas fa-calendar-clock me-2"></i>
                        Escolha Data e Horário
                      </h5>
                    </div>
                    <div className="card-body">
                      <div className="mb-4">
                        <label className="form-label fw-bold">
                          <i className="fas fa-calendar-alt me-2"></i>
                          Data do Agendamento:
                        </label>
                        <input
                          type="date"
                          className="form-control form-control-lg"
                          value={formData.data}
                          min={getMinDate()}
                          onChange={(e) => handleDataChange(e.target.value)}
                        />
                      </div>

                      {formData.data && (
                        <div>
                          <label className="form-label fw-bold mb-3">
                            <i className="fas fa-clock me-2"></i>
                            Horários para Agendamento:
                          </label>
                          
                          {horarios.length === 0 ? (
                            <div className="alert alert-warning text-center">
                              <i className="fas fa-exclamation-triangle me-2"></i>
                              Nenhum horário encontrado para esta data.
                            </div>
                          ) : (
                            <>
                              <div className="alert alert-info mb-3">
                                <i className="fas fa-info-circle me-2"></i>
                                Clique nos horários <strong>disponíveis</strong> (em azul) para selecioná-los
                              </div>
                              <div className="row g-2 horarios-grid">
                                {horarios.map((horario, index) => {
                                  // Verificar se é objeto com disponibilidade ou string simples
                                  const isObject = typeof horario === 'object';
                                  const horaInicio = isObject ? horario.hora_inicio?.substring(0, 5) : horario;
                                  const disponivel = isObject ? horario.disponivel : true;
                                  const motivo = isObject ? horario.motivo : null;
                                  
                                  return (
                                    <div key={index} className="col-6 col-md-4 col-lg-3">
                                      <button
                                        className={`btn w-100 position-relative ${
                                          !disponivel 
                                            ? 'btn-outline-secondary' 
                                            : formData.horario === horaInicio 
                                              ? 'btn-primary' 
                                              : 'btn-outline-primary'
                                        }`}
                                        onClick={() => disponivel && selecionarHorario(horaInicio)}
                                        disabled={!disponivel}
                                        title={motivo || 'Horário disponível'}
                                      >
                                        <i className={`fas ${disponivel ? 'fa-clock' : 'fa-times'} me-1`}></i>
                                        {horaInicio}
                                        {!disponivel && (
                                          <small className="d-block text-muted" style={{fontSize: '0.7rem'}}>
                                            {motivo === 'Horário já agendado' ? 'Ocupado' : 
                                             motivo === 'Fora do horário de funcionamento' ? 'Fechado' :
                                             motivo === 'Horário já passou' ? 'Passou' : 'Indisponível'}
                                          </small>
                                        )}
                                      </button>
                                    </div>
                                  );
                                })}
                              </div>
                              
                              {formData.horario && (
                                <div className="alert alert-success mt-3">
                                  <i className="fas fa-check-circle me-2"></i>
                                  Horário selecionado: <strong>{formData.horario}</strong>
                                </div>
                              )}
                            </>
                          )}
                        </div>
                      )}
                    </div>
                  </div>

                  {formData.horario && (
                    <div className="text-center mt-4">
                      <div className="alert alert-success mb-3">
                        <i className="fas fa-check-circle me-2"></i>
                        <strong>Horário selecionado:</strong> {formData.horario}
                      </div>
                      <button className="btn btn-success btn-lg" onClick={() => setStep(5)}>
                        Continuar para Confirmação
                        <i className="fas fa-arrow-right ms-2"></i>
                      </button>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}

        {step === 5 && (
          <div className="step-content">
            <h2>Confirmar Agendamento</h2>
            <button className="btn-voltar" onClick={voltarEtapa}>← Voltar</button>
            
            <div className="resumo-agendamento">
              <h3>Resumo do Agendamento</h3>
              <p><strong>Data:</strong> {formData.data}</p>
              <p><strong>Horário:</strong> {formData.horario}</p>
            </div>

            <div className="observacoes">
              <label htmlFor="observacoes">Observações (opcional):</label>
              <textarea
                id="observacoes"
                value={formData.observacoes}
                onChange={(e) => setFormData(prev => ({ ...prev, observacoes: e.target.value }))}
                placeholder="Alguma observação especial?"
              />
            </div>

            <button 
              className="btn-confirmar" 
              onClick={confirmarAgendamento}
              disabled={loading}
            >
              {loading ? 'Confirmando...' : 'Confirmar Agendamento'}
            </button>
          </div>
        )}
      </div>

      {/* Modal de Pagamento */}
      {showPaymentModal && (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-lg modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  <i className="fas fa-qrcode me-2"></i>
                  Pagamento do Agendamento
                </h5>
                {paymentStatus !== 'confirmado' && (
                  <button 
                    type="button" 
                    className="btn-close"
                    onClick={() => {
                      setShowPaymentModal(false);
                      if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        setPaymentCheckInterval(null);
                      }
                    }}
                  ></button>
                )}
              </div>
              <div className="modal-body text-center">
                {paymentStatus === 'pending' && (
                  <>
                    <h6 className="mb-4">Escaneie o QR Code para pagar via PIX</h6>
                    
                    {qrCodeUrl && (
                      <div className="mb-4">
                        <img src={qrCodeUrl} alt="QR Code PIX" className="img-fluid" style={{ maxWidth: '256px' }} />
                      </div>
                    )}
                    
                    <div className="alert alert-info">
                      <i className="fas fa-info-circle me-2"></i>
                      <strong>Taxa de Agendamento:</strong> R$ 1,29<br/>
                      <strong>Agendamento:</strong> #{agendamentoId}
                    </div>
                    
                    <div className="alert alert-warning">
                      <i className="fas fa-exclamation-triangle me-2"></i>
                      <strong>Importante:</strong> O valor do serviço será pago diretamente no salão.
                    </div>
                    
                    <div className="d-flex align-items-center justify-content-center mt-3">
                      <div className="spinner-border spinner-border-sm me-2" role="status"></div>
                      <span>Aguardando confirmação do pagamento...</span>
                    </div>
                    
                    {/* Botão para simular confirmação (apenas para testes) */}
                    <div className="mt-4">
                      <button 
                        className="btn btn-success btn-sm"
                        onClick={async () => {
                          try {
                            console.log('🧪 Simulando confirmação de pagamento para teste...');
                            console.log('🆔 ID do agendamento:', agendamentoId);
                            
                            if (!agendamentoId) {
                              console.error('❌ ID do agendamento não encontrado');
                              alert('Erro: ID do agendamento não encontrado');
                              return;
                            }
                            
                            const response = await apiService.confirmarPagamento(agendamentoId);
                            console.log('📥 Resposta da confirmação:', response);
                            
                            if (response.success) {
                              console.log('✅ Pagamento confirmado via simulação');
                              setPaymentStatus('confirmado');
                            } else {
                              console.error('❌ Falha na confirmação:', response);
                              alert('Erro na confirmação: ' + (response.error || 'Erro desconhecido'));
                            }
                          } catch (error) {
                            console.error('❌ Erro ao simular confirmação:', error);
                            console.error('📊 Detalhes do erro:', error.response?.data || error.message);
                            alert('Erro ao simular confirmação: ' + (error.response?.data?.error || error.message));
                          }
                        }}
                      >
                        <i className="fas fa-check me-1"></i>
                        Simular Confirmação (Teste)
                      </button>
                      <div className="text-muted mt-2" style={{fontSize: '0.8em'}}>
                        <i className="fas fa-info-circle me-1"></i>
                        Este botão é apenas para testes. Em produção, a confirmação virá do banco.
                      </div>
                    </div>
                  </>
                )}
                
                {paymentStatus === 'confirmado' && (
                  <>
                    <div className="text-success mb-4">
                      <i className="fas fa-check-circle fa-4x"></i>
                    </div>
                    <h4 className="text-success mb-3">Pagamento Confirmado!</h4>
                    <p className="mb-3">Seu agendamento foi confirmado com sucesso.</p>
                    <div className="d-flex align-items-center justify-content-center">
                      <div className="spinner-border spinner-border-sm me-2" role="status"></div>
                      <span>Redirecionando para seus agendamentos...</span>
                    </div>
                  </>
                )}
                
                {paymentStatus === 'failed' && (
                  <>
                    <div className="text-danger mb-4">
                      <i className="fas fa-times-circle fa-4x"></i>
                    </div>
                    <h4 className="text-danger mb-3">Pagamento Não Confirmado</h4>
                    <p className="mb-3">Não foi possível confirmar o pagamento. Tente novamente.</p>
                    <button 
                      className="btn btn-primary"
                      onClick={() => {
                        setPaymentStatus('pending');
                        iniciarVerificacaoPagamento(agendamentoId);
                      }}
                    >
                      Tentar Novamente
                    </button>
                  </>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Agendar;