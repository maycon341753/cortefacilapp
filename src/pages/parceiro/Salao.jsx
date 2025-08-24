import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Salao = () => {
  const { user } = useAuth()
  const { formatPhone, formatCEP, fetchAddressByCEP } = useApp()
  const [salaoData, setSalaoData] = useState({
    nome: '',
    descricao: '',
    telefone: '',
    email: '',
    endereco: {
      cep: '',
      logradouro: '',
      numero: '',
      complemento: '',
      bairro: '',
      cidade: '',
      estado: ''
    },
    horario_funcionamento: {
      segunda: { abertura: '08:00', fechamento: '18:00', fechado: false },
      terca: { abertura: '08:00', fechamento: '18:00', fechado: false },
      quarta: { abertura: '08:00', fechamento: '18:00', fechado: false },
      quinta: { abertura: '08:00', fechamento: '18:00', fechado: false },
      sexta: { abertura: '08:00', fechamento: '18:00', fechado: false },
      sabado: { abertura: '08:00', fechamento: '16:00', fechado: false },
      domingo: { abertura: '08:00', fechamento: '16:00', fechado: true }
    },
    servicos: [],
    fotos: []
  })
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [activeTab, setActiveTab] = useState('dados')

  // Mock data para demonstração
  useEffect(() => {
    const mockSalaoData = {
      nome: 'Salão Beleza & Estilo',
      descricao: 'Um salão moderno e aconchegante, especializado em cortes, coloração e tratamentos capilares. Nossa equipe de profissionais qualificados está pronta para realçar sua beleza.',
      telefone: '(11) 99999-9999',
      email: 'contato@belezaestilo.com',
      endereco: {
        cep: '01310-100',
        logradouro: 'Av. Paulista',
        numero: '1000',
        complemento: 'Sala 101',
        bairro: 'Bela Vista',
        cidade: 'São Paulo',
        estado: 'SP'
      },
      horario_funcionamento: {
        segunda: { abertura: '08:00', fechamento: '18:00', fechado: false },
        terca: { abertura: '08:00', fechamento: '18:00', fechado: false },
        quarta: { abertura: '08:00', fechamento: '18:00', fechado: false },
        quinta: { abertura: '08:00', fechamento: '18:00', fechado: false },
        sexta: { abertura: '08:00', fechamento: '18:00', fechado: false },
        sabado: { abertura: '08:00', fechamento: '16:00', fechado: false },
        domingo: { abertura: '08:00', fechamento: '16:00', fechado: true }
      },
      servicos: [
        { id: 1, nome: 'Corte Masculino', preco: 25.00, duracao: 30 },
        { id: 2, nome: 'Corte Feminino', preco: 35.00, duracao: 45 },
        { id: 3, nome: 'Coloração', preco: 80.00, duracao: 120 }
      ],
      fotos: []
    }
    
    setTimeout(() => {
      setSalaoData(mockSalaoData)
      setLoading(false)
    }, 1000)
  }, [])

  const handleInputChange = (field, value) => {
    if (field.includes('.')) {
      const [parent, child] = field.split('.')
      setSalaoData(prev => ({
        ...prev,
        [parent]: {
          ...prev[parent],
          [child]: value
        }
      }))
    } else {
      setSalaoData(prev => ({
        ...prev,
        [field]: value
      }))
    }
  }

  const handleHorarioChange = (dia, campo, valor) => {
    setSalaoData(prev => ({
      ...prev,
      horario_funcionamento: {
        ...prev.horario_funcionamento,
        [dia]: {
          ...prev.horario_funcionamento[dia],
          [campo]: valor
        }
      }
    }))
  }

  const handleCEPChange = async (cep) => {
    const formattedCEP = formatCEP(cep)
    handleInputChange('endereco.cep', formattedCEP)
    
    if (formattedCEP.length === 9) {
      try {
        const address = await fetchAddressByCEP(formattedCEP)
        if (address) {
          setSalaoData(prev => ({
            ...prev,
            endereco: {
              ...prev.endereco,
              logradouro: address.logradouro,
              bairro: address.bairro,
              cidade: address.localidade,
              estado: address.uf
            }
          }))
        }
      } catch (error) {
        console.error('Erro ao buscar CEP:', error)
      }
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSaving(true)
    
    try {
      // Simular salvamento
      await new Promise(resolve => setTimeout(resolve, 2000))
      toast.success('Dados do salão atualizados com sucesso!')
    } catch (error) {
      toast.error('Erro ao salvar dados do salão')
    } finally {
      setSaving(false)
    }
  }

  const diasSemana = {
    segunda: 'Segunda-feira',
    terca: 'Terça-feira',
    quarta: 'Quarta-feira',
    quinta: 'Quinta-feira',
    sexta: 'Sexta-feira',
    sabado: 'Sábado',
    domingo: 'Domingo'
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Configurações do Salão</h1>
        <p className="text-gray-600">Gerencie as informações e configurações do seu salão</p>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('dados')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'dados'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Dados Básicos
          </button>
          <button
            onClick={() => setActiveTab('endereco')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'endereco'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Endereço
          </button>
          <button
            onClick={() => setActiveTab('horarios')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'horarios'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Horários
          </button>
          <button
            onClick={() => setActiveTab('fotos')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'fotos'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Fotos
          </button>
        </nav>
      </div>

      <form onSubmit={handleSubmit}>
        {/* Dados Básicos */}
        {activeTab === 'dados' && (
          <div className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
            <h3 className="text-lg font-medium text-gray-900">Informações Básicas</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Nome do Salão</label>
                <input
                  type="text"
                  required
                  value={salaoData.nome}
                  onChange={(e) => handleInputChange('nome', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                <input
                  type="tel"
                  required
                  value={salaoData.telefone}
                  onChange={(e) => handleInputChange('telefone', formatPhone(e.target.value))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  type="email"
                  required
                  value={salaoData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <textarea
                  rows={4}
                  value={salaoData.descricao}
                  onChange={(e) => handleInputChange('descricao', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Descreva seu salão, especialidades e diferenciais..."
                />
              </div>
            </div>
          </div>
        )}

        {/* Endereço */}
        {activeTab === 'endereco' && (
          <div className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
            <h3 className="text-lg font-medium text-gray-900">Endereço</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.cep}
                  onChange={(e) => handleCEPChange(e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="00000-000"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.logradouro}
                  onChange={(e) => handleInputChange('endereco.logradouro', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Número</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.numero}
                  onChange={(e) => handleInputChange('endereco.numero', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                <input
                  type="text"
                  value={salaoData.endereco.complemento}
                  onChange={(e) => handleInputChange('endereco.complemento', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.bairro}
                  onChange={(e) => handleInputChange('endereco.bairro', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.cidade}
                  onChange={(e) => handleInputChange('endereco.cidade', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <input
                  type="text"
                  required
                  value={salaoData.endereco.estado}
                  onChange={(e) => handleInputChange('endereco.estado', e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>
        )}

        {/* Horários */}
        {activeTab === 'horarios' && (
          <div className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
            <h3 className="text-lg font-medium text-gray-900">Horários de Funcionamento</h3>
            
            <div className="space-y-4">
              {Object.entries(diasSemana).map(([dia, nome]) => (
                <div key={dia} className="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                  <div className="w-32">
                    <span className="font-medium text-gray-900">{nome}</span>
                  </div>
                  
                  <div className="flex items-center space-x-2">
                    <input
                      type="checkbox"
                      checked={!salaoData.horario_funcionamento[dia].fechado}
                      onChange={(e) => handleHorarioChange(dia, 'fechado', !e.target.checked)}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span className="text-sm text-gray-600">Aberto</span>
                  </div>
                  
                  {!salaoData.horario_funcionamento[dia].fechado && (
                    <>
                      <div className="flex items-center space-x-2">
                        <label className="text-sm text-gray-600">Abertura:</label>
                        <input
                          type="time"
                          value={salaoData.horario_funcionamento[dia].abertura}
                          onChange={(e) => handleHorarioChange(dia, 'abertura', e.target.value)}
                          className="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                      
                      <div className="flex items-center space-x-2">
                        <label className="text-sm text-gray-600">Fechamento:</label>
                        <input
                          type="time"
                          value={salaoData.horario_funcionamento[dia].fechamento}
                          onChange={(e) => handleHorarioChange(dia, 'fechamento', e.target.value)}
                          className="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                    </>
                  )}
                  
                  {salaoData.horario_funcionamento[dia].fechado && (
                    <span className="text-sm text-gray-500 italic">Fechado</span>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Fotos */}
        {activeTab === 'fotos' && (
          <div className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
            <h3 className="text-lg font-medium text-gray-900">Fotos do Salão</h3>
            
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
              <svg className="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              <div className="mt-4">
                <label htmlFor="file-upload" className="cursor-pointer">
                  <span className="mt-2 block text-sm font-medium text-gray-900">
                    Clique para fazer upload ou arraste as fotos aqui
                  </span>
                  <input id="file-upload" name="file-upload" type="file" className="sr-only" multiple accept="image/*" />
                </label>
                <p className="mt-1 text-xs text-gray-500">
                  PNG, JPG, GIF até 10MB cada
                </p>
              </div>
            </div>
            
            <p className="text-sm text-gray-600">
              Adicione fotos do seu salão para atrair mais clientes. Recomendamos pelo menos 3 fotos mostrando o ambiente, cadeiras e trabalhos realizados.
            </p>
          </div>
        )}

        {/* Botões de Ação */}
        <div className="flex justify-end space-x-3">
          <button
            type="button"
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
          >
            Cancelar
          </button>
          <button
            type="submit"
            disabled={saving}
            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {saving ? 'Salvando...' : 'Salvar Alterações'}
          </button>
        </div>
      </form>
    </div>
  )
}

export default Salao