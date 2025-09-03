# 🔧 Guia de Solução - Conectividade MySQL Hostinger

## 📊 Diagnóstico Atual

✅ **Configurações atualizadas**: Todas as variáveis de ambiente estão corretas  
✅ **Servidor acessível**: O servidor `srv973908.hstgr.cloud` responde a ping  
❌ **Porta 3306 bloqueada**: A porta MySQL não está acessível externamente  

### Erro Atual:
```
Error: connect ECONNREFUSED 31.97.171.104:3306
TcpTestSucceeded: False
```

## 🎯 Soluções Necessárias

### 1. **Verificar Configurações no Painel Hostinger**

#### A. Acessar Banco de Dados MySQL
1. Faça login no painel do Hostinger
2. Vá em **Banco de Dados** → **MySQL**
3. Localize o banco `cortefacil`

#### B. Habilitar Conexões Remotas
1. Clique em **Gerenciar** no banco `cortefacil`
2. Vá na aba **Acesso Remoto** ou **Remote Access**
3. **Adicione o IP do EasyPanel** à whitelist
4. Se não souber o IP, adicione `%` (qualquer IP) temporariamente

#### C. Verificar Usuário MySQL
1. Confirme que o usuário `cortefacil_user` existe
2. Verifique se tem permissões para conexão remota
3. O host do usuário deve ser `%` ou o IP específico do EasyPanel

### 2. **Configurações de Firewall**

#### A. Porta 3306
1. Verifique se a porta 3306 está aberta no firewall
2. No painel Hostinger, procure por **Firewall** ou **Segurança**
3. Adicione regra para porta 3306 (TCP)

#### B. Configurações de Rede
1. Alguns provedores bloqueiam a porta 3306 por padrão
2. Verifique se há opção para "Permitir conexões externas MySQL"

### 3. **Alternativas de Conexão**

#### A. Usar Porta Alternativa
Se o Hostinger oferece MySQL em porta diferente:
```env
DB_PORT=3307  # ou outra porta disponível
```

#### B. Túnel SSH (se disponível)
Se o Hostinger oferece acesso SSH:
```env
DB_HOST=localhost
DB_PORT=3306
# Configurar túnel SSH no EasyPanel
```

#### C. API Proxy
Criar uma API intermediária no próprio Hostinger que acesse o MySQL localmente.

### 4. **Verificações no EasyPanel**

#### A. Variáveis de Ambiente
Confirme no EasyPanel que as variáveis estão corretas:
```env
NODE_ENV=production
DB_HOST=srv973908.hstgr.cloud
DB_PORT=3306
DB_USER=cortefacil_user
DB_PASSWORD=Maycon341753
DB_NAME=cortefacil
JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
DATABASE_URL=mysql://cortefacil_user:Maycon341753@srv973908.hstgr.cloud:3306/cortefacil
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://cortefacil.app/api
```

#### B. Redeploy
Após configurar o MySQL no Hostinger, faça redeploy do backend no EasyPanel.

## 🔍 Comandos de Teste

### Testar Conectividade
```bash
# No seu computador local
Test-NetConnection -ComputerName srv973908.hstgr.cloud -Port 3306

# Ou usando telnet
telnet srv973908.hstgr.cloud 3306
```

### Testar Conexão MySQL
```bash
# Execute o script de teste
node test-mysql-connection.js
```

## 📞 Próximos Passos

1. **Imediato**: Acessar painel Hostinger e habilitar conexões remotas
2. **Configurar**: Adicionar IP do EasyPanel à whitelist MySQL
3. **Testar**: Executar `Test-NetConnection` novamente
4. **Deploy**: Fazer redeploy no EasyPanel após configurações
5. **Verificar**: Testar aplicação em produção

## ⚠️ Notas Importantes

- **Segurança**: Evite usar `%` (qualquer IP) em produção
- **Backup**: Faça backup do banco antes de mudanças
- **Suporte**: Se não conseguir configurar, contate suporte Hostinger
- **Alternativa**: Considere usar banco de dados do próprio EasyPanel se disponível

## 📋 Checklist de Verificação

- [ ] Conexões remotas habilitadas no MySQL
- [ ] IP do EasyPanel na whitelist
- [ ] Porta 3306 aberta no firewall
- [ ] Usuário MySQL com permissões remotas
- [ ] Variáveis de ambiente corretas no EasyPanel
- [ ] Redeploy realizado
- [ ] Teste de conectividade bem-sucedido
- [ ] Aplicação funcionando em produção