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
    <div className="min-vh-100 d-flex align-items-center bg-light">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-md-6 text-center">
            <div className="mb-4">
              <div className="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '120px', height: '120px' }}>
                <i className="fas fa-search text-white" style={{ fontSize: '3rem' }}></i>
              </div>
            </div>
            
            <h1 className="display-1 fw-bold text-gradient mb-3">404</h1>
            <h2 className="fw-bold mb-3">Página não encontrada</h2>
            <p className="text-muted mb-4 fs-5">
              Ops! A página que você está procurando não existe ou foi movida.
            </p>
            
            <div className="d-flex flex-column flex-sm-row gap-3 justify-content-center">
              <button 
                onClick={handleGoBack}
                className="btn btn-outline-primary btn-lg"
              >
                <i className="fas fa-arrow-left me-2"></i>
                Voltar
              </button>
              
              <Link 
                to={getHomeLink()}
                className="btn btn-primary btn-lg"
              >
                <i className="fas fa-home me-2"></i>
                Ir para o Início
              </Link>
            </div>
            
            {/* Sugestões de navegação */}
            <div className="mt-5">
              <h5 className="fw-bold mb-3">Ou tente uma dessas opções:</h5>
              <div className="row">
                {!isAuthenticated ? (
                  <>
                    <div className="col-md-6 mb-3">
                      <Link to="/auth/login" className="btn btn-outline-info w-100">
                        <i className="fas fa-sign-in-alt me-2"></i>
                        Fazer Login
                      </Link>
                    </div>
                    <div className="col-md-6 mb-3">
                      <Link to="/auth/register" className="btn btn-outline-success w-100">
                        <i className="fas fa-user-plus me-2"></i>
                        Criar Conta
                      </Link>
                    </div>
                  </>
                ) : (
                  <>
                    {user?.tipo === 'cliente' && (
                      <>
                        <div className="col-md-6 mb-3">
                          <Link to="/cliente/agendar" className="btn btn-outline-info w-100">
                            <i className="fas fa-calendar-plus me-2"></i>
                            Agendar Serviço
                          </Link>
                        </div>
                        <div className="col-md-6 mb-3">
                          <Link to="/cliente/agendamentos" className="btn btn-outline-success w-100">
                            <i className="fas fa-calendar-check me-2"></i>
                            Meus Agendamentos
                          </Link>
                        </div>
                      </>
                    )}
                    
                    {user?.tipo === 'parceiro' && (
                      <>
                        <div className="col-md-6 mb-3">
                          <Link to="/parceiro/agendamentos" className="btn btn-outline-info w-100">
                            <i className="fas fa-calendar-check me-2"></i>
                            Agendamentos
                          </Link>
                        </div>
                        <div className="col-md-6 mb-3">
                          <Link to="/parceiro/salao" className="btn btn-outline-success w-100">
                            <i className="fas fa-store me-2"></i>
                            Meu Salão
                          </Link>
                        </div>
                      </>
                    )}
                    
                    {user?.tipo === 'admin' && (
                      <>
                        <div className="col-md-6 mb-3">
                          <Link to="/admin/usuarios" className="btn btn-outline-info w-100">
                            <i className="fas fa-users me-2"></i>
                            Usuários
                          </Link>
                        </div>
                        <div className="col-md-6 mb-3">
                          <Link to="/admin/saloes" className="btn btn-outline-success w-100">
                            <i className="fas fa-store me-2"></i>
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
            <div className="mt-5 pt-4 border-top">
              <p className="text-muted mb-2">
                Se você acredita que isso é um erro, entre em contato conosco:
              </p>
              <div className="d-flex justify-content-center gap-3">
                <a href="mailto:suporte@cortefacil.com" className="text-decoration-none">
                  <i className="fas fa-envelope me-1"></i>
                  suporte@cortefacil.com
                </a>
                <a href="tel:+5511999999999" className="text-decoration-none">
                  <i className="fas fa-phone me-1"></i>
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