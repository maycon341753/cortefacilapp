# 🚀 Guia Completo: Setup do Banco de Dados no Hostinger

## 📋 Situação Atual
- ✅ Você está conectado ao phpMyAdmin do Hostinger
- ✅ Banco de dados `u690889028_cortefacil` existe mas está vazio
- ✅ Script SQL de setup foi criado: `hostinger-database-setup.sql`

## 🎯 Próximos Passos

### 1. **Executar Script SQL no phpMyAdmin**

1. **No phpMyAdmin que você já tem aberto:**
   - Clique na aba **"SQL"** (no topo da página)
   - Copie todo o conteúdo do arquivo `hostinger-database-setup.sql`
   - Cole no campo de texto do SQL
   - Clique em **"Executar"**

2. **O que o script fará:**
   - ✅ Criar 5 tabelas: `usuarios`, `saloes`, `profissionais`, `agendamentos`, `password_resets`
   - ✅ Inserir usuário administrador padrão
   - ✅ Inserir dados de teste para desenvolvimento
   - ✅ Criar índices para melhor performance
   - ✅ Mostrar resumo do que foi criado

### 2. **Verificar se Funcionou**

Após executar o script, você deve ver:
- ✅ Mensagem de sucesso
- ✅ Lista das 5 tabelas criadas
- ✅ Dados de usuários, salões e profissionais inseridos

### 3. **Testar Conexão da Aplicação**

Após criar as tabelas:

```bash
# No terminal, execute:
node test-database-connection.js
```

Agora deve funcionar e mostrar as 5 tabelas criadas!

## 📊 Estrutura das Tabelas Criadas

### 👥 **usuarios**
- Armazena clientes, parceiros e administradores
- Campos: id, nome, email, senha, tipo_usuario, telefone

### 🏪 **saloes** 
- Informações dos salões de beleza
- Campos: id, id_dono, nome, endereco, telefone, descricao

### 💇 **profissionais**
- Profissionais que trabalham nos salões
- Campos: id, id_salao, nome, especialidade

### 📅 **agendamentos**
- Agendamentos de clientes
- Campos: id, id_cliente, id_salao, id_profissional, data, hora, status

### 🔑 **password_resets**
- Tokens para redefinição de senha
- Campos: id, email, token, expires_at, used

## 🔐 Usuários Criados

### **Administrador**
- **Email:** admin@cortefacil.com
- **Senha:** password
- **Tipo:** admin

### **Usuários de Teste**
- **Cliente:** joao@teste.com (senha: password)
- **Parceiro:** maria@salao.com (senha: password)

## 🛠️ Solução de Problemas

### **Se der erro de permissão:**
- Verifique se está usando o banco correto: `u690889028_cortefacil`
- Confirme que o usuário tem permissões de CREATE TABLE

### **Se der erro de sintaxe:**
- Execute o script em partes menores
- Verifique se copiou o script completo

### **Se a conexão da aplicação ainda falhar:**
- Use uma das soluções do arquivo `SOLUCOES_MYSQL_HOSTINGER.md`:
  - SSH Tunnel (recomendado)
  - Correção de permissões MySQL
  - Contato com suporte Hostinger

## 🎉 Após o Setup

1. **Teste a aplicação:**
   ```bash
   npm start  # Backend
   npm run dev  # Frontend (em outro terminal)
   ```

2. **Acesse:** http://localhost:3000

3. **Faça login com:**
   - Email: admin@cortefacil.com
   - Senha: password

## 📞 Próximos Passos

1. ✅ **Executar script SQL no phpMyAdmin**
2. ✅ **Testar conexão da aplicação**
3. ✅ **Configurar dados reais do seu negócio**
4. ✅ **Fazer deploy da aplicação atualizada**

---

**💡 Dica:** Mantenha o phpMyAdmin aberto para monitorar os dados conforme você testa a aplicação!