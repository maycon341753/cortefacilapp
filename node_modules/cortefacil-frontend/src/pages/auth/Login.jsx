import React, { useState, useEffect } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { toast } from 'react-toastify'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const Login = () => {
  const navigate = useNavigate()
  const location = useLocation()
  const { login, isAuthenticated } = useAuth()
  const { showLoading, hideLoading } = useApp()
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
      // Não redirecionar automaticamente, deixar o usuário escolher
      // O redirecionamento será feito após o login manual
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

    return newErrors
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    const newErrors = validateForm()
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors)
      return
    }

    setIsSubmitting(true)
    showLoading()
    
    try {
      const result = await login(formData.email, formData.senha, formData.lembrar)
      
      if (result.success) {
        toast.success('Login realizado com sucesso!')
        
        // Redirecionar baseado no tipo de usuário
        const userType = result.user.tipo
        const dashboardPath = {
          cliente: '/cliente/dashboard',
          parceiro: '/parceiro/dashboard', 
          admin: '/admin/dashboard'
        }[userType] || '/'
        
        // Usar página de origem se for uma rota válida, senão usar dashboard do usuário
        const from = location.state?.from?.pathname
        const redirectPath = (from && from !== '/login' && from !== '/register') ? from : dashboardPath
        
        navigate(redirectPath, { replace: true })
      }
    } catch (error) {
      console.error('Erro no login:', error)
      toast.error(error.message || 'Erro ao fazer login. Tente novamente.')
      setErrors({ submit: error.message || 'Erro ao fazer login' })
    } finally {
      setIsSubmitting(false)
      hideLoading()
    }
  }

  return (
    <div className="min-h-screen flex items-center bg-gray-50">
      <div className="w-full max-w-md mx-auto px-4">
        <div className="bg-white rounded-lg shadow-lg">
          <div className="p-6 sm:p-8">
            {/* Logo e título */}
            <div className="text-center mb-6">
              <div className="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i className="fas fa-cut text-white text-2xl"></i>
              </div>
              <h2 className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">CorteFácil</h2>
              <p className="text-gray-600 text-sm">Faça login em sua conta</p>
            </div>

            {/* Formulário */}
            <form onSubmit={handleSubmit}>
              <div className="mb-4">
                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                  <i className="fas fa-envelope mr-2 text-gray-400"></i>
                  Email
                </label>
                <input
                  type="email"
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${errors.email ? 'border-red-500' : 'border-gray-300'}`}
                  id="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  placeholder="seu@email.com"
                  disabled={isSubmitting}
                />
                {errors.email && (
                  <div className="text-red-500 text-sm mt-1">{errors.email}</div>
                )}
              </div>

              <div className="mb-4">
                <label htmlFor="senha" className="block text-sm font-medium text-gray-700 mb-2">
                  <i className="fas fa-lock mr-2 text-gray-400"></i>
                  Senha
                </label>
                <input
                  type="password"
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${errors.senha ? 'border-red-500' : 'border-gray-300'}`}
                  id="senha"
                  name="senha"
                  value={formData.senha}
                  onChange={handleChange}
                  placeholder="Sua senha"
                  disabled={isSubmitting}
                />
                {errors.senha && (
                  <div className="text-red-500 text-sm mt-1">{errors.senha}</div>
                )}
              </div>

              <div className="mb-6">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                  <div className="flex items-center">
                    <input
                      className="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                      type="checkbox"
                      id="lembrar"
                      name="lembrar"
                      checked={formData.lembrar}
                      onChange={handleChange}
                      disabled={isSubmitting}
                    />
                    <label className="ml-2 text-sm text-gray-600" htmlFor="lembrar">
                      Lembrar de mim
                    </label>
                  </div>
                  <Link 
                    to="/auth/esqueci-senha" 
                    className="text-blue-600 hover:text-blue-800 text-sm"
                  >
                    Esqueci minha senha
                  </Link>
                </div>
              </div>

              <button
                type="submit"
                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 mb-4"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <>
                    <span className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    Entrando...
                  </>
                ) : (
                  <>
                    <i className="fas fa-sign-in-alt mr-2"></i>
                    Entrar
                  </>
                )}
              </button>
            </form>

            {/* Divisor */}
            <div className="text-center mb-4">
              <div className="flex items-center">
                <hr className="flex-grow border-gray-300" />
                <span className="px-3 text-gray-500 text-sm">ou</span>
                <hr className="flex-grow border-gray-300" />
              </div>
            </div>

            {/* Link para cadastro */}
            <div className="text-center">
              <p className="text-gray-600 mb-0">
                Não tem uma conta?
                <Link 
                  to="/register" 
                  className="text-blue-600 hover:text-blue-800 font-medium ml-1"
                >
                  Cadastre-se aqui
                </Link>
              </p>
            </div>
          </div>
        </div>

        {/* Informações adicionais */}
        <div className="text-center mt-6">
          <p className="text-gray-500 text-sm mb-2">
            Ao fazer login, você concorda com nossos
            <Link to="/termos" className="text-blue-600 hover:text-blue-800 ml-1">Termos de Uso</Link>
            {' '}e{' '}
            <Link to="/privacidade" className="text-blue-600 hover:text-blue-800">Política de Privacidade</Link>
          </p>
          <p className="text-gray-500 text-sm">
            © 2025 CorteFácil. Todos os direitos reservados.
          </p>
        </div>
      </div>
    </div>
  )
}

export default Login