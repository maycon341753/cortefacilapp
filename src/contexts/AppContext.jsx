import React, { createContext, useContext, useState, useEffect } from 'react'

const AppContext = createContext({})

export const useApp = () => {
  const context = useContext(AppContext)
  if (!context) {
    throw new Error('useApp deve ser usado dentro de um AppProvider')
  }
  return context
}

export const AppProvider = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  const [theme, setTheme] = useState('light')
  const [notifications, setNotifications] = useState([])
  const [isMobile, setIsMobile] = useState(false)

  // Detectar se é mobile
  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth <= 768)
    }

    checkMobile()
    window.addEventListener('resize', checkMobile)

    return () => window.removeEventListener('resize', checkMobile)
  }, [])

  // Carregar tema do localStorage
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme) {
      setTheme(savedTheme)
    }
  }, [])

  // Aplicar tema
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme)
    localStorage.setItem('theme', theme)
  }, [theme])

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen)
  }

  const closeSidebar = () => {
    setSidebarOpen(false)
  }

  const openSidebar = () => {
    setSidebarOpen(true)
  }

  const toggleTheme = () => {
    setTheme(theme === 'light' ? 'dark' : 'light')
  }

  const showLoading = () => {
    setLoading(true)
  }

  const hideLoading = () => {
    setLoading(false)
  }

  const addNotification = (notification) => {
    const id = Date.now()
    const newNotification = {
      id,
      ...notification,
      timestamp: new Date()
    }
    setNotifications(prev => [newNotification, ...prev])
    
    // Auto remover após 5 segundos se não for persistente
    if (!notification.persistent) {
      setTimeout(() => {
        removeNotification(id)
      }, 5000)
    }
  }

  const removeNotification = (id) => {
    setNotifications(prev => prev.filter(notif => notif.id !== id))
  }

  const clearNotifications = () => {
    setNotifications([])
  }

  const markNotificationAsRead = (id) => {
    setNotifications(prev => 
      prev.map(notif => 
        notif.id === id ? { ...notif, read: true } : notif
      )
    )
  }

  const getUnreadNotificationsCount = () => {
    return notifications.filter(notif => !notif.read).length
  }

  // Utilitários para formatação
  const formatDate = (date, format = 'dd/MM/yyyy') => {
    if (!date) return ''
    
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    
    switch (format) {
      case 'dd/MM/yyyy':
        return `${day}/${month}/${year}`
      case 'dd/MM/yyyy HH:mm':
        return `${day}/${month}/${year} ${hours}:${minutes}`
      case 'HH:mm':
        return `${hours}:${minutes}`
      case 'yyyy-MM-dd':
        return `${year}-${month}-${day}`
      default:
        return `${day}/${month}/${year}`
    }
  }

  const formatCurrency = (value) => {
    if (!value && value !== 0) return 'R$ 0,00'
    
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value)
  }

  const formatPhone = (phone) => {
    if (!phone) return ''
    
    const cleaned = phone.replace(/\D/g, '')
    
    if (cleaned.length === 11) {
      return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
    } else if (cleaned.length === 10) {
      return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
    }
    
    return phone
  }

  const formatCPF = (cpf) => {
    if (!cpf) return ''
    
    const cleaned = cpf.replace(/\D/g, '')
    
    if (cleaned.length === 11) {
      return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
    }
    
    return cpf
  }

  const formatCNPJ = (cnpj) => {
    if (!cnpj) return ''
    
    const cleaned = cnpj.replace(/\D/g, '')
    
    if (cleaned.length === 14) {
      return cleaned.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
    }
    
    return cnpj
  }

  // Debounce function
  const debounce = (func, wait) => {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }

  const value = {
    // Sidebar
    sidebarOpen,
    toggleSidebar,
    closeSidebar,
    openSidebar,
    
    // Loading
    loading,
    showLoading,
    hideLoading,
    
    // Theme
    theme,
    toggleTheme,
    
    // Notifications
    notifications,
    addNotification,
    removeNotification,
    clearNotifications,
    markNotificationAsRead,
    getUnreadNotificationsCount,
    
    // Device
    isMobile,
    
    // Utilities
    formatDate,
    formatCurrency,
    formatPhone,
    formatCPF,
    formatCNPJ,
    debounce
  }

  return (
    <AppContext.Provider value={value}>
      {children}
    </AppContext.Provider>
  )
}

export default AppContext