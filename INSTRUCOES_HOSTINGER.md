# Instruções para Resolver Erro 500 no Hostinger - CorteFácil

## Problema Atual
O site `https://cortefacil.app/cadastro.php` está apresentando erro HTTP 500 (Internal Server Error).

## Arquivos de Diagnóstico Criados

### 1. `debug_500.php`
- **Propósito**: Diagnosticar a causa do erro 500
- **Como usar**: Faça upload deste arquivo para o servidor e acesse `https://cortefacil.app/debug_500.php`
- **O que faz**: Verifica extensões PHP, arquivos de configuração, permissões e configurações do servidor

### 2. `test_simple.php`
- **Propósito**: Teste básico de funcionamento do PHP
- **Como usar**: Acesse `https://cortefacil.app/test_simple.php`
- **O que faz**: Verifica se o PHP está funcionando e se as extensões necessárias estão disponíveis

### 3. `test_connection.php`
- **Propósito**: Testar conexão com o banco de dados
- **Como usar**: Acesse `https://cortefacil.app/test_connection.php`
- **O que faz**: Tenta conectar com o banco de dados da Hostinger

### 4. `.htaccess`
- **Propósito**: Configurações otimizadas para Hostinger
- **O que faz**: Define limites de memória, configurações de segurança e cache

## Passos para Resolver o Problema

### Passo 1: Upload dos Arquivos
1. Faça upload de todos os arquivos do projeto para o diretório `public_html` do Hostinger
2. Certifique-se de que a estrutura de diretórios está correta:
   ```
   public_html/
   ├── cadastro.php
   ├── debug_500.php
   ├── test_simple.php
   ├── test_connection.php
   ├── .htaccess
   ├── config/
   │   └── database.php
   ├── includes/
   │   ├── auth.php
   │   └── functions.php
   └── models/
       └── Usuario.php
   ```

### Passo 2: Verificar Permissões
No painel do Hostinger ou via FTP, defina as permissões:
- **Arquivos PHP**: 644
- **Diretórios**: 755
- **Arquivo .htaccess**: 644

### Passo 3: Executar Diagnósticos
1. Acesse `https://cortefacil.app/debug_500.php`
2. Verifique se há erros reportados
3. Se necessário, acesse `https://cortefacil.app/test_simple.php` para teste básico

### Passo 4: Verificar Banco de Dados
1. No painel da Hostinger, certifique-se de que o banco `u690889028_cortefacil` existe
2. Execute o script `schema.sql` no banco de dados:
   - Acesse phpMyAdmin no painel da Hostinger
   - Selecione o banco `u690889028_cortefacil`
   - Vá em "Importar" e faça upload do arquivo `schema.sql`
3. Teste a conexão acessando `https://cortefacil.app/test_connection.php`

### Passo 5: Verificar Logs de Erro
No painel da Hostinger:
1. Vá em "Arquivos" > "Gerenciador de Arquivos"
2. Procure por arquivos de log de erro (geralmente em `logs/` ou `error_logs/`)
3. Verifique se há mensagens de erro específicas

## Possíveis Causas do Erro 500

### 1. Arquivo .htaccess Problemático
- **Solução**: Renomeie temporariamente o `.htaccess` para `.htaccess_backup` e teste
- Se o site funcionar, o problema está no .htaccess

### 2. Limites de Memória PHP
- **Sintoma**: Site funciona localmente mas não no servidor
- **Solução**: O arquivo .htaccess já inclui `php_value memory_limit 256M`

### 3. Versão PHP Incompatível
- **Verificação**: No painel da Hostinger, vá em "PHP" e verifique a versão
- **Solução**: Use PHP 8.0 ou superior

### 4. Problemas de Conexão com Banco
- **Sintoma**: Erro ao conectar com o banco de dados
- **Verificação**: Use o arquivo `test_connection.php`
- **Solução**: Verifique as credenciais no arquivo `config/database.php`

### 5. Arquivos Corrompidos no Upload
- **Sintoma**: Arquivos funcionam localmente mas não no servidor
- **Solução**: Refaça o upload dos arquivos, preferencialmente via FTP

### 6. Permissões de Arquivo Incorretas
- **Sintoma**: Erro 500 em arquivos específicos
- **Solução**: Defina permissões 644 para arquivos e 755 para diretórios

## Configurações Atuais do Banco

```php
$host = 'srv488.hstgr.io';
$database = 'u690889028_cortefacil';
$username = 'u690889028';
$password = 'Brava1997?';
```

## Contato com Suporte

Se o problema persistir após seguir todos os passos:
1. Entre em contato com o suporte da Hostinger
2. Informe que está tendo erro 500 em arquivos PHP
3. Mencione que já verificou permissões, .htaccess e configurações PHP
4. Forneça os resultados dos arquivos de diagnóstico

## Arquivos para Backup

Antes de fazer alterações, faça backup dos seguintes arquivos:
- `config/database.php`
- `.htaccess`
- Qualquer arquivo personalizado

---

**Nota**: Estes arquivos de diagnóstico devem ser removidos após resolver o problema por questões de segurança.