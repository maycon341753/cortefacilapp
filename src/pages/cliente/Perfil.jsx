import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Perfil = () => {
  const { user, updateUser } = useAuth()
  const { formatPhone, formatCPF, formatCEP, fetchAddressByCEP } = useApp()
  const [loading, setLoading] = useState(false)
  const [activeTab, setActiveTab] = useState('dados')
  const [formData, setFormData] = useState({
    nome: '',
    email: '',
    telefone: '',
    cpf: '',
    data_nascimento: '',
    endereco: {
      cep: '',
      logradouro: '',
      numero: '',
      complemento: '',
      bairro: '',
      cidade: '',
      estado: ''
    },
    preferencias: {
      notificacoes_email: true,
      notificacoes_sms: true,
      promocoes: true,
      lembrete_agendamento: true
    }
  })
  const [passwordData, setPasswordData] = useState({
    senha_atual: '',
    nova_senha: '',
    confirmar_senha: ''
  })
  const [historico, setHistorico] = useState([])

  // Carregar dados do usuário
  useEffect(() => {
    if (user) {
      setFormData({
        nome: user.nome || '',
        email: user.email || '',
        telefone: user.telefone || '',
        cpf: user.cpf || '',
        data_nascimento: user.data_nascimento || '',
        endereco: user.endereco || {
          cep: '',
          logradouro: '',
          numero: '',
          complemento: '',
          bairro: '',
          cidade: '',
          estado: ''
        },
        preferencias: user.preferencias || {
          notificacoes_email: true,
          notificacoes_sms: true,
          promocoes: true,
          lembrete_agendamento: true
        }
      })
    }

    // Mock histórico de agendamentos
    const mockHistorico = [
      {
        id: 1,
        data: '2024-01-15',
        salao: 'Salão Beleza & Estilo',
        servico: 'Corte Feminino',
        valor: 35.00,
        status: 'concluido'
      },
      {
        id: 2,
        data: '2023-12-20',
        salao: 'Studio Hair',
        servico: 'Coloração',
        valor: 80.00,
        status: 'concluido'
      },
      {
        id: 3,
        data: '2023-11-10',
        salao: 'Salão Beleza & Estilo',
        servico: 'Hidratação',
        valor: 40.00,
        status: 'concluido'
      }
    ]
    setHistorico(mockHistorico)
  }, [user])

  const handleInputChange = (field, value) => {
    if (field.includes('.')) {
      const [parent, child] = field.split('.')
      setFormData(prev => ({
        ...prev,
        [parent]: {
          ...prev[parent],
          [child]: value
        }
      }))
    } else {
      setFormData(prev => ({
        ...prev,
        [field]: value
      }))
    }
  }

  const handlePreferenceChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      preferencias: {
        ...prev.preferencias,
        [field]: value
      }
    }))
  }

  const handlePasswordChange = (field, value) => {
    setPasswordData(prev => ({
      ...prev,
      [field]: value
    }))
  }

  const handleCEPChange = async (cep) => {
    const formattedCEP = formatCEP(cep)
    handleInputChange('endereco.cep', formattedCEP)
    
    if (formattedCEP.length === 9) {
      try {
        const address = await fetchAddressByCEP(formattedCEP)
        if (address) {
          setFormData(prev => ({
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

  const handleSubmitProfile = async (e) => {
    e.preventDefault()
    setLoading(true)
    
    try {
      // Simular atualização do perfil
      await new Promise(resolve => setTimeout(resolve, 2000))
      
      // Atualizar contexto do usuário
      await updateUser(formData)
      
      toast.success('Perfil atualizado com sucesso!')
    } catch (error) {
      toast.error('Erro ao atualizar perfil')
    } finally {
      setLoading(false)
    }
  }

  const handleSubmitPassword = async (e) => {
    e.preventDefault()
    
    if (passwordData.nova_senha !== passwordData.confirmar_senha) {
      toast.error('As senhas não coincidem')
      return
    }
    
    if (passwordData.nova_senha.length < 6) {
      toast.error('A nova senha deve ter pelo menos 6 caracteres')
      return
    }
    
    setLoading(true)
    
    try {
      // Simular alteração de senha
      await new Promise(resolve => setTimeout(resolve, 2000))
      
      toast.success('Senha alterada com sucesso!')
      setPasswordData({
        senha_atual: '',
        nova_senha: '',
        confirmar_senha: ''
      })
    } catch (error) {
      toast.error('Erro ao alterar senha')
    } finally {
      setLoading(false)
    }
  }

  const totalGasto = historico.reduce((total, item) => total + item.valor, 0)
  const totalAgendamentos = historico.length

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Meu Perfil</h1>
        <p className="text-gray-600">Gerencie suas informações pessoais e preferências</p>
      </div>

      {/* Estatísticas Rápidas */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-3 bg-blue-100 rounded-full">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Total de Agendamentos</p>
              <p className="text-2xl font-bold text-gray-900">{totalAgendamentos}</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-3 bg-green-100 rounded-full">
              <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Total Gasto</p>
              <p className="text-2xl font-bold text-gray-900">R$ {totalGasto.toFixed(2)}</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-3 bg-purple-100 rounded-full">
              <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Cliente desde</p>
              <p className="text-2xl font-bold text-gray-900">2023</p>
            </div>
          </div>
        </div>
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
            Dados Pessoais
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
            onClick={() => setActiveTab('preferencias')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'preferencias'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Preferências
          </button>
          <button
            onClick={() => setActiveTab('senha')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'senha'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Alterar Senha
          </button>
          <button
            onClick={() => setActiveTab('historico')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'historico'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Histórico
          </button>
        </nav>
      </div>

      {/* Dados Pessoais */}
      {activeTab === 'dados' && (
        <form onSubmit={handleSubmitProfile} className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
          <h3 className="text-lg font-medium text-gray-900">Informações Pessoais</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
              <input
                type="text"
                required
                value={formData.nome}
                onChange={(e) => handleInputChange('nome', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                type="email"
                required
                value={formData.email}
                onChange={(e) => handleInputChange('email', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <input
                type="tel"
                required
                value={formData.telefone}
                onChange={(e) => handleInputChange('telefone', formatPhone(e.target.value))}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">CPF</label>
              <input
                type="text"
                value={formData.cpf}
                onChange={(e) => handleInputChange('cpf', formatCPF(e.target.value))}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
              <input
                type="date"
                value={formData.data_nascimento}
                onChange={(e) => handleInputChange('data_nascimento', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Salvando...' : 'Salvar Alterações'}
            </button>
          </div>
        </form>
      )}

      {/* Endereço */}
      {activeTab === 'endereco' && (
        <form onSubmit={handleSubmitProfile} className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
          <h3 className="text-lg font-medium text-gray-900">Endereço</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">CEP</label>
              <input
                type="text"
                value={formData.endereco.cep}
                onChange={(e) => handleCEPChange(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="00000-000"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
              <input
                type="text"
                value={formData.endereco.logradouro}
                onChange={(e) => handleInputChange('endereco.logradouro', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Número</label>
              <input
                type="text"
                value={formData.endereco.numero}
                onChange={(e) => handleInputChange('endereco.numero', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
              <input
                type="text"
                value={formData.endereco.complemento}
                onChange={(e) => handleInputChange('endereco.complemento', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
              <input
                type="text"
                value={formData.endereco.bairro}
                onChange={(e) => handleInputChange('endereco.bairro', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
              <input
                type="text"
                value={formData.endereco.cidade}
                onChange={(e) => handleInputChange('endereco.cidade', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <input
                type="text"
                value={formData.endereco.estado}
                onChange={(e) => handleInputChange('endereco.estado', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Salvando...' : 'Salvar Endereço'}
            </button>
          </div>
        </form>
      )}

      {/* Preferências */}
      {activeTab === 'preferencias' && (
        <form onSubmit={handleSubmitProfile} className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
          <h3 className="text-lg font-medium text-gray-900">Preferências de Notificação</h3>
          
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">Notificações por Email</h4>
                <p className="text-sm text-gray-600">Receber confirmações e lembretes por email</p>
              </div>
              <input
                type="checkbox"
                checked={formData.preferencias.notificacoes_email}
                onChange={(e) => handlePreferenceChange('notificacoes_email', e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">Notificações por SMS</h4>
                <p className="text-sm text-gray-600">Receber lembretes por mensagem de texto</p>
              </div>
              <input
                type="checkbox"
                checked={formData.preferencias.notificacoes_sms}
                onChange={(e) => handlePreferenceChange('notificacoes_sms', e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">Promoções e Ofertas</h4>
                <p className="text-sm text-gray-600">Receber informações sobre promoções especiais</p>
              </div>
              <input
                type="checkbox"
                checked={formData.preferencias.promocoes}
                onChange={(e) => handlePreferenceChange('promocoes', e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-sm font-medium text-gray-900">Lembrete de Agendamento</h4>
                <p className="text-sm text-gray-600">Receber lembretes 24h antes do agendamento</p>
              </div>
              <input
                type="checkbox"
                checked={formData.preferencias.lembrete_agendamento}
                onChange={(e) => handlePreferenceChange('lembrete_agendamento', e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </div>
          </div>
          
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Salvando...' : 'Salvar Preferências'}
            </button>
          </div>
        </form>
      )}

      {/* Alterar Senha */}
      {activeTab === 'senha' && (
        <form onSubmit={handleSubmitPassword} className="bg-white p-6 rounded-lg shadow-sm border space-y-6">
          <h3 className="text-lg font-medium text-gray-900">Alterar Senha</h3>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Senha Atual</label>
              <input
                type="password"
                required
                value={passwordData.senha_atual}
                onChange={(e) => handlePasswordChange('senha_atual', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
              <input
                type="password"
                required
                minLength={6}
                value={passwordData.nova_senha}
                onChange={(e) => handlePasswordChange('nova_senha', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p className="text-xs text-gray-500 mt-1">Mínimo de 6 caracteres</p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha</label>
              <input
                type="password"
                required
                value={passwordData.confirmar_senha}
                onChange={(e) => handlePasswordChange('confirmar_senha', e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Alterando...' : 'Alterar Senha'}
            </button>
          </div>
        </form>
      )}

      {/* Histórico */}
      {activeTab === 'historico' && (
        <div className="bg-white rounded-lg shadow-sm border">
          <div className="p-6 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Histórico de Agendamentos</h3>
          </div>
          
          {historico.length === 0 ? (
            <div className="p-8 text-center">
              <div className="text-gray-400 text-4xl mb-4">📅</div>
              <h4 className="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento no histórico</h4>
              <p className="text-gray-600">Seus agendamentos concluídos aparecerão aqui.</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Data
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Salão
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Serviço
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Valor
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {historico.map((item) => (
                    <tr key={item.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {new Date(item.data).toLocaleDateString('pt-BR')}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {item.salao}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {item.servico}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        R$ {item.valor.toFixed(2)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          ✨ Concluído
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
    </div>
  )
}

export default Perfil