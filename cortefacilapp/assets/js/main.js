/**
 * JavaScript principal para o Sistema SaaS de Agendamentos
 * Funcionalidades gerais e validações
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