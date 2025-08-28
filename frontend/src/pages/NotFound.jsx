import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'

const NotFound = () => {
  const navigate = useNavigate()
  const { isAuthenticated, user } = useAuth()

  const handleGoBack = () => {
    navigate(-1)
  }

  const getHomeLink = () => {
    if (!isAuthenticated) {
      return '/'
    }
    
    if (user?.tipo === 'admin') {
      return '/admin/dashboard'
    } else if (user?.tipo === 'parceiro') {
      return '/parceiro/dashboard'
    } else {
      return '/cliente/dashboard'
    }
  }

  return (
    <div className="min-h-screen flex items-center bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="flex justify-center">
          <div className="w-full max-w-md text-center">
            <div className="mb-6">
              <div className="w-32 h-32 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full inline-flex items-center justify-center mb-6">
                <i className="fas fa-search text-white text-5xl"></i>
              </div>
            </div>
            
            <h1 className="text-8xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">404</h1>
            <h2 className="text-2xl font-bold mb-4">Página não encontrada</h2>
            <p className="text-gray-600 mb-6 text-lg">
              Ops! A página que você está procurando não existe ou foi movida.
            </p>
            
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <button 
                onClick={handleGoBack}
                className="px-6 py-3 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg font-medium transition duration-200"
              >
                <i className="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>
              
              <Link 
                to={getHomeLink()}
                className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition duration-200 inline-block"
              >
                <i className="fas fa-home mr-2"></i>
                Ir para o Início
              </Link>
            </div>
            
            {/* Sugestões de navegação */}
            <div className="mt-8">
              <h5 className="text-lg font-bold mb-4">Ou tente uma dessas opções:</h5>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {!isAuthenticated ? (
                  <>
                    <div>
                      <Link to="/auth/login" className="w-full px-4 py-3 border-2 border-cyan-500 text-cyan-600 hover:bg-cyan-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                        <i className="fas fa-sign-in-alt mr-2"></i>
                        Fazer Login
                      </Link>
                    </div>
                    <div>
                      <Link to="/auth/register" className="w-full px-4 py-3 border-2 border-green-500 text-green-600 hover:bg-green-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                        <i className="fas fa-user-plus mr-2"></i>
                        Criar Conta
                      </Link>
                    </div>
                  </>
                ) : (
                  <>
                    {user?.tipo === 'cliente' && (
                      <>
                        <div>
                          <Link to="/cliente/agendar" className="w-full px-4 py-3 border-2 border-cyan-500 text-cyan-600 hover:bg-cyan-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-calendar-plus mr-2"></i>
                            Agendar Serviço
                          </Link>
                        </div>
                        <div>
                          <Link to="/cliente/agendamentos" className="w-full px-4 py-3 border-2 border-green-500 text-green-600 hover:bg-green-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-calendar-check mr-2"></i>
                            Meus Agendamentos
                          </Link>
                        </div>
                      </>
                    )}
                    
                    {user?.tipo === 'parceiro' && (
                      <>
                        <div>
                          <Link to="/parceiro/agendamentos" className="w-full px-4 py-3 border-2 border-cyan-500 text-cyan-600 hover:bg-cyan-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-calendar-check mr-2"></i>
                            Agendamentos
                          </Link>
                        </div>
                        <div>
                          <Link to="/parceiro/salao" className="w-full px-4 py-3 border-2 border-green-500 text-green-600 hover:bg-green-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-store mr-2"></i>
                            Meu Salão
                          </Link>
                        </div>
                      </>
                    )}
                    
                    {user?.tipo === 'admin' && (
                      <>
                        <div>
                          <Link to="/admin/usuarios" className="w-full px-4 py-3 border-2 border-cyan-500 text-cyan-600 hover:bg-cyan-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-users mr-2"></i>
                            Usuários
                          </Link>
                        </div>
                        <div>
                          <Link to="/admin/saloes" className="w-full px-4 py-3 border-2 border-green-500 text-green-600 hover:bg-green-500 hover:text-white rounded-lg font-medium transition duration-200 inline-block text-center">
                            <i className="fas fa-store mr-2"></i>
                            Salões
                          </Link>
                        </div>
                      </>
                    )}
                  </>
                )}
              </div>
            </div>
            
            {/* Informações de contato */}
            <div className="mt-8 pt-6 border-t border-gray-200">
              <p className="text-gray-600 mb-3">
                Se você acredita que isso é um erro, entre em contato conosco:
              </p>
              <div className="flex justify-center gap-6">
                <a href="mailto:suporte@cortefacil.com" className="text-blue-600 hover:text-blue-800 no-underline">
                  <i className="fas fa-envelope mr-2"></i>
                  suporte@cortefacil.com
                </a>
                <a href="tel:+5511999999999" className="text-blue-600 hover:text-blue-800 no-underline">
                  <i className="fas fa-phone mr-2"></i>
                  (11) 99999-9999
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default NotFound