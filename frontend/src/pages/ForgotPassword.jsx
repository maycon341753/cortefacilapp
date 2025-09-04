import React, { useState } from 'react'
import { Link } from 'react-router-dom'
import authService from '../services/authService'
import '../styles/Auth.css'

const ForgotPassword = () => {
  const [email, setEmail] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [emailSent, setEmailSent] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setMessage('')

    try {
      const response = await authService.forgotPassword(email)
      setMessage(response.message || 'Email de recuperação enviado com sucesso!')
      setEmailSent(true)
    } catch (error) {
      setError(
        error.response?.data?.message || 
        'Erro ao enviar email de recuperação. Tente novamente.'
      )
    } finally {
      setLoading(false)
    }
  }

  if (emailSent) {
    return (
      <div className="auth-container">
        <div className="auth-card">
          <div className="auth-header">
            <h2>Email Enviado!</h2>
          </div>
          <div className="auth-content">
            <div className="success-message">
              <p>{message}</p>
              <p>Verifique sua caixa de entrada e siga as instruções para redefinir sua senha.</p>
            </div>
            <div className="auth-links">
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
          <h2>Esqueci Minha Senha</h2>
          <p>Digite seu email para receber as instruções de recuperação</p>
        </div>
        
        <form onSubmit={handleSubmit} className="auth-form">
          <div className="form-group">
            <label htmlFor="email">Email</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Digite seu email"
              required
              disabled={loading}
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
            disabled={loading || !email}
          >
            {loading ? 'Enviando...' : 'Enviar Email de Recuperação'}
          </button>
        </form>

        <div className="auth-links">
          <Link to="/login" className="auth-link">
            Voltar ao Login
          </Link>
          <Link to="/register" className="auth-link">
            Criar Nova Conta
          </Link>
        </div>
      </div>
    </div>
  )
}

export default ForgotPassword