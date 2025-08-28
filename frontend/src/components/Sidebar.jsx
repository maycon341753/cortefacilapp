import React from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { useApp } from '../contexts/AppContext'
import { useAuth } from '../contexts/AuthContext'

const Sidebar = ({ userType }) => {
  const { sidebarOpen, closeSidebar, isMobile } = useApp()
  const { user } = useAuth()
  const location = useLocation()

  // Configuração dos menus por tipo de usuário
  const menuItems = {
    cliente: [
      {
        path: '/cliente/dashboard',
        icon: 'fas fa-tachometer-alt',
        label: 'Dashboard',
        description: 'Visão geral'
      },
      {
        path: '/cliente/agendar',
        icon: 'fas fa-calendar-plus',
        label: 'Agendar',
        description: 'Novo agendamento'
      },
      {
        path: '/cliente/agendamentos',
        icon: 'fas fa-calendar-check',
        label: 'Meus Agendamentos',
        description: 'Histórico e próximos'
      },
      {
        path: '/cliente/saloes',
        icon: 'fas fa-store',
        label: 'Salões',
        description: 'Encontrar salões'
      },
      {
        path: '/cliente/perfil',
        icon: 'fas fa-user-circle',
        label: 'Meu Perfil',
        description: 'Dados pessoais'
      }
    ],
    parceiro: [
      {
        path: '/parceiro/dashboard',
        icon: 'fas fa-tachometer-alt',
        label: 'Dashboard',
        description: 'Visão geral'
      },
      {
        path: '/parceiro/agendamentos',
        icon: 'fas fa-calendar-check',
        label: 'Agendamentos',
        description: 'Gerenciar agenda'
      },
      {
        path: '/parceiro/profissionais',
        icon: 'fas fa-users',
        label: 'Profissionais',
        description: 'Equipe do salão'
      },
      {
        path: '/parceiro/servicos',
        icon: 'fas fa-cut',
        label: 'Serviços',
        description: 'Catálogo de serviços'
      },
      {
        path: '/parceiro/salao',
        icon: 'fas fa-store',
        label: 'Meu Salão',
        description: 'Informações do salão'
      },
      {
        path: '/parceiro/relatorios',
        icon: 'fas fa-chart-bar',
        label: 'Relatórios',
        description: 'Análises e métricas'
      }
    ],
    admin: [
      {
        path: '/admin/dashboard',
        icon: 'fas fa-tachometer-alt',
        label: 'Dashboard',
        description: 'Visão geral'
      },
      {
        path: '/admin/usuarios',
        icon: 'fas fa-users',
        label: 'Usuários',
        description: 'Gerenciar usuários'
      },
      {
        path: '/admin/saloes',
        icon: 'fas fa-store',
        label: 'Salões',
        description: 'Gerenciar salões'
      },
      {
        path: '/admin/agendamentos',
        icon: 'fas fa-calendar-check',
        label: 'Agendamentos',
        description: 'Todos os agendamentos'
      },
      {
        path: '/admin/relatorios',
        icon: 'fas fa-chart-line',
        label: 'Relatórios',
        description: 'Relatórios gerais'
      }
    ]
  }

  const currentMenuItems = menuItems[userType] || []

  const handleLinkClick = () => {
    if (isMobile) {
      closeSidebar()
    }
  }

  return (
    <aside className={`app-sidebar ${sidebarOpen && isMobile ? 'show' : ''} ${!sidebarOpen && isMobile ? 'collapsed' : ''}`}>
      {/* Logo e título */}
      <div className="p-3 p-md-4 border-bottom">
        <div className="d-flex align-items-center">
          <div className="bg-gradient rounded-circle d-flex align-items-center justify-content-center me-3" style={{ width: '36px', height: '36px' }}>
            <i className="fas fa-cut text-white"></i>
          </div>
          <div className="min-width-0 flex-grow-1">
            <h6 className="mb-0 text-gradient fw-bold text-truncate">CorteFácil</h6>
            <small className="text-muted text-truncate d-block">
              {userType === 'admin' && 'Administração'}
              {userType === 'parceiro' && 'Parceiro'}
              {userType === 'cliente' && 'Cliente'}
            </small>
          </div>
        </div>
      </div>

      {/* Informações do usuário */}
      <div className="p-3 border-bottom bg-light">
        <div className="d-flex align-items-center">
          <div className="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style={{ width: '36px', height: '36px' }}>
            <i className="fas fa-user text-white small"></i>
          </div>
          <div className="flex-grow-1 min-width-0">
            <div className="fw-medium text-truncate" style={{ fontSize: '0.9rem' }}>
              {user?.nome || 'Usuário'}
            </div>
            <div className="text-muted text-truncate" style={{ fontSize: '0.75rem' }}>
              {user?.email}
            </div>
          </div>
        </div>
      </div>

      {/* Menu de navegação */}
      <nav className="flex-grow-1 p-2 p-md-3">
        <ul className="list-unstyled">
          {currentMenuItems.map((item, index) => {
            const isActive = location.pathname === item.path
            
            return (
              <li key={index} className="mb-1">
                <NavLink
                  to={item.path}
                  className={({ isActive }) => 
                    `nav-link d-flex align-items-center px-2 px-md-3 py-2 rounded transition-custom text-decoration-none ${
                      isActive 
                        ? 'bg-primary text-white shadow-sm' 
                        : 'text-dark hover-bg-light'
                    }`
                  }
                  onClick={handleLinkClick}
                >
                  <i className={`${item.icon} me-2 me-md-3 flex-shrink-0`} style={{ width: '20px' }}></i>
                  <div className="flex-grow-1 min-width-0">
                    <div className="fw-medium text-truncate" style={{ fontSize: '0.9rem' }}>
                      {item.label}
                    </div>
                    <div 
                      className={`small text-truncate ${
                        isActive ? 'text-white-50' : 'text-muted'
                      }`}
                      style={{ fontSize: '0.75rem' }}
                    >
                      {item.description}
                    </div>
                  </div>
                </NavLink>
              </li>
            )
          })}
        </ul>
      </nav>

      {/* Rodapé da sidebar */}
      <div className="p-3 border-top">
        <div className="text-center">
          <small className="text-muted">
            © 2025 CorteFácil
          </small>
        </div>
      </div>
    </aside>
  )
}

export default Sidebar