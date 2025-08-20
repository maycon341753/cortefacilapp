/**
 * CorteFácil - JavaScript Principal
 * Funcionalidades interativas do sistema
 */

// Configurações globais
const CorteFacil = {
    baseUrl: window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/'),
    
    // Inicialização
    init: function() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupFormValidation();
    },
    
    // Event Listeners
    setupEventListeners: function() {
        // Smooth scroll para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Criar overlay da sidebar
        this.createSidebarOverlay();
        
        // Toggle sidebar em mobile
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', this.toggleSidebar);
        }
        
        // Fechar sidebar ao clicar no overlay
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('sidebar-overlay')) {
                CorteFacil.closeSidebar();
            }
        });
        
        // Fechar sidebar com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                CorteFacil.closeSidebar();
            }
        });
        
        // Fechar alertas automaticamente
        this.autoCloseAlerts();
        
        // Máscara para telefone
        this.setupPhoneMask();
        
        // Validação em tempo real
        this.setupRealTimeValidation();
    },
    
    // Inicializar componentes
    initializeComponents: function() {
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Animações de entrada
        this.animateOnScroll();
    },
    
    // Criar overlay para sidebar
    createSidebarOverlay: function() {
        if (!document.querySelector('.sidebar-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }
    },
    
    // Toggle sidebar
    toggleSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar && overlay) {
            const isOpen = sidebar.classList.contains('show');
            
            if (isOpen) {
                CorteFacil.closeSidebar();
            } else {
                CorteFacil.openSidebar();
            }
        }
    },
    
    // Abrir sidebar
    openSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },
    
    // Fechar sidebar
    closeSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    },
    
    // Fechar alertas automaticamente
    autoCloseAlerts: function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    },
    
    // Máscara para telefone
    setupPhoneMask: function() {
        const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="telefone"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    if (value.length <= 10) {
                        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    } else {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    }
                }
                
                e.target.value = value;
            });
        });
    },
    
    // Validação em tempo real
    setupRealTimeValidation: function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    CorteFacil.validateField(this);
                });
            });
        });
    },
    
    // Validar campo individual
    validateField: function(field) {
        const value = field.value.trim();
        const type = field.type;
        const name = field.name;
        let isValid = true;
        let message = '';
        
        // Remover classes anteriores
        field.classList.remove('is-valid', 'is-invalid');
        
        // Validações específicas
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Este campo é obrigatório.';
        } else if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Digite um email válido.';
        } else if (name.includes('telefone') && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Digite um telefone válido.';
        } else if (type === 'password' && value && value.length < 6) {
            isValid = false;
            message = 'A senha deve ter pelo menos 6 caracteres.';
        }
        
        // Aplicar classes e mensagens
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
            this.showFieldError(field, message);
        }
        
        return isValid;
    },
    
    // Mostrar erro do campo
    showFieldError: function(field, message) {
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    },
    
    // Validação de formulário completo
    setupFormValidation: function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!CorteFacil.validateForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    },
    
    // Validar formulário
    validateForm: function(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Validar email
    isValidEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },
    
    // Validar telefone
    isValidPhone: function(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 11;
    },
    
    // Animações no scroll
    animateOnScroll: function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        });
        
        const elements = document.querySelectorAll('.feature-card, .dashboard-card');
        elements.forEach(el => observer.observe(el));
    },
    
    // Utilitários AJAX
    ajax: {
        get: function(url, callback) {
            fetch(url)
                .then(response => response.json())
                .then(data => callback(null, data))
                .catch(error => callback(error, null));
        },
        
        post: function(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => callback(null, data))
            .catch(error => callback(error, null));
        }
    },
    
    // Mostrar loading
    showLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const loading = document.createElement('div');
            loading.className = 'loading-overlay';
            loading.innerHTML = '<div class="loading"></div>';
            element.style.position = 'relative';
            element.appendChild(loading);
        }
    },
    
    // Esconder loading
    hideLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const loading = element.querySelector('.loading-overlay');
            if (loading) {
                loading.remove();
            }
        }
    },
    
    // Mostrar notificação
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove após 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    },
    
    // Confirmar ação
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // Formatar data
    formatDate: function(date, format = 'dd/mm/yyyy') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        return format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year);
    },
    
    // Formatar moeda
    formatCurrency: function(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },
    
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Funcionalidades específicas para agendamento
const Agendamento = {
    // Carregar salões
    carregarSaloes: function(callback) {
        CorteFacil.ajax.get('/api/saloes.php', callback);
    },
    
    // Carregar profissionais
    carregarProfissionais: function(salaoId, callback) {
        CorteFacil.ajax.get(`/api/profissionais.php?salao=${salaoId}`, callback);
    },
    
    // Carregar horários disponíveis
    carregarHorarios: function(profissionalId, data, callback) {
        CorteFacil.ajax.get(`/api/horarios.php?profissional=${profissionalId}&data=${data}`, callback);
    },
    
    // Criar agendamento
    criar: function(dados, callback) {
        CorteFacil.ajax.post('/api/agendamentos.php', dados, callback);
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    CorteFacil.init();
});

// Exportar para uso global
window.CorteFacil = CorteFacil;
window.Agendamento = Agendamento;