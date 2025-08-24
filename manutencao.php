<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .maintenance-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .maintenance-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .maintenance-text {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-retry {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .status-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #555;
        }
        .loading-dots {
            display: inline-block;
        }
        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(5, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1 class="maintenance-title h2">
            <i class="fas fa-cut me-2"></i>
            CorteFácil
        </h1>
        
        <h2 class="h4 mb-3">Sistema em Manutenção</h2>
        
        <p class="maintenance-text">
            Estamos realizando melhorias em nosso sistema para oferecer uma experiência ainda melhor.
            <br><br>
            <strong>Tempo estimado:</strong> alguns minutos
        </p>
        
        <button class="btn btn-primary btn-retry" onclick="location.reload()">
            <i class="fas fa-sync-alt me-2"></i>
            Tentar Novamente
        </button>
        
        <div class="status-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Status:</strong> Verificando conexões<span class="loading-dots"></span>
            <br>
            <small class="text-muted mt-2 d-block">
                Se o problema persistir, entre em contato conosco.
            </small>
        </div>
        
        <div class="mt-4">
            <a href="mailto:suporte@cortefacil.app" class="text-decoration-none me-3">
                <i class="fas fa-envelope me-1"></i>
                Suporte
            </a>
            <a href="tel:+5511999999999" class="text-decoration-none">
                <i class="fas fa-phone me-1"></i>
                Contato
            </a>
        </div>
    </div>
    
    <script>
        // Auto-refresh a cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Mostrar tempo decorrido
        let startTime = new Date().getTime();
        setInterval(function() {
            let elapsed = Math.floor((new Date().getTime() - startTime) / 1000);
            let minutes = Math.floor(elapsed / 60);
            let seconds = elapsed % 60;
            
            if (minutes > 0) {
                document.querySelector('.status-info small').innerHTML = 
                    `Aguardando há ${minutes}m ${seconds}s. Se o problema persistir, entre em contato conosco.`;
            } else {
                document.querySelector('.status-info small').innerHTML = 
                    `Aguardando há ${seconds}s. Se o problema persistir, entre em contato conosco.`;
            }
        }, 1000);
    </script>
</body>
</html>