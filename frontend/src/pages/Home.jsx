import React, { useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'

const Home = () => {
  const { user } = useAuth()
  const [activeTab, setActiveTab] = useState('cliente')

  if (user) {
    // Redirecionar para dashboard baseado no tipo de usuário
    const dashboardPath = {
      cliente: '/cliente/dashboard',
      parceiro: '/parceiro/dashboard',
      admin: '/admin/dashboard'
    }[user.tipo] || '/login'
    
    return (
      <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center p-4">
        <div className="max-w-lg w-full bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/30 p-10">
          <div className="text-center">
            <div className="w-20 h-20 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-lg">
              <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h1 className="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent mb-6">Bem-vindo, {user.nome}!</h1>
            <p className="text-gray-600 mb-10 text-xl leading-relaxed">Você já está logado no sistema.</p>
            <Link
              to={dashboardPath}
              className="w-full bg-gradient-to-r from-indigo-600 to-cyan-600 text-white py-4 px-8 rounded-2xl hover:from-indigo-700 hover:to-cyan-700 transition-all duration-300 inline-block text-center font-semibold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 hover:scale-105"
            >
              Ir para Dashboard
            </Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
      {/* Header */}
      <header className="relative z-10 bg-white/80 backdrop-blur-lg border-b border-white/20 sticky top-0">
        <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <div className="flex items-center space-x-4">
              <div className="w-12 h-12 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2" />
                </svg>
              </div>
              <span className="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">CorteFácil</span>
            </div>
            <div className="flex items-center space-x-3">
              <Link
                to="/login"
                className="text-gray-700 hover:text-indigo-600 px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:bg-indigo-50"
              >
                Entrar
              </Link>
              <Link
                to="/register"
                className="bg-gradient-to-r from-indigo-600 to-cyan-600 text-white px-8 py-3 rounded-xl font-semibold hover:from-indigo-700 hover:to-cyan-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                Cadastrar
              </Link>
            </div>
          </div>
        </nav>
      </header>

      {/* Main Content */}
      <main>
        {/* Hero Section */}
        <div className="relative overflow-hidden">
          {/* Background decorativo */}
          <div className="absolute inset-0 bg-gradient-to-br from-indigo-100/50 via-white to-cyan-100/50"></div>
          <div className="absolute top-0 left-0 w-full h-full">
            <div className="absolute top-20 left-10 w-72 h-72 bg-gradient-to-r from-indigo-400/20 to-cyan-400/20 rounded-full blur-3xl"></div>
            <div className="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-r from-cyan-400/20 to-indigo-400/20 rounded-full blur-3xl"></div>
          </div>
          
          <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div className="text-center mb-16">
              <div className="inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-emerald-100 to-cyan-100 text-emerald-800 text-base font-semibold mb-8 shadow-lg animate-pulse">
                <span className="mr-2">🔥</span>
                Mais de 10.000 agendamentos realizados!
              </div>
              <h1 className="text-5xl md:text-7xl font-black bg-gradient-to-r from-gray-900 via-indigo-900 to-cyan-900 bg-clip-text text-transparent mb-8 leading-tight">
                Transforme Seu Negócio
                <br />
                <span className="bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">
                  de Beleza Hoje!
                </span>
              </h1>
              <p className="text-2xl md:text-3xl text-gray-700 mb-12 max-w-5xl mx-auto leading-relaxed font-medium">
                A única plataforma que <span className="font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">multiplica sua receita</span> conectando você a milhares de clientes que procuram seus serviços agora mesmo!
              </p>
            </div>

            {/* Tabs para diferentes públicos */}
            <div className="max-w-5xl mx-auto mb-16">
              <div className="flex justify-center mb-12">
                <div className="bg-white/90 backdrop-blur-lg rounded-3xl p-3 shadow-2xl border border-white/30">
                  <button
                    onClick={() => setActiveTab('cliente')}
                    className={`px-10 py-4 rounded-2xl font-bold text-lg transition-all duration-300 ${
                      activeTab === 'cliente'
                        ? 'bg-gradient-to-r from-indigo-600 to-cyan-600 text-white shadow-xl transform scale-105'
                        : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50'
                    }`}
                  >
                    <span className="mr-3">👤</span>
                    Sou Cliente
                  </button>
                  <button
                    onClick={() => setActiveTab('salao')}
                    className={`px-10 py-4 rounded-2xl font-bold text-lg transition-all duration-300 ${
                      activeTab === 'salao'
                        ? 'bg-gradient-to-r from-indigo-600 to-cyan-600 text-white shadow-xl transform scale-105'
                        : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50'
                    }`}
                  >
                    <span className="mr-3">💼</span>
                    Tenho um Salão
                  </button>
                </div>
              </div>

              {/* Conteúdo para Clientes */}
              {activeTab === 'cliente' && (
                <div className="bg-white/95 backdrop-blur-lg rounded-3xl p-12 shadow-2xl border border-white/30 transform transition-all duration-500 hover:shadow-xl">
                  <div className="text-center mb-12">
                    <div className="w-24 h-24 bg-gradient-to-r from-pink-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl">
                      <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                      </svg>
                    </div>
                    <h2 className="text-4xl font-bold text-gray-900 mb-6">Pare de Perder Tempo Procurando!</h2>
                    <p className="text-2xl text-gray-600 mb-12 leading-relaxed">Encontre o salão perfeito em segundos e agende sem sair de casa</p>
                  </div>
                  
                  <div className="grid md:grid-cols-3 gap-8 mb-12">
                    <div className="text-center p-8 bg-gradient-to-br from-indigo-50 to-cyan-50 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                      <div className="text-5xl mb-6">⚡</div>
                      <h3 className="font-bold text-gray-900 mb-4 text-xl">Agendamento Instantâneo</h3>
                      <p className="text-gray-600 text-base leading-relaxed">Reserve em 30 segundos, sem ligações ou espera</p>
                    </div>
                    <div className="text-center p-8 bg-gradient-to-br from-emerald-50 to-indigo-50 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                      <div className="text-5xl mb-6">💰</div>
                      <h3 className="font-bold text-gray-900 mb-4 text-xl">Preços Transparentes</h3>
                      <p className="text-gray-600 text-base leading-relaxed">Veja valores antes de agendar, sem surpresas</p>
                    </div>
                    <div className="text-center p-8 bg-gradient-to-br from-purple-50 to-pink-50 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                      <div className="text-5xl mb-6">⭐</div>
                      <h3 className="font-bold text-gray-900 mb-4 text-xl">Avaliações Reais</h3>
                      <p className="text-gray-600 text-base leading-relaxed">Escolha com base em experiências de outros clientes</p>
                    </div>
                  </div>

                  <div className="text-center">
                    <div className="bg-gradient-to-r from-red-100 to-pink-100 rounded-3xl p-8 mb-8 border-2 border-red-200 shadow-lg">
                      <p className="text-red-800 font-bold text-2xl mb-3">🚨 OFERTA LIMITADA!</p>
                      <p className="text-red-700 text-lg">Primeiros 1000 usuários ganham <span className="font-bold text-xl bg-red-200 px-2 py-1 rounded-lg">20% de desconto</span> no primeiro agendamento!</p>
                    </div>
                    <Link
                      to="/register"
                      className="inline-flex items-center px-12 py-5 text-2xl font-bold rounded-3xl text-white bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 shadow-2xl hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105"
                    >
                      <svg className="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                      QUERO MEU DESCONTO AGORA!
                    </Link>
                    <p className="text-gray-500 text-base mt-4 font-medium">✅ Cadastro gratuito • ✅ Sem compromisso • ✅ Cancele quando quiser</p>
                  </div>
                </div>
              )}

              {/* Conteúdo para Salões */}
              {activeTab === 'salao' && (
                <div className="bg-white/95 backdrop-blur-lg rounded-3xl p-12 shadow-2xl border border-white/30 transform transition-all duration-500 hover:shadow-xl">
                  <div className="text-center mb-12">
                    <div className="w-24 h-24 bg-gradient-to-r from-green-500 to-blue-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl">
                      <svg className="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <h2 className="text-4xl font-bold text-gray-900 mb-6">Multiplique Sua Receita em 30 Dias!</h2>
                    <p className="text-2xl text-gray-600 mb-12 leading-relaxed">Atraia novos clientes automaticamente e nunca mais tenha horários vazios</p>
                  </div>
                  
                  <div className="grid md:grid-cols-2 gap-8 mb-12">
                    <div className="bg-gradient-to-br from-green-50 to-blue-50 rounded-3xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                      <h3 className="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span className="text-3xl mr-3">📈</span>
                        Resultados Comprovados
                      </h3>
                      <ul className="space-y-3 text-gray-700">
                        <li className="flex items-center"><span className="text-green-500 mr-2">✓</span> +300% mais agendamentos</li>
                        <li className="flex items-center"><span className="text-green-500 mr-2">✓</span> +150% aumento na receita</li>
                        <li className="flex items-center"><span className="text-green-500 mr-2">✓</span> 95% redução em cancelamentos</li>
                        <li className="flex items-center"><span className="text-green-500 mr-2">✓</span> Agenda sempre lotada</li>
                      </ul>
                    </div>
                    
                    <div className="bg-gradient-to-br from-purple-50 to-pink-50 rounded-3xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                      <h3 className="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span className="text-3xl mr-3">🎯</span>
                        Ferramentas Poderosas
                      </h3>
                      <ul className="space-y-3 text-gray-700">
                        <li className="flex items-center"><span className="text-purple-500 mr-2">✓</span> Agendamento online 24/7</li>
                        <li className="flex items-center"><span className="text-purple-500 mr-2">✓</span> Gestão completa de clientes</li>
                        <li className="flex items-center"><span className="text-purple-500 mr-2">✓</span> Relatórios de vendas</li>
                        <li className="flex items-center"><span className="text-purple-500 mr-2">✓</span> Marketing automático</li>
                      </ul>
                    </div>
                  </div>

                  <div className="bg-gradient-to-r from-yellow-100 to-orange-100 rounded-3xl p-8 mb-8 text-center border-2 border-orange-200 shadow-lg">
                    <p className="text-orange-800 font-bold text-2xl mb-3">⏰ ATENÇÃO: VAGAS LIMITADAS!</p>
                    <p className="text-orange-700 text-lg">Apenas <span className="font-bold text-2xl text-red-600 bg-orange-200 px-2 py-1 rounded-lg">47 vagas</span> restantes para o plano gratuito de 3 meses!</p>
                  </div>

                  <div className="text-center">
                    <Link
                      to="/register"
                      className="inline-flex items-center px-12 py-5 text-2xl font-bold rounded-3xl text-white bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 shadow-2xl hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105"
                    >
                      <svg className="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                      GARANTIR MINHA VAGA GRATUITA!
                    </Link>
                    <p className="text-gray-500 text-base mt-4 font-medium">✅ 3 meses grátis • ✅ Sem taxa de setup • ✅ Suporte completo incluído</p>
                    <p className="text-red-600 font-semibold text-sm mt-2">⚠️ Oferta válida apenas hoje!</p>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Seção de Depoimentos e Prova Social */}
        <div className="py-24 bg-gradient-to-br from-gray-50 to-blue-50">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center mb-20">
              <div className="inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-green-100 to-blue-100 text-green-800 text-base font-semibold mb-6 shadow-lg">
                ⭐ Depoimentos Reais
              </div>
              <h2 className="text-5xl font-extrabold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent sm:text-6xl mb-6">
                Veja o que nossos usuários dizem
              </h2>
              <p className="text-2xl text-gray-600 max-w-4xl mx-auto font-medium">
                Mais de <span className="font-bold text-blue-600">50.000 pessoas</span> já transformaram sua experiência com beleza
              </p>
            </div>

            {/* Estatísticas */}
            <div className="grid md:grid-cols-4 gap-8 mb-16">
              <div className="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg">
                <div className="text-4xl font-bold text-blue-600 mb-2">50K+</div>
                <p className="text-gray-600 font-medium">Usuários Ativos</p>
              </div>
              <div className="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg">
                <div className="text-4xl font-bold text-green-600 mb-2">2.5K+</div>
                <p className="text-gray-600 font-medium">Salões Parceiros</p>
              </div>
              <div className="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg">
                <div className="text-4xl font-bold text-purple-600 mb-2">98%</div>
                <p className="text-gray-600 font-medium">Satisfação</p>
              </div>
              <div className="text-center p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg">
                <div className="text-4xl font-bold text-orange-600 mb-2">4.9★</div>
                <p className="text-gray-600 font-medium">Avaliação Média</p>
              </div>
            </div>

            {/* Depoimentos */}
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
              <div className="bg-white/95 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/30 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                <div className="flex items-center mb-6">
                  <div className="w-16 h-16 bg-gradient-to-r from-pink-400 to-purple-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mr-5 shadow-lg">
                    M
                  </div>
                  <div>
                    <h4 className="font-bold text-gray-900 text-lg">Maria Silva</h4>
                    <p className="text-gray-600">Cliente há 8 meses</p>
                  </div>
                </div>
                <div className="flex mb-4">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  ))}
                </div>
                <p className="text-gray-700 italic text-base leading-relaxed">
                  "Nunca mais perdi tempo ligando para salões! Agendo tudo pelo app e sempre encontro horários disponíveis. Economizei muito tempo e dinheiro!"
                </p>
              </div>

              <div className="bg-white/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20">
                <div className="flex items-center mb-4">
                  <div className="w-12 h-12 bg-gradient-to-r from-blue-400 to-green-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                    C
                  </div>
                  <div>
                    <h4 className="font-bold text-gray-900">Carlos Mendes</h4>
                    <p className="text-gray-600 text-sm">Dono do Salão Elegance</p>
                  </div>
                </div>
                <div className="flex mb-3">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  ))}
                </div>
                <p className="text-gray-700 italic">
                  "Minha receita aumentou 280% em 6 meses! A plataforma trouxe centenas de novos clientes. Melhor investimento que já fiz para meu negócio!"
                </p>
              </div>

              <div className="bg-white/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20">
                <div className="flex items-center mb-4">
                  <div className="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                    A
                  </div>
                  <div>
                    <h4 className="font-bold text-gray-900">Ana Costa</h4>
                    <p className="text-gray-600 text-sm">Cliente há 1 ano</p>
                  </div>
                </div>
                <div className="flex mb-3">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  ))}
                </div>
                <p className="text-gray-700 italic">
                  "Descobri salões incríveis que nunca conheceria! Os preços são transparentes e as avaliações me ajudam a escolher sempre o melhor."
                </p>
              </div>
            </div>

            {/* Call to Action Final */}
            <div className="text-center mt-16">
              <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 text-white shadow-2xl">
                <h3 className="text-3xl font-bold mb-4">Junte-se a milhares de pessoas satisfeitas!</h3>
                <p className="text-xl mb-6 opacity-90">Não perca mais tempo. Sua transformação começa agora!</p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                  <Link
                    to="/register"
                    className="bg-white text-blue-600 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-gray-100 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 shadow-lg"
                  >
                    🚀 COMEÇAR AGORA GRÁTIS
                  </Link>
                  <p className="text-blue-100 text-sm">✅ Sem cartão de crédito • ✅ Ativação imediata</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Seção Final de CTA */}
        <div className="bg-gradient-to-br from-indigo-600 via-cyan-600 to-blue-700 py-24">
          <div className="max-w-5xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <div className="bg-white/15 backdrop-blur-lg rounded-3xl p-12 border border-white/30 shadow-2xl">
              <h2 className="text-5xl md:text-6xl font-black text-white mb-8 leading-tight">
                Não Perca Mais Tempo!
              </h2>
              <p className="text-2xl text-blue-100 mb-12 leading-relaxed font-medium">
                Junte-se a milhares de pessoas que já descobriram a forma mais fácil de cuidar da beleza.
                <br />
                <span className="font-bold text-white text-3xl bg-white/20 px-4 py-2 rounded-xl inline-block mt-4">Cadastre-se agora e ganhe 20% de desconto!</span>
              </p>
              <div className="flex flex-col sm:flex-row gap-6 justify-center items-center mb-8">
                <Link
                  to="/register"
                  className="bg-white text-indigo-600 px-12 py-5 rounded-3xl font-bold text-2xl hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-xl transform hover:-translate-y-2 hover:scale-105"
                >
                  COMEÇAR AGORA GRÁTIS
                </Link>
                <Link
                  to="/login"
                  className="text-white border-2 border-white px-10 py-5 rounded-3xl font-bold text-xl hover:bg-white hover:text-indigo-600 transition-all duration-300 shadow-xl"
                >
                  Já tenho conta
                </Link>
              </div>
              <p className="text-blue-200 text-lg font-medium">
                ✅ Sem cartão de crédito • ✅ Ativação imediata • ✅ Suporte 24/7
              </p>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <div className="flex items-center justify-center space-x-4 mb-8">
              <div className="w-14 h-14 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2" />
                </svg>
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">CorteFácil</span>
            </div>
            <p className="text-gray-300 mb-8 max-w-3xl mx-auto text-lg leading-relaxed">
              A plataforma que conecta você aos melhores profissionais de beleza da sua região. Agende com facilidade, pague com segurança.
            </p>
            <div className="flex justify-center space-x-8 mb-8">
              <Link to="/sobre" className="text-gray-400 hover:text-indigo-400 transition-colors text-lg font-medium">Sobre</Link>
              <Link to="/contato" className="text-gray-400 hover:text-indigo-400 transition-colors text-lg font-medium">Contato</Link>
              <Link to="/privacidade" className="text-gray-400 hover:text-indigo-400 transition-colors text-lg font-medium">Privacidade</Link>
              <Link to="/termos" className="text-gray-400 hover:text-indigo-400 transition-colors text-lg font-medium">Termos</Link>
            </div>
            <div className="border-t border-gray-700 pt-8">
              <p className="text-gray-400 text-base">
                © 2024 CorteFácil. Todos os direitos reservados.
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}

export default Home