# 🚀 DEPLOY HOSTINGER - PASSO A PASSO

## ✅ STATUS ATUAL
- ✅ Banco de dados criado: `u690889028_cortefacilapp`
- ✅ Credenciais configuradas nos arquivos
- ✅ Script SQL pronto para execução
- ✅ Arquivo de teste criado

## 📋 PRÓXIMOS PASSOS

### 1. EXECUTAR SQL NO PHPMY ADMIN
1. Acesse o painel da Hostinger
2. Vá em **Bancos de Dados > phpMyAdmin**
3. Selecione o banco `u690889028_cortefacilapp`
4. Vá na aba **SQL**
5. Cole todo o conteúdo do arquivo `database/hostinger_schema.sql`
6. Clique em **Executar**

### 2. FAZER UPLOAD DOS ARQUIVOS
1. Acesse o **Gerenciador de Arquivos** da Hostinger
2. Vá para a pasta `public_html`
3. Faça upload de TODOS os arquivos do projeto
4. Mantenha a estrutura de pastas

### 3. TESTAR A CONEXÃO
1. Acesse: `https://cortefacil.app/teste_hostinger.php`
2. Verifique se todos os testes passam
3. **IMPORTANTE:** Remova o arquivo após o teste

### 4. ACESSAR O SISTEMA
- URL: `https://cortefacil.app`
- Login Admin: `admin@cortefacil.app` / `password`

## 🔐 LOGINS DE TESTE
- **Admin:** admin@cortefacil.app / password
- **Cliente:** maria@email.com / password  
- **Parceiro:** joao@email.com / password

## 📁 ARQUIVOS IMPORTANTES
- `config/database.php` - Configurações da Hostinger
- `database/hostinger_schema.sql` - Script SQL completo
- `teste_hostinger.php` - Teste de conexão (remover após uso)

## ⚠️ LEMBRETE
1. Remover `teste_hostinger.php` após teste
2. Configurar SSL/HTTPS
3. Alterar senhas padrão
4. Fazer backup regular

---
**Tudo pronto para deploy! 🎉**