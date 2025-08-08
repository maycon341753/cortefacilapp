# 🗄️ Banco de Dados - CorteFácil

## ✅ Status: Banco Criado com Sucesso!

O banco de dados `cortefacil_db` foi criado e populado com dados de teste.

## 📊 Estrutura Criada

### 📋 Tabelas
- **usuarios** - 7 registros
- **saloes** - 3 registros  
- **profissionais** - 6 registros
- **agendamentos** - 4 registros

### 👥 Usuários de Teste Disponíveis

#### 🔧 Administrador
- **Email:** admin@cortefacil.com
- **Senha:** admin123
- **Tipo:** admin

#### 🏪 Parceiros (Donos de Salão)
- **Email:** salao@teste.com | **Senha:** senha123
- **Email:** joao@email.com | **Senha:** senha123
- **Email:** barbearia@teste.com | **Senha:** senha123

#### 👤 Clientes
- **Email:** cliente@teste.com | **Senha:** senha123
- **Email:** maria@email.com | **Senha:** senha123
- **Email:** ana@cliente.com | **Senha:** senha123

## 🏪 Salões Cadastrados

1. **Salão Beleza Total**
   - Endereço: Rua das Flores, 123 - Centro
   - Profissionais: Ana Costa (Cabeleireira), Carlos Mendes (Barbeiro)

2. **Salão Elegante**
   - Endereço: Av. Principal, 456 - Jardins
   - Profissionais: Fernanda Lima (Manicure), Roberto Silva (Cabeleireiro)

3. **Barbearia Central**
   - Endereço: Rua do Comércio, 789 - Centro
   - Profissionais: Paulo Barbeiro, Diego Cortes

## 📅 Agendamentos de Exemplo

- 4 agendamentos criados para demonstração
- Status variados: confirmado, pendente
- Datas futuras para teste

## 🔧 Scripts Disponíveis

### 📄 schema.sql
Script principal que cria:
- Estrutura completa do banco
- Usuário administrador
- Dados básicos de exemplo

### 📄 usuarios_teste.sql
Script adicional que insere:
- Mais usuários de teste
- Salões adicionais
- Profissionais extras
- Agendamentos de exemplo

## 🚀 Como Usar

### Recriar o Banco (se necessário)
```bash
# Executar script principal
C:\xampp\mysql\bin\mysql.exe -u root -e "source c:/xampp/htdocs/cortefacilapp/database/schema.sql"

# Executar dados adicionais
C:\xampp\mysql\bin\mysql.exe -u root -e "source c:/xampp/htdocs/cortefacilapp/database/usuarios_teste.sql"
```

### Verificar Status
```bash
# Ver tabelas
C:\xampp\mysql\bin\mysql.exe -u root -e "USE cortefacil_db; SHOW TABLES;"

# Contar registros
C:\xampp\mysql\bin\mysql.exe -u root -e "USE cortefacil_db; SELECT COUNT(*) FROM usuarios;"
```

## 🔐 Configuração de Conexão

O arquivo `config/database.php` está configurado para:
- **Host:** localhost
- **Banco:** cortefacil_db
- **Usuário:** root
- **Senha:** (vazia - padrão XAMPP)

## ✅ Testes Realizados

- [x] Banco criado com sucesso
- [x] Todas as tabelas criadas
- [x] Dados de teste inseridos
- [x] Relacionamentos funcionando
- [x] Índices únicos aplicados
- [x] Sistema conectando corretamente

## 🎯 Próximos Passos

1. **Testar Login:** Use os usuários de teste
2. **Criar Agendamentos:** Teste o fluxo completo
3. **Verificar Relatórios:** Acesse os dashboards
4. **Personalizar:** Adicione seus próprios dados

---

**🎉 Banco de Dados Pronto para Uso!**
*Acesse: http://localhost/cortefacilapp*