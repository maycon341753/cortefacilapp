import React from 'react'
import { useAuth } from '../contexts/AuthContext'
import { useApp } from '../contexts/AppContext'
import Sidebar from './Sidebar'
import Header from './Header'

const Layout = ({ children, userType }) => {
  const { user } = useAuth()
  const { sidebarOpen, isMobile } = useApp()

  return (
    <div className="app-container">
      {/* Sidebar */}
      <Sidebar userType={userType} />
      
      {/* Overlay para mobile */}
      {isMobile && sidebarOpen && (
        <div className="sidebar-overlay show" />
      )}
      
      {/* Conteúdo principal */}
      <div className={`main-content ${!isMobile ? 'with-sidebar' : ''}`}>
        {/* Header */}
        <Header userType={userType} />
        
        {/* Conteúdo da página */}
        <main className="container-fluid py-4">
          <div className="fade-in">
            {children}
          </div>
        </main>
      </div>
    </div>
  )
}

export default Layout