import React, { useState, useEffect } from 'react'
import { Link, useSearchParams, useNavigate } from 'react-router-dom'
import authService from '../services/authService'
import '../styles/Auth.css'

const ResetPassword = () => {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const [formData, setFormData] = useState({
    newPassword: '',
    confirmPassword: ''
  })
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [token, setToken] = useState('')
  const [passwordReset, setPasswordReset] = useState(false)

  useEffect(() => {
    const tokenFromUrl = searchParams.get('token')
    if (!tokenFromUrl) {
      setError('Token de redefinição não encontrado. Solicite um novo link de recuperação.')
    } else {
      setToken(tokenFromUrl)
    }
  }, [searchParams])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
    }))
  }

  const validateForm = () => {
    if (formData.newPassword.length < 6) {
      setError('A senha deve ter pelo menos 6 caracteres')
      return false
    }
    
    if (formData.newPassword !== formData.confirmPassword) {
      setError('As senhas não coincidem')
      return false
    }
    
    return true
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setMessage('')
    if (!validateForm()) {
      setLoading(false)
      return
    }

    try {
      const response = await authService.resetPassword(token, formData.newPassword)
      setMessage(response.message || 'Senha redefinida com sucesso!')
      setPasswordReset(true)
      
      // Redirecionar para login após 3 segundos
      setTimeout(() => {
        navigate('/login')
      }, 3000)
    } catch (error) {
      setError(
        error.response?.data?.message || 
        'Erro ao redefinir senha. O token pode ter expirado.'
      )
    } finally {
      setLoading(false)
    }
  }

  if (passwordReset) {
    return (
      <div className="auth-container">
        <div className="auth-card">
          <div className="auth-header">
            <h2>Senha Redefinida!</h2>
          </div>
          <div className="auth-content">
            <div className="success-message">
              <p>{message}</p>
              <p>Você será redirecionado para o login em alguns segundos...</p>
            </div>
            <div className="auth-links">
              <Link to="/login" className="auth-link">
                Ir para Login
              </Link>
            </div>
          </div>
        </div>
      </div>
    )
  }

  if (!token) {
    return (
      <div className="auth-container">
        <div className="auth-card">
          <div className="auth-header">
            <h2>Link Inválido</h2>
          </div>
          <div className="auth-content">
            <div className="error-message">
              <p>Token de redefinição não encontrado ou inválido.</p>
            </div>
            <div className="auth-links">
              <Link to="/auth/esqueci-senha" className="auth-link">
                Solicitar Novo Link
              </Link>
              <Link to="/login" className="auth-link">
                Voltar ao Login
              </Link>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="auth-container">
      <div className="auth-card">
        <div className="auth-header">
          <h2>Redefinir Senha</h2>
          <p>Digite sua nova senha</p>
        </div>
        
        <form onSubmit={handleSubmit} className="auth-form">
          <div className="form-group">
            <label htmlFor="newPassword">Nova Senha</label>
            <input
              type="password"
              id="newPassword"
              name="newPassword"
              value={formData.newPassword}
              onChange={handleChange}
              placeholder="Digite sua nova senha"
              required
              disabled={loading}
              minLength={6}
            />
          </div>

          <div className="form-group">
            <label htmlFor="confirmPassword">Confirmar Nova Senha</label>
            <input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              placeholder="Confirme sua nova senha"
              required
              disabled={loading}
              minLength={6}
            />
          </div>

          {error && (
            <div className="error-message">
              {error}
            </div>
          )}

          {message && (
            <div className="success-message">
              {message}
            </div>
          )}

          <button 
            type="submit" 
            className="auth-button"
            disabled={loading || !formData.newPassword || !formData.confirmPassword}
          >
            {loading ? 'Redefinindo...' : 'Redefinir Senha'}
          </button>
        </form>

        <div className="auth-links">
          <Link to="/login" className="auth-link">
            Voltar ao Login
          </Link>
          <Link to="/auth/esqueci-senha" className="auth-link">
            Solicitar Novo Link
          </Link>
        </div>
      </div>
    </div>
  )
}

export default ResetPassword