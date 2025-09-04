# 🔧 Solução para Problema de Conexão MySQL - EasyPanel/Hostinger

## 📋 Problema Identificado

**Erro:** `Access denied for user 'u690889028_mayconwender'@'45.181.72.123' (using password: YES)`

**Causa:** O usuário MySQL não tem permissão para conectar do IP atual (45.181.72.123)

## 🎯 Credenciais Confirmadas

```env
DB_HOST=srv973908.hstgr.cloud
DB_PORT=3306
DB_USER=u690889028_mayconwender
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_cortefacil
```

## 🚀 Soluções Disponíveis

### 1. 🌐 Configurar Hosts Remotos (RECOMENDADO)

#### No painel Hostinger:
1. Acesse **Painel de Controle** → **Bancos de Dados** → **MySQL**
2. Clique em **Gerenciar** no banco `u690889028_cortefacil`
3. Vá para **Hosts Remotos**
4. Adicione os seguintes IPs:
   - `45.181.72.123` (seu IP atual)
   - `%` (qualquer IP - menos seguro, mas funcional)
   - `0.0.0.0/0` (alternativa para qualquer IP)

#### Comandos SQL alternativos:
```sql
-- Execute no phpMyAdmin do Hostinger
GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'%' IDENTIFIED BY 'Maycon341753@';
GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'45.181.72.123' IDENTIFIED BY 'Maycon341753@';
FLUSH PRIVILEGES;
```

### 2. 🔐 Usar SSH Tunnel (ALTERNATIVA)

#### Pré-requisitos:
- Acesso SSH ao servidor Hostinger
- Chave SSH configurada

#### Script pronto:
```bash
# Execute este comando para criar o túnel
ssh -L 3307:localhost:3306 u690889028@srv973908.hstgr.cloud

# Em outro terminal, teste a conexão
node test-ssh-tunnel.js
```

### 3. 📱 Usar phpMyAdmin (TEMPORÁRIO)

#### Para configurar o banco:
1. Acesse phpMyAdmin no painel Hostinger
2. Selecione o banco `u690889028_cortefacil`
3. Execute o script `hostinger-database-setup.sql`
4. Verifique se as tabelas foram criadas

## 🛠️ Scripts de Teste Disponíveis

### Teste de Conexão Direta
```bash
node test-easypanel-connection.js
```

### Teste com SSH Tunnel
```bash
node test-ssh-tunnel.js
```

### Teste de Configuração do Banco
```bash
node test-database-connection.js
```

## 📊 Status do Banco de Dados

### Tabelas Necessárias:
- ✅ `usuarios` - Clientes, parceiros e admins
- ✅ `saloes` - Estabelecimentos cadastrados
- ✅ `profissionais` - Funcionários dos salões
- ✅ `agendamentos` - Reservas de horários
- ✅ `password_resets` - Recuperação de senhas

### Dados de Teste:
- 👤 Admin: `admin@cortefacil.com` (senha: `password`)
- 👤 Cliente: `joao@teste.com` (senha: `password`)
- 👤 Parceiro: `maria@salao.com` (senha: `password`)

## 🔄 Próximos Passos

### 1. Configurar Hosts Remotos
```bash
# Após configurar no painel Hostinger, teste:
node test-easypanel-connection.js
```

### 2. Configurar Banco de Dados
```bash
# Se a conexão funcionar, configure o banco:
node setup-database.js
```

### 3. Testar Aplicação
```bash
# Backend
cd backend/server
npm start

# Frontend (em outro terminal)
cd frontend
npm run dev
```

## 🆘 Suporte

### Se nada funcionar:
1. **Contate o Suporte Hostinger**
   - Solicite liberação de conexões remotas
   - Peça para verificar permissões do usuário MySQL
   - Informe o erro específico e seu IP atual

2. **Verifique Configurações EasyPanel**
   - Confirme se o MySQL está ativo
   - Verifique se o banco foi criado corretamente
   - Confirme as credenciais no painel

3. **Alternativas de Deploy**
   - Considere usar Vercel para frontend
   - Use Railway ou Render para backend
   - Configure banco em PlanetScale ou Supabase

## 📝 Logs de Erro Comuns

### `ER_ACCESS_DENIED_ERROR`
- **Causa:** Permissões de IP
- **Solução:** Configurar hosts remotos

### `ECONNREFUSED`
- **Causa:** Firewall ou serviço inativo
- **Solução:** Verificar status do MySQL

### `ENOTFOUND`
- **Causa:** Host incorreto
- **Solução:** Verificar URL do servidor

---

**✅ Problema Resolvido?** Execute `node test-easypanel-connection.js` para confirmar!

**❌ Ainda com problemas?** Verifique os logs detalhados e contate o suporte.