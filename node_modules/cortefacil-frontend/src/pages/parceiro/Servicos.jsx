import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { toast } from 'react-toastify'

const Servicos = () => {
  const { user } = useAuth()
  const [servicos, setServicos] = useState([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [editingServico, setEditingServico] = useState(null)
  const [formData, setFormData] = useState({
    nome: '',
    descricao: '',
    preco: '',
    duracao: '',
    categoria: '',
    ativo: true
  })
  const [searchTerm, setSearchTerm] = useState('')
  const [filterCategoria, setFilterCategoria] = useState('')

  // Mock data para demonstração
  useEffect(() => {
    const mockServicos = [
      {
        id: 1,
        nome: 'Corte Masculino',
        descricao: 'Corte tradicional masculino com acabamento',
        preco: 25.00,
        duracao: 30,
        categoria: 'Cortes',
        ativo: true,
        created_at: '2024-01-15'
      },
      {
        id: 2,
        nome: 'Corte Feminino',
        descricao: 'Corte feminino com lavagem e finalização',
        preco: 35.00,
        duracao: 45,
        categoria: 'Cortes',
        ativo: true,
        created_at: '2024-01-15'
      },
      {
        id: 3,
        nome: 'Coloração Completa',
        descricao: 'Coloração completa com produtos de qualidade',
        preco: 80.00,
        duracao: 120,
        categoria: 'Coloração',
        ativo: true,
        created_at: '2024-01-16'
      },
      {
        id: 4,
        nome: 'Mechas',
        descricao: 'Mechas tradicionais ou californianas',
        preco: 60.00,
        duracao: 90,
        categoria: 'Coloração',
        ativo: true,
        created_at: '2024-01-16'
      },
      {
        id: 5,
        nome: 'Escova Progressiva',
        descricao: 'Alisamento com escova progressiva',
        preco: 120.00,
        duracao: 180,
        categoria: 'Tratamentos',
        ativo: false,
        created_at: '2024-01-17'
      },
      {
        id: 6,
        nome: 'Hidratação',
        descricao: 'Hidratação profunda para cabelos ressecados',
        preco: 40.00,
        duracao: 60,
        categoria: 'Tratamentos',
        ativo: true,
        created_at: '2024-01-17'
      }
    ]
    
    setTimeout(() => {
      setServicos(mockServicos)
      setLoading(false)
    }, 1000)
  }, [])

  const categorias = ['Cortes', 'Coloração', 'Tratamentos', 'Penteados', 'Barba', 'Sobrancelha']

  const filteredServicos = servicos.filter(servico => {
    const matchesSearch = servico.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         servico.descricao.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesCategoria = !filterCategoria || servico.categoria === filterCategoria
    return matchesSearch && matchesCategoria
  })

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    try {
      const servicoData = {
        ...formData,
        preco: parseFloat(formData.preco),
        duracao: parseInt(formData.duracao)
      }
      
      if (editingServico) {
        // Atualizar serviço existente
        setServicos(prev => prev.map(servico => 
          servico.id === editingServico.id 
            ? { ...servico, ...servicoData }
            : servico
        ))
        toast.success('Serviço atualizado com sucesso!')
      } else {
        // Criar novo serviço
        const novoServico = {
          id: Date.now(),
          ...servicoData,
          created_at: new Date().toISOString().split('T')[0]
        }
        setServicos(prev => [novoServico, ...prev])
        toast.success('Serviço criado com sucesso!')
      }
      
      handleCloseModal()
    } catch (error) {
      toast.error('Erro ao salvar serviço')
    }
  }

  const handleEdit = (servico) => {
    setEditingServico(servico)
    setFormData({
      nome: servico.nome,
      descricao: servico.descricao,
      preco: servico.preco.toString(),
      duracao: servico.duracao.toString(),
      categoria: servico.categoria,
      ativo: servico.ativo
    })
    setShowModal(true)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Tem certeza que deseja excluir este serviço?')) {
      try {
        setServicos(prev => prev.filter(servico => servico.id !== id))
        toast.success('Serviço excluído com sucesso!')
      } catch (error) {
        toast.error('Erro ao excluir serviço')
      }
    }
  }

  const handleToggleStatus = async (id) => {
    try {
      setServicos(prev => prev.map(servico => 
        servico.id === id 
          ? { ...servico, ativo: !servico.ativo }
          : servico
      ))
      toast.success('Status do serviço atualizado!')
    } catch (error) {
      toast.error('Erro ao atualizar status do serviço')
    }
  }

  const handleCloseModal = () => {
    setShowModal(false)
    setEditingServico(null)
    setFormData({
      nome: '',
      descricao: '',
      preco: '',
      duracao: '',
      categoria: '',
      ativo: true
    })
  }

  const formatDuration = (minutes) => {
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    if (hours > 0) {
      return `${hours}h${mins > 0 ? ` ${mins}min` : ''}`
    }
    return `${mins}min`
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
          <h1 className="text-2xl font-bold text-gray-900">Serviços</h1>
          <p className="text-gray-600">Gerencie os serviços oferecidos pelo seu salão</p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        >
          + Novo Serviço
        </button>
      </div>

      {/* Filtros */}
      <div className="bg-white p-4 rounded-lg shadow-sm border">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="flex-1">
            <input
              type="text"
              placeholder="Buscar serviços..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <select
              value={filterCategoria}
              onChange={(e) => setFilterCategoria(e.target.value)}
              className="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Todas as categorias</option>
              {categorias.map(categoria => (
                <option key={categoria} value={categoria}>{categoria}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Lista de Serviços */}
      <div className="bg-white rounded-lg shadow-sm border overflow-hidden">
        {filteredServicos.length === 0 ? (
          <div className="p-8 text-center">
            <div className="text-gray-400 text-4xl mb-4">🔍</div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Nenhum serviço encontrado</h3>
            <p className="text-gray-600">Tente ajustar os filtros ou criar um novo serviço.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Serviço
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Categoria
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Preço
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Duração
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredServicos.map((servico) => (
                  <tr key={servico.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{servico.nome}</div>
                        <div className="text-sm text-gray-500">{servico.descricao}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {servico.categoria}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      R$ {servico.preco.toFixed(2)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {formatDuration(servico.duracao)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <button
                        onClick={() => handleToggleStatus(servico.id)}
                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          servico.ativo
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                        }`}
                      >
                        {servico.ativo ? 'Ativo' : 'Inativo'}
                      </button>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex justify-end space-x-2">
                        <button
                          onClick={() => handleEdit(servico)}
                          className="text-blue-600 hover:text-blue-900"
                        >
                          Editar
                        </button>
                        <button
                          onClick={() => handleDelete(servico.id)}
                          className="text-red-600 hover:text-red-900"
                        >
                          Excluir
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                {editingServico ? 'Editar Serviço' : 'Novo Serviço'}
              </h3>
              
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço</label>
                  <input
                    type="text"
                    required
                    value={formData.nome}
                    onChange={(e) => handleInputChange('nome', e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                  <textarea
                    rows={3}
                    value={formData.descricao}
                    onChange={(e) => handleInputChange('descricao', e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      value={formData.preco}
                      onChange={(e) => handleInputChange('preco', e.target.value)}
                      className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Duração (min)</label>
                    <input
                      type="number"
                      min="1"
                      required
                      value={formData.duracao}
                      onChange={(e) => handleInputChange('duracao', e.target.value)}
                      className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                  <select
                    required
                    value={formData.categoria}
                    onChange={(e) => handleInputChange('categoria', e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Selecione uma categoria</option>
                    {categorias.map(categoria => (
                      <option key={categoria} value={categoria}>{categoria}</option>
                    ))}
                  </select>
                </div>
                
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    id="ativo"
                    checked={formData.ativo}
                    onChange={(e) => handleInputChange('ativo', e.target.checked)}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label htmlFor="ativo" className="ml-2 block text-sm text-gray-900">
                    Serviço ativo
                  </label>
                </div>
                
                <div className="flex justify-end space-x-3 pt-4">
                  <button
                    type="button"
                    onClick={handleCloseModal}
                    className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                  >
                    {editingServico ? 'Atualizar' : 'Criar'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Servicos