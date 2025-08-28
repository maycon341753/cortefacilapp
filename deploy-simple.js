#!/usr/bin/env node

/**
 * Script de Deploy Simples para Hostinger
 * CorteFácil - Sistema de Agendamento para Salões
 */

const fs = require('fs');
const path = require('path');

const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  cyan: '\x1b[36m'
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function copyDirectory(src, dest) {
  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true });
  }
  
  const entries = fs.readdirSync(src, { withFileTypes: true });
  
  for (const entry of entries) {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);
    
    if (entry.isDirectory()) {
      copyDirectory(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}

function createDeployStructure() {
  log('🚀 Criando estrutura de deploy...', 'cyan');
  
  const deployDir = path.join(__dirname, 'deploy-hostinger');
  if (fs.existsSync(deployDir)) {
    log('📁 Removendo deploy anterior...', 'yellow');
    fs.rmSync(deployDir, { recursive: true, force: true });
  }
  
  fs.mkdirSync(deployDir, { recursive: true });
  log('✅ Diretório de deploy criado', 'green');
  
  return deployDir;
}

function createIndexHTML(deployDir) {
  log('📄 Criando index.html...', 'blue');
  
  const indexHTML = `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Sistema de Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn-custom {
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: #ff6b6b;
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }
        
        .btn-outline-custom {
            border: 2px solid white;
            color: white;
            background: transparent;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
        }
        
        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .feature-card {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .stats-section {
            background: #667eea;
            color: white;
            padding: 60px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(102, 126, 234, 0.95); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-cut me-2"></i>CorteFácil
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showLogin()">Entrar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showRegister()">Cadastrar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Transforme seu Salão</h1>
            <p class="hero-subtitle">A plataforma completa para agendamentos que seus clientes vão amar</p>
            <div class="mt-4">
                <button class="btn btn-primary-custom btn-custom" onclick="showRegister('parceiro')">
                    <i class="fas fa-store me-2"></i>Sou Salão
                </button>
                <button class="btn btn-outline-custom btn-custom" onclick="showRegister('cliente')">
                    <i class="fas fa-user me-2"></i>Sou Cliente
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold mb-3">Por que escolher o CorteFácil?</h2>
                    <p class="lead text-muted">Tecnologia que conecta salões e clientes de forma inteligente</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-calendar-check feature-icon"></i>
                        <h4>Agendamento Inteligente</h4>
                        <p class="text-muted">Sistema automatizado que evita conflitos e otimiza sua agenda</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-map-marker-alt feature-icon"></i>
                        <h4>Localização Precisa</h4>
                        <p class="text-muted">Encontre salões próximos com avaliações reais de clientes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-star feature-icon"></i>
                        <h4>Qualidade Garantida</h4>
                        <p class="text-muted">Sistema de avaliações que garante a melhor experiência</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <p>Salões Parceiros</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">10k+</span>
                        <p>Clientes Ativos</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">50k+</span>
                        <p>Agendamentos</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number">4.9★</span>
                        <p>Avaliação Média</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5><i class="fas fa-cut me-2"></i>CorteFácil</h5>
                    <p class="mb-0">Conectando salões e clientes com tecnologia e qualidade.</p>
                    <div class="mt-3">
                        <small>&copy; 2024 CorteFácil. Todos os direitos reservados.</small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.8); z-index: 9999;">
        <div class="text-center text-white">
            <div class="spinner-border text-light mb-3" role="status"></div>
            <p>Carregando aplicação...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogin() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            // Simular carregamento da aplicação React
            setTimeout(() => {
                alert('Em desenvolvimento: Sistema de login será carregado aqui!');
                document.getElementById('loadingOverlay').style.display = 'none';
            }, 2000);
        }
        
        function showRegister(type = 'cliente') {
            document.getElementById('loadingOverlay').style.display = 'flex';
            // Simular carregamento da aplicação React
            setTimeout(() => {
                alert('Em desenvolvimento: Cadastro de ' + type + ' será carregado aqui!');
                document.getElementById('loadingOverlay').style.display = 'none';
            }, 2000);
        }
        
        // Animações suaves
        window.addEventListener('scroll', () => {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        });
        
        // Inicializar animações
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
            });
        });
    </script>
</body>
</html>`;
  
  fs.writeFileSync(path.join(deployDir, 'index.html'), indexHTML);
  log('✅ index.html criado', 'green');
}

function setupBackend(deployDir) {
  log('⚙️ Configurando backend...', 'blue');
  
  const serverDir = path.join(deployDir, 'server');
  fs.mkdirSync(serverDir, { recursive: true });
  
  // Copiar arquivos do backend se existirem
  const backendPath = path.join(__dirname, 'backend');
  if (fs.existsSync(backendPath)) {
    copyDirectory(backendPath, serverDir);
    log('📄 Backend copiado', 'green');
  } else {
    // Criar backend básico
    createBasicBackend(serverDir);
  }
}

function createBasicBackend(serverDir) {
  log('🔧 Criando backend básico...', 'blue');
  
  // server.js
  const serverJS = `const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, '../')));

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', message: 'CorteFácil API funcionando!' });
});

// Rota principal - servir o frontend
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, '../index.html'));
});

app.listen(PORT, () => {
  console.log(\`🚀 Servidor rodando na porta \${PORT}\`);
  console.log(\`📱 Acesse: http://localhost:\${PORT}\`);
});`;
  
  fs.writeFileSync(path.join(serverDir, 'server.js'), serverJS);
  
  // package.json
  const packageJSON = {
    "name": "cortefacil-backend",
    "version": "1.0.0",
    "description": "Backend do CorteFácil",
    "main": "server.js",
    "scripts": {
      "start": "node server.js",
      "dev": "nodemon server.js"
    },
    "dependencies": {
      "express": "^4.18.2",
      "cors": "^2.8.5",
      "dotenv": "^16.3.1"
    },
    "engines": {
      "node": ">=16.0.0"
    }
  };
  
  fs.writeFileSync(
    path.join(serverDir, 'package.json'),
    JSON.stringify(packageJSON, null, 2)
  );
  
  log('✅ Backend básico criado', 'green');
}

function createHtaccess(deployDir) {
  log('🔧 Criando .htaccess...', 'blue');
  
  const htaccessContent = `RewriteEngine On

# Redirecionar API para Node.js
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]

# Servir arquivos estáticos
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L]

# Headers de segurança
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Compressão GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>`;
  
  fs.writeFileSync(path.join(deployDir, '.htaccess'), htaccessContent);
  log('✅ .htaccess criado', 'green');
}

function createEnvTemplate(deployDir) {
  log('📝 Criando .env template...', 'blue');
  
  const envTemplate = `# Configurações de Produção - Hostinger
NODE_ENV=production
PORT=3000

# Banco de Dados (configure com seus dados)
DB_HOST=localhost
DB_USER=seu_usuario_mysql
DB_PASSWORD=sua_senha_mysql
DB_NAME=cortefacil

# JWT Secret (gere uma chave segura)
JWT_SECRET=seu_jwt_secret_super_seguro

# URLs
FRONTEND_URL=https://seudominio.com
BACKEND_URL=https://seudominio.com/api`;
  
  fs.writeFileSync(path.join(deployDir, 'server', '.env.template'), envTemplate);
  log('✅ Template .env criado', 'green');
}

function createInstructions(deployDir) {
  log('📋 Criando instruções...', 'blue');
  
  const instructions = `# INSTRUÇÕES DE DEPLOY - HOSTINGER

## ✅ Arquivos Preparados
- index.html (página principal)
- server/ (backend Node.js)
- .htaccess (configuração Apache)

## 📤 COMO FAZER O DEPLOY:

### 1. Upload dos Arquivos
- Faça upload de TODOS os arquivos desta pasta para seu domínio no Painel Fácil
- Estrutura no servidor:
  /public_html/seudominio/
    - index.html
    - .htaccess
    - server/ (pasta completa)

### 2. Configurar Node.js no Painel Fácil
- Vá em "Configurações" do seu projeto
- Selecione "Node.js" como runtime
- Arquivo principal: server/server.js
- Porta: 3000

### 3. Instalar Dependências
- No terminal do Painel Fácil:
  cd /public_html/seudominio/server
  npm install

### 4. Configurar Variáveis (Opcional)
- Copie server/.env.template para server/.env
- Configure com seus dados se necessário

### 5. Iniciar Aplicação
- No Painel Fácil, clique em "Iniciar"
- Ou no terminal: node server/server.js

### 6. Testar
- Acesse: https://seudominio.com
- API: https://seudominio.com/api/health

## 🎉 Pronto!
Seu site estará funcionando com:
- ✅ Página principal responsiva
- ✅ Backend Node.js
- ✅ API básica funcionando

---
📞 Suporte: Consulte a documentação da Hostinger para mais detalhes.`;
  
  fs.writeFileSync(path.join(deployDir, 'LEIA-ME.txt'), instructions);
  log('✅ Instruções criadas', 'green');
}

function main() {
  try {
    log('🎯 CorteFácil - Deploy Simples para Hostinger', 'bright');
    log('===============================================', 'bright');
    
    const deployDir = createDeployStructure();
    createIndexHTML(deployDir);
    setupBackend(deployDir);
    createHtaccess(deployDir);
    createEnvTemplate(deployDir);
    createInstructions(deployDir);
    
    log('', 'reset');
    log('🎉 DEPLOY PREPARADO COM SUCESSO!', 'green');
    log('===============================================', 'green');
    log('📁 Arquivos prontos em: ./deploy-hostinger/', 'cyan');
    log('📋 Leia: ./deploy-hostinger/LEIA-ME.txt', 'yellow');
    log('', 'reset');
    log('🚀 Faça upload da pasta deploy-hostinger/ para o Painel Fácil!', 'bright');
    
  } catch (error) {
    log(`❌ Erro: ${error.message}`, 'red');
    process.exit(1);
  }
}

if (require.main === module) {
  main();
}

module.exports = { main };