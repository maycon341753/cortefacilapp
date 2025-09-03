# 🔐 Guia SSH Tunnel para MySQL Hostinger

## 📋 Visão Geral

Como a porta 3306 está bloqueada para conexões diretas, podemos usar um túnel SSH para acessar o MySQL do Hostinger de forma segura através do EasyPanel.

## 🎯 Vantagens do Túnel SSH

✅ **Segurança**: Conexão criptografada  
✅ **Bypass de Firewall**: Contorna bloqueios de porta  
✅ **Estabilidade**: Conexão mais confiável  
✅ **Sem configuração no Hostinger**: Não precisa alterar firewall  

## 🔧 Configuração no EasyPanel

### 1. **Verificar Acesso SSH no Hostinger**

Primeiro, confirme se você tem acesso SSH:

1. **Painel Hostinger** → **Avançado** → **SSH Access**
2. Anote as informações:
   ```
   Host SSH: srv973908.hstgr.cloud
   Porta SSH: 22 (padrão)
   Usuário: [seu_usuario_hostinger]
   ```

### 2. **Configurar Túnel SSH no EasyPanel**

#### A. Método 1: Variáveis de Ambiente com Túnel

No EasyPanel, configure as seguintes variáveis:

```env
# Configuração SSH Tunnel
SSH_HOST=srv973908.hstgr.cloud
SSH_PORT=22
SSH_USER=seu_usuario_hostinger
SSH_PRIVATE_KEY=-----BEGIN PRIVATE KEY-----
[sua_chave_privada_ssh]
-----END PRIVATE KEY-----

# MySQL via túnel (localhost após túnel)
DB_HOST=localhost
DB_PORT=3306
DB_USER=cortefacil_user
DB_PASSWORD=Maycon341753
DB_NAME=cortefacil

# Outras configurações
NODE_ENV=production
JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://cortefacil.app/api
```

#### B. Método 2: Script de Inicialização

Crie um script que estabeleça o túnel antes de iniciar a aplicação:

```bash
#!/bin/bash
# start-with-tunnel.sh

# Estabelecer túnel SSH
ssh -f -N -L 3306:localhost:3306 seu_usuario@srv973908.hstgr.cloud

# Aguardar túnel
sleep 5

# Iniciar aplicação
node server.js
```

### 3. **Configuração de Chaves SSH**

#### A. Gerar Par de Chaves (se necessário)

```bash
# No seu computador local
ssh-keygen -t rsa -b 4096 -C "easypanel@cortefacil.app"
```

#### B. Adicionar Chave Pública no Hostinger

1. **Painel Hostinger** → **SSH Access** → **Manage SSH Keys**
2. Adicione o conteúdo de `~/.ssh/id_rsa.pub`
3. Teste a conexão:
   ```bash
   ssh seu_usuario@srv973908.hstgr.cloud
   ```

#### C. Configurar Chave Privada no EasyPanel

1. Copie o conteúdo de `~/.ssh/id_rsa`
2. Adicione como variável de ambiente `SSH_PRIVATE_KEY`
3. Ou monte como volume/secret no EasyPanel

## 🐳 Configuração Docker (EasyPanel)

### Dockerfile Modificado

```dockerfile
FROM node:18-alpine

# Instalar SSH client
RUN apk add --no-cache openssh-client

# Criar diretório SSH
RUN mkdir -p /root/.ssh

# Copiar aplicação
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

COPY . .

# Script de inicialização
COPY start-with-tunnel.sh /start-with-tunnel.sh
RUN chmod +x /start-with-tunnel.sh

EXPOSE 3001

# Usar script de inicialização
CMD ["/start-with-tunnel.sh"]
```

### Script de Inicialização

```bash
#!/bin/sh
# start-with-tunnel.sh

echo "🔐 Configurando túnel SSH..."

# Configurar chave SSH
echo "$SSH_PRIVATE_KEY" > /root/.ssh/id_rsa
chmod 600 /root/.ssh/id_rsa

# Adicionar host conhecido
ssh-keyscan -H $SSH_HOST >> /root/.ssh/known_hosts

# Estabelecer túnel SSH
echo "⏳ Estabelecendo túnel SSH..."
ssh -f -N -L 3306:localhost:3306 $SSH_USER@$SSH_HOST

# Verificar se túnel está ativo
if pgrep -f "ssh.*3306:localhost:3306" > /dev/null; then
    echo "✅ Túnel SSH estabelecido com sucesso!"
else
    echo "❌ Falha ao estabelecer túnel SSH"
    exit 1
fi

# Aguardar estabilização
sleep 5

# Iniciar aplicação
echo "🚀 Iniciando aplicação..."
node server.js
```

## 🔍 Teste Local do Túnel

### Comando de Teste

```bash
# Estabelecer túnel manualmente
ssh -L 3306:localhost:3306 seu_usuario@srv973908.hstgr.cloud

# Em outro terminal, testar conexão
mysql -h localhost -P 3306 -u cortefacil_user -p cortefacil
```

### Script de Teste Node.js

```javascript
// test-ssh-tunnel.js
const mysql = require('mysql2/promise');
const { spawn } = require('child_process');

async function testWithTunnel() {
    console.log('🔐 Estabelecendo túnel SSH...');
    
    // Estabelecer túnel
    const tunnel = spawn('ssh', [
        '-L', '3306:localhost:3306',
        'seu_usuario@srv973908.hstgr.cloud'
    ]);
    
    // Aguardar túnel
    await new Promise(resolve => setTimeout(resolve, 5000));
    
    try {
        console.log('⏳ Testando conexão MySQL via túnel...');
        
        const connection = await mysql.createConnection({
            host: 'localhost',
            port: 3306,
            user: 'cortefacil_user',
            password: 'Maycon341753',
            database: 'cortefacil'
        });
        
        console.log('✅ Conexão via túnel SSH bem-sucedida!');
        
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Query de teste:', rows);
        
        await connection.end();
        
    } catch (error) {
        console.error('❌ Erro na conexão via túnel:', error.message);
    } finally {
        tunnel.kill();
    }
}

testWithTunnel();
```

## 📞 Contato com Suporte Hostinger

### Informações para o Suporte

Quando contatar o suporte, mencione:

1. **Problema**: Porta 3306 bloqueada para conexões externas
2. **Solicitação**: Habilitar conexões remotas MySQL ou confirmar suporte SSH
3. **Detalhes técnicos**:
   - Servidor: `srv973908.hstgr.cloud`
   - Banco: `cortefacil`
   - Usuário: `cortefacil_user`
   - Erro: `ECONNREFUSED 31.97.171.104:3306`

### Alternativas a Solicitar

1. **Habilitar porta 3306** para conexões externas
2. **Confirmar acesso SSH** e porta 22
3. **Verificar whitelist** de IPs para MySQL
4. **Porta alternativa** para MySQL (se disponível)

## ⚡ Implementação Rápida

### Para Testar Agora

1. **Verificar SSH**:
   ```bash
   ssh seu_usuario@srv973908.hstgr.cloud
   ```

2. **Se SSH funcionar**, configure túnel no EasyPanel

3. **Se SSH não funcionar**, contate suporte Hostinger

### Configuração Mínima EasyPanel

```env
# Se SSH estiver disponível
DB_HOST=localhost  # Após túnel
DB_PORT=3306
SSH_HOST=srv973908.hstgr.cloud
SSH_USER=seu_usuario_hostinger
# + configurar chave SSH
```

## 🎯 Próximos Passos

1. **Testar acesso SSH** ao Hostinger
2. **Configurar chaves SSH** se necessário
3. **Implementar túnel** no EasyPanel
4. **Testar conexão** via túnel
5. **Deploy e verificação** em produção

O túnel SSH é uma solução robusta que resolve o problema de conectividade sem depender de mudanças no firewall do Hostinger.