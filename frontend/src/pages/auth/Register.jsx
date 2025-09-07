import React, { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'

const Register = () => {
  const navigate = useNavigate()
  const { register, isAuthenticated } = useAuth()
  const { showLoading, hideLoading, formatCPF, formatPhone } = useApp()
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
    showLoading()

    try {
      // Mapear campos do frontend para o formato do backend
      const backendData = {
        nome: formData.nome,
        email: formData.email,
        password: formData.senha, // Mapear 'senha' para 'password'
        telefone: formData.telefone,
        tipo: formData.tipoUsuario, // Mapear 'tipoUsuario' para 'tipo'
        // Campos específicos para parceiros
        ...(formData.tipoUsuario === 'parceiro' && {
          nome_salao: formData.nomeSalao,
          endereco: `${formData.endereco}, ${formData.cidade} - ${formData.estado}, CEP: ${formData.cep}`
        })
      }
      
      const result = await register(backendData)
      
      if (result.success && result.user) {
        // Redirecionar baseado no tipo de usuário
        const userType = result.user.tipo
        
        if (userType === 'cliente') {
          navigate('/cliente/dashboard', { replace: true })
        } else if (userType === 'parceiro') {
          navigate('/parceiro/dashboard', { replace: true })
        } else {
          // Fallback para dashboard genérico
          navigate('/dashboard', { replace: true })
        }
      }
    } catch (error) {
      console.error('Erro no cadastro:', error)
      toast.error(error.message || 'Erro ao realizar cadastro. Tente novamente.')
    } finally {
      setIsSubmitting(false)
      hideLoading()
    }
  }

  const estados = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
  ]

  return (
    <div className="min-h-screen flex items-center bg-gray-50 py-4 md:py-8">
      <div className="container mx-auto px-4">
        <div className="flex justify-center">
          <div className="w-full max-w-lg">
            <div className="bg-white rounded-lg shadow-lg border-0">
              <div className="p-6 md:p-8">
                {/* Logo e título */}
                <div className="text-center mb-6">
                  <div className="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full inline-flex items-center justify-center mb-3">
                    <i className="fas fa-cut text-white text-xl"></i>
                  </div>
                  <h2 className="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">CorteFácil</h2>
                  <p className="text-gray-600 text-sm">Crie sua conta</p>
                </div>

                {/* Indicador de progresso */}
                <div className="mb-6">
                  <div className="flex justify-between items-center mb-2">
                    <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                      currentStep >= 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'
                    }`}>
                      <span className="hidden sm:inline">1. Dados Pessoais</span>
                      <span className="sm:hidden">1. Dados</span>
                    </span>
                    <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                      currentStep >= 2 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'
                    }`}>
                      2. Finalizar
                    </span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-1">
                    <div 
                      className="bg-blue-500 h-1 rounded-full transition-all duration-300" 
                      style={{ width: `${(currentStep / 2) * 100}%` }}
                    ></div>
                  </div>
                </div>

                <form onSubmit={handleSubmit}>
                  {/* Etapa 1: Dados Pessoais */}
                  {currentStep === 1 && (
                    <div>
                      <h5 className="text-lg font-bold mb-4">Dados Pessoais</h5>
                      
                      <div className="mb-4">
                        <label htmlFor="nome" className="block text-sm font-medium text-gray-700 mb-2">
                          <i className="fas fa-user mr-2 text-gray-500"></i>
                          Nome Completo
                        </label>
                        <input
                          type="text"
                          className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                            errors.nome ? 'border-red-500 bg-red-50' : 'border-gray-300'
                          }`}
                          id="nome"
                          name="nome"
                          value={formData.nome}
                          onChange={handleChange}
                          placeholder="Seu nome completo"
                        />
                        {errors.nome && <div className="text-red-500 text-sm mt-1">{errors.nome}</div>}
                      </div>

                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="mb-4">
                          <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-envelope mr-2 text-gray-500"></i>
                            Email
                          </label>
                          <input
                            type="email"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.email ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            placeholder="seu@email.com"
                          />
                          {errors.email && <div className="text-red-500 text-sm mt-1">{errors.email}</div>}
                        </div>
                        <div className="mb-4">
                          <label htmlFor="telefone" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-phone mr-2 text-gray-500"></i>
                            Telefone
                          </label>
                          <input
                            type="text"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.telefone ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="telefone"
                            name="telefone"
                            value={formData.telefone}
                            onChange={handleChange}
                            placeholder="(11) 99999-9999"
                            maxLength="15"
                          />
                          {errors.telefone && <div className="text-red-500 text-sm mt-1">{errors.telefone}</div>}
                        </div>
                      </div>

                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="mb-4">
                          <label htmlFor="cpf" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-id-card mr-2 text-gray-500"></i>
                            CPF
                          </label>
                          <input
                            type="text"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.cpf ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="cpf"
                            name="cpf"
                            value={formData.cpf}
                            onChange={handleChange}
                            placeholder="000.000.000-00"
                            maxLength="14"
                          />
                          {errors.cpf && <div className="text-red-500 text-sm mt-1">{errors.cpf}</div>}
                        </div>
                        <div className="mb-4">
                          <label htmlFor="dataNascimento" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-calendar mr-2 text-gray-500"></i>
                            Data de Nascimento
                          </label>
                          <input
                            type="date"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.dataNascimento ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="dataNascimento"
                            name="dataNascimento"
                            value={formData.dataNascimento}
                            onChange={handleChange}
                          />
                          {errors.dataNascimento && <div className="text-red-500 text-sm mt-1">{errors.dataNascimento}</div>}
                        </div>
                      </div>

                      <div className="flex justify-between">
                        <Link to="/auth/login" className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                          <i className="fas fa-arrow-left mr-2"></i>
                          Voltar ao Login
                        </Link>
                        <button type="button" className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors" onClick={handleNext}>
                          Próximo
                          <i className="fas fa-arrow-right ml-2"></i>
                        </button>
                      </div>
                    </div>
                  )}

                  {/* Etapa 2: Finalizar */}
                  {currentStep === 2 && (
                    <div>
                      <h5 className="text-lg font-bold mb-4">Finalizar Cadastro</h5>
                      
                      {/* Tipo de usuário */}
                      <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-3">
                          <i className="fas fa-user-tag mr-2 text-gray-500"></i>
                          Tipo de Conta
                        </label>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <div className="flex items-start">
                              <input
                                className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                type="radio"
                                name="tipoUsuario"
                                id="cliente"
                                value="cliente"
                                checked={formData.tipoUsuario === 'cliente'}
                                onChange={handleChange}
                              />
                              <label className="ml-3 cursor-pointer" htmlFor="cliente">
                                <div className="flex items-center">
                                  <i className="fas fa-user mr-2 text-blue-600"></i>
                                  <span className="font-medium">Cliente</span>
                                </div>
                                <small className="block text-gray-500 mt-1">Agendar serviços</small>
                              </label>
                            </div>
                          </div>
                          <div>
                            <div className="flex items-start">
                              <input
                                className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                type="radio"
                                name="tipoUsuario"
                                id="parceiro"
                                value="parceiro"
                                checked={formData.tipoUsuario === 'parceiro'}
                                onChange={handleChange}
                              />
                              <label className="ml-3 cursor-pointer" htmlFor="parceiro">
                                <div className="flex items-center">
                                  <i className="fas fa-store mr-2 text-blue-600"></i>
                                  <span className="font-medium">Parceiro</span>
                                </div>
                                <small className="block text-gray-500 mt-1">Gerenciar salão</small>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Dados do salão (apenas para parceiros) */}
                      {formData.tipoUsuario === 'parceiro' && (
                        <div className="mb-6">
                          <h6 className="text-base font-bold mb-4 text-blue-600">Dados do Salão</h6>
                          
                          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="md:col-span-2 mb-4">
                              <label htmlFor="nomeSalao" className="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Salão
                              </label>
                              <input
                                type="text"
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                  errors.nomeSalao ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                }`}
                                id="nomeSalao"
                                name="nomeSalao"
                                value={formData.nomeSalao}
                                onChange={handleChange}
                                placeholder="Nome do seu salão"
                              />
                              {errors.nomeSalao && <div className="text-red-500 text-sm mt-1">{errors.nomeSalao}</div>}
                            </div>
                            <div className="mb-4">
                              <label htmlFor="cnpj" className="block text-sm font-medium text-gray-700 mb-2">
                                CNPJ
                              </label>
                              <input
                                type="text"
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                  errors.cnpj ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                }`}
                                id="cnpj"
                                name="cnpj"
                                value={formData.cnpj}
                                onChange={handleChange}
                                placeholder="00.000.000/0000-00"
                                maxLength="18"
                              />
                              {errors.cnpj && <div className="text-red-500 text-sm mt-1">{errors.cnpj}</div>}
                            </div>
                          </div>

                          <div className="mb-4">
                            <label htmlFor="endereco" className="block text-sm font-medium text-gray-700 mb-2">
                              Endereço
                            </label>
                            <input
                              type="text"
                              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                errors.endereco ? 'border-red-500 bg-red-50' : 'border-gray-300'
                              }`}
                              id="endereco"
                              name="endereco"
                              value={formData.endereco}
                              onChange={handleChange}
                              placeholder="Rua, número, bairro"
                            />
                            {errors.endereco && <div className="text-red-500 text-sm mt-1">{errors.endereco}</div>}
                          </div>

                          <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div className="md:col-span-5 mb-4">
                              <label htmlFor="cidade" className="block text-sm font-medium text-gray-700 mb-2">
                                Cidade
                              </label>
                              <input
                                type="text"
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                  errors.cidade ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                }`}
                                id="cidade"
                                name="cidade"
                                value={formData.cidade}
                                onChange={handleChange}
                                placeholder="Cidade"
                              />
                              {errors.cidade && <div className="text-red-500 text-sm mt-1">{errors.cidade}</div>}
                            </div>
                            <div className="md:col-span-3 mb-4">
                              <label htmlFor="estado" className="block text-sm font-medium text-gray-700 mb-2">
                                Estado
                              </label>
                              <select
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                  errors.estado ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                }`}
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
                              {errors.estado && <div className="text-red-500 text-sm mt-1">{errors.estado}</div>}
                            </div>
                            <div className="md:col-span-4 mb-4">
                              <label htmlFor="cep" className="block text-sm font-medium text-gray-700 mb-2">
                                CEP
                              </label>
                              <input
                                type="text"
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                                  errors.cep ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                }`}
                                id="cep"
                                name="cep"
                                value={formData.cep}
                                onChange={handleChange}
                                placeholder="00000-000"
                                maxLength="9"
                              />
                              {errors.cep && <div className="text-red-500 text-sm mt-1">{errors.cep}</div>}
                            </div>
                          </div>
                        </div>
                      )}

                      {/* Senha */}
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="mb-4">
                          <label htmlFor="senha" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-lock mr-2 text-gray-500"></i>
                            Senha
                          </label>
                          <input
                            type="password"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.senha ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="senha"
                            name="senha"
                            value={formData.senha}
                            onChange={handleChange}
                            placeholder="Mínimo 6 caracteres"
                          />
                          {errors.senha && <div className="text-red-500 text-sm mt-1">{errors.senha}</div>}
                        </div>
                        <div className="mb-4">
                          <label htmlFor="confirmarSenha" className="block text-sm font-medium text-gray-700 mb-2">
                            <i className="fas fa-lock mr-2 text-gray-500"></i>
                            Confirmar Senha
                          </label>
                          <input
                            type="password"
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                              errors.confirmarSenha ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            }`}
                            id="confirmarSenha"
                            name="confirmarSenha"
                            value={formData.confirmarSenha}
                            onChange={handleChange}
                            placeholder="Repita a senha"
                          />
                          {errors.confirmarSenha && <div className="text-red-500 text-sm mt-1">{errors.confirmarSenha}</div>}
                        </div>
                      </div>

                      {/* Termos */}
                      <div className="mb-6">
                        <div className="flex items-start mb-3">
                          <input
                            className={`mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ${
                              errors.aceitarTermos ? 'border-red-500' : ''
                            }`}
                            type="checkbox"
                            id="aceitarTermos"
                            name="aceitarTermos"
                            checked={formData.aceitarTermos}
                            onChange={handleChange}
                          />
                          <label className="ml-3 text-sm text-gray-700 cursor-pointer" htmlFor="aceitarTermos">
                            Aceito os <Link to="/termos" target="_blank" className="text-blue-600 hover:text-blue-800 underline">Termos de Uso</Link> e a <Link to="/privacidade" target="_blank" className="text-blue-600 hover:text-blue-800 underline">Política de Privacidade</Link>
                          </label>
                        </div>
                        {errors.aceitarTermos && <div className="text-red-500 text-sm mb-3">{errors.aceitarTermos}</div>}
                        
                        <div className="flex items-start">
                          <input
                            className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            type="checkbox"
                            id="receberEmails"
                            name="receberEmails"
                            checked={formData.receberEmails}
                            onChange={handleChange}
                          />
                          <label className="ml-3 text-sm text-gray-500 cursor-pointer" htmlFor="receberEmails">
                            Desejo receber emails promocionais e novidades
                          </label>
                        </div>
                      </div>

                      <div className="flex flex-col sm:flex-row justify-between gap-3">
                        <button type="button" className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors" onClick={handleBack}>
                          <i className="fas fa-arrow-left mr-2"></i>
                          <span className="hidden sm:inline">Voltar</span>
                          <span className="sm:hidden">Voltar</span>
                        </button>
                        <button
                          type="submit"
                          className="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                          disabled={isSubmitting}
                        >
                          {isSubmitting ? (
                            <>
                              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                              <span className="hidden sm:inline">Cadastrando...</span>
                              <span className="sm:hidden">Criando...</span>
                            </>
                          ) : (
                            <>
                              <i className="fas fa-user-plus mr-2"></i>
                              <span className="hidden sm:inline">Criar Conta</span>
                              <span className="sm:hidden">Criar</span>
                            </>
                          )}
                        </button>
                      </div>
                    </div>
                  )}
                </form>

                {/* Link para login */}
                <div className="text-center mt-6">
                  <p className="text-gray-600 mb-0">
                    Já tem uma conta?
                    <Link 
                      to="/auth/login" 
                      className="text-blue-600 hover:text-blue-800 font-medium ml-1 no-underline hover:underline transition-colors"
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