import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'
import { useApp } from '../../contexts/AppContext'
import { toast } from 'react-toastify'

const Profile = () => {
  const { user, updateProfile } = useAuth()
  const { showLoading, hideLoading, formatCPF, formatPhone } = useApp()
  const [activeTab, setActiveTab] = useState('personal')
  const [formData, setFormData] = useState({
    nome: '',
    email: '',
    telefone: '',
    cpf: '',
    data_nascimento: '',
    endereco: {
      cep: '',
      rua: '',
      numero: '',
      complemento: '',
      bairro: '',
      cidade: '',
      estado: ''
    }
  })
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: ''
  })
  const [preferences, setPreferences] = useState({
    notifications_email: true,
    notifications_sms: false,
    notifications_push: true,
    marketing_emails: false
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    if (user) {
      setFormData({
        nome: user.nome || '',
        email: user.email || '',
        telefone: user.telefone || '',
        cpf: user.cpf || '',
        data_nascimento: user.data_nascimento || '',
        endereco: {
          cep: user.endereco?.cep || '',
          rua: user.endereco?.rua || '',
          numero: user.endereco?.numero || '',
          complemento: user.endereco?.complemento || '',
          bairro: user.endereco?.bairro || '',
          cidade: user.endereco?.cidade || '',
          estado: user.endereco?.estado || ''
        }
      })
    }
  }, [user])

  const handleInputChange = (e) => {
    const { name, value } = e.target
    
    if (name.startsWith('endereco.')) {
      const field = name.split('.')[1]
      setFormData(prev => ({
        ...prev,
        endereco: {
          ...prev.endereco,
          [field]: value
        }
      }))
    } else {
      let formattedValue = value
      
      // Aplicar formatação
      if (name === 'cpf') {
        formattedValue = formatCPF(value)
      } else if (name === 'telefone') {
        formattedValue = formatPhone(value)
      }
      
      setFormData(prev => ({
        ...prev,
        [name]: formattedValue
      }))
    }
    
    // Limpar erro do campo
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const handlePasswordChange = (e) => {
    const { name, value } = e.target
    setPasswordData(prev => ({ ...prev, [name]: value }))
    
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const handlePreferenceChange = (e) => {
    const { name, checked } = e.target
    setPreferences(prev => ({ ...prev, [name]: checked }))
  }

  const validatePersonalData = () => {
    const newErrors = {}
    
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
    
    return newErrors
  }

  const validatePassword = () => {
    const newErrors = {}
    
    if (!passwordData.current_password) {
      newErrors.current_password = 'Senha atual é obrigatória'
    }
    
    if (!passwordData.new_password) {
      newErrors.new_password = 'Nova senha é obrigatória'
    } else if (passwordData.new_password.length < 6) {
      newErrors.new_password = 'Nova senha deve ter pelo menos 6 caracteres'
    }
    
    if (passwordData.new_password !== passwordData.confirm_password) {
      newErrors.confirm_password = 'Confirmação de senha não confere'
    }
    
    return newErrors
  }

  const handlePersonalDataSubmit = async (e) => {
    e.preventDefault()
    
    const newErrors = validatePersonalData()
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors)
      return
    }
    
    try {
      showLoading()
      await updateProfile(formData)
      toast.success('Dados pessoais atualizados com sucesso!')
    } catch (error) {
      toast.error('Erro ao atualizar dados pessoais')
    } finally {
      hideLoading()
    }
  }

  const handlePasswordSubmit = async (e) => {
    e.preventDefault()
    
    const newErrors = validatePassword()
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors)
      return
    }
    
    try {
      setLoading(true)
      // Aqui seria a chamada para alterar senha
      // await authService.changePassword(passwordData)
      
      toast.success('Senha alterada com sucesso!')
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: ''
      })
    } catch (error) {
      toast.error('Erro ao alterar senha')
    } finally {
      setLoading(false)
    }
  }

  const handlePreferencesSubmit = async (e) => {
    e.preventDefault()
    
    try {
      setLoading(true)
      // Aqui seria a chamada para salvar preferências
      // await userService.updatePreferences(preferences)
      
      toast.success('Preferências atualizadas com sucesso!')
    } catch (error) {
      toast.error('Erro ao atualizar preferências')
    } finally {
      setLoading(false)
    }
  }

  const fetchAddressByCEP = async (cep) => {
    if (cep.length === 9) {
      try {
        const response = await fetch(`https://viacep.com.br/ws/${cep.replace('-', '')}/json/`)
        const data = await response.json()
        
        if (!data.erro) {
          setFormData(prev => ({
            ...prev,
            endereco: {
              ...prev.endereco,
              rua: data.logradouro,
              bairro: data.bairro,
              cidade: data.localidade,
              estado: data.uf
            }
          }))
        }
      } catch (error) {
        console.error('Erro ao buscar CEP:', error)
      }
    }
  }

  return (
    <div className="container-fluid">
      <div className="row">
        <div className="col-12">
          <div className="mb-4">
            <h1 className="h3 mb-0">Meu Perfil</h1>
            <p className="text-muted mb-0">Gerencie suas informações pessoais e preferências</p>
          </div>

          <div className="row">
            <div className="col-md-3 mb-4">
              <div className="card">
                <div className="card-body text-center">
                  <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                       style={{ width: '80px', height: '80px' }}>
                    <i className="fas fa-user text-white" style={{ fontSize: '2rem' }}></i>
                  </div>
                  <h5 className="fw-bold mb-1">{user?.nome}</h5>
                  <p className="text-muted small mb-3">{user?.email}</p>
                  <span className="badge bg-success">
                    <i className="fas fa-check-circle me-1"></i>
                    Cliente Ativo
                  </span>
                </div>
              </div>

              <div className="card mt-3">
                <div className="card-body">
                  <h6 className="fw-bold mb-3">Navegação</h6>
                  <div className="list-group list-group-flush">
                    <button 
                      className={`list-group-item list-group-item-action border-0 ${
                        activeTab === 'personal' ? 'active' : ''
                      }`}
                      onClick={() => setActiveTab('personal')}
                    >
                      <i className="fas fa-user me-2"></i>
                      Dados Pessoais
                    </button>
                    <button 
                      className={`list-group-item list-group-item-action border-0 ${
                        activeTab === 'password' ? 'active' : ''
                      }`}
                      onClick={() => setActiveTab('password')}
                    >
                      <i className="fas fa-lock me-2"></i>
                      Alterar Senha
                    </button>
                    <button 
                      className={`list-group-item list-group-item-action border-0 ${
                        activeTab === 'preferences' ? 'active' : ''
                      }`}
                      onClick={() => setActiveTab('preferences')}
                    >
                      <i className="fas fa-cog me-2"></i>
                      Preferências
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-md-9">
              {/* Dados Pessoais */}
              {activeTab === 'personal' && (
                <div className="card">
                  <div className="card-header">
                    <h5 className="mb-0">
                      <i className="fas fa-user me-2"></i>
                      Dados Pessoais
                    </h5>
                  </div>
                  <div className="card-body">
                    <form onSubmit={handlePersonalDataSubmit}>
                      <div className="row">
                        <div className="col-md-6 mb-3">
                          <label htmlFor="nome" className="form-label">Nome Completo *</label>
                          <input
                            type="text"
                            className={`form-control ${errors.nome ? 'is-invalid' : ''}`}
                            id="nome"
                            name="nome"
                            value={formData.nome}
                            onChange={handleInputChange}
                          />
                          {errors.nome && <div className="invalid-feedback">{errors.nome}</div>}
                        </div>
                        
                        <div className="col-md-6 mb-3">
                          <label htmlFor="email" className="form-label">Email *</label>
                          <input
                            type="email"
                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                            id="email"
                            name="email"
                            value={formData.email}
                            onChange={handleInputChange}
                          />
                          {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                        </div>
                      </div>
                      
                      <div className="row">
                        <div className="col-md-6 mb-3">
                          <label htmlFor="telefone" className="form-label">Telefone *</label>
                          <input
                            type="text"
                            className={`form-control ${errors.telefone ? 'is-invalid' : ''}`}
                            id="telefone"
                            name="telefone"
                            value={formData.telefone}
                            onChange={handleInputChange}
                            placeholder="(11) 99999-9999"
                          />
                          {errors.telefone && <div className="invalid-feedback">{errors.telefone}</div>}
                        </div>
                        
                        <div className="col-md-6 mb-3">
                          <label htmlFor="cpf" className="form-label">CPF *</label>
                          <input
                            type="text"
                            className={`form-control ${errors.cpf ? 'is-invalid' : ''}`}
                            id="cpf"
                            name="cpf"
                            value={formData.cpf}
                            onChange={handleInputChange}
                            placeholder="000.000.000-00"
                          />
                          {errors.cpf && <div className="invalid-feedback">{errors.cpf}</div>}
                        </div>
                      </div>
                      
                      <div className="row">
                        <div className="col-md-6 mb-3">
                          <label htmlFor="data_nascimento" className="form-label">Data de Nascimento</label>
                          <input
                            type="date"
                            className="form-control"
                            id="data_nascimento"
                            name="data_nascimento"
                            value={formData.data_nascimento}
                            onChange={handleInputChange}
                          />
                        </div>
                      </div>
                      
                      <hr className="my-4" />
                      
                      <h6 className="fw-bold mb-3">Endereço</h6>
                      
                      <div className="row">
                        <div className="col-md-4 mb-3">
                          <label htmlFor="endereco.cep" className="form-label">CEP</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.cep"
                            name="endereco.cep"
                            value={formData.endereco.cep}
                            onChange={handleInputChange}
                            onBlur={(e) => fetchAddressByCEP(e.target.value)}
                            placeholder="00000-000"
                          />
                        </div>
                        
                        <div className="col-md-6 mb-3">
                          <label htmlFor="endereco.rua" className="form-label">Rua</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.rua"
                            name="endereco.rua"
                            value={formData.endereco.rua}
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="col-md-2 mb-3">
                          <label htmlFor="endereco.numero" className="form-label">Número</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.numero"
                            name="endereco.numero"
                            value={formData.endereco.numero}
                            onChange={handleInputChange}
                          />
                        </div>
                      </div>
                      
                      <div className="row">
                        <div className="col-md-4 mb-3">
                          <label htmlFor="endereco.complemento" className="form-label">Complemento</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.complemento"
                            name="endereco.complemento"
                            value={formData.endereco.complemento}
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="col-md-4 mb-3">
                          <label htmlFor="endereco.bairro" className="form-label">Bairro</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.bairro"
                            name="endereco.bairro"
                            value={formData.endereco.bairro}
                            onChange={handleInputChange}
                          />
                        </div>
                        
                        <div className="col-md-4 mb-3">
                          <label htmlFor="endereco.cidade" className="form-label">Cidade</label>
                          <input
                            type="text"
                            className="form-control"
                            id="endereco.cidade"
                            name="endereco.cidade"
                            value={formData.endereco.cidade}
                            onChange={handleInputChange}
                          />
                        </div>
                      </div>
                      
                      <div className="row">
                        <div className="col-md-4 mb-3">
                          <label htmlFor="endereco.estado" className="form-label">Estado</label>
                          <select
                            className="form-select"
                            id="endereco.estado"
                            name="endereco.estado"
                            value={formData.endereco.estado}
                            onChange={handleInputChange}
                          >
                            <option value="">Selecione...</option>
                            <option value="SP">São Paulo</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="MG">Minas Gerais</option>
                            {/* Adicionar outros estados */}
                          </select>
                        </div>
                      </div>
                      
                      <div className="d-flex justify-content-end">
                        <button type="submit" className="btn btn-primary">
                          <i className="fas fa-save me-2"></i>
                          Salvar Alterações
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              )}

              {/* Alterar Senha */}
              {activeTab === 'password' && (
                <div className="card">
                  <div className="card-header">
                    <h5 className="mb-0">
                      <i className="fas fa-lock me-2"></i>
                      Alterar Senha
                    </h5>
                  </div>
                  <div className="card-body">
                    <form onSubmit={handlePasswordSubmit}>
                      <div className="row">
                        <div className="col-md-6">
                          <div className="mb-3">
                            <label htmlFor="current_password" className="form-label">Senha Atual *</label>
                            <input
                              type="password"
                              className={`form-control ${errors.current_password ? 'is-invalid' : ''}`}
                              id="current_password"
                              name="current_password"
                              value={passwordData.current_password}
                              onChange={handlePasswordChange}
                            />
                            {errors.current_password && <div className="invalid-feedback">{errors.current_password}</div>}
                          </div>
                          
                          <div className="mb-3">
                            <label htmlFor="new_password" className="form-label">Nova Senha *</label>
                            <input
                              type="password"
                              className={`form-control ${errors.new_password ? 'is-invalid' : ''}`}
                              id="new_password"
                              name="new_password"
                              value={passwordData.new_password}
                              onChange={handlePasswordChange}
                            />
                            {errors.new_password && <div className="invalid-feedback">{errors.new_password}</div>}
                          </div>
                          
                          <div className="mb-3">
                            <label htmlFor="confirm_password" className="form-label">Confirmar Nova Senha *</label>
                            <input
                              type="password"
                              className={`form-control ${errors.confirm_password ? 'is-invalid' : ''}`}
                              id="confirm_password"
                              name="confirm_password"
                              value={passwordData.confirm_password}
                              onChange={handlePasswordChange}
                            />
                            {errors.confirm_password && <div className="invalid-feedback">{errors.confirm_password}</div>}
                          </div>
                          
                          <button type="submit" className="btn btn-primary">
                            <i className="fas fa-key me-2"></i>
                            Alterar Senha
                          </button>
                        </div>
                        
                        <div className="col-md-6">
                          <div className="card bg-light">
                            <div className="card-body">
                              <h6 className="fw-bold mb-3">
                                <i className="fas fa-shield-alt me-2"></i>
                                Dicas de Segurança
                              </h6>
                              <ul className="list-unstyled small">
                                <li className="mb-2">
                                  <i className="fas fa-check text-success me-2"></i>
                                  Use pelo menos 8 caracteres
                                </li>
                                <li className="mb-2">
                                  <i className="fas fa-check text-success me-2"></i>
                                  Combine letras maiúsculas e minúsculas
                                </li>
                                <li className="mb-2">
                                  <i className="fas fa-check text-success me-2"></i>
                                  Inclua números e símbolos
                                </li>
                                <li className="mb-2">
                                  <i className="fas fa-check text-success me-2"></i>
                                  Evite informações pessoais
                                </li>
                                <li className="mb-0">
                                  <i className="fas fa-check text-success me-2"></i>
                                  Use uma senha única
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              )}

              {/* Preferências */}
              {activeTab === 'preferences' && (
                <div className="card">
                  <div className="card-header">
                    <h5 className="mb-0">
                      <i className="fas fa-cog me-2"></i>
                      Preferências
                    </h5>
                  </div>
                  <div className="card-body">
                    <form onSubmit={handlePreferencesSubmit}>
                      <h6 className="fw-bold mb-3">Notificações</h6>
                      
                      <div className="mb-3">
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="notifications_email"
                            name="notifications_email"
                            checked={preferences.notifications_email}
                            onChange={handlePreferenceChange}
                          />
                          <label className="form-check-label" htmlFor="notifications_email">
                            <strong>Notificações por Email</strong>
                            <br />
                            <small className="text-muted">Receber confirmações de agendamento e lembretes por email</small>
                          </label>
                        </div>
                      </div>
                      
                      <div className="mb-3">
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="notifications_sms"
                            name="notifications_sms"
                            checked={preferences.notifications_sms}
                            onChange={handlePreferenceChange}
                          />
                          <label className="form-check-label" htmlFor="notifications_sms">
                            <strong>Notificações por SMS</strong>
                            <br />
                            <small className="text-muted">Receber lembretes de agendamento por SMS</small>
                          </label>
                        </div>
                      </div>
                      
                      <div className="mb-3">
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="notifications_push"
                            name="notifications_push"
                            checked={preferences.notifications_push}
                            onChange={handlePreferenceChange}
                          />
                          <label className="form-check-label" htmlFor="notifications_push">
                            <strong>Notificações Push</strong>
                            <br />
                            <small className="text-muted">Receber notificações no navegador</small>
                          </label>
                        </div>
                      </div>
                      
                      <hr className="my-4" />
                      
                      <h6 className="fw-bold mb-3">Marketing</h6>
                      
                      <div className="mb-4">
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="marketing_emails"
                            name="marketing_emails"
                            checked={preferences.marketing_emails}
                            onChange={handlePreferenceChange}
                          />
                          <label className="form-check-label" htmlFor="marketing_emails">
                            <strong>Emails Promocionais</strong>
                            <br />
                            <small className="text-muted">Receber ofertas especiais e novidades dos salões</small>
                          </label>
                        </div>
                      </div>
                      
                      <div className="d-flex justify-content-end">
                        <button type="submit" className="btn btn-primary">
                          <i className="fas fa-save me-2"></i>
                          Salvar Preferências
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Profile