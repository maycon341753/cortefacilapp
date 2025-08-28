#!/usr/bin/env node

/**
 * Script de Deploy Automatizado para Hostinger
 * CorteFácil - Sistema de Agendamento para Salões
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m'
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function createDeployStructure() {
  log('🚀 Iniciando processo de deploy...', 'cyan');
  
  // Criar diretório de deploy
  const deployDir = path.join(__dirname, 'deploy');
  if (fs.existsSync(deployDir)) {
    log('📁 Removendo deploy anterior...', 'yellow');
    fs.rmSync(deployDir, { recursive: true, force: true });
  }
  
  fs.mkdirSync(deployDir, { recursive: true });
  log('📁 Diretório de deploy criado', 'green');
  
  return deployDir;
}

function buildFrontend(deployDir) {
  log('🔨 Fazendo build do frontend...', 'blue');
  
  try {
    // Build do React
    execSync('npm run build', { stdio: 'inherit' });
    
    // Copiar arquivos do build para deploy
    const buildDir = path.join(__dirname, 'build');
    if (fs.existsSync(buildDir)) {
      copyDirectory(buildDir, deployDir);
      log('✅ Frontend buildado com sucesso', 'green');
    } else {
      throw new Error('Diretório build não encontrado');
    }
  } catch (error) {
    log(`❌ Erro no build do frontend: ${error.message}`, 'red');
    process.exit(1);
  }
}

function setupBackend(deployDir) {
  log('⚙️ Configurando backend...', 'blue');
  
  const serverDir = path.join(deployDir, 'server');
  fs.mkdirSync(serverDir, { recursive: true });
  
  // Copiar arquivos do backend
  const backendFiles = [
    'backend/server.js',
    'backend/package.json',
    'backend/config',
    'backend/routes',
    'backend/middleware',
    'backend/models',
    'backend/utils'
  ];
  
  backendFiles.forEach(file => {
    const sourcePath = path.join(__dirname, file);
    const destPath = path.join(serverDir, path.basename(file));
    
    if (fs.existsSync(sourcePath)) {
      if (fs.statSync(sourcePath).isDirectory()) {
        copyDirectory(sourcePath, destPath);
      } else {
        fs.copyFileSync(sourcePath, destPath);
      }
      log(`📄 Copiado: ${file}`, 'green');
    }
  });
  
  // Criar package.json otimizado para produção
  const productionPackage = {
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
      "mysql2": "^3.6.0",
      "bcryptjs": "^2.4.3",
      "jsonwebtoken": "^9.0.2",
      "cors": "^2.8.5",
      "dotenv": "^16.3.1",
      "helmet": "^7.0.0",
      "express-rate-limit": "^6.10.0",
      "express-validator": "^7.0.1"
    },
    "engines": {
      "node": ">=18.0.0"
    }
  };
  
  fs.writeFileSync(
    path.join(serverDir, 'package.json'),
    JSON.stringify(productionPackage, null, 2)
  );
  
  log('✅ Backend configurado', 'green');
}

function createHtaccess(deployDir) {
  log('🔧 Criando .htaccess...', 'blue');
  
  const htaccessContent = `RewriteEngine On

# Redirecionar API para Node.js (porta 3000)
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]

# Servir arquivos estáticos do React
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L]

# Headers de segurança
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Compressão GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache para arquivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>`;
  
  fs.writeFileSync(path.join(deployDir, '.htaccess'), htaccessContent);
  log('✅ .htaccess criado', 'green');
}

function createEnvTemplate(deployDir) {
  log('📝 Criando template de variáveis de ambiente...', 'blue');
  
  const envTemplate = `# Configurações de Produção - Hostinger
# Copie este arquivo para .env no servidor e configure os valores

NODE_ENV=production
PORT=3000

# Banco de Dados MySQL
DB_HOST=localhost
DB_USER=seu_usuario_mysql
DB_PASSWORD=sua_senha_mysql
DB_NAME=cortefacil
DB_PORT=3306

# JWT Secret (gere uma chave segura)
JWT_SECRET=seu_jwt_secret_super_seguro_aqui

# URLs
FRONTEND_URL=https://seudominio.com
BACKEND_URL=https://seudominio.com/api

# Configurações de Email (opcional)
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=seu_email@seudominio.com
SMTP_PASS=sua_senha_email

# Configurações de Upload
UPLOAD_MAX_SIZE=5242880
UPLOAD_PATH=/uploads

# Rate Limiting
RATE_LIMIT_WINDOW=900000
RATE_LIMIT_MAX=100`;
  
  fs.writeFileSync(path.join(deployDir, 'server', '.env.template'), envTemplate);
  log('✅ Template .env criado', 'green');
}

function createDatabaseSchema(deployDir) {
  log('🗄️ Criando schema do banco de dados...', 'blue');
  
  const databaseDir = path.join(deployDir, 'database');
  fs.mkdirSync(databaseDir, { recursive: true });
  
  const schemaSQL = `-- Schema do Banco de Dados CorteFácil
-- Execute este script no phpMyAdmin ou Adminer

CREATE DATABASE IF NOT EXISTS cortefacil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cortefacil;

-- Tabela de usuários
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('cliente', 'parceiro', 'admin') DEFAULT 'cliente',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de salões
CREATE TABLE salons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    opening_hours JSON,
    services JSON,
    images JSON,
    rating DECIMAL(3,2) DEFAULT 0,
    total_reviews INT DEFAULT 0,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de agendamentos
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    salon_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_price DECIMAL(10,2) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
);

-- Tabela de avaliações
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    salon_id INT NOT NULL,
    appointment_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
);

-- Inserir usuário admin padrão
INSERT INTO users (name, email, password, role) VALUES 
('Administrador', 'admin@cortefacil.com', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Índices para performance
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_salon ON appointments(salon_id);
CREATE INDEX idx_appointments_client ON appointments(client_id);
CREATE INDEX idx_salons_status ON salons(status);
CREATE INDEX idx_users_role ON users(role);
`;
  
  fs.writeFileSync(path.join(databaseDir, 'schema.sql'), schemaSQL);
  log('✅ Schema do banco criado', 'green');
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

function createDeployInstructions(deployDir) {
  log('📋 Criando instruções de deploy...', 'blue');
  
  const instructions = `# 🚀 INSTRUÇÕES DE DEPLOY - HOSTINGER

## ✅ Arquivos Preparados
Todos os arquivos necessários estão na pasta 'deploy/':

- ✅ Frontend buildado (React)
- ✅ Backend configurado (Node.js)
- ✅ .htaccess configurado
- ✅ Schema do banco de dados
- ✅ Template de variáveis de ambiente

## 📤 PRÓXIMOS PASSOS:

### 1. Upload dos Arquivos
- Faça upload de TODOS os arquivos da pasta 'deploy/' para seu domínio no Painel Fácil
- Estrutura final no servidor:
  /public_html/seudominio/
    - index.html
    - .htaccess
    - static/ (arquivos CSS/JS)
    - server/ (backend Node.js)
    - database/ (schema SQL)

### 2. Configurar Banco de Dados
- No Painel Fácil, crie um banco MySQL chamado 'cortefacil'
- Importe o arquivo 'database/schema.sql' via phpMyAdmin
- Anote usuário e senha do banco

### 3. Configurar Variáveis de Ambiente
- Copie 'server/.env.template' para 'server/.env'
- Configure com seus dados reais:
  - DB_USER, DB_PASSWORD (dados do MySQL)
  - JWT_SECRET (gere uma chave segura)
  - FRONTEND_URL (seu domínio)

### 4. Instalar Dependências
- No terminal do Painel Fácil:
  cd /public_html/seudominio/server
  npm install --production

### 5. Iniciar Aplicação
- Configure Node.js no Painel Fácil:
  - Runtime: Node.js 18+
  - Arquivo principal: server/server.js
  - Porta: 3000
- Inicie a aplicação

### 6. Testar
- Frontend: https://seudominio.com
- API: https://seudominio.com/api/health

## 🆘 Suporte
Se precisar de ajuda, consulte o arquivo 'DEPLOY_HOSTINGER.md' para instruções detalhadas.

---
✅ **Deploy preparado com sucesso!**
`;
  
  fs.writeFileSync(path.join(deployDir, 'LEIA-ME.txt'), instructions);
  log('✅ Instruções criadas', 'green');
}

function main() {
  try {
    log('🎯 CorteFácil - Deploy Automatizado', 'bright');
    log('=====================================', 'bright');
    
    const deployDir = createDeployStructure();
    buildFrontend(deployDir);
    setupBackend(deployDir);
    createHtaccess(deployDir);
    createEnvTemplate(deployDir);
    createDatabaseSchema(deployDir);
    createDeployInstructions(deployDir);
    
    log('', 'reset');
    log('🎉 DEPLOY PREPARADO COM SUCESSO!', 'green');
    log('=====================================', 'green');
    log('📁 Arquivos prontos em: ./deploy/', 'cyan');
    log('📋 Leia o arquivo: ./deploy/LEIA-ME.txt', 'yellow');
    log('📖 Guia completo: ./DEPLOY_HOSTINGER.md', 'yellow');
    log('', 'reset');
    log('🚀 Agora faça upload da pasta deploy/ para o Painel Fácil!', 'bright');
    
  } catch (error) {
    log(`❌ Erro durante o deploy: ${error.message}`, 'red');
    process.exit(1);
  }
}

if (require.main === module) {
  main();
}

module.exports = { main };