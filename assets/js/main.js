/**
 * JavaScript principal para o Sistema SaaS de Agendamentos
 * Funcionalidades gerais e validações - Versão HTML/CSS/JS
 */

// Aguarda o carregamento completo da página
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializa a aplicação
 */
function initializeApp() {
    // Inicializa validações de formulário
    initFormValidations();
    
    // Inicializa modais
    initModals();
    
    // Inicializa tooltips e outros componentes
    initComponents();
    
    // Inicializa sistema de agendamento se estiver na página
    if (document.querySelector('.booking-system')) {
        initBookingSystem();
    }
    
    // Inicializa funcionalidades modernas
    initMobileMenu();
    initSmoothScrolling();
    initAnimations();
    initCounters();
}

/**
 * Validações de formulário
 */
function initFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Validação em tempo real
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

/**
 * Valida um formulário completo
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Valida um campo específico
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let message = '';
    
    // Remove mensagens de erro anteriores
    removeFieldError(field);
    
    // Verifica se é obrigatório
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'Este campo é obrigatório';
    }
    
    // Validações específicas por tipo
    if (value && isValid) {
        switch (type) {
            case 'email':
                if (!isValidEmail(value)) {
                    isValid = false;
                    message = 'Email inválido';
                }
                break;
                
            case 'tel':
                if (!isValidPhone(value)) {
                    isValid = false;
                    message = 'Telefone inválido';
                }
                break;
                
            case 'password':
                if (value.length < 6) {
                    isValid = false;
                    message = 'Senha deve ter pelo menos 6 caracteres';
                }
                break;
        }
    }
    
    // Validação de confirmação de senha
    if (field.name === 'confirm_password') {
        const passwordField = document.querySelector('input[name="senha"]');
        if (passwordField && value !== passwordField.value) {
            isValid = false;
            message = 'Senhas não coincidem';
        }
    }
    
    // Exibe erro se inválido
    if (!isValid) {
        showFieldError(field, message);
    }
    
    return isValid;
}

/**
 * Valida formato de email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valida formato de telefone
 */
function isValidPhone(phone) {
    const regex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
    return regex.test(phone);
}

/**
 * Exibe erro em um campo
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Remove erro de um campo
 */
function removeFieldError(field) {
    field.classList.remove('error');
    
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Inicializa sistema de modais
 */
function initModals() {
    // Botões que abrem modais
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Botões de fechar modal
    const closeButtons = document.querySelectorAll('.modal-close, [data-close-modal]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeModal();
        });
    });
    
    // Fechar modal clicando fora
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal();
        }
    });
}

/**
 * Abre um modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Fecha modal ativo
 */
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
}

/**
 * Inicializa componentes gerais
 */
function initComponents() {
    // Máscaras de input
    initInputMasks();
    
    // Confirmações de ação
    initConfirmations();
    
    // Auto-hide de alertas
    initAlertAutoHide();
}

/**
 * Inicializa máscaras de input
 */
function initInputMasks() {
    // Máscara de telefone
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatPhone(this.value);
        });
    });
}

/**
 * Formata telefone
 */
function formatPhone(value) {
    const numbers = value.replace(/\D/g, '');
    
    if (numbers.length <= 10) {
        return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    } else {
        return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
}

/**
 * Inicializa confirmações de ação
 */
function initConfirmations() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Auto-hide de alertas
 */
function initAlertAutoHide() {
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    alerts.forEach(alert => {
        const delay = parseInt(alert.getAttribute('data-auto-hide')) || 5000;
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, delay);
    });
}

/**
 * Sistema de agendamento
 */
function initBookingSystem() {
    // Seleção de salão
    const salaoSelect = document.getElementById('salao');
    if (salaoSelect) {
        salaoSelect.addEventListener('change', function() {
            loadProfissionais(this.value);
        });
    }
    
    // Seleção de profissional
    const profissionalSelect = document.getElementById('profissional');
    if (profissionalSelect) {
        profissionalSelect.addEventListener('change', function() {
            loadHorariosDisponiveis();
        });
    }
    
    // Seleção de data
    const dataInput = document.getElementById('data');
    if (dataInput) {
        dataInput.addEventListener('change', function() {
            loadHorariosDisponiveis();
        });
    }
}

/**
 * Carrega profissionais do salão selecionado
 */
function loadProfissionais(salaoId) {
    if (!salaoId) {
        document.getElementById('profissional').innerHTML = '<option value="">Selecione um profissional</option>';
        return;
    }
    
    fetch(`../api/get_profissionais.php?salao_id=${salaoId}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('profissional');
            select.innerHTML = '<option value="">Selecione um profissional</option>';
            
            data.forEach(profissional => {
                const option = document.createElement('option');
                option.value = profissional.id;
                option.textContent = `${profissional.nome} - ${profissional.especialidade}`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar profissionais:', error);
            showAlert('Erro ao carregar profissionais', 'danger');
        });
}

/**
 * Carrega horários disponíveis
 */
function loadHorariosDisponiveis() {
    const profissionalId = document.getElementById('profissional').value;
    const data = document.getElementById('data').value;
    
    if (!profissionalId || !data) {
        document.getElementById('horarios').innerHTML = '';
        return;
    }
    
    fetch(`../api/get_horarios.php?profissional_id=${profissionalId}&data=${data}`)
        .then(response => response.json())
        .then(data => {
            displayHorarios(data);
        })
        .catch(error => {
            console.error('Erro ao carregar horários:', error);
            showAlert('Erro ao carregar horários disponíveis', 'danger');
        });
}

/**
 * Exibe horários disponíveis
 */
function displayHorarios(horarios) {
    const container = document.getElementById('horarios');
    container.innerHTML = '';
    
    if (horarios.length === 0) {
        container.innerHTML = '<p class="text-center">Nenhum horário disponível para esta data</p>';
        return;
    }
    
    horarios.forEach(horario => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `time-slot ${horario.disponivel ? '' : 'unavailable'}`;
        button.textContent = horario.hora;
        button.disabled = !horario.disponivel;
        
        if (horario.disponivel) {
            button.addEventListener('click', function() {
                selectHorario(this, horario.hora);
            });
        }
        
        container.appendChild(button);
    });
}

/**
 * Seleciona um horário
 */
function selectHorario(button, hora) {
    // Remove seleção anterior
    document.querySelectorAll('.time-slot.selected').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Adiciona seleção atual
    button.classList.add('selected');
    
    // Define valor no input hidden
    const horarioInput = document.getElementById('horario_selecionado');
    if (horarioInput) {
        horarioInput.value = hora;
    }
    
    // Habilita botão de confirmar
    const confirmarBtn = document.getElementById('confirmar_agendamento');
    if (confirmarBtn) {
        confirmarBtn.disabled = false;
    }
}

/**
 * Exibe alerta
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insere no topo da página
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove após 5 segundos
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
}

/**
 * Confirma agendamento
 */
function confirmarAgendamento() {
    const form = document.getElementById('form_agendamento');
    if (!form) return;
    
    const formData = new FormData(form);
    
    fetch('../api/criar_agendamento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Agendamento realizado com sucesso!', 'success');
            // Redireciona para página de pagamento
            setTimeout(() => {
                window.location.href = `pagamento.php?agendamento_id=${data.agendamento_id}`;
            }, 2000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro ao criar agendamento:', error);
        showAlert('Erro ao processar agendamento', 'danger');
    });
}

/**
 * Simular Pagamento
 */
function simularPagamento(agendamentoId) {
    const btnPagar = document.getElementById('btn_pagar');
    
    if (!agendamentoId) {
        showAlert('ID do agendamento não fornecido', 'error');
        return;
    }
    
    // Desabilitar botão durante processamento
    btnPagar.disabled = true;
    btnPagar.innerHTML = '⏳ Processando...';
    
    // Simular delay de processamento
    setTimeout(() => {
        fetch('../api/processar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                agendamento_id: agendamentoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar sucesso
                showAlert('Pagamento processado com sucesso! Redirecionando...', 'success');
                
                // Redirecionar após 2 segundos
                setTimeout(() => {
                    window.location.href = 'dashboard.php?pagamento=sucesso';
                }, 2000);
            } else {
                throw new Error(data.message || 'Erro no pagamento');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro ao processar pagamento: ' + error.message, 'error');
            
            // Reabilitar botão
            btnPagar.disabled = false;
            btnPagar.innerHTML = '💳 Pagar R$ 1,29';
        });
    }, 1500); // Simular delay de 1.5 segundos
}

/**
 * Utilitários
 */

// Formatar data para exibição
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

// Formatar hora para exibição
function formatTime(timeString) {
    return timeString.substring(0, 5);
}

// Debounce para otimizar chamadas de API
function debounce(func, wait) {
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

/**
 * Funcionalidades Modernas Adicionais
 */

/**
 * Inicializa menu mobile
 */
function initMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Fechar menu ao clicar em um link
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
    }
}

/**
 * Inicializa scroll suave
 */
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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
}

/**
 * Inicializa animações de scroll
 */
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    // Observar elementos com classe fade-in
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
    
    // Observar cards de features
    document.querySelectorAll('.feature-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
    
    // Observar cards de steps
    document.querySelectorAll('.step-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
    
    // Observar CTA cards
    document.querySelectorAll('.cta-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
}

/**
 * Inicializa contadores animados
 */
function initCounters() {
    const counters = document.querySelectorAll('.hero-stat-number');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-target') || counter.textContent.replace(/\D/g, ''));
        const duration = 2000; // 2 segundos
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current).toLocaleString('pt-BR');
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString('pt-BR');
            }
        };
        
        updateCounter();
    };
    
    // Observer para iniciar animação quando visível
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                animateCounter(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

/**
 * Simulação de dados para demonstração
 */
function loadDemoData() {
    // Simular dados de estatísticas
    const stats = {
        salons: 1250,
        appointments: 15000,
        clients: 8500
    };
    
    // Atualizar estatísticas na página
    const salonStat = document.querySelector('[data-stat="salons"]');
    const appointmentStat = document.querySelector('[data-stat="appointments"]');
    const clientStat = document.querySelector('[data-stat="clients"]');
    
    if (salonStat) salonStat.setAttribute('data-target', stats.salons);
    if (appointmentStat) appointmentStat.setAttribute('data-target', stats.appointments);
    if (clientStat) clientStat.setAttribute('data-target', stats.clients);
}

/**
 * Funcionalidades de formulário aprimoradas
 */
function initEnhancedForms() {
    // Máscara para telefone
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d{4,5})(\d{4})/, '($1) $2-$3');
                e.target.value = value;
            }
        });
    });
    
    // Validação em tempo real aprimorada
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.classList.remove('error');
            const errorMsg = this.parentNode.querySelector('.field-error');
            if (errorMsg) errorMsg.remove();
        });
    });
}

/**
 * Sistema de notificações
 */
class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
    }
    
    createContainer() {
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
        return container;
    }
    
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            background: white;
            border-left: 4px solid ${this.getColor(type)};
            padding: 1rem;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        this.container.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto-remover
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
    
    getColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        return colors[type] || colors.info;
    }
}

// Instanciar sistema de notificações
const notifications = new NotificationSystem();

/**
 * Inicializar todas as funcionalidades quando a página carregar
 */
document.addEventListener('DOMContentLoaded', function() {
    loadDemoData();
    initEnhancedForms();
});