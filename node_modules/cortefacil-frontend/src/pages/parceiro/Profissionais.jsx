import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Profissionais = () => {
  const { user } = useAuth()
  const { formatPhone } = useApp()
  const [profissionais, setProfissionais] = useState([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [editingProfissional, setEditingProfissional] = useState(null)
  const [formData, setFormData] = useState({
    nome: '',
    email: '',
    telefone: '',
    especialidades: '',
    comissao: '',
    ativo: true
  })

  // Mock data para demonstração
  useEffect(() => {
    const mockProfissionais = [
      {
        id: 1,
        nome: 'Maria Santos',
        email: 'maria@email.com',
        telefone: '(11) 99999-9999',
        especialidades: ['Corte Feminino', 'Coloração', 'Escova'],
        comissao: 40,
        ativo: true,
        agendamentos_mes: 45,
        avaliacao: 4.8
      },
      {
        id: 2,
        nome: 'Carlos Oliveira',
        email: 'carlos@email.com',
        telefone: '(11) 88888-8888',
        especialidades: ['Corte Masculino', 'Barba', 'Bigode'],
        comissao: 35,
        ativo: true,
        agendamentos_mes: 38,
        avaliacao: 4.6
      },
      {
        id: 3,
        nome: 'Ana Costa',
        email: 'ana@email.com',
        telefone: '(11) 77777-7777',
        especialidades: ['Manicure', 'Pedicure', 'Esmaltação'],
        comissao: 30,
        ativo: false,
        agendamentos_mes: 0,
        avaliacao: 4.2
      }
    ]
    
    setTimeout(() => {
      setProfissionais(mockProfissionais)
      setLoading(false)
    }, 1000)
  }, [])

  const handleSubmit = (e) => {
    e.preventDefault()
    
    if (editingProfissional) {
      // Editar profissional existente
      setProfissionais(prev => 
        prev.map(prof => 
          prof.id === editingProfissional.id 
            ? { 
                ...prof, 
                ...formData,
                especialidades: formData.especialidades.split(',').map(e => e.trim())
              }
            : prof
        )
      )
      toast.success('Profissional atualizado com sucesso!')
    } else {
      // Adicionar novo profissional
      const novoProfissional = {
        id: Date.now(),
        ...formData,
        especialidades: formData.especialidades.split(',').map(e => e.trim()),
        agendamentos_mes: 0,
        avaliacao: 0
      }
      setProfissionais(prev => [...prev, novoProfissional])
      toast.success('Profissional adicionado com sucesso!')
    }
    
    setShowModal(false)
    setEditingProfissional(null)
    setFormData({
      nome: '',
      email: '',
      telefone: '',
      especialidades: '',
      comissao: '',
      ativo: true
    })
  }

  const handleEdit = (profissional) => {
    setEditingProfissional(profissional)
    setFormData({
      nome: profissional.nome,
      email: profissional.email,
      telefone: profissional.telefone,
      especialidades: profissional.especialidades.join(', '),
      comissao: profissional.comissao,
      ativo: profissional.ativo
    })
    setShowModal(true)
  }

  const handleToggleStatus = (profissionalId) => {
    setProfissionais(prev => 
      prev.map(prof => 
        prof.id === profissionalId 
          ? { ...prof, ativo: !prof.ativo }
          : prof
      )
    )
    toast.success('Status do profissional atualizado!')
  }

  const handleDelete = (profissionalId) => {
    if (window.confirm('Tem certeza que deseja remover este profissional?')) {
      setProfissionais(prev => prev.filter(prof => prof.id !== profissionalId))
      toast.success('Profissional removido com sucesso!')
    }
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
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Profissionais</h1>
          <p className="text-gray-600">Gerencie a equipe do seu salão</p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200"
        >
          Adicionar Profissional
        </button>
      </div>

      {/* Estatísticas */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Total de Profissionais</p>
              <p className="text-2xl font-bold text-gray-900">{profissionais.length}</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Profissionais Ativos</p>
              <p className="text-2xl font-bold text-gray-900">
                {profissionais.filter(p => p.ativo).length}
              </p>
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center">
            <div className="p-2 bg-yellow-100 rounded-lg">
              <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Avaliação Média</p>
              <p className="text-2xl font-bold text-gray-900">
                {profissionais.length > 0 
                  ? (profissionais.reduce((acc, p) => acc + p.avaliacao, 0) / profissionais.length).toFixed(1)
                  : '0.0'
                }
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Lista de Profissionais */}
      <div className="bg-white rounded-lg shadow-sm border">
        <div className="px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">Equipe</h3>
        </div>
        
        {profissionais.length === 0 ? (
          <div className="p-6 text-center text-gray-500">
            <p>Nenhum profissional cadastrado ainda.</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-200">
            {profissionais.map((profissional) => (
              <div key={profissional.id} className="p-6 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-4">
                      <div className="flex-shrink-0">
                        <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                          <span className="text-blue-600 font-medium text-lg">
                            {profissional.nome.charAt(0)}
                          </span>
                        </div>
                      </div>
                      <div className="flex-1">
                        <div className="flex items-center space-x-2">
                          <h4 className="text-lg font-medium text-gray-900">
                            {profissional.nome}
                          </h4>
                          <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                            profissional.ativo 
                              ? 'bg-green-100 text-green-800' 
                              : 'bg-red-100 text-red-800'
                          }`}>
                            {profissional.ativo ? 'Ativo' : 'Inativo'}
                          </span>
                        </div>
                        <p className="text-sm text-gray-600">{profissional.email}</p>
                        <p className="text-sm text-gray-600">{formatPhone(profissional.telefone)}</p>
                        <div className="mt-2">
                          <p className="text-sm text-gray-600">
                            <strong>Especialidades:</strong> {profissional.especialidades.join(', ')}
                          </p>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="text-sm text-gray-600">
                          <strong>Comissão:</strong> {profissional.comissao}%
                        </p>
                        <p className="text-sm text-gray-600">
                          <strong>Agendamentos/mês:</strong> {profissional.agendamentos_mes}
                        </p>
                        <p className="text-sm text-gray-600">
                          <strong>Avaliação:</strong> ⭐ {profissional.avaliacao}
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <div className="flex items-center space-x-2 ml-4">
                    <button
                      onClick={() => handleEdit(profissional)}
                      className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                      Editar
                    </button>
                    <button
                      onClick={() => handleToggleStatus(profissional.id)}
                      className={`text-sm font-medium ${
                        profissional.ativo 
                          ? 'text-red-600 hover:text-red-800' 
                          : 'text-green-600 hover:text-green-800'
                      }`}
                    >
                      {profissional.ativo ? 'Desativar' : 'Ativar'}
                    </button>
                    <button
                      onClick={() => handleDelete(profissional.id)}
                      className="text-red-600 hover:text-red-800 text-sm font-medium"
                    >
                      Remover
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {editingProfissional ? 'Editar Profissional' : 'Adicionar Profissional'}
            </h3>
            
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input
                  type="text"
                  required
                  value={formData.nome}
                  onChange={(e) => setFormData(prev => ({ ...prev, nome: e.target.value }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  type="email"
                  required
                  value={formData.email}
                  onChange={(e) => setFormData(prev => ({ ...prev, email: e.target.value }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input
                  type="tel"
                  required
                  value={formData.telefone}
                  onChange={(e) => setFormData(prev => ({ ...prev, telefone: e.target.value }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Especialidades (separadas por vírgula)
                </label>
                <input
                  type="text"
                  required
                  placeholder="Ex: Corte Masculino, Barba, Bigode"
                  value={formData.especialidades}
                  onChange={(e) => setFormData(prev => ({ ...prev, especialidades: e.target.value }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Comissão (%)</label>
                <input
                  type="number"
                  required
                  min="0"
                  max="100"
                  value={formData.comissao}
                  onChange={(e) => setFormData(prev => ({ ...prev, comissao: parseInt(e.target.value) }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="ativo"
                  checked={formData.ativo}
                  onChange={(e) => setFormData(prev => ({ ...prev, ativo: e.target.checked }))}
                  className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label htmlFor="ativo" className="ml-2 block text-sm text-gray-900">
                  Profissional ativo
                </label>
              </div>
              
              <div className="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowModal(false)
                    setEditingProfissional(null)
                    setFormData({
                      nome: '',
                      email: '',
                      telefone: '',
                      especialidades: '',
                      comissao: '',
                      ativo: true
                    })
                  }}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                >
                  {editingProfissional ? 'Atualizar' : 'Adicionar'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}

export default Profissionais