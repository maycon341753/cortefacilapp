import React, { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const Register = () => {
  const navigate = useNavigate()
  const { register, isAuthenticated } = useAuth()
  const { setLoading, formatCPF, formatPhone } = useApp()
  const [currentStep, setCurrentStep] = useState(1)
  const [formData, setFormData] = useState({
    // Dados pessoais
    nome: '',
    email: '',
    telefone: '',
    cpf: '',
    dataNascimento: '',
    
    // Dados de acesso
    senha: '',
    confirmarSenha: '',
    
    // Tipo de usuário
    tipoUsuario: 'cliente', // cliente, parceiro
    
    // Dados do salão (apenas para parceiros)
    nomeSalao: '',
    cnpj: '',
    endereco: '',
    cidade: '',
    estado: '',
    cep: '',
    
    // Termos
    aceitarTermos: false,
    receberEmails: true
  })
  const [errors, setErrors] = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Redirecionar se já estiver autenticado
  useEffect(() => {
    if (isAuthenticated) {
      navigate('/dashboard', { replace: true })
    }
  }, [isAuthenticated, navigate])

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    let newValue = type === 'checkbox' ? checked : value

    // Formatação automática
    if (name === 'cpf') {
      newValue = formatCPF(value)
    } else if (name === 'telefone') {
      newValue = formatPhone(value)
    } else if (name === 'cep') {
      newValue = value.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2')
    } else if (name === 'cnpj') {
      newValue = value.replace(/\D/g, '').replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
    }

    setFormData(prev => ({
      ...prev,
      [name]: newValue
    }))
    
    // Limpar erro do campo quando o usuário começar a digitar
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateStep = (step) => {
    const newErrors = {}

    if (step === 1) {
      if (!formData.nome.trim()) {
        newErrors.nome = 'Nome é obrigatório'
      }

      if (!formData.email.trim()) {
        newErrors.email = 'Email é obrigatório'
      } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
        newErrors.email = 'Email inválido'
      }

      if (!formData.telefone.trim()) {
        newErrors.telefone = 'Telefone é obrigatório'
      }

      if (!formData.cpf.trim()) {
        newErrors.cpf = 'CPF é obrigatório'
      }

      if (!formData.dataNascimento) {
        newErrors.dataNascimento = 'Data de nascimento é obrigatória'
      }
    }

    if (step === 2) {
      if (!formData.senha) {
        newErrors.senha = 'Senha é obrigatória'
      } else if (formData.senha.length < 6) {
        newErrors.senha = 'Senha deve ter pelo menos 6 caracteres'
      }

      if (!formData.confirmarSenha) {
        newErrors.confirmarSenha = 'Confirmação de senha é obrigatória'
      } else if (formData.senha !== formData.confirmarSenha) {
        newErrors.confirmarSenha = 'Senhas não coincidem'
      }

      if (formData.tipoUsuario === 'parceiro') {
        if (!formData.nomeSalao.trim()) {
          newErrors.nomeSalao = 'Nome do salão é obrigatório'
        }
        if (!formData.cnpj.trim()) {
          newErrors.cnpj = 'CNPJ é obrigatório'
        }
        if (!formData.endereco.trim()) {
          newErrors.endereco = 'Endereço é obrigatório'
        }
        if (!formData.cidade.trim()) {
          newErrors.cidade = 'Cidade é obrigatória'
        }
        if (!formData.estado.trim()) {
          newErrors.estado = 'Estado é obrigatório'
        }
        if (!formData.cep.trim()) {
          newErrors.cep = 'CEP é obrigatório'
        }
      }

      if (!formData.aceitarTermos) {
        newErrors.aceitarTermos = 'Você deve aceitar os termos de uso'
      }
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleNext = () => {
    if (validateStep(currentStep)) {
      setCurrentStep(2)
    }
  }

  const handleBack = () => {
    setCurrentStep(1)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateStep(2)) {
      return
    }

    setIsSubmitting(true)
    setLoading(true)

    try {
      await register(formData)
      toast.success('Cadastro realizado com sucesso! Faça login para continuar.')
      navigate('/auth/login')
    } catch (error) {
      console.error('Erro no cadastro:', error)
      toast.error(error.message || 'Erro ao realizar cadastro. Tente novamente.')
    } finally {
      setIsSubmitting(false)
      setLoading(false)
    }
  }

  const estados = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
  ]

  return (
    <div className="min-vh-100 d-flex align-items-center bg-light py-3 py-md-5">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-12 col-sm-10 col-md-8 col-lg-6">
            <div className="card border-0 shadow-lg">
              <div className="card-body p-3 p-md-4 p-lg-5">
                {/* Logo e título */}
                <div className="text-center mb-3 mb-md-4">
                  <div className="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-md-3" style={{ width: '60px', height: '60px' }}>
                    <i className="fas fa-cut text-white fs-4 fs-md-2"></i>
                  </div>
                  <h2 className="text-gradient fw-bold mb-1 mb-md-2 fs-4 fs-md-3">CorteFácil</h2>
                  <p className="text-muted small">Crie sua conta</p>
                </div>

                {/* Indicador de progresso */}
                <div className="mb-3 mb-md-4">
                  <div className="d-flex justify-content-between align-items-center mb-2">
                    <span className={`badge small ${currentStep >= 1 ? 'bg-primary' : 'bg-light text-dark'}`}>
                      <span className="d-none d-sm-inline">1. Dados Pessoais</span>
                      <span className="d-sm-none">1. Dados</span>
                    </span>
                    <span className={`badge small ${currentStep >= 2 ? 'bg-primary' : 'bg-light text-dark'}`}>
                      2. Finalizar
                    </span>
                  </div>
                  <div className="progress" style={{ height: '4px' }}>
                    <div 
                      className="progress-bar bg-primary" 
                      style={{ width: `${(currentStep / 2) * 100}%` }}
                    ></div>
                  </div>
                </div>

                <form onSubmit={handleSubmit}>
                  {/* Etapa 1: Dados Pessoais */}
                  {currentStep === 1 && (
                    <div>
                      <h5 className="fw-bold mb-3">Dados Pessoais</h5>
                      
                      <div className="mb-3">
                        <label htmlFor="nome" className="form-label fw-medium">
                          <i className="fas fa-user me-2 text-muted"></i>
                          Nome Completo
                        </label>
                        <input
                          type="text"
                          className={`form-control ${errors.nome ? 'is-invalid' : ''}`}
                          id="nome"
                          name="nome"
                          value={formData.nome}
                          onChange={handleChange}
                          placeholder="Seu nome completo"
                        />
                        {errors.nome && <div className="invalid-feedback">{errors.nome}</div>}
                      </div>

                      <div className="row">
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="email" className="form-label fw-medium small">
                            <i className="fas fa-envelope me-2 text-muted"></i>
                            Email
                          </label>
                          <input
                            type="email"
                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                            id="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            placeholder="seu@email.com"
                          />
                          {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                        </div>
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="telefone" className="form-label fw-medium small">
                            <i className="fas fa-phone me-2 text-muted"></i>
                            Telefone
                          </label>
                          <input
                            type="text"
                            className={`form-control ${errors.telefone ? 'is-invalid' : ''}`}
                            id="telefone"
                            name="telefone"
                            value={formData.telefone}
                            onChange={handleChange}
                            placeholder="(11) 99999-9999"
                            maxLength="15"
                          />
                          {errors.telefone && <div className="invalid-feedback">{errors.telefone}</div>}
                        </div>
                      </div>

                      <div className="row">
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="cpf" className="form-label fw-medium small">
                            <i className="fas fa-id-card me-2 text-muted"></i>
                            CPF
                          </label>
                          <input
                            type="text"
                            className={`form-control ${errors.cpf ? 'is-invalid' : ''}`}
                            id="cpf"
                            name="cpf"
                            value={formData.cpf}
                            onChange={handleChange}
                            placeholder="000.000.000-00"
                            maxLength="14"
                          />
                          {errors.cpf && <div className="invalid-feedback">{errors.cpf}</div>}
                        </div>
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="dataNascimento" className="form-label fw-medium">
                            <i className="fas fa-calendar me-2 text-muted"></i>
                            Data de Nascimento
                          </label>
                          <input
                            type="date"
                            className={`form-control ${errors.dataNascimento ? 'is-invalid' : ''}`}
                            id="dataNascimento"
                            name="dataNascimento"
                            value={formData.dataNascimento}
                            onChange={handleChange}
                          />
                          {errors.dataNascimento && <div className="invalid-feedback">{errors.dataNascimento}</div>}
                        </div>
                      </div>

                      <div className="d-flex justify-content-between">
                        <Link to="/auth/login" className="btn btn-outline-secondary">
                          <i className="fas fa-arrow-left me-2"></i>
                          Voltar ao Login
                        </Link>
                        <button type="button" className="btn btn-primary" onClick={handleNext}>
                          Próximo
                          <i className="fas fa-arrow-right ms-2"></i>
                        </button>
                      </div>
                    </div>
                  )}

                  {/* Etapa 2: Finalizar */}
                  {currentStep === 2 && (
                    <div>
                      <h5 className="fw-bold mb-3">Finalizar Cadastro</h5>
                      
                      {/* Tipo de usuário */}
                      <div className="mb-4">
                        <label className="form-label fw-medium">
                          <i className="fas fa-user-tag me-2 text-muted"></i>
                          Tipo de Conta
                        </label>
                        <div className="row">
                          <div className="col-md-6">
                            <div className="form-check">
                              <input
                                className="form-check-input"
                                type="radio"
                                name="tipoUsuario"
                                id="cliente"
                                value="cliente"
                                checked={formData.tipoUsuario === 'cliente'}
                                onChange={handleChange}
                              />
                              <label className="form-check-label" htmlFor="cliente">
                                <i className="fas fa-user me-2"></i>
                                Cliente
                                <small className="d-block text-muted">Agendar serviços</small>
                              </label>
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="form-check">
                              <input
                                className="form-check-input"
                                type="radio"
                                name="tipoUsuario"
                                id="parceiro"
                                value="parceiro"
                                checked={formData.tipoUsuario === 'parceiro'}
                                onChange={handleChange}
                              />
                              <label className="form-check-label" htmlFor="parceiro">
                                <i className="fas fa-store me-2"></i>
                                Parceiro
                                <small className="d-block text-muted">Gerenciar salão</small>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Dados do salão (apenas para parceiros) */}
                      {formData.tipoUsuario === 'parceiro' && (
                        <div className="mb-4">
                          <h6 className="fw-bold mb-3 text-primary">Dados do Salão</h6>
                          
                          <div className="row">
                            <div className="col-md-8 mb-3">
                              <label htmlFor="nomeSalao" className="form-label fw-medium">
                                Nome do Salão
                              </label>
                              <input
                                type="text"
                                className={`form-control ${errors.nomeSalao ? 'is-invalid' : ''}`}
                                id="nomeSalao"
                                name="nomeSalao"
                                value={formData.nomeSalao}
                                onChange={handleChange}
                                placeholder="Nome do seu salão"
                              />
                              {errors.nomeSalao && <div className="invalid-feedback">{errors.nomeSalao}</div>}
                            </div>
                            <div className="col-md-4 mb-3">
                              <label htmlFor="cnpj" className="form-label fw-medium">
                                CNPJ
                              </label>
                              <input
                                type="text"
                                className={`form-control ${errors.cnpj ? 'is-invalid' : ''}`}
                                id="cnpj"
                                name="cnpj"
                                value={formData.cnpj}
                                onChange={handleChange}
                                placeholder="00.000.000/0000-00"
                                maxLength="18"
                              />
                              {errors.cnpj && <div className="invalid-feedback">{errors.cnpj}</div>}
                            </div>
                          </div>

                          <div className="mb-3">
                            <label htmlFor="endereco" className="form-label fw-medium">
                              Endereço
                            </label>
                            <input
                              type="text"
                              className={`form-control ${errors.endereco ? 'is-invalid' : ''}`}
                              id="endereco"
                              name="endereco"
                              value={formData.endereco}
                              onChange={handleChange}
                              placeholder="Rua, número, bairro"
                            />
                            {errors.endereco && <div className="invalid-feedback">{errors.endereco}</div>}
                          </div>

                          <div className="row">
                            <div className="col-md-5 mb-3">
                              <label htmlFor="cidade" className="form-label fw-medium">
                                Cidade
                              </label>
                              <input
                                type="text"
                                className={`form-control ${errors.cidade ? 'is-invalid' : ''}`}
                                id="cidade"
                                name="cidade"
                                value={formData.cidade}
                                onChange={handleChange}
                                placeholder="Cidade"
                              />
                              {errors.cidade && <div className="invalid-feedback">{errors.cidade}</div>}
                            </div>
                            <div className="col-md-3 mb-3">
                              <label htmlFor="estado" className="form-label fw-medium">
                                Estado
                              </label>
                              <select
                                className={`form-select ${errors.estado ? 'is-invalid' : ''}`}
                                id="estado"
                                name="estado"
                                value={formData.estado}
                                onChange={handleChange}
                              >
                                <option value="">Selecione</option>
                                {estados.map(estado => (
                                  <option key={estado} value={estado}>{estado}</option>
                                ))}
                              </select>
                              {errors.estado && <div className="invalid-feedback">{errors.estado}</div>}
                            </div>
                            <div className="col-md-4 mb-3">
                              <label htmlFor="cep" className="form-label fw-medium">
                                CEP
                              </label>
                              <input
                                type="text"
                                className={`form-control ${errors.cep ? 'is-invalid' : ''}`}
                                id="cep"
                                name="cep"
                                value={formData.cep}
                                onChange={handleChange}
                                placeholder="00000-000"
                                maxLength="9"
                              />
                              {errors.cep && <div className="invalid-feedback">{errors.cep}</div>}
                            </div>
                          </div>
                        </div>
                      )}

                      {/* Senha */}
                      <div className="row">
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="senha" className="form-label fw-medium small">
                            <i className="fas fa-lock me-2 text-muted"></i>
                            Senha
                          </label>
                          <input
                            type="password"
                            className={`form-control ${errors.senha ? 'is-invalid' : ''}`}
                            id="senha"
                            name="senha"
                            value={formData.senha}
                            onChange={handleChange}
                            placeholder="Mínimo 6 caracteres"
                          />
                          {errors.senha && <div className="invalid-feedback">{errors.senha}</div>}
                        </div>
                        <div className="col-12 col-md-6 mb-3">
                          <label htmlFor="confirmarSenha" className="form-label fw-medium small">
                            <i className="fas fa-lock me-2 text-muted"></i>
                            Confirmar Senha
                          </label>
                          <input
                            type="password"
                            className={`form-control ${errors.confirmarSenha ? 'is-invalid' : ''}`}
                            id="confirmarSenha"
                            name="confirmarSenha"
                            value={formData.confirmarSenha}
                            onChange={handleChange}
                            placeholder="Repita a senha"
                          />
                          {errors.confirmarSenha && <div className="invalid-feedback">{errors.confirmarSenha}</div>}
                        </div>
                      </div>

                      {/* Termos */}
                      <div className="mb-4">
                        <div className="form-check mb-2">
                          <input
                            className={`form-check-input ${errors.aceitarTermos ? 'is-invalid' : ''}`}
                            type="checkbox"
                            id="aceitarTermos"
                            name="aceitarTermos"
                            checked={formData.aceitarTermos}
                            onChange={handleChange}
                          />
                          <label className="form-check-label" htmlFor="aceitarTermos">
                            Aceito os <Link to="/termos" target="_blank">Termos de Uso</Link> e a <Link to="/privacidade" target="_blank">Política de Privacidade</Link>
                          </label>
                          {errors.aceitarTermos && <div className="invalid-feedback d-block">{errors.aceitarTermos}</div>}
                        </div>
                        
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="receberEmails"
                            name="receberEmails"
                            checked={formData.receberEmails}
                            onChange={handleChange}
                          />
                          <label className="form-check-label text-muted" htmlFor="receberEmails">
                            Desejo receber emails promocionais e novidades
                          </label>
                        </div>
                      </div>

                      <div className="d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <button type="button" className="btn btn-outline-secondary" onClick={handleBack}>
                          <i className="fas fa-arrow-left me-2"></i>
                          <span className="d-none d-sm-inline">Voltar</span>
                          <span className="d-sm-none">Voltar</span>
                        </button>
                        <button
                          type="submit"
                          className="btn btn-primary"
                          disabled={isSubmitting}
                        >
                          {isSubmitting ? (
                            <>
                              <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                              <span className="d-none d-sm-inline">Cadastrando...</span>
                              <span className="d-sm-none">Criando...</span>
                            </>
                          ) : (
                            <>
                              <i className="fas fa-user-plus me-2"></i>
                              <span className="d-none d-sm-inline">Criar Conta</span>
                              <span className="d-sm-none">Criar</span>
                            </>
                          )}
                        </button>
                      </div>
                    </div>
                  )}
                </form>

                {/* Link para login */}
                <div className="text-center mt-4">
                  <p className="text-muted mb-0">
                    Já tem uma conta?
                    <Link 
                      to="/auth/login" 
                      className="text-decoration-none fw-medium ms-1"
                    >
                      Faça login aqui
                    </Link>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Register