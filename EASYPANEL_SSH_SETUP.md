# 🔧 Configuração SSH Tunnel no EasyPanel

## ⚠️ SOLUÇÃO PARA ERRO ECONNREFUSED MySQL

O erro `ECONNREFUSED 31.97.171.104:3306` ocorre porque a porta 3306 do MySQL está bloqueada no Hostinger. A solução é usar um túnel SSH.

## 📋 Variáveis de Ambiente Necessárias

Adicione estas variáveis no EasyPanel (seção Environment Variables):

```bash
# SSH Configuration (usando senha)
SSH_HOST=srv973908.hstgr.cloud
SSH_USER=u973908341
SSH_PASSWORD=[SUA_SENHA_HOSTINGER]

# Database Configuration (manter as existentes)
DB_HOST=localhost
DB_PORT=3306
DB_USER=u973908341_cortefacil
DB_PASSWORD=Maycon341753
DB_NAME=u973908341_cortefacil
DATABASE_URL=mysql://u973908341_cortefacil:Maycon341753@localhost:3306/u973908341_cortefacil
```

## 🔑 Como Obter a Senha SSH

### Usar Senha do Painel Hostinger

1. **Acesse o painel do Hostinger**
2. **Vá em "Hosting" → "Gerenciar"**
3. **Procure por "SSH Access" ou "Acesso SSH"**
4. **Use a mesma senha do seu painel ou crie uma senha SSH específica**
5. **Anote a senha para usar no EasyPanel**

### Testar Conexão SSH

Antes de configurar no EasyPanel, teste localmente:
```bash
ssh u973908341@srv973908.hstgr.cloud
```

## 🚀 Passos para Deploy

### 1. Configurar Variáveis no EasyPanel

- Acesse seu projeto no EasyPanel
- Vá em **Settings** → **Environment Variables**
- Adicione todas as variáveis listadas acima
- **IMPORTANTE:** Use a senha correta do seu acesso SSH do Hostinger

### 2. Fazer Redeploy

- Clique em **Deploy** no EasyPanel
- Aguarde o build completar
- Verifique os logs para confirmar:
  - ✅ Túnel SSH configurado com sucesso
  - ✅ Pool de conexões MySQL criado
  - ✅ Servidor API rodando

## 🔍 Verificação de Logs

Nos logs do EasyPanel, você deve ver:

```
🔧 Configurando túnel SSH para MySQL...
🚇 Iniciando túnel SSH...
✅ Túnel SSH configurado com sucesso
📍 MySQL acessível via localhost:3306
🚀 Túnel SSH pronto - iniciando servidor Node.js...
✅ Pool de conexões MySQL criado
🚀 Servidor API rodando na porta 3001
```

## ❌ Troubleshooting

### Erro: "Variáveis SSH não configuradas"
- Verifique se todas as variáveis SSH estão definidas no EasyPanel
- Certifique-se de que não há espaços extras nos nomes das variáveis

### Erro: "Permission denied (publickey)"
- Verifique se a chave pública foi adicionada ao Hostinger
- Teste a conexão SSH manualmente: `ssh u973908341@srv973908.hstgr.cloud`

### Erro: "Host key verification failed"
- O script adiciona automaticamente o host às known_hosts
- Se persistir, verifique se o SSH_HOST está correto

## 🔄 Alternativas

Se o túnel SSH não funcionar:

1. **Contatar Suporte Hostinger:**
   - Solicitar liberação da porta 3306 para conexões externas
   - Adicionar IP do EasyPanel à whitelist

2. **Migrar para Banco EasyPanel:**
   - Criar banco MySQL no próprio EasyPanel
   - Importar dados do Hostinger
   - Atualizar configurações

## 📞 Suporte

Se precisar de ajuda:
- Verifique os logs do EasyPanel
- Teste a conexão SSH manualmente
- Confirme se as variáveis estão corretas