# 🗄️ Configuração do Banco de Dados - CorteFácil

## 📋 Visão Geral

O CorteFácil possui **configuração automática do banco de dados**. As tabelas são criadas automaticamente quando o servidor inicia pela primeira vez.

## 🚀 Configuração Automática (Recomendado)

### 1. Configurar Variáveis de Ambiente

Certifique-se de que o arquivo `.env` está configurado:

```env
# Banco de Dados
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=sua_senha
DB_NAME=cortefacil

# Para EasyPanel (produção)
DB_HOST_ONLINE=seu_host_easypanel
DB_USER_ONLINE=seu_usuario
DB_PASSWORD_ONLINE=sua_senha
DB_NAME_ONLINE=cortefacil
```

### 2. Iniciar o Servidor

Quando você executar o servidor, o banco será configurado automaticamente:

```bash
npm run dev
```

O sistema irá:
- ✅ Criar o banco `cortefacil` se não existir
- ✅ Criar todas as tabelas necessárias
- ✅ Inserir o usuário administrador padrão
- ✅ Criar índices para performance

## 🔧 Configuração Manual (Opcional)

Se preferir configurar o banco manualmente:

### Opção 1: Script Automatizado

```bash
# Configurar banco de dados
npm run setup-db

# Testar conexão
npm run test-db
```

### Opção 2: Executar SQL Manualmente

1. Acesse seu MySQL (phpMyAdmin, Adminer, etc.)
2. Execute o arquivo `database/schema.sql`

## 📊 Estrutura das Tabelas

### `usuarios`
- Armazena clientes, parceiros e administradores
- Campos: id, nome, email, senha, tipo_usuario, telefone

### `saloes`
- Informações dos salões de beleza
- Campos: id, id_dono, nome, endereco, telefone, descricao

### `profissionais`
- Profissionais que trabalham nos salões
- Campos: id, id_salao, nome, especialidade

### `agendamentos`
- Agendamentos dos clientes
- Campos: id, id_cliente, id_salao, id_profissional, data, hora, status

## 👤 Usuário Administrador Padrão

**Email:** `admin@cortefacil.com`  
**Senha:** `password` (altere após o primeiro login)

## 🔍 Verificação

Para verificar se tudo está funcionando:

1. **Teste de Conexão:**
   ```bash
   npm run test-db
   ```

2. **Health Check da API:**
   ```
   GET http://localhost:3001/api/health/database
   ```

3. **Verificar Tabelas:**
   - Acesse seu gerenciador MySQL
   - Confirme que o banco `cortefacil` existe
   - Verifique se as 4 tabelas foram criadas

## 🚨 Solução de Problemas

### Erro: "Access denied for user"
- Verifique as credenciais no `.env`
- Confirme se o usuário MySQL tem permissões

### Erro: "Can't connect to MySQL server"
- Verifique se o MySQL está rodando
- Confirme o host e porta no `.env`

### Erro: "Database doesn't exist"
- O sistema criará automaticamente
- Verifique se o usuário tem permissão para criar bancos

### Tabelas não são criadas
- Execute manualmente: `npm run setup-db`
- Verifique os logs do servidor para erros específicos

## 🌐 Configuração para EasyPanel

Para produção no EasyPanel:

1. **Configure as variáveis no EasyPanel:**
   ```env
   DB_HOST_ONLINE=seu_mysql_host
   DB_USER_ONLINE=seu_usuario
   DB_PASSWORD_ONLINE=sua_senha
   DB_NAME_ONLINE=cortefacil
   ```

2. **O sistema detectará automaticamente** e usará as configurações online

3. **Primeira execução:** As tabelas serão criadas automaticamente

## 📝 Comandos Úteis

```bash
# Configurar banco completo
npm run setup-db

# Testar conexão
npm run test-db

# Iniciar com configuração automática
npm run dev

# Verificar saúde do sistema
curl http://localhost:3001/api/health
```

## 🔄 Atualizações do Schema

Quando houver mudanças na estrutura:

1. **Desenvolvimento:** O sistema detecta e aplica automaticamente
2. **Produção:** Execute `npm run setup-db` após deploy
3. **Manual:** Execute o SQL atualizado no `database/schema.sql`

---

**💡 Dica:** O sistema é projetado para funcionar "out of the box". Na maioria dos casos, apenas configurar o `.env` e executar `npm run dev` é suficiente!