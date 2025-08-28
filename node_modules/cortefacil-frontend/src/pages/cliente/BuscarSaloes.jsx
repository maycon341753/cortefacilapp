import React, { useState, useEffect } from 'react'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const BuscarSaloes = () => {
  const { formatPhone } = useApp()
  const [loading, setLoading] = useState(false)
  const [saloes, setSaloes] = useState([])
  const [filteredSaloes, setFilteredSaloes] = useState([])
  const [filters, setFilters] = useState({
    busca: '',
    cidade: '',
    servico: '',
    avaliacao: '',
    ordenacao: 'relevancia'
  })
  const [selectedSalao, setSelectedSalao] = useState(null)
  const [showModal, setShowModal] = useState(false)

  // Mock data dos salões
  useEffect(() => {
    const mockSaloes = [
      {
        id: 1,
        nome: 'Salão Beleza & Estilo',
        descricao: 'Especializado em cortes femininos e coloração',
        endereco: {
          logradouro: 'Rua das Flores, 123',
          bairro: 'Centro',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 99999-9999',
        email: 'contato@belezaestilo.com',
        avaliacao: 4.8,
        total_avaliacoes: 127,
        foto: 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400',
        horario_funcionamento: {
          segunda: '09:00-18:00',
          terca: '09:00-18:00',
          quarta: '09:00-18:00',
          quinta: '09:00-18:00',
          sexta: '09:00-19:00',
          sabado: '08:00-17:00',
          domingo: 'Fechado'
        },
        servicos: [
          { nome: 'Corte Feminino', preco: 35.00, duracao: 60 },
          { nome: 'Coloração', preco: 80.00, duracao: 120 },
          { nome: 'Hidratação', preco: 40.00, duracao: 45 },
          { nome: 'Escova', preco: 25.00, duracao: 30 }
        ],
        distancia: 2.5,
        ativo: true
      },
      {
        id: 2,
        nome: 'Studio Hair',
        descricao: 'Cortes modernos e tratamentos capilares',
        endereco: {
          logradouro: 'Av. Paulista, 456',
          bairro: 'Bela Vista',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 88888-8888',
        email: 'contato@studiohair.com',
        avaliacao: 4.6,
        total_avaliacoes: 89,
        foto: 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?w=400',
        horario_funcionamento: {
          segunda: '10:00-19:00',
          terca: '10:00-19:00',
          quarta: '10:00-19:00',
          quinta: '10:00-19:00',
          sexta: '10:00-20:00',
          sabado: '09:00-18:00',
          domingo: 'Fechado'
        },
        servicos: [
          { nome: 'Corte Masculino', preco: 30.00, duracao: 45 },
          { nome: 'Barba', preco: 20.00, duracao: 30 },
          { nome: 'Corte + Barba', preco: 45.00, duracao: 75 },
          { nome: 'Tratamento Capilar', preco: 60.00, duracao: 90 }
        ],
        distancia: 1.8,
        ativo: true
      },
      {
        id: 3,
        nome: 'Espaço Zen Beauty',
        descricao: 'Ambiente relaxante com foco em bem-estar',
        endereco: {
          logradouro: 'Rua Augusta, 789',
          bairro: 'Consolação',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 77777-7777',
        email: 'contato@espacozen.com',
        avaliacao: 4.9,
        total_avaliacoes: 156,
        foto: 'https://images.unsplash.com/photo-1562322140-8baeececf3df?w=400',
        horario_funcionamento: {
          segunda: '08:00-18:00',
          terca: '08:00-18:00',
          quarta: '08:00-18:00',
          quinta: '08:00-18:00',
          sexta: '08:00-19:00',
          sabado: '08:00-17:00',
          domingo: '09:00-15:00'
        },
        servicos: [
          { nome: 'Massagem Capilar', preco: 50.00, duracao: 60 },
          { nome: 'Reflexologia', preco: 70.00, duracao: 90 },
          { nome: 'Limpeza de Pele', preco: 80.00, duracao: 120 },
          { nome: 'Manicure', preco: 25.00, duracao: 45 }
        ],
        distancia: 3.2,
        ativo: true
      },
      {
        id: 4,
        nome: 'Barbearia Clássica',
        descricao: 'Tradição em cortes masculinos desde 1985',
        endereco: {
          logradouro: 'Rua da Consolação, 321',
          bairro: 'República',
          cidade: 'São Paulo',
          estado: 'SP'
        },
        telefone: '(11) 66666-6666',
        email: 'contato@barbeariaclassica.com',
        avaliacao: 4.7,
        total_avaliacoes: 203,
        foto: 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=400',
        horario_funcionamento: {
          segunda: '09:00-19:00',
          terca: '09:00-19:00',
          quarta: '09:00-19:00',
          quinta: '09:00-19:00',
          sexta: '09:00-20:00',
          sabado: '08:00-18:00',
          domingo: 'Fechado'
        },
        servicos: [
          { nome: 'Corte Clássico', preco: 25.00, duracao: 30 },
          { nome: 'Barba Tradicional', preco: 18.00, duracao: 25 },
          { nome: 'Bigode', preco: 10.00, duracao: 15 },
          { nome: 'Pacote Completo', preco: 40.00, duracao: 60 }
        ],
        distancia: 4.1,
        ativo: true
      }
    ]
    
    setSaloes(mockSaloes)
    setFilteredSaloes(mockSaloes)
  }, [])

  // Aplicar filtros
  useEffect(() => {
    let filtered = [...saloes]

    // Filtro por busca (nome ou descrição)
    if (filters.busca) {
      filtered = filtered.filter(salao => 
        salao.nome.toLowerCase().includes(filters.busca.toLowerCase()) ||
        salao.descricao.toLowerCase().includes(filters.busca.toLowerCase())
      )
    }

    // Filtro por cidade
    if (filters.cidade) {
      filtered = filtered.filter(salao => 
        salao.endereco.cidade.toLowerCase().includes(filters.cidade.toLowerCase())
      )
    }

    // Filtro por serviço
    if (filters.servico) {
      filtered = filtered.filter(salao => 
        salao.servicos.some(servico => 
          servico.nome.toLowerCase().includes(filters.servico.toLowerCase())
        )
      )
    }

    // Filtro por avaliação
    if (filters.avaliacao) {
      const minAvaliacao = parseFloat(filters.avaliacao)
      filtered = filtered.filter(salao => salao.avaliacao >= minAvaliacao)
    }

    // Ordenação
    switch (filters.ordenacao) {
      case 'avaliacao':
        filtered.sort((a, b) => b.avaliacao - a.avaliacao)
        break
      case 'distancia':
        filtered.sort((a, b) => a.distancia - b.distancia)
        break
      case 'preco_menor':
        filtered.sort((a, b) => {
          const menorPrecoA = Math.min(...a.servicos.map(s => s.preco))
          const menorPrecoB = Math.min(...b.servicos.map(s => s.preco))
          return menorPrecoA - menorPrecoB
        })
        break
      case 'preco_maior':
        filtered.sort((a, b) => {
          const maiorPrecoA = Math.max(...a.servicos.map(s => s.preco))
          const maiorPrecoB = Math.max(...b.servicos.map(s => s.preco))
          return maiorPrecoB - maiorPrecoA
        })
        break
      default: // relevancia
        filtered.sort((a, b) => {
          const scoreA = (a.avaliacao * 0.4) + ((5 - a.distancia) * 0.3) + (a.total_avaliacoes * 0.3)
          const scoreB = (b.avaliacao * 0.4) + ((5 - b.distancia) * 0.3) + (b.total_avaliacoes * 0.3)
          return scoreB - scoreA
        })
    }

    setFilteredSaloes(filtered)
  }, [filters, saloes])

  const handleFilterChange = (field, value) => {
    setFilters(prev => ({
      ...prev,
      [field]: value
    }))
  }

  const clearFilters = () => {
    setFilters({
      busca: '',
      cidade: '',
      servico: '',
      avaliacao: '',
      ordenacao: 'relevancia'
    })
  }

  const handleVerDetalhes = (salao) => {
    setSelectedSalao(salao)
    setShowModal(true)
  }

  const handleAgendar = (salao) => {
    toast.info(`Redirecionando para agendamento no ${salao.nome}`)
    // Aqui seria implementada a navegação para a página de agendamento
  }

  const renderStars = (rating) => {
    const stars = []
    const fullStars = Math.floor(rating)
    const hasHalfStar = rating % 1 !== 0

    for (let i = 0; i < fullStars; i++) {
      stars.push(
        <svg key={i} className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
          <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
        </svg>
      )
    }

    if (hasHalfStar) {
      stars.push(
        <svg key="half" className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
          <defs>
            <linearGradient id="half">
              <stop offset="50%" stopColor="currentColor"/>
              <stop offset="50%" stopColor="#e5e7eb"/>
            </linearGradient>
          </defs>
          <path fill="url(#half)" d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
        </svg>
      )
    }

    const emptyStars = 5 - Math.ceil(rating)
    for (let i = 0; i < emptyStars; i++) {
      stars.push(
        <svg key={`empty-${i}`} className="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
          <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
        </svg>
      )
    }

    return stars
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Buscar Salões</h1>
        <p className="text-gray-600">Encontre o salão perfeito para você</p>
      </div>

      {/* Filtros */}
      <div className="bg-white p-6 rounded-lg shadow-sm border">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              type="text"
              placeholder="Nome ou descrição..."
              value={filters.busca}
              onChange={(e) => handleFilterChange('busca', e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
            <input
              type="text"
              placeholder="Cidade..."
              value={filters.cidade}
              onChange={(e) => handleFilterChange('cidade', e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Serviço</label>
            <input
              type="text"
              placeholder="Tipo de serviço..."
              value={filters.servico}
              onChange={(e) => handleFilterChange('servico', e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Avaliação Mínima</label>
            <select
              value={filters.avaliacao}
              onChange={(e) => handleFilterChange('avaliacao', e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Todas</option>
              <option value="4.5">4.5+ estrelas</option>
              <option value="4.0">4.0+ estrelas</option>
              <option value="3.5">3.5+ estrelas</option>
              <option value="3.0">3.0+ estrelas</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
            <select
              value={filters.ordenacao}
              onChange={(e) => handleFilterChange('ordenacao', e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="relevancia">Relevância</option>
              <option value="avaliacao">Melhor Avaliação</option>
              <option value="distancia">Mais Próximo</option>
              <option value="preco_menor">Menor Preço</option>
              <option value="preco_maior">Maior Preço</option>
            </select>
          </div>
          
          <div className="flex items-end">
            <button
              onClick={clearFilters}
              className="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
            >
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>

      {/* Resultados */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <p className="text-sm text-gray-600">
            {filteredSaloes.length} salão{filteredSaloes.length !== 1 ? 'ões' : ''} encontrado{filteredSaloes.length !== 1 ? 's' : ''}
          </p>
        </div>

        {filteredSaloes.length === 0 ? (
          <div className="bg-white p-8 rounded-lg shadow-sm border text-center">
            <div className="text-gray-400 text-4xl mb-4">🔍</div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Nenhum salão encontrado</h3>
            <p className="text-gray-600">Tente ajustar os filtros para encontrar mais resultados.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredSaloes.map((salao) => (
              <div key={salao.id} className="bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-shadow">
                <div className="relative h-48">
                  <img
                    src={salao.foto}
                    alt={salao.nome}
                    className="w-full h-full object-cover"
                  />
                  <div className="absolute top-2 right-2 bg-white px-2 py-1 rounded-full text-xs font-medium">
                    📍 {salao.distancia}km
                  </div>
                </div>
                
                <div className="p-4">
                  <div className="flex items-start justify-between mb-2">
                    <h3 className="text-lg font-semibold text-gray-900">{salao.nome}</h3>
                    <div className="flex items-center space-x-1">
                      {renderStars(salao.avaliacao)}
                      <span className="text-sm text-gray-600 ml-1">
                        {salao.avaliacao} ({salao.total_avaliacoes})
                      </span>
                    </div>
                  </div>
                  
                  <p className="text-sm text-gray-600 mb-3">{salao.descricao}</p>
                  
                  <div className="flex items-center text-sm text-gray-600 mb-2">
                    <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {salao.endereco.logradouro}, {salao.endereco.bairro}
                  </div>
                  
                  <div className="flex items-center text-sm text-gray-600 mb-4">
                    <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {formatPhone(salao.telefone)}
                  </div>
                  
                  <div className="mb-4">
                    <p className="text-sm font-medium text-gray-700 mb-2">Serviços populares:</p>
                    <div className="flex flex-wrap gap-1">
                      {salao.servicos.slice(0, 3).map((servico, index) => (
                        <span key={index} className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          {servico.nome} - R$ {servico.preco.toFixed(2)}
                        </span>
                      ))}
                      {salao.servicos.length > 3 && (
                        <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                          +{salao.servicos.length - 3} mais
                        </span>
                      )}
                    </div>
                  </div>
                  
                  <div className="flex space-x-2">
                    <button
                      onClick={() => handleVerDetalhes(salao)}
                      className="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                    >
                      Ver Detalhes
                    </button>
                    <button
                      onClick={() => handleAgendar(salao)}
                      className="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                    >
                      Agendar
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Modal de Detalhes */}
      {showModal && selectedSalao && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-xl font-bold text-gray-900">{selectedSalao.nome}</h2>
                <button
                  onClick={() => setShowModal(false)}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div className="mb-4">
                <img
                  src={selectedSalao.foto}
                  alt={selectedSalao.nome}
                  className="w-full h-64 object-cover rounded-lg"
                />
              </div>
              
              <div className="space-y-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">Sobre</h3>
                  <p className="text-gray-600">{selectedSalao.descricao}</p>
                </div>
                
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">Contato</h3>
                  <div className="space-y-2">
                    <p className="text-gray-600">
                      📍 {selectedSalao.endereco.logradouro}, {selectedSalao.endereco.bairro}, {selectedSalao.endereco.cidade} - {selectedSalao.endereco.estado}
                    </p>
                    <p className="text-gray-600">📞 {formatPhone(selectedSalao.telefone)}</p>
                    <p className="text-gray-600">✉️ {selectedSalao.email}</p>
                  </div>
                </div>
                
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">Horário de Funcionamento</h3>
                  <div className="grid grid-cols-2 gap-2 text-sm">
                    <div className="flex justify-between">
                      <span>Segunda:</span>
                      <span>{selectedSalao.horario_funcionamento.segunda}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Terça:</span>
                      <span>{selectedSalao.horario_funcionamento.terca}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Quarta:</span>
                      <span>{selectedSalao.horario_funcionamento.quarta}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Quinta:</span>
                      <span>{selectedSalao.horario_funcionamento.quinta}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Sexta:</span>
                      <span>{selectedSalao.horario_funcionamento.sexta}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Sábado:</span>
                      <span>{selectedSalao.horario_funcionamento.sabado}</span>
                    </div>
                    <div className="flex justify-between col-span-2">
                      <span>Domingo:</span>
                      <span>{selectedSalao.horario_funcionamento.domingo}</span>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-2">Serviços</h3>
                  <div className="space-y-2">
                    {selectedSalao.servicos.map((servico, index) => (
                      <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                          <p className="font-medium text-gray-900">{servico.nome}</p>
                          <p className="text-sm text-gray-600">{servico.duracao} minutos</p>
                        </div>
                        <p className="font-semibold text-blue-600">R$ {servico.preco.toFixed(2)}</p>
                      </div>
                    ))}
                  </div>
                </div>
                
                <div className="flex space-x-3 pt-4">
                  <button
                    onClick={() => setShowModal(false)}
                    className="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                  >
                    Fechar
                  </button>
                  <button
                    onClick={() => {
                      handleAgendar(selectedSalao)
                      setShowModal(false)
                    }}
                    className="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                  >
                    Agendar Agora
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default BuscarSaloes