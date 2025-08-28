# 🚀 Guia de Deploy - Painel Fácil VPS Hostinger

## 📋 Pré-requisitos

- Acesso ao Painel Fácil VPS da Hostinger
- Projeto CorteFácil já buildado
- Dados de acesso ao MySQL

## 🔧 Passo 1: Preparar o Build de Produção

### 1.1 Build do Frontend
```bash
# No diretório raiz do projeto
npm run build
```

### 1.2 Verificar arquivos gerados
Após o build, você deve ter:
- `deploy/` - Pasta com todos os arquivos de produção
- `deploy/index.html` - Frontend buildado
- `deploy/server/` - Backend Node.js
- `deploy/assets/` - Arquivos estáticos

## 🌐 Passo 2: Configurar o VPS no Painel Fácil

### 2.1 Acessar o Painel
1. Faça login no Painel Fácil da Hostinger
2. Vá em **"Projetos"** → **"Novo"**
3. Selecione **"Aplicativo"** como tipo

### 2.2 Configurar Node.js
1. No painel, vá em **"Configurações"**
2. Selecione **Node.js** como runtime
3. Defina a versão: **Node.js 18.x** ou superior
4. Defina o arquivo de entrada: `server/server.js`

### 2.3 Configurar Variáveis de Ambiente
No painel, adicione as seguintes variáveis:

```env
NODE_ENV=production
PORT=3000
DB_HOST=localhost
DB_USER=seu_usuario_mysql
DB_PASSWORD=sua_senha_mysql
DB_NAME=cortefacil
JWT_SECRET=seu_jwt_secret_super_seguro
FRONTEND_URL=https://seudominio.com
```

## 📁 Passo 3: Upload dos Arquivos

### 3.1 Via File Manager do Painel
1. No Painel Fácil, vá em **"Arquivos"**
2. Navegue até a pasta do seu projeto
3. Faça upload de todos os arquivos da pasta `deploy/`:
   - `index.html`
   - `server/` (pasta completa)
   - `assets/` (pasta completa)
   - `.htaccess`

### 3.2 Estrutura final no servidor
```
/public_html/seudominio/
├── index.html
├── .htaccess
├── assets/
│   ├── *.css
│   ├── *.js
│   └── *.ttf, *.woff2
└── server/
    ├── server.js
    ├── package.json
    ├── config/
    ├── routes/
    └── middleware/
```

## 🗄️ Passo 4: Configurar o Banco de Dados

### 4.1 Criar Banco MySQL
1. No Painel Fácil, vá em **"Banco de Dados"**
2. Clique em **"Criar Banco"**
3. Nome: `cortefacil`
4. Anote usuário e senha gerados

### 4.2 Importar Schema
1. No **phpMyAdmin** ou **Adminer**
2. Selecione o banco `cortefacil`
3. Importe o arquivo `database/schema.sql`

### 4.3 Configurar Conexão
Atualize as variáveis de ambiente com os dados do MySQL:
```env
DB_HOST=localhost
DB_USER=usuario_gerado_pelo_painel
DB_PASSWORD=senha_gerada_pelo_painel
DB_NAME=cortefacil
```

## ⚙️ Passo 5: Configurar o .htaccess

O arquivo `.htaccess` já está configurado no deploy, mas verifique se contém:

```apache
RewriteEngine On

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
```

## 🚀 Passo 6: Iniciar a Aplicação

### 6.1 Instalar Dependências
1. No terminal do Painel Fácil ou via SSH:
```bash
cd /public_html/seudominio/server
npm install --production
```

### 6.2 Iniciar o Servidor
1. No Painel Fácil, vá em **"Aplicativo"**
2. Clique em **"Iniciar"**
3. Ou via terminal:
```bash
node server.js
```

### 6.3 Configurar PM2 (Recomendado)
Para manter o servidor sempre ativo:
```bash
npm install -g pm2
pm2 start server.js --name "cortefacil"
pm2 startup
pm2 save
```

## 🔍 Passo 7: Verificar o Deploy

### 7.1 Testar Frontend
- Acesse: `https://seudominio.com`
- Verifique se a página carrega corretamente
- Teste navegação entre páginas

### 7.2 Testar Backend
- Acesse: `https://seudominio.com/api/health`
- Deve retornar: `{"status": "OK"}`

### 7.3 Testar Funcionalidades
- Cadastro de usuário
- Login
- Agendamentos
- Dashboard

## 🛠️ Comandos Úteis

### Logs do Servidor
```bash
# Ver logs em tempo real
pm2 logs cortefacil

# Ver status
pm2 status

# Reiniciar aplicação
pm2 restart cortefacil
```

### Backup do Banco
```bash
mysqldump -u usuario -p cortefacil > backup_$(date +%Y%m%d).sql
```

## 🔧 Troubleshooting

### Problema: Erro 500
- Verifique logs do servidor
- Confirme variáveis de ambiente
- Teste conexão com banco

### Problema: API não funciona
- Verifique se Node.js está rodando
- Confirme configuração do .htaccess
- Teste endpoint diretamente

### Problema: Frontend não carrega
- Verifique se arquivos foram enviados
- Confirme permissões dos arquivos
- Teste acesso direto aos assets

## 📞 Suporte

Se encontrar problemas:
1. Verifique logs do Painel Fácil
2. Consulte documentação da Hostinger
3. Entre em contato com suporte técnico

---

✅ **Deploy Concluído!** Seu projeto CorteFácil está agora rodando no Painel Fácil VPS da Hostinger!