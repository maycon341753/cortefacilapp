import React, { useState } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { useApp } from '../contexts/AppContext'
import { useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'

const Header = ({ userType }) => {
  const { user, logout } = useAuth()
  const { toggleSidebar, isMobile, notifications, getUnreadNotificationsCount } = useApp()
  const navigate = useNavigate()
  const [showUserMenu, setShowUserMenu] = useState(false)
  const [showNotifications, setShowNotifications] = useState(false)

  const handleLogout = async () => {
    try {
      await logout()
      navigate('/login')
    } catch (error) {
      toast.error('Erro ao fazer logout')
    }
  }

  const getUserTypeLabel = (type) => {
    switch (type) {
      case 'admin':
        return 'Administrador'
      case 'parceiro':
        return 'Parceiro'
      case 'cliente':
        return 'Cliente'
      default:
        return 'Usuário'
    }
  }

  const unreadCount = getUnreadNotificationsCount()

  return (
    <header className="app-header d-flex align-items-center justify-content-between px-4">
      {/* Lado esquerdo - Toggle sidebar e título */}
      <div className="d-flex align-items-center">
        <button
          className="btn btn-link p-0 me-3 sidebar-toggle"
          onClick={toggleSidebar}
          aria-label="Toggle sidebar"
        >
          <i className="fas fa-bars fs-5"></i>
        </button>
        
        <div className="d-flex align-items-center">
          <h1 className="h4 mb-0 text-gradient fw-bold">
            CorteFácil
          </h1>
          {userType && (
            <span className="badge bg-primary ms-2">
              {getUserTypeLabel(userType)}
            </span>
          )}
        </div>
      </div>

      {/* Lado direito - Notificações e usuário */}
      <div className="d-flex align-items-center gap-3">
        {/* Notificações */}
        <div className="position-relative">
          <button
            className="btn btn-link p-0 position-relative"
            onClick={() => setShowNotifications(!showNotifications)}
            aria-label="Notificações"
          >
            <i className="fas fa-bell fs-5 text-muted"></i>
            {unreadCount > 0 && (
              <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {unreadCount > 99 ? '99+' : unreadCount}
                <span className="visually-hidden">notificações não lidas</span>
              </span>
            )}
          </button>
          
          {/* Dropdown de notificações */}
          {showNotifications && (
            <div className="position-absolute end-0 mt-2 bg-white border rounded shadow-lg" style={{ width: '320px', zIndex: 1050 }}>
              <div className="p-3 border-bottom">
                <h6 className="mb-0">Notificações</h6>
              </div>
              <div className="max-height-300 overflow-auto">
                {notifications.length > 0 ? (
                  notifications.slice(0, 5).map((notification) => (
                    <div
                      key={notification.id}
                      className={`p-3 border-bottom ${!notification.read ? 'bg-light' : ''}`}
                    >
                      <div className="d-flex align-items-start">
                        <div className="flex-grow-1">
                          <p className="mb-1 small">{notification.message}</p>
                          <small className="text-muted">
                            {new Date(notification.timestamp).toLocaleString('pt-BR')}
                          </small>
                        </div>
                        {!notification.read && (
                          <div className="bg-primary rounded-circle" style={{ width: '8px', height: '8px' }}></div>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="p-3 text-center text-muted">
                    <i className="fas fa-bell-slash mb-2"></i>
                    <p className="mb-0 small">Nenhuma notificação</p>
                  </div>
                )}
              </div>
              {notifications.length > 5 && (
                <div className="p-2 text-center border-top">
                  <button className="btn btn-link btn-sm">Ver todas</button>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Menu do usuário */}
        <div className="position-relative">
          <button
            className="btn btn-link p-0 d-flex align-items-center text-decoration-none"
            onClick={() => setShowUserMenu(!showUserMenu)}
            aria-label="Menu do usuário"
          >
            <div className="d-flex align-items-center">
              <div className="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style={{ width: '32px', height: '32px' }}>
                <i className="fas fa-user text-white small"></i>
              </div>
              {!isMobile && (
                <div className="text-start">
                  <div className="fw-medium small text-dark">
                    {user?.nome || 'Usuário'}
                  </div>
                  <div className="text-muted" style={{ fontSize: '0.75rem' }}>
                    {user?.email}
                  </div>
                </div>
              )}
              <i className="fas fa-chevron-down ms-2 small text-muted"></i>
            </div>
          </button>
          
          {/* Dropdown do usuário */}
          {showUserMenu && (
            <div className="position-absolute end-0 mt-2 bg-white border rounded shadow-lg" style={{ minWidth: '200px', zIndex: 1050 }}>
              <div className="p-3 border-bottom">
                <div className="fw-medium">{user?.nome}</div>
                <div className="text-muted small">{user?.email}</div>
                <div className="text-primary small">{getUserTypeLabel(user?.tipo_usuario)}</div>
              </div>
              
              <div className="py-1">
                <button
                  className="dropdown-item d-flex align-items-center px-3 py-2"
                  onClick={() => {
                    setShowUserMenu(false)
                    navigate(`/${userType}/perfil`)
                  }}
                >
                  <i className="fas fa-user me-2"></i>
                  Meu Perfil
                </button>
                
                <button
                  className="dropdown-item d-flex align-items-center px-3 py-2"
                  onClick={() => {
                    setShowUserMenu(false)
                    // Implementar configurações
                  }}
                >
                  <i className="fas fa-cog me-2"></i>
                  Configurações
                </button>
                
                <hr className="my-1" />
                
                <button
                  className="dropdown-item d-flex align-items-center px-3 py-2 text-danger"
                  onClick={() => {
                    setShowUserMenu(false)
                    handleLogout()
                  }}
                >
                  <i className="fas fa-sign-out-alt me-2"></i>
                  Sair
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
      
      {/* Overlay para fechar dropdowns */}
      {(showUserMenu || showNotifications) && (
        <div
          className="position-fixed top-0 start-0 w-100 h-100"
          style={{ zIndex: 1040 }}
          onClick={() => {
            setShowUserMenu(false)
            setShowNotifications(false)
          }}
        />
      )}
    </header>
  )
}

export default Header