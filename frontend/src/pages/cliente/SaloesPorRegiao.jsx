import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'

const SaloesPorRegiao = () => {
  const { user } = useAuth()
  const [saloes, setSaloes] = useState([])
  const [loading, setLoading] = useState(true)
  const [filtros, setFiltros] = useState({
    regiao: '',
    cidade: '',
    bairro: '',
    servico: '',
    ordenacao: 'distancia'
  })
  const [busca, setBusca] = useState('')

  // Dados mockados para demonstração
  const saloesMock = [
    {
      id: 1,
      nome: 'Salão Elegance',
      endereco: 'Rua das Flores, 123 - Centro',
      cidade: 'São Paulo',
      bairro: 'Centro',
      regiao: 'Centro',
      telefone: '(11) 3333-4444',
      avaliacao: 4.8,
      totalAvaliacoes: 127,
      distancia: '0.5 km',
      imagem: '/api/placeholder/300/200',
      servicos: ['Corte Masculino', 'Barba', 'Sobrancelha'],
      precoMinimo: 25,
      precoMaximo: 80,
      horarioFuncionamento: 'Seg-Sex: 8h-18h | Sáb: 8h-16h',
      whatsapp: '11999887766'
    },
    {
      id: 2,
      nome: 'Barbearia Moderna',
      endereco: 'Av. Paulista, 456 - Bela Vista',
      cidade: 'São Paulo',
      bairro: 'Bela Vista',
      regiao: 'Centro',
      telefone: '(11) 2222-3333',
      avaliacao: 4.6,
      totalAvaliacoes: 89,
      distancia: '1.2 km',
      imagem: '/api/placeholder/300/200',
      servicos: ['Corte Masculino', 'Barba', 'Bigode'],
      precoMinimo: 30,
      precoMaximo: 60,
      horarioFuncionamento: 'Seg-Sáb: 9h-19h',
      whatsapp: '11988776655'
    },
    {
      id: 3,
      nome: 'Studio Hair & Beauty',
      endereco: 'Rua Augusta, 789 - Consolação',
      cidade: 'São Paulo',
      bairro: 'Consolação',
      regiao: 'Centro',
      telefone: '(11) 4444-5555',
      avaliacao: 4.9,
      totalAvaliacoes: 203,
      distancia: '2.1 km',
      imagem: '/api/placeholder/300/200',
      servicos: ['Corte Feminino', 'Coloração', 'Escova', 'Manicure'],
      precoMinimo: 40,
      precoMaximo: 150,
      horarioFuncionamento: 'Ter-Sáb: 9h-20h',
      whatsapp: '11977665544'
    },
    {
      id: 4,
      nome: 'Salão Beleza Pura',
      endereco: 'Rua da Liberdade, 321 - Liberdade',
      cidade: 'São Paulo',
      bairro: 'Liberdade',
      regiao: 'Centro',
      telefone: '(11) 5555-6666',
      avaliacao: 4.7,
      totalAvaliacoes: 156,
      distancia: '3.5 km',
      imagem: '/api/placeholder/300/200',
      servicos: ['Corte Feminino', 'Tratamentos', 'Maquiagem'],
      precoMinimo: 35,
      precoMaximo: 120,
      horarioFuncionamento: 'Seg-Sáb: 8h-18h',
      whatsapp: '11966554433'
    }
  ]

  useEffect(() => {
    // Simular carregamento de dados
    setTimeout(() => {
      setSaloes(saloesMock)
      setLoading(false)
    }, 1000)
  }, [])

  const handleFiltroChange = (campo, valor) => {
    setFiltros(prev => ({
      ...prev,
      [campo]: valor
    }))
  }

  const saloesFiltrados = saloes.filter(salao => {
    const matchBusca = !busca || 
      salao.nome.toLowerCase().includes(busca.toLowerCase()) ||
      salao.bairro.toLowerCase().includes(busca.toLowerCase()) ||
      salao.servicos.some(servico => servico.toLowerCase().includes(busca.toLowerCase()))
    
    const matchRegiao = !filtros.regiao || salao.regiao === filtros.regiao
    const matchCidade = !filtros.cidade || salao.cidade === filtros.cidade
    const matchBairro = !filtros.bairro || salao.bairro === filtros.bairro
    
    return matchBusca && matchRegiao && matchCidade && matchBairro
  })

  const renderEstrelas = (avaliacao) => {
    const estrelas = []
    const estrelasCompletas = Math.floor(avaliacao)
    const temMeiaEstrela = avaliacao % 1 !== 0
    
    for (let i = 0; i < estrelasCompletas; i++) {
      estrelas.push(<i key={i} className="fas fa-star text-yellow-400"></i>)
    }
    
    if (temMeiaEstrela) {
      estrelas.push(<i key="meia" className="fas fa-star-half-alt text-yellow-400"></i>)
    }
    
    const estrelasVazias = 5 - Math.ceil(avaliacao)
    for (let i = 0; i < estrelasVazias; i++) {
      estrelas.push(<i key={`vazia-${i}`} className="far fa-star text-gray-300"></i>)
    }
    
    return estrelas
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Carregando salões...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="container mx-auto px-4 py-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
              <h1 className="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                Salões por Região
              </h1>
              <p className="text-gray-600">
                Encontre os melhores salões próximos a você
              </p>
            </div>
            <div className="mt-4 md:mt-0">
              <Link 
                to="/cliente/dashboard" 
                className="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors"
              >
                <i className="fas fa-arrow-left mr-2"></i>
                Voltar ao Dashboard
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="flex flex-col lg:flex-row gap-6">
          {/* Sidebar de Filtros */}
          <div className="lg:w-1/4">
            <div className="bg-white rounded-lg shadow-sm p-6 sticky top-6">
              <h3 className="text-lg font-semibold mb-4 text-gray-800">
                <i className="fas fa-filter mr-2 text-blue-600"></i>
                Filtros
              </h3>
              
              {/* Busca */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Buscar
                </label>
                <div className="relative">
                  <input
                    type="text"
                    value={busca}
                    onChange={(e) => setBusca(e.target.value)}
                    placeholder="Nome, bairro ou serviço..."
                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <i className="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
              </div>

              {/* Região */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Região
                </label>
                <select
                  value={filtros.regiao}
                  onChange={(e) => handleFiltroChange('regiao', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Todas as regiões</option>
                  <option value="Centro">Centro</option>
                  <option value="Zona Norte">Zona Norte</option>
                  <option value="Zona Sul">Zona Sul</option>
                  <option value="Zona Leste">Zona Leste</option>
                  <option value="Zona Oeste">Zona Oeste</option>
                </select>
              </div>

              {/* Cidade */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Cidade
                </label>
                <select
                  value={filtros.cidade}
                  onChange={(e) => handleFiltroChange('cidade', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Todas as cidades</option>
                  <option value="São Paulo">São Paulo</option>
                  <option value="Guarulhos">Guarulhos</option>
                  <option value="Osasco">Osasco</option>
                  <option value="Santo André">Santo André</option>
                </select>
              </div>

              {/* Bairro */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Bairro
                </label>
                <select
                  value={filtros.bairro}
                  onChange={(e) => handleFiltroChange('bairro', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Todos os bairros</option>
                  <option value="Centro">Centro</option>
                  <option value="Bela Vista">Bela Vista</option>
                  <option value="Consolação">Consolação</option>
                  <option value="Liberdade">Liberdade</option>
                </select>
              </div>

              {/* Ordenação */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Ordenar por
                </label>
                <select
                  value={filtros.ordenacao}
                  onChange={(e) => handleFiltroChange('ordenacao', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="distancia">Distância</option>
                  <option value="avaliacao">Melhor avaliação</option>
                  <option value="preco">Menor preço</option>
                  <option value="nome">Nome A-Z</option>
                </select>
              </div>

              {/* Botão Limpar Filtros */}
              <button
                onClick={() => {
                  setFiltros({
                    regiao: '',
                    cidade: '',
                    bairro: '',
                    servico: '',
                    ordenacao: 'distancia'
                  })
                  setBusca('')
                }}
                className="w-full px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <i className="fas fa-times mr-2"></i>
                Limpar Filtros
              </button>
            </div>
          </div>

          {/* Lista de Salões */}
          <div className="lg:w-3/4">
            <div className="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <p className="text-gray-600 mb-2 sm:mb-0">
                {saloesFiltrados.length} salão(ões) encontrado(s)
              </p>
              <div className="flex items-center space-x-2">
                <span className="text-sm text-gray-500">Visualização:</span>
                <button className="p-2 text-blue-600 bg-blue-50 rounded-lg">
                  <i className="fas fa-th-large"></i>
                </button>
                <button className="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <i className="fas fa-list"></i>
                </button>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {saloesFiltrados.map(salao => (
                <div key={salao.id} className="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                  {/* Imagem */}
                  <div className="relative h-48 bg-gray-200">
                    <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    <div className="absolute top-3 right-3">
                      <span className="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                        {salao.distancia}
                      </span>
                    </div>
                    <div className="absolute bottom-3 left-3">
                      <div className="flex items-center space-x-1">
                        {renderEstrelas(salao.avaliacao)}
                        <span className="text-white text-sm font-medium ml-2">
                          {salao.avaliacao} ({salao.totalAvaliacoes})
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Conteúdo */}
                  <div className="p-4">
                    <div className="flex items-start justify-between mb-2">
                      <h3 className="text-lg font-semibold text-gray-800">
                        {salao.nome}
                      </h3>
                      <button className="text-gray-400 hover:text-red-500 transition-colors">
                        <i className="far fa-heart"></i>
                      </button>
                    </div>
                    
                    <div className="flex items-center text-gray-600 mb-2">
                      <i className="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                      <span className="text-sm">{salao.endereco}</span>
                    </div>
                    
                    <div className="flex items-center text-gray-600 mb-3">
                      <i className="fas fa-clock mr-2 text-blue-600"></i>
                      <span className="text-sm">{salao.horarioFuncionamento}</span>
                    </div>

                    {/* Serviços */}
                    <div className="mb-3">
                      <div className="flex flex-wrap gap-1">
                        {salao.servicos.slice(0, 3).map((servico, index) => (
                          <span 
                            key={index}
                            className="px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded-full"
                          >
                            {servico}
                          </span>
                        ))}
                        {salao.servicos.length > 3 && (
                          <span className="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                            +{salao.servicos.length - 3}
                          </span>
                        )}
                      </div>
                    </div>

                    {/* Preços */}
                    <div className="flex items-center justify-between mb-4">
                      <div className="text-sm text-gray-600">
                        <span className="font-medium text-green-600">
                          R$ {salao.precoMinimo} - R$ {salao.precoMaximo}
                        </span>
                      </div>
                      <div className="flex items-center space-x-2">
                        <a 
                          href={`https://wa.me/${salao.whatsapp}`}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="WhatsApp"
                        >
                          <i className="fab fa-whatsapp"></i>
                        </a>
                        <a 
                          href={`tel:${salao.telefone}`}
                          className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                          title="Ligar"
                        >
                          <i className="fas fa-phone"></i>
                        </a>
                      </div>
                    </div>

                    {/* Botões de Ação */}
                    <div className="flex space-x-2">
                      <Link 
                        to={`/cliente/agendar?salao=${salao.id}`}
                        className="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                      >
                        <i className="fas fa-calendar-plus mr-2"></i>
                        Agendar
                      </Link>
                      <Link 
                        to={`/cliente/salao/${salao.id}`}
                        className="flex-1 border border-blue-600 text-blue-600 text-center py-2 px-4 rounded-lg hover:bg-blue-50 transition-colors font-medium"
                      >
                        Ver Detalhes
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Mensagem quando não há resultados */}
            {saloesFiltrados.length === 0 && (
              <div className="text-center py-12">
                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <i className="fas fa-search text-gray-400 text-3xl"></i>
                </div>
                <h3 className="text-xl font-semibold text-gray-800 mb-2">
                  Nenhum salão encontrado
                </h3>
                <p className="text-gray-600 mb-4">
                  Tente ajustar os filtros ou buscar por outros termos.
                </p>
                <button
                  onClick={() => {
                    setFiltros({
                      regiao: '',
                      cidade: '',
                      bairro: '',
                      servico: '',
                      ordenacao: 'distancia'
                    })
                    setBusca('')
                  }}
                  className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Limpar Filtros
                </button>
              </div>
            )}

            {/* Paginação (placeholder) */}
            {saloesFiltrados.length > 0 && (
              <div className="flex justify-center mt-8">
                <div className="flex items-center space-x-2">
                  <button className="px-3 py-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <i className="fas fa-chevron-left"></i>
                  </button>
                  <button className="px-3 py-2 bg-blue-600 text-white rounded-lg">1</button>
                  <button className="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">2</button>
                  <button className="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">3</button>
                  <button className="px-3 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <i className="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

export default SaloesPorRegiao