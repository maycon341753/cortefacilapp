import React, { useState, useEffect } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { toast } from 'react-toastify'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const Login = () => {
  const navigate = useNavigate()
  const location = useLocation()
  const { login, isAuthenticated } = useAuth()
  const { setLoading } = useApp()
  const [formData, setFormData] = useState({
    email: '',
    senha: '',
    lembrar: false
  })
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Redirecionar se já estiver autenticado
  useEffect(() => {
    if (isAuthenticated) {
      const from = location.state?.from?.pathname || '/dashboard'
      navigate(from, { replace: true })
    }
  }, [isAuthenticated, navigate, location])

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }))
    
    // Limpar erro do campo quando o usuário começar a digitar
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}

    if (!formData.email.trim()) {
      newErrors.email = 'Email é obrigatório'
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email inválido'
    }

    if (!formData.senha) {
      newErrors.senha = 'Senha é obrigatória'
    } else if (formData.senha.length < 6) {
      newErrors.senha = 'Senha deve ter pelo menos 6 caracteres'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) {
      return
    }

    setIsSubmitting(true)
    setLoading(true)

    try {
      await login(formData.email, formData.senha, formData.lembrar)
      toast.success('Login realizado com sucesso!')
      
      // Redirecionar para a página de origem ou dashboard
      const from = location.state?.from?.pathname || '/dashboard'
      navigate(from, { replace: true })
    } catch (error) {
      console.error('Erro no login:', error)
      toast.error(error.message || 'Erro ao fazer login. Verifique suas credenciais.')
    } finally {
      setIsSubmitting(false)
      setLoading(false)
    }
  }

  return (
    <div className="min-vh-100 d-flex align-items-center bg-light">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div className="card border-0 shadow-lg">
              <div className="card-body p-3 p-sm-4 p-md-5">
                {/* Logo e título */}
                <div className="text-center mb-3 mb-md-4">
                  <div className="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '60px', height: '60px' }}>
                    <i className="fas fa-cut text-white fs-3"></i>
                  </div>
                  <h2 className="text-gradient fw-bold mb-2 fs-3 fs-md-2">CorteFácil</h2>
                  <p className="text-muted small">Faça login em sua conta</p>
                </div>

                {/* Formulário */}
                <form onSubmit={handleSubmit}>
                  <div className="mb-3">
                    <label htmlFor="email" className="form-label fw-medium">
                      <i className="fas fa-envelope me-2 text-muted"></i>
                      Email
                    </label>
                    <input
                      type="email"
                      className={`form-control form-control-lg ${errors.email ? 'is-invalid' : ''}`}
                      id="email"
                      name="email"
                      value={formData.email}
                      onChange={handleChange}
                      placeholder="seu@email.com"
                      disabled={isSubmitting}
                    />
                    {errors.email && (
                      <div className="invalid-feedback">{errors.email}</div>
                    )}
                  </div>

                  <div className="mb-3">
                    <label htmlFor="senha" className="form-label fw-medium">
                      <i className="fas fa-lock me-2 text-muted"></i>
                      Senha
                    </label>
                    <input
                      type="password"
                      className={`form-control form-control-lg ${errors.senha ? 'is-invalid' : ''}`}
                      id="senha"
                      name="senha"
                      value={formData.senha}
                      onChange={handleChange}
                      placeholder="Sua senha"
                      disabled={isSubmitting}
                    />
                    {errors.senha && (
                      <div className="invalid-feedback">{errors.senha}</div>
                    )}
                  </div>

                  <div className="mb-3 mb-md-4">
                    <div className="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                      <div className="form-check">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id="lembrar"
                          name="lembrar"
                          checked={formData.lembrar}
                          onChange={handleChange}
                          disabled={isSubmitting}
                        />
                        <label className="form-check-label text-muted small" htmlFor="lembrar">
                          Lembrar de mim
                        </label>
                      </div>
                      <Link 
                        to="/auth/esqueci-senha" 
                        className="text-decoration-none small"
                      >
                        Esqueci minha senha
                      </Link>
                    </div>
                  </div>

                  <button
                    type="submit"
                    className="btn btn-primary btn-lg w-100 mb-3"
                    disabled={isSubmitting}
                  >
                    {isSubmitting ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Entrando...
                      </>
                    ) : (
                      <>
                        <i className="fas fa-sign-in-alt me-2"></i>
                        Entrar
                      </>
                    )}
                  </button>
                </form>

                {/* Divisor */}
                <div className="text-center mb-3">
                  <div className="d-flex align-items-center">
                    <hr className="flex-grow-1" />
                    <span className="px-3 text-muted small">ou</span>
                    <hr className="flex-grow-1" />
                  </div>
                </div>

                {/* Link para cadastro */}
                <div className="text-center">
                  <p className="text-muted mb-0">
                    Não tem uma conta?
                    <Link 
                      to="/auth/register" 
                      className="text-decoration-none fw-medium ms-1"
                    >
                      Cadastre-se aqui
                    </Link>
                  </p>
                </div>
              </div>
            </div>

            {/* Informações adicionais */}
            <div className="text-center mt-4">
              <p className="text-muted small mb-2">
                Ao fazer login, você concorda com nossos
                <Link to="/termos" className="text-decoration-none ms-1">Termos de Uso</Link>
                {' '}e{' '}
                <Link to="/privacidade" className="text-decoration-none">Política de Privacidade</Link>
              </p>
              <p className="text-muted small">
                © 2025 CorteFácil. Todos os direitos reservados.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Login