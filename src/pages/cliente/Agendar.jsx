import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Layout from '../../components/Layout';
import { useAuth } from '../../contexts/AuthContext';
import api from '../../services/api';
import './Agendar.css';

const Agendar = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [loading, setLoading] = useState(false);
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    salao_id: '',
    profissional_id: '',
    data: '',
    hora: '',
    observacoes: ''
  });
  const [saloes, setSaloes] = useState([]);
  const [profissionais, setProfissionais] = useState([]);
  const [servicos, setServicos] = useState([]);
  const [horariosDisponiveis, setHorariosDisponiveis] = useState([]);
  const [loadingHorarios, setLoadingHorarios] = useState(false);

  useEffect(() => {
    if (user?.tipo !== 'cliente') {
      navigate('/dashboard');
      return;
    }
    carregarSaloes();
  }, [user, navigate]);

  const carregarSaloes = async () => {
    try {
      setLoading(true);
      const response = await api.get('/api/saloes');
      if (response.data.success) {
        setSaloes(response.data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar salões:', error);
    } finally {
      setLoading(false);
    }
  };

  const carregarProfissionais = async (salaoId) => {
    try {
      setLoading(true);
      const response = await api.get(`/api/profissionais?salao_id=${salaoId}`);
      if (response.data.success) {
        setProfissionais(response.data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar profissionais:', error);
    } finally {
      setLoading(false);
    }
  };

  const carregarServicos = async (salaoId) => {
    try {
      const response = await api.get(`/api/servicos?salao_id=${salaoId}`);
      if (response.data.success) {
        setServicos(response.data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
    }
  };

  const carregarHorarios = async () => {
    if (!formData.profissional_id || !formData.data) return;

    try {
      setLoadingHorarios(true);
      const response = await api.get('/api/agendamentos/horarios-disponiveis', {
        params: {
          salao_id: formData.salao_id,
          profissional_id: formData.profissional_id,
          data: formData.data
        }
      });
      
      if (response.data.success) {
        setHorariosDisponiveis(response.data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar horários:', error);
      setHorariosDisponiveis([]);
    } finally {
      setLoadingHorarios(false);
    }
  };

  const handleSalaoChange = (salaoId) => {
    setFormData({
      ...formData,
      salao_id: salaoId,
      profissional_id: '',
      data: '',
      hora: ''
    });
    setProfissionais([]);
    setServicos([]);
    setHorariosDisponiveis([]);
    
    if (salaoId) {
      carregarProfissionais(salaoId);
      carregarServicos(salaoId);
    }
  };

  const handleProfissionalChange = (profissionalId) => {
    setFormData({
      ...formData,
      profissional_id: profissionalId,
      data: '',
      hora: ''
    });
    setHorariosDisponiveis([]);
  };

  const handleDataChange = (data) => {
    setFormData({
      ...formData,
      data: data,
      hora: ''
    });
    setHorariosDisponiveis([]);
  };

  useEffect(() => {
    if (formData.profissional_id && formData.data) {
      carregarHorarios();
    }
  }, [formData.profissional_id, formData.data]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.salao_id || !formData.profissional_id || !formData.data || !formData.hora) {
      alert('Por favor, preencha todos os campos obrigatórios.');
      return;
    }

    try {
      setLoading(true);
      
      const agendamentoData = {
        id_cliente: user.id,
        id_salao: parseInt(formData.salao_id),
        id_profissional: parseInt(formData.profissional_id),
        data: formData.data,
        hora: formData.hora.split(' - ')[0], // Extrair apenas o horário de início
        observacoes: formData.observacoes
      };

      const response = await api.post('/api/agendamentos', agendamentoData);
      
      if (response.data.success) {
        alert('Agendamento realizado com sucesso!');
        navigate('/cliente/agendamentos');
      }
    } catch (error) {
      console.error('Erro ao criar agendamento:', error);
      const errorMessage = error.response?.data?.error || 'Erro ao realizar agendamento';
      alert(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const nextStep = () => {
    if (step === 1 && !formData.salao_id) {
      alert('Selecione um salão');
      return;
    }
    if (step === 2 && !formData.profissional_id) {
      alert('Selecione um profissional');
      return;
    }
    if (step === 3 && !formData.data) {
      alert('Selecione uma data');
      return;
    }
    setStep(step + 1);
  };

  const prevStep = () => {
    setStep(step - 1);
  };

  const getMinDate = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
  };

  const getMaxDate = () => {
    const today = new Date();
    const maxDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 dias
    return maxDate.toISOString().split('T')[0];
  };

  const salaoSelecionado = saloes.find(s => s.id === parseInt(formData.salao_id));
  const profissionalSelecionado = profissionais.find(p => p.id === parseInt(formData.profissional_id));

  return (
    <Layout>
      <div className="agendar-container">
        <div className="agendar-header">
          <h1>Novo Agendamento</h1>
          <div className="step-indicator">
            <div className={`step ${step >= 1 ? 'active' : ''}`}>1</div>
            <div className={`step ${step >= 2 ? 'active' : ''}`}>2</div>
            <div className={`step ${step >= 3 ? 'active' : ''}`}>3</div>
            <div className={`step ${step >= 4 ? 'active' : ''}`}>4</div>
          </div>
        </div>

        <div className="agendar-content">
          {step === 1 && (
            <div className="step-content">
              <h2>Escolha o Salão</h2>
              <div className="saloes-grid">
                {loading ? (
                  <div className="loading">Carregando salões...</div>
                ) : (
                  saloes.map(salao => (
                    <div 
                      key={salao.id} 
                      className={`salao-card ${formData.salao_id === salao.id.toString() ? 'selected' : ''}`}
                      onClick={() => handleSalaoChange(salao.id.toString())}
                    >
                      <h3>{salao.nome}</h3>
                      <p>{salao.endereco}</p>
                      <p>{salao.telefone}</p>
                      {salao.horario_funcionamento && (
                        <p className="horario">Funcionamento: {salao.horario_funcionamento}</p>
                      )}
                    </div>
                  ))
                )}
              </div>
              <div className="step-actions">
                <button 
                  className="btn btn-primary" 
                  onClick={nextStep}
                  disabled={!formData.salao_id}
                >
                  Próximo
                </button>
              </div>
            </div>
          )}

          {step === 2 && (
            <div className="step-content">
              <h2>Escolha o Profissional</h2>
              {salaoSelecionado && (
                <div className="selected-info">
                  <p><strong>Salão:</strong> {salaoSelecionado.nome}</p>
                </div>
              )}
              <div className="profissionais-grid">
                {loading ? (
                  <div className="loading">Carregando profissionais...</div>
                ) : (
                  profissionais.map(profissional => (
                    <div 
                      key={profissional.id} 
                      className={`profissional-card ${formData.profissional_id === profissional.id.toString() ? 'selected' : ''}`}
                      onClick={() => handleProfissionalChange(profissional.id.toString())}
                    >
                      <h3>{profissional.nome}</h3>
                      <p className="especialidade">{profissional.especialidade}</p>
                      {profissional.descricao && (
                        <p className="descricao">{profissional.descricao}</p>
                      )}
                    </div>
                  ))
                )}
              </div>
              <div className="step-actions">
                <button className="btn btn-secondary" onClick={prevStep}>
                  Voltar
                </button>
                <button 
                  className="btn btn-primary" 
                  onClick={nextStep}
                  disabled={!formData.profissional_id}
                >
                  Próximo
                </button>
              </div>
            </div>
          )}

          {step === 3 && (
            <div className="step-content">
              <h2>Escolha a Data</h2>
              {profissionalSelecionado && (
                <div className="selected-info">
                  <p><strong>Profissional:</strong> {profissionalSelecionado.nome}</p>
                  <p><strong>Especialidade:</strong> {profissionalSelecionado.especialidade}</p>
                </div>
              )}
              <div className="date-selection">
                <label htmlFor="data">Data do Agendamento:</label>
                <input
                  type="date"
                  id="data"
                  value={formData.data}
                  onChange={(e) => handleDataChange(e.target.value)}
                  min={getMinDate()}
                  max={getMaxDate()}
                  className="form-control"
                />
              </div>
              <div className="step-actions">
                <button className="btn btn-secondary" onClick={prevStep}>
                  Voltar
                </button>
                <button 
                  className="btn btn-primary" 
                  onClick={nextStep}
                  disabled={!formData.data}
                >
                  Próximo
                </button>
              </div>
            </div>
          )}

          {step === 4 && (
            <div className="step-content">
              <h2>Escolha o Horário</h2>
              <div className="selected-info">
                <p><strong>Data:</strong> {new Date(formData.data + 'T00:00:00').toLocaleDateString('pt-BR')}</p>
              </div>
              
              <div className="horarios-section">
                {loadingHorarios ? (
                  <div className="loading">Carregando horários disponíveis...</div>
                ) : horariosDisponiveis.length > 0 ? (
                  <div className="horarios-grid">
                    {horariosDisponiveis.map((horario, index) => (
                      <button
                        key={index}
                        className={`horario-btn ${formData.hora === horario ? 'selected' : ''}`}
                        onClick={() => setFormData({...formData, hora: horario})}
                      >
                        {horario}
                      </button>
                    ))}
                  </div>
                ) : (
                  <div className="no-horarios">
                    <p>Não há horários disponíveis para esta data.</p>
                    <p>Tente selecionar outra data.</p>
                  </div>
                )}
              </div>

              <div className="observacoes-section">
                <label htmlFor="observacoes">Observações (opcional):</label>
                <textarea
                  id="observacoes"
                  value={formData.observacoes}
                  onChange={(e) => setFormData({...formData, observacoes: e.target.value})}
                  placeholder="Alguma observação especial para o agendamento..."
                  className="form-control"
                  rows="3"
                />
              </div>

              <div className="step-actions">
                <button className="btn btn-secondary" onClick={prevStep}>
                  Voltar
                </button>
                <button 
                  className="btn btn-success" 
                  onClick={handleSubmit}
                  disabled={!formData.hora || loading}
                >
                  {loading ? 'Agendando...' : 'Confirmar Agendamento'}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
};

export default Agendar;