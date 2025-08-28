import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import { execSync } from 'child_process'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const rootDir = path.resolve(__dirname, '..')

console.log('🚀 Preparando arquivos para deploy...')

const buildDir = path.join(rootDir, 'frontend', 'dist')
const deployDir = path.join(rootDir, 'deploy')

// Limpar diretório de deploy se existir
if (fs.existsSync(deployDir)) {
  fs.rmSync(deployDir, { recursive: true, force: true })
}

// Criar diretório de deploy
fs.mkdirSync(deployDir, { recursive: true })

try {
  // Copiar arquivos de build
  console.log('📦 Copiando arquivos de build...')
  
  function copyDirectory(src, dest) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true })
    }

    const entries = fs.readdirSync(src, { withFileTypes: true })

    for (const entry of entries) {
      const srcPath = path.join(src, entry.name)
      const destPath = path.join(dest, entry.name)

      if (entry.isDirectory()) {
        copyDirectory(srcPath, destPath)
      } else {
        fs.copyFileSync(srcPath, destPath)
      }
    }
  }

  // Copiar build do frontend
  if (fs.existsSync(buildDir)) {
    copyDirectory(buildDir, deployDir)
  }

  // Criar arquivo .htaccess para Apache (CyberPanel/Hostinger)
  const htaccessContent = `# Configuração para React Router
RewriteEngine On
RewriteBase /

# Handle Angular and React Router
RewriteRule ^(?!.*\\.).*$ /index.html [L]

# Configurações de segurança
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

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
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
`

  fs.writeFileSync(path.join(deployDir, '.htaccess'), htaccessContent)

  // Criar arquivo de configuração do Node.js para CyberPanel
  const appJsContent = `const express = require('express');
const path = require('path');
const app = express();

// Servir arquivos estáticos
app.use(express.static(path.join(__dirname)));

// Rota para React Router
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

const port = process.env.PORT || 3000;
app.listen(port, () => {
  console.log(\`Servidor rodando na porta \${port}\`);
});
`

  fs.writeFileSync(path.join(deployDir, 'app.js'), appJsContent)

  // Criar package.json para deploy
  const deployPackageJson = {
    "name": "cortefacil-frontend",
    "version": "1.0.0",
    "main": "app.js",
    "scripts": {
      "start": "node app.js"
    },
    "dependencies": {
      "express": "^4.18.2"
    },
    "engines": {
      "node": ">=16.0.0",
      "npm": ">=8.0.0"
    }
  }

  fs.writeFileSync(
    path.join(deployDir, 'package.json'),
    JSON.stringify(deployPackageJson, null, 2)
  )

  // Criar arquivo README para deploy
  const readmeContent = `# CorteFácil - Deploy

## Instruções de Deploy para CyberPanel/Hostinger

### 1. Upload dos Arquivos
- Faça upload de todos os arquivos desta pasta para o diretório público do seu domínio
- Normalmente é \`public_html\` ou \`www\`

### 2. Configuração do Node.js (se disponível)
- Se o seu hosting suporta Node.js:
  \`\`\`bash
  npm install
  npm start
  \`\`\`

### 3. Configuração apenas Apache/PHP
- Se usar apenas Apache, o arquivo \`.htaccess\` já está configurado
- Certifique-se de que o mod_rewrite está ativado

### 4. Configuração do Backend
- Configure as variáveis de ambiente no servidor
- Atualize as URLs da API no frontend se necessário

### 5. Banco de Dados
- Importe o schema do banco de dados
- Configure as credenciais de conexão

## Variáveis de Ambiente Necessárias

\`\`\`
NODE_ENV=production
DATABASE_HOST=seu-host-mysql
DATABASE_USER=seu-usuario
DATABASE_PASSWORD=sua-senha
DATABASE_NAME=cortefacil
JWT_SECRET=sua-chave-secreta-jwt
API_URL=https://seu-dominio.com/api
\`\`\`

## Estrutura de Arquivos

\`\`\`
public_html/
├── index.html          # Página principal
├── assets/             # CSS, JS, imagens
├── server/             # Backend Node.js
├── .htaccess          # Configuração Apache
├── app.js             # Servidor Express (opcional)
└── package.json       # Dependências
\`\`\`
`

  fs.writeFileSync(path.join(deployDir, 'README.md'), readmeContent)

  console.log('✅ Arquivos de deploy preparados!')
  console.log(`📁 Arquivos disponíveis em: ${deployDir}`)
  console.log('\n📋 Próximos passos:')
  console.log('1. Faça upload dos arquivos da pasta "deploy" para seu servidor')
  console.log('2. Configure as variáveis de ambiente')
  console.log('3. Configure o banco de dados')
  console.log('4. Teste a aplicação')

} catch (error) {
  console.error('❌ Erro durante a preparação do deploy:', error)
  process.exit(1)
}