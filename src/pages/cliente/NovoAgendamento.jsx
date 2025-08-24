import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const NovoAgendamento = () => {
  const { user } = useAuth()
  const { formatPhone } = useApp()
  const [loading, setLoading] = useState(false)
  const [step, setStep] = useState(1)
  const [saloes, setSaloes] = useState([])
  const [selectedSalao, setSelectedSalao] = useState(null)
  const [selectedServico, setSelectedServico] = useState(null)
  const [selectedProfissional, setSelectedProfissional] = useState(null)
  const [selectedData, setSelectedData] = useState('')
  const [selectedHorario, setSelectedHorario] = useState('')
  const [horariosDisponiveis, setHorariosDisponiveis] = useState([])
  const [observacoes, setObservacoes] = useState('')

  // Mock data dos salões
  useEffect(() => {
    const mockSaloes = [
      {
        id: 1,
        nome: 'Salão Beleza & Estilo',
        endereco: {
          logradouro: 'Rua das Flores, 123',
          bairro: 'Centro',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 99999-9999',
        foto: 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400',
        avaliacao: 4.8,
        servicos: [
          { id: 1, nome: 'Corte Feminino', preco: 35.00, duracao: 60 },
          { id: 2, nome: 'Coloração', preco: 80.00, duracao: 120 },
          { id: 3, nome: 'Hidratação', preco: 40.00, duracao: 45 },
          { id: 4, nome: 'Escova', preco: 25.00, duracao: 30 }
        ],
        profissionais: [
          { id: 1, nome: 'Maria Silva', especialidades: ['Corte Feminino', 'Coloração'], foto: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150' },
          { id: 2, nome: 'Ana Costa', especialidades: ['Hidratação', 'Escova'], foto: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150' }
        ]
      },
      {
        id: 2,
        nome: 'Studio Hair',
        endereco: {
          logradouro: 'Av. Paulista, 456',
          bairro: 'Bela Vista',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 88888-8888',
        foto: 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?w=400',
        avaliacao: 4.6,
        servicos: [
          { id: 5, nome: 'Corte Masculino', preco: 30.00, duracao: 45 },
          { id: 6, nome: 'Barba', preco: 20.00, duracao: 30 },
          { id: 7, nome: 'Corte + Barba', preco: 45.00, duracao: 75 },
          { id: 8, nome: 'Tratamento Capilar', preco: 60.00, duracao: 90 }
        ],
        profissionais: [
          { id: 3, nome: 'João Santos', especialidades: ['Corte Masculino', 'Barba'], foto: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150' },
          { id: 4, nome: 'Pedro Lima', especialidades: ['Tratamento Capilar'], foto: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150' }
        ]
      },
      {
        id: 3,
        nome: 'Espaço Zen Beauty',
        endereco: {
          logradouro: 'Rua Augusta, 789',
          bairro: 'Consolação',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 77777-7777',
        foto: 'https://images.unsplash.com/photo-1562322140-8baeececf3df?w=400',
        avaliacao: 4.9,
        servicos: [
          { id: 9, nome: 'Massagem Capilar', preco: 50.00, duracao: 60 },
          { id: 10, nome: 'Reflexologia', preco: 70.00, duracao: 90 },
          { id: 11, nome: 'Limpeza de Pele', preco: 80.00, duracao: 120 },
          { id: 12, nome: 'Manicure', preco: 25.00, duracao: 45 }
        ],
        profissionais: [
          { id: 5, nome: 'Carla Mendes', especialidades: ['Massagem Capilar', 'Reflexologia'], foto: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150' },
          { id: 6, nome: 'Lucia Oliveira', especialidades: ['Limpeza de Pele', 'Manicure'], foto: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150' }
        ]
      }
    ]
    
    setSaloes(mockSaloes)
  }, [])

  // Gerar horários disponíveis quando data e profissional são selecionados
  useEffect(() => {
    if (selectedData && selectedProfissional && selectedServico) {
      generateAvailableHours()
    }
  }, [selectedData, selectedProfissional, selectedServico])

  const generateAvailableHours = () => {
    // Mock de horários disponíveis
    const baseHours = [
      '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
      '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'
    ]
    
    // Simular alguns horários ocupados
    const occupiedHours = ['10:00', '14:30', '16:00']
    
    const available = baseHours.filter(hour => !occupiedHours.includes(hour))
    setHorariosDisponiveis(available)
  }

  const handleSalaoSelect = (salao) => {
    setSelectedSalao(salao)
    setSelectedServico(null)
    setSelectedProfissional(null)
    setSelectedData('')
    setSelectedHorario('')
    setStep(2)
  }

  const handleServicoSelect = (servico) => {
    setSelectedServico(servico)
    setSelectedProfissional(null)
    setSelectedData('')
    setSelectedHorario('')
    setStep(3)
  }

  const handleProfissionalSelect = (profissional) => {
    setSelectedProfissional(profissional)
    setSelectedData('')
    setSelectedHorario('')
    setStep(4)
  }

  const handleDataSelect = (data) => {
    setSelectedData(data)
    setSelectedHorario('')
    setStep(5)
  }

  const handleHorarioSelect = (horario) => {
    setSelectedHorario(horario)
    setStep(6)
  }

  const handleConfirmarAgendamento = async () => {
    if (!selectedSalao || !selectedServico || !selectedProfissional || !selectedData || !selectedHorario) {
      toast.error('Por favor, complete todas as etapas do agendamento')
      return
    }

    setLoading(true)
    
    try {
      // Simular criação do agendamento
      await new Promise(resolve => setTimeout(resolve, 2000))
      
      const agendamento = {
        id: Date.now(),
        salao: selectedSalao.nome,
        servico: selectedServico.nome,
        profissional: selectedProfissional.nome,
        data: selectedData,
        horario: selectedHorario,
        valor: selectedServico.preco,
        duracao: selectedServico.duracao,
        observacoes,
        status: 'agendado'
      }
      
      toast.success('Agendamento realizado com sucesso!')
      
      // Reset form
      setStep(1)
      setSelectedSalao(null)
      setSelectedServico(null)
      setSelectedProfissional(null)
      setSelectedData('')
      setSelectedHorario('')
      setObservacoes('')
      
    } catch (error) {
      toast.error('Erro ao realizar agendamento')
    } finally {
      setLoading(false)
    }
  }

  const getMinDate = () => {
    const today = new Date()
    return today.toISOString().split('T')[0]
  }

  const getMaxDate = () => {
    const today = new Date()
    const maxDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000)) // 30 dias
    return maxDate.toISOString().split('T')[0]
  }

  const renderSteps = () => {
    const steps = [
      { number: 1, title: 'Salão', completed: selectedSalao !== null },
      { number: 2, title: 'Serviço', completed: selectedServico !== null },
      { number: 3, title: 'Profissional', completed: selectedProfissional !== null },
      { number: 4, title: 'Data', completed: selectedData !== '' },
      { number: 5, title: 'Horário', completed: selectedHorario !== '' },
      { number: 6, title: 'Confirmação', completed: false }
    ]

    return (
      <div className="flex items-center justify-between mb-8">
        {steps.map((stepItem, index) => (
          <div key={stepItem.number} className="flex items-center">
            <div className={`flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium ${
              step === stepItem.number
                ? 'bg-blue-600 text-white'
                : stepItem.completed
                ? 'bg-green-600 text-white'
                : 'bg-gray-300 text-gray-600'
            }`}>
              {stepItem.completed ? '✓' : stepItem.number}
            </div>
            <span className={`ml-2 text-sm font-medium ${
              step === stepItem.number ? 'text-blue-600' : 'text-gray-600'
            }`}>
              {stepItem.title}
            </span>
            {index < steps.length - 1 && (
              <div className={`w-8 h-0.5 mx-4 ${
                stepItem.completed ? 'bg-green-600' : 'bg-gray-300'
              }`} />
            )}
          </div>
        ))}
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Novo Agendamento</h1>
        <p className="text-gray-600">Agende seu próximo atendimento em poucos passos</p>
      </div>

      {/* Steps */}
      {renderSteps()}

      {/* Step 1: Selecionar Salão */}
      {step === 1 && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Escolha o Salão</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {saloes.map((salao) => (
              <div
                key={salao.id}
                onClick={() => handleSalaoSelect(salao)}
                className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all"
              >
                <img
                  src={salao.foto}
                  alt={salao.nome}
                  className="w-full h-32 object-cover rounded-lg mb-3"
                />
                <h3 className="font-semibold text-gray-900 mb-2">{salao.nome}</h3>
                <p className="text-sm text-gray-600 mb-2">
                  {salao.endereco.logradouro}, {salao.endereco.bairro}
                </p>
                <p className="text-sm text-gray-600 mb-2">{formatPhone(salao.telefone)}</p>
                <div className="flex items-center">
                  <span className="text-yellow-400">⭐</span>
                  <span className="text-sm text-gray-600 ml-1">{salao.avaliacao}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Step 2: Selecionar Serviço */}
      {step === 2 && selectedSalao && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Escolha o Serviço</h2>
            <button
              onClick={() => setStep(1)}
              className="text-sm text-blue-600 hover:text-blue-800"
            >
              ← Voltar
            </button>
          </div>
          
          <div className="mb-4 p-3 bg-gray-50 rounded-lg">
            <p className="text-sm text-gray-600">Salão selecionado:</p>
            <p className="font-medium text-gray-900">{selectedSalao.nome}</p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {selectedSalao.servicos.map((servico) => (
              <div
                key={servico.id}
                onClick={() => handleServicoSelect(servico)}
                className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all"
              >
                <div className="flex justify-between items-start mb-2">
                  <h3 className="font-semibold text-gray-900">{servico.nome}</h3>
                  <span className="text-lg font-bold text-blue-600">R$ {servico.preco.toFixed(2)}</span>
                </div>
                <p className="text-sm text-gray-600">Duração: {servico.duracao} minutos</p>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Step 3: Selecionar Profissional */}
      {step === 3 && selectedServico && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Escolha o Profissional</h2>
            <button
              onClick={() => setStep(2)}
              className="text-sm text-blue-600 hover:text-blue-800"
            >
              ← Voltar
            </button>
          </div>
          
          <div className="mb-4 p-3 bg-gray-50 rounded-lg">
            <p className="text-sm text-gray-600">Serviço selecionado:</p>
            <p className="font-medium text-gray-900">{selectedServico.nome} - R$ {selectedServico.preco.toFixed(2)}</p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {selectedSalao.profissionais
              .filter(prof => prof.especialidades.includes(selectedServico.nome))
              .map((profissional) => (
                <div
                  key={profissional.id}
                  onClick={() => handleProfissionalSelect(profissional)}
                  className="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all"
                >
                  <img
                    src={profissional.foto}
                    alt={profissional.nome}
                    className="w-16 h-16 rounded-full mx-auto mb-3 object-cover"
                  />
                  <h3 className="font-semibold text-gray-900 text-center mb-2">{profissional.nome}</h3>
                  <div className="text-center">
                    <p className="text-sm text-gray-600">Especialidades:</p>
                    <div className="flex flex-wrap justify-center gap-1 mt-1">
                      {profissional.especialidades.map((esp, index) => (
                        <span key={index} className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          {esp}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              ))}
          </div>
        </div>
      )}

      {/* Step 4: Selecionar Data */}
      {step === 4 && selectedProfissional && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Escolha a Data</h2>
            <button
              onClick={() => setStep(3)}
              className="text-sm text-blue-600 hover:text-blue-800"
            >
              ← Voltar
            </button>
          </div>
          
          <div className="mb-4 p-3 bg-gray-50 rounded-lg">
            <p className="text-sm text-gray-600">Profissional selecionado:</p>
            <p className="font-medium text-gray-900">{selectedProfissional.nome}</p>
          </div>
          
          <div className="max-w-md">
            <label className="block text-sm font-medium text-gray-700 mb-2">Data do Agendamento</label>
            <input
              type="date"
              min={getMinDate()}
              max={getMaxDate()}
              value={selectedData}
              onChange={(e) => handleDataSelect(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p className="text-xs text-gray-500 mt-1">Agendamentos disponíveis até 30 dias</p>
          </div>
        </div>
      )}

      {/* Step 5: Selecionar Horário */}
      {step === 5 && selectedData && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Escolha o Horário</h2>
            <button
              onClick={() => setStep(4)}
              className="text-sm text-blue-600 hover:text-blue-800"
            >
              ← Voltar
            </button>
          </div>
          
          <div className="mb-4 p-3 bg-gray-50 rounded-lg">
            <p className="text-sm text-gray-600">Data selecionada:</p>
            <p className="font-medium text-gray-900">{new Date(selectedData).toLocaleDateString('pt-BR')}</p>
          </div>
          
          <div className="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            {horariosDisponiveis.map((horario) => (
              <button
                key={horario}
                onClick={() => handleHorarioSelect(horario)}
                className="p-3 border border-gray-200 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50 transition-all"
              >
                {horario}
              </button>
            ))}
          </div>
          
          {horariosDisponiveis.length === 0 && (
            <div className="text-center py-8">
              <p className="text-gray-600">Nenhum horário disponível para esta data.</p>
              <p className="text-sm text-gray-500">Tente selecionar outra data.</p>
            </div>
          )}
        </div>
      )}

      {/* Step 6: Confirmação */}
      {step === 6 && selectedHorario && (
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Confirmar Agendamento</h2>
            <button
              onClick={() => setStep(5)}
              className="text-sm text-blue-600 hover:text-blue-800"
            >
              ← Voltar
            </button>
          </div>
          
          <div className="space-y-4">
            <div className="bg-gray-50 p-4 rounded-lg">
              <h3 className="font-semibold text-gray-900 mb-3">Resumo do Agendamento</h3>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-600">Salão:</p>
                  <p className="font-medium text-gray-900">{selectedSalao.nome}</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-600">Serviço:</p>
                  <p className="font-medium text-gray-900">{selectedServico.nome}</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-600">Profissional:</p>
                  <p className="font-medium text-gray-900">{selectedProfissional.nome}</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-600">Data e Horário:</p>
                  <p className="font-medium text-gray-900">
                    {new Date(selectedData).toLocaleDateString('pt-BR')} às {selectedHorario}
                  </p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-600">Duração:</p>
                  <p className="font-medium text-gray-900">{selectedServico.duracao} minutos</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-600">Valor:</p>
                  <p className="font-medium text-green-600 text-lg">R$ {selectedServico.preco.toFixed(2)}</p>
                </div>
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Observações (opcional)</label>
              <textarea
                value={observacoes}
                onChange={(e) => setObservacoes(e.target.value)}
                rows={3}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Alguma observação especial para o profissional..."
              />
            </div>
            
            <div className="flex space-x-4 pt-4">
              <button
                onClick={() => setStep(1)}
                className="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancelar
              </button>
              <button
                onClick={handleConfirmarAgendamento}
                disabled={loading}
                className="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? 'Confirmando...' : 'Confirmar Agendamento'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default NovoAgendamento