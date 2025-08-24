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
    <div className="min-vh-100 d-flex align-items-center bg-light">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-md-8 text-center">
            <div className="mb-4">
              <div className="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '120px', height: '120px' }}>
                <i className="fas fa-exclamation-triangle text-white" style={{ fontSize: '3rem' }}></i>
              </div>
            </div>
            
            <h1 className="display-4 fw-bold text-danger mb-3">Oops!</h1>
            <h2 className="fw-bold mb-3">Algo deu errado</h2>
            <p className="text-muted mb-4 fs-5">
              Ocorreu um erro inesperado. Nossa equipe foi notificada e está trabalhando para resolver o problema.
            </p>
            
            {/* Detalhes do erro (apenas em desenvolvimento) */}
            {process.env.NODE_ENV === 'development' && error && (
              <div className="alert alert-danger text-start mb-4">
                <h6 className="fw-bold mb-2">
                  <i className="fas fa-bug me-2"></i>
                  Detalhes do erro (desenvolvimento):
                </h6>
                <pre className="mb-0 text-wrap" style={{ fontSize: '0.875rem' }}>
                  {error.message || error.toString()}
                </pre>
                {error.stack && (
                  <details className="mt-2">
                    <summary className="cursor-pointer">Stack trace</summary>
                    <pre className="mt-2 text-wrap" style={{ fontSize: '0.75rem' }}>
                      {error.stack}
                    </pre>
                  </details>
                )}
              </div>
            )}
            
            <div className="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
              <button 
                onClick={handleReset}
                className="btn btn-primary btn-lg"
              >
                <i className="fas fa-redo me-2"></i>
                Tentar Novamente
              </button>
              
              <button 
                onClick={handleGoBack}
                className="btn btn-outline-secondary btn-lg"
              >
                <i className="fas fa-arrow-left me-2"></i>
                Voltar
              </button>
              
              <Link 
                to={getHomeLink()}
                className="btn btn-outline-primary btn-lg"
              >
                <i className="fas fa-home me-2"></i>
                Ir para o Início
              </Link>
            </div>
            
            {/* Dicas para o usuário */}
            <div className="mt-5">
              <h5 className="fw-bold mb-3">O que você pode fazer:</h5>
              <div className="row text-start">
                <div className="col-md-4 mb-3">
                  <div className="card h-100 border-0 shadow-sm">
                    <div className="card-body text-center">
                      <i className="fas fa-sync-alt text-primary mb-3" style={{ fontSize: '2rem' }}></i>
                      <h6 className="fw-bold">Recarregar a página</h6>
                      <p className="text-muted small mb-0">
                        Às vezes um simples recarregamento resolve o problema.
                      </p>
                    </div>
                  </div>
                </div>
                
                <div className="col-md-4 mb-3">
                  <div className="card h-100 border-0 shadow-sm">
                    <div className="card-body text-center">
                      <i className="fas fa-clock text-warning mb-3" style={{ fontSize: '2rem' }}></i>
                      <h6 className="fw-bold">Aguardar um momento</h6>
                      <p className="text-muted small mb-0">
                        O problema pode ser temporário. Tente novamente em alguns minutos.
                      </p>
                    </div>
                  </div>
                </div>
                
                <div className="col-md-4 mb-3">
                  <div className="card h-100 border-0 shadow-sm">
                    <div className="card-body text-center">
                      <i className="fas fa-headset text-success mb-3" style={{ fontSize: '2rem' }}></i>
                      <h6 className="fw-bold">Entrar em contato</h6>
                      <p className="text-muted small mb-0">
                        Se o problema persistir, nossa equipe está aqui para ajudar.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Informações de contato */}
            <div className="mt-5 pt-4 border-top">
              <p className="text-muted mb-3">
                <strong>Precisa de ajuda?</strong> Nossa equipe de suporte está disponível:
              </p>
              <div className="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="mailto:suporte@cortefacil.com" className="btn btn-outline-info">
                  <i className="fas fa-envelope me-2"></i>
                  suporte@cortefacil.com
                </a>
                <a href="tel:+5511999999999" className="btn btn-outline-success">
                  <i className="fas fa-phone me-2"></i>
                  (11) 99999-9999
                </a>
                <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" className="btn btn-outline-success">
                  <i className="fab fa-whatsapp me-2"></i>
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