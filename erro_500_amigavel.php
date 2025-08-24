<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviço Temporariamente Indisponível - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .error-icon {
            font-size: 5rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .error-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .status-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid #007bff;
        }
        .loading-dots {
            display: inline-block;
        }
        .loading-dots::after {
            content: '';
            animation: dots 2s infinite;
        }
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
        .retry-timer {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-tools"></i>
            </div>
            
            <h1 class="error-title h2">Serviço Temporariamente Indisponível</h1>
            
            <div class="error-message">
                <p class="mb-3">
                    <strong>Ops! Estamos passando por uma manutenção rápida.</strong>
                </p>
                <p class="mb-3">
                    Nossa equipe técnica está trabalhando para resolver este problema e 
                    restabelecer o serviço o mais rápido possível.
                </p>
                <p class="mb-0">
                    Pedimos desculpas pelo inconveniente e agradecemos sua paciência.
                </p>
            </div>
            
            <div class="status-info">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Status do Sistema
                </h5>
                <div class="row text-start">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            <strong>Início:</strong> <?php echo date('d/m/Y H:i'); ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-cog text-warning me-2"></i>
                            <strong>Status:</strong> Manutenção
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-users text-muted me-2"></i>
                            <strong>Afetados:</strong> Todos os usuários
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-wrench text-info me-2"></i>
                            <strong>Progresso:</strong> <span class="loading-dots">Trabalhando</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="/" class="btn-home me-3">
                    <i class="fas fa-home me-2"></i>
                    Voltar ao Início
                </a>
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt me-2"></i>
                    Tentar Novamente
                </button>
            </div>
            
            <div class="mt-4">
                <p class="text-muted small mb-2">
                    <i class="fas fa-clock me-1"></i>
                    Página será atualizada automaticamente em <span class="retry-timer" id="countdown">30</span> segundos
                </p>
                <p class="text-muted small">
                    <i class="fas fa-envelope me-1"></i>
                    Dúvidas? Entre em contato: <a href="mailto:suporte@cortefacil.app">suporte@cortefacil.app</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Contador regressivo para reload automático
        let countdown = 30;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                location.reload();
            }
        }, 1000);
        
        // Verificar status do servidor a cada 10 segundos
        const checkStatus = () => {
            fetch('/', { method: 'HEAD' })
                .then(response => {
                    if (response.ok) {
                        // Servidor voltou, redirecionar
                        window.location.href = '/';
                    }
                })
                .catch(() => {
                    // Ainda com problema, continuar verificando
                });
        };
        
        // Verificar status a cada 10 segundos
        setInterval(checkStatus, 10000);
        
        // Adicionar efeito de hover nos botões
        document.querySelectorAll('.btn-home, .btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>