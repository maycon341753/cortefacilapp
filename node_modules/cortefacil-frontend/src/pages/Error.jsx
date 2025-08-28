import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'

const Error = ({ error = null, resetError = null }) => {
  const navigate = useNavigate()
  const { isAuthenticated, user } = useAuth()

  const handleGoBack = () => {
    navigate(-1)
  }

  const handleReload = () => {
    window.location.reload()
  }

  const handleReset = () => {
    if (resetError) {
      resetError()
    } else {
      handleReload()
    }
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
          <div className="w-full max-w-4xl text-center">
            <div className="mb-6">
              <div className="w-32 h-32 bg-red-500 rounded-full inline-flex items-center justify-center mb-6">
                <i className="fas fa-exclamation-triangle text-white text-5xl"></i>
              </div>
            </div>
            
            <h1 className="text-6xl font-bold text-red-500 mb-4">Oops!</h1>
            <h2 className="text-2xl font-bold mb-4">Algo deu errado</h2>
            <p className="text-gray-600 mb-6 text-lg">
              Ocorreu um erro inesperado. Nossa equipe foi notificada e está trabalhando para resolver o problema.
            </p>
            
            {/* Detalhes do erro (apenas em desenvolvimento) */}
            {process.env.NODE_ENV === 'development' && error && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-left mb-6">
                <h6 className="font-bold mb-2 text-red-700">
                  <i className="fas fa-bug mr-2"></i>
                  Detalhes do erro (desenvolvimento):
                </h6>
                <pre className="mb-0 whitespace-pre-wrap text-sm text-red-600">
                  {error.message || error.toString()}
                </pre>
                {error.stack && (
                  <details className="mt-2">
                    <summary className="cursor-pointer text-red-700">Stack trace</summary>
                    <pre className="mt-2 whitespace-pre-wrap text-xs text-red-600">
                      {error.stack}
                    </pre>
                  </details>
                )}
              </div>
            )}
            
            <div className="flex flex-col sm:flex-row gap-3 justify-center mb-6">
              <button 
                onClick={handleReset}
                className="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg transition-colors"
              >
                <i className="fas fa-redo mr-2"></i>
                Tentar Novamente
              </button>
              
              <button 
                onClick={handleGoBack}
                className="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-3 px-6 rounded-lg transition-colors"
              >
                <i className="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>
              
              <Link 
                to={getHomeLink()}
                className="border border-blue-500 hover:bg-blue-50 text-blue-500 font-medium py-3 px-6 rounded-lg transition-colors inline-flex items-center justify-center"
              >
                <i className="fas fa-home mr-2"></i>
                Ir para o Início
              </Link>
            </div>
            
            {/* Dicas para o usuário */}
            <div className="mt-8">
              <h5 className="text-xl font-bold mb-4">O que você pode fazer:</h5>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-left">
                <div className="bg-white rounded-lg shadow-sm p-6 text-center h-full">
                  <i className="fas fa-sync-alt text-blue-500 mb-4 text-3xl"></i>
                  <h6 className="font-bold text-gray-800 mb-2">Recarregar a página</h6>
                  <p className="text-gray-600 text-sm mb-0">
                    Às vezes um simples recarregamento resolve o problema.
                  </p>
                </div>
                
                <div className="bg-white rounded-lg shadow-sm p-6 text-center h-full">
                  <i className="fas fa-clock text-yellow-500 mb-4 text-3xl"></i>
                  <h6 className="font-bold text-gray-800 mb-2">Aguardar um momento</h6>
                  <p className="text-gray-600 text-sm mb-0">
                    O problema pode ser temporário. Tente novamente em alguns minutos.
                  </p>
                </div>
                
                <div className="bg-white rounded-lg shadow-sm p-6 text-center h-full">
                  <i className="fas fa-headset text-green-500 mb-4 text-3xl"></i>
                  <h6 className="font-bold text-gray-800 mb-2">Entrar em contato</h6>
                  <p className="text-gray-600 text-sm mb-0">
                    Se o problema persistir, nossa equipe está aqui para ajudar.
                  </p>
                </div>
              </div>
            </div>
            
            {/* Informações de contato */}
            <div className="mt-8 pt-6 border-t border-gray-200">
              <p className="text-gray-600 mb-4">
                <strong>Precisa de ajuda?</strong> Nossa equipe de suporte está disponível:
              </p>
              <div className="flex flex-col sm:flex-row justify-center gap-3">
                <a href="mailto:suporte@cortefacil.com" className="border border-cyan-500 hover:bg-cyan-50 text-cyan-600 font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center justify-center">
                  <i className="fas fa-envelope mr-2"></i>
                  suporte@cortefacil.com
                </a>
                <a href="tel:+5511999999999" className="border border-green-500 hover:bg-green-50 text-green-600 font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center justify-center">
                  <i className="fas fa-phone mr-2"></i>
                  (11) 99999-9999
                </a>
                <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" className="border border-green-500 hover:bg-green-50 text-green-600 font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center justify-center">
                  <i className="fab fa-whatsapp mr-2"></i>
                  WhatsApp
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Error