import React, { useState, useEffect } from 'react';
import { FaSearch, FaMapMarkerAlt, FaStar, FaPhone, FaWhatsapp, FaClock, FaFilter } from 'react-icons/fa';

const Saloes = () => {
  const [saloes, setSaloes] = useState([]);
  const [filteredSaloes, setFilteredSaloes] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedRegiao, setSelectedRegiao] = useState('');
  const [selectedServico, setSelectedServico] = useState('');
  const [loading, setLoading] = useState(true);

  // Dados mockados de salões
  const saloesData = [
    {
      id: 1,
      nome: 'Salão Elegance',
      endereco: 'Rua das Flores, 123 - Centro',
      regiao: 'Centro',
      telefone: '(11) 99999-9999',
      whatsapp: '(11) 99999-9999',
      avaliacao: 4.8,
      totalAvaliacoes: 127,
      servicos: ['Corte Masculino', 'Corte Feminino', 'Barba', 'Coloração'],
      horarioFuncionamento: 'Seg-Sex: 8h-18h | Sáb: 8h-16h',
      preco: 'A partir de R$ 25',
      imagem: 'https://via.placeholder.com/300x200?text=Salão+Elegance',
      destaque: true
    },
    {
      id: 2,
      nome: 'Barbearia Moderna',
      endereco: 'Av. Principal, 456 - Zona Norte',
      regiao: 'Zona Norte',
      telefone: '(11) 88888-8888',
      whatsapp: '(11) 88888-8888',
      avaliacao: 4.6,
      totalAvaliacoes: 89,
      servicos: ['Corte Masculino', 'Barba', 'Bigode', 'Sobrancelha'],
      horarioFuncionamento: 'Seg-Sáb: 9h-19h',
      preco: 'A partir de R$ 20',
      imagem: 'https://via.placeholder.com/300x200?text=Barbearia+Moderna',
      destaque: false
    },
    {
      id: 3,
      nome: 'Studio Hair & Beauty',
      endereco: 'Rua da Beleza, 789 - Zona Sul',
      regiao: 'Zona Sul',
      telefone: '(11) 77777-7777',
      whatsapp: '(11) 77777-7777',
      avaliacao: 4.9,
      totalAvaliacoes: 203,
      servicos: ['Corte Feminino', 'Coloração', 'Escova', 'Tratamentos'],
      horarioFuncionamento: 'Ter-Sáb: 9h-20h',
      preco: 'A partir de R$ 35',
      imagem: 'https://via.placeholder.com/300x200?text=Studio+Hair',
      destaque: true
    },
    {
      id: 4,
      nome: 'Salão Família',
      endereco: 'Rua Comunitária, 321 - Zona Oeste',
      regiao: 'Zona Oeste',
      telefone: '(11) 66666-6666',
      whatsapp: '(11) 66666-6666',
      avaliacao: 4.4,
      totalAvaliacoes: 156,
      servicos: ['Corte Masculino', 'Corte Feminino', 'Corte Infantil', 'Escova'],
      horarioFuncionamento: 'Seg-Sáb: 8h-18h',
      preco: 'A partir de R$ 18',
      imagem: 'https://via.placeholder.com/300x200?text=Salão+Família',
      destaque: false
    },
    {
      id: 5,
      nome: 'Premium Hair',
      endereco: 'Av. Luxo, 654 - Zona Leste',
      regiao: 'Zona Leste',
      telefone: '(11) 55555-5555',
      whatsapp: '(11) 55555-5555',
      avaliacao: 4.7,
      totalAvaliacoes: 98,
      servicos: ['Corte Premium', 'Coloração Especial', 'Tratamentos VIP'],
      horarioFuncionamento: 'Ter-Dom: 10h-22h',
      preco: 'A partir de R$ 50',
      imagem: 'https://via.placeholder.com/300x200?text=Premium+Hair',
      destaque: true
    },
    {
      id: 6,
      nome: 'Corte & Estilo',
      endereco: 'Rua do Estilo, 987 - Centro',
      regiao: 'Centro',
      telefone: '(11) 44444-4444',
      whatsapp: '(11) 44444-4444',
      avaliacao: 4.5,
      totalAvaliacoes: 74,
      servicos: ['Corte Masculino', 'Corte Feminino', 'Barba', 'Penteados'],
      horarioFuncionamento: 'Seg-Sex: 9h-19h | Sáb: 9h-17h',
      preco: 'A partir de R$ 30',
      imagem: 'https://via.placeholder.com/300x200?text=Corte+Estilo',
      destaque: false
    }
  ];

  const regioes = ['Todas', 'Centro', 'Zona Norte', 'Zona Sul', 'Zona Oeste', 'Zona Leste'];
  const servicos = ['Todos', 'Corte Masculino', 'Corte Feminino', 'Barba', 'Coloração', 'Escova', 'Tratamentos'];

  useEffect(() => {
    // Simular carregamento
    setTimeout(() => {
      setSaloes(saloesData);
      setFilteredSaloes(saloesData);
      setLoading(false);
    }, 1000);
  }, []);

  useEffect(() => {
    let filtered = saloes;

    // Filtrar por termo de busca
    if (searchTerm) {
      filtered = filtered.filter(salao => 
        salao.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
        salao.endereco.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Filtrar por região
    if (selectedRegiao && selectedRegiao !== 'Todas') {
      filtered = filtered.filter(salao => salao.regiao === selectedRegiao);
    }

    // Filtrar por serviço
    if (selectedServico && selectedServico !== 'Todos') {
      filtered = filtered.filter(salao => 
        salao.servicos.includes(selectedServico)
      );
    }

    setFilteredSaloes(filtered);
  }, [searchTerm, selectedRegiao, selectedServico, saloes]);

  const renderStars = (rating) => {
    return Array.from({ length: 5 }, (_, index) => (
      <FaStar 
        key={index} 
        className={index < Math.floor(rating) ? 'text-yellow-400' : 'text-gray-300'}
      />
    ));
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Carregando salões...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="text-center">
            <h1 className="text-3xl font-bold text-gray-900 mb-2">Encontre o Salão Perfeito</h1>
            <p className="text-gray-600">Descubra os melhores salões e barbearias da sua região</p>
          </div>
        </div>
      </div>

      {/* Filtros */}
      <div className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {/* Busca */}
            <div className="relative">
              <FaSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                placeholder="Buscar por nome ou endereço..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            {/* Filtro por Região */}
            <div className="relative">
              <FaMapMarkerAlt className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <select
                value={selectedRegiao}
                onChange={(e) => setSelectedRegiao(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none"
              >
                {regioes.map(regiao => (
                  <option key={regiao} value={regiao === 'Todas' ? '' : regiao}>
                    {regiao}
                  </option>
                ))}
              </select>
            </div>

            {/* Filtro por Serviço */}
            <div className="relative">
              <FaFilter className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <select
                value={selectedServico}
                onChange={(e) => setSelectedServico(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none"
              >
                {servicos.map(servico => (
                  <option key={servico} value={servico === 'Todos' ? '' : servico}>
                    {servico}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </div>

      {/* Resultados */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Contador de resultados */}
        <div className="mb-6">
          <p className="text-gray-600">
            {filteredSaloes.length} {filteredSaloes.length === 1 ? 'salão encontrado' : 'salões encontrados'}
          </p>
        </div>

        {/* Grid de Salões */}
        {filteredSaloes.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredSaloes.map(salao => (
              <div key={salao.id} className={`bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 ${salao.destaque ? 'ring-2 ring-blue-500' : ''}`}>
                {/* Badge de Destaque */}
                {salao.destaque && (
                  <div className="bg-blue-500 text-white text-xs font-bold px-2 py-1 absolute z-10 m-2 rounded">
                    DESTAQUE
                  </div>
                )}

                {/* Imagem */}
                <div className="relative h-48 bg-gray-200">
                  <img 
                    src={salao.imagem} 
                    alt={salao.nome}
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Conteúdo */}
                <div className="p-4">
                  {/* Nome e Avaliação */}
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="text-lg font-semibold text-gray-900 flex-1">{salao.nome}</h3>
                    <div className="flex items-center ml-2">
                      <div className="flex">
                        {renderStars(salao.avaliacao)}
                      </div>
                      <span className="ml-1 text-sm text-gray-600">({salao.totalAvaliacoes})</span>
                    </div>
                  </div>

                  {/* Endereço */}
                  <div className="flex items-center text-gray-600 mb-2">
                    <FaMapMarkerAlt className="mr-2 text-sm" />
                    <span className="text-sm">{salao.endereco}</span>
                  </div>

                  {/* Horário */}
                  <div className="flex items-center text-gray-600 mb-2">
                    <FaClock className="mr-2 text-sm" />
                    <span className="text-sm">{salao.horarioFuncionamento}</span>
                  </div>

                  {/* Preço */}
                  <div className="text-green-600 font-semibold mb-3">
                    {salao.preco}
                  </div>

                  {/* Serviços */}
                  <div className="mb-4">
                    <div className="flex flex-wrap gap-1">
                      {salao.servicos.slice(0, 3).map((servico, index) => (
                        <span key={index} className="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                          {servico}
                        </span>
                      ))}
                      {salao.servicos.length > 3 && (
                        <span className="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                          +{salao.servicos.length - 3}
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Botões de Ação */}
                  <div className="flex gap-2">
                    <button className="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                      Agendar
                    </button>
                    <a 
                      href={`tel:${salao.telefone}`}
                      className="bg-gray-100 text-gray-700 p-2 rounded-lg hover:bg-gray-200 transition-colors duration-200"
                    >
                      <FaPhone className="text-sm" />
                    </a>
                    <a 
                      href={`https://wa.me/${salao.whatsapp.replace(/\D/g, '')}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 transition-colors duration-200"
                    >
                      <FaWhatsapp className="text-sm" />
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <div className="text-gray-400 mb-4">
              <FaSearch className="mx-auto text-4xl" />
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Nenhum salão encontrado</h3>
            <p className="text-gray-600">Tente ajustar os filtros ou termo de busca</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default Saloes;