# Instruções para Upload no Hostinger - CorteFácil

## 📋 Checklist Pré-Upload

### 1. Arquivos Atualizados
- ✅ `.htaccess` - Configurado para produção
- ✅ `profissionais.php` - Correções CSRF implementadas
- ✅ `auth.php` - Funções CSRF atualizadas
- ✅ `get_csrf_token.php` - Endpoint para regeneração de token

### 2. Configurações de Produção
- ✅ Erros PHP desabilitados no `.htaccess`
- ✅ Configurações de sessão seguras
- ✅ Cabeçalhos de segurança configurados
- ✅ Compressão GZIP habilitada
- ✅ Cache otimizado

## 🚀 Passos para Upload

### Passo 1: Preparar Arquivos
1. Faça backup do site atual no Hostinger
2. Baixe todos os arquivos atualizados:
   - `.htaccess`
   - `profissionais.php`
   - `auth.php`
   - `get_csrf_token.php`

### Passo 2: Upload via File Manager
1. Acesse o **File Manager** no painel do Hostinger
2. Navegue até a pasta `public_html` (ou pasta do seu domínio)
3. Faça upload dos arquivos atualizados
4. **IMPORTANTE**: Substitua os arquivos existentes

### Passo 3: Configurações Específicas do Hostinger

#### A. Verificar PHP Version
- Certifique-se de estar usando **PHP 7.4** ou superior
- Acesse: **Hosting → Advanced → PHP Configuration**

#### B. Configurações de Sessão (se necessário)
Se o `.htaccess` não funcionar para sessões, configure no painel:
```
session.cookie_httponly = On
session.use_only_cookies = On
session.gc_maxlifetime = 7200
```

#### C. Habilitar HTTPS (Recomendado)
1. Acesse **SSL/TLS** no painel
2. Ative o certificado SSL gratuito
3. Descomente as linhas HTTPS no `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Passo 4: Configurar Banco de Dados
1. Verifique as configurações em `config/database.php`
2. Atualize as credenciais do banco Hostinger se necessário
3. Teste a conexão

## 🔧 Configurações Específicas do .htaccess

### Recursos Habilitados
- **Segurança**: Cabeçalhos de segurança, CSP, proteção de arquivos
- **Performance**: GZIP, cache, otimizações
- **CSRF**: Configurações de sessão seguras
- **URL Rewriting**: URLs amigáveis para todas as seções
- **Tratamento de Erros**: Logs configurados

### Configurações de Produção
```apache
# Erros desabilitados
php_flag display_errors Off

# Timezone configurado
php_value date.timezone "America/Sao_Paulo"

# Sessões seguras para CSRF
php_value session.cookie_httponly 1
php_value session.use_only_cookies 1
php_value session.gc_maxlifetime 7200
```

## 🧪 Testes Pós-Upload

### 1. Teste Básico
- [ ] Site carrega sem erros 500
- [ ] Login funciona corretamente
- [ ] Páginas principais acessíveis

### 2. Teste CSRF
- [ ] Acesse `parceiro/profissionais.php`
- [ ] Tente cadastrar um profissional
- [ ] Verifique se não há erro de "Token inválido"
- [ ] Confirme que o token é regenerado após operações

### 3. Teste de Segurança
- [ ] Tente acessar `config/database.php` diretamente (deve dar erro 403)
- [ ] Verifique cabeçalhos de segurança no navegador (F12 → Network)
- [ ] Confirme que arquivos sensíveis estão protegidos

### 4. Teste de Performance
- [ ] Verifique compressão GZIP (ferramentas online)
- [ ] Teste velocidade de carregamento
- [ ] Confirme cache de arquivos estáticos

## 🚨 Troubleshooting

### Erro 500 - Internal Server Error
**Possíveis causas:**
1. Sintaxe incorreta no `.htaccess`
2. Módulos Apache não disponíveis no Hostinger
3. Configurações PHP incompatíveis

**Soluções:**
1. Renomeie `.htaccess` para `.htaccess_backup` temporariamente
2. Adicione configurações gradualmente
3. Verifique logs de erro no painel Hostinger

### Problemas de CSRF
**Se ainda houver erros de token:**
1. Verifique se as sessões estão funcionando
2. Confirme timezone do servidor
3. Teste o endpoint `get_csrf_token.php` diretamente
4. Verifique logs de erro PHP

### Problemas de Redirecionamento
**Se URLs não funcionarem:**
1. Confirme que mod_rewrite está habilitado
2. Verifique se as regras RewriteRule estão corretas
3. Teste URLs diretas primeiro (ex: `login.php`)

## 📞 Suporte

### Logs Importantes
- **Error Log**: `error.log` (criado automaticamente)
- **Access Log**: Disponível no painel Hostinger
- **PHP Error Log**: Configurado no `.htaccess`

### Comandos Úteis (via SSH se disponível)
```bash
# Verificar logs de erro
tail -f error.log

# Testar configuração Apache
apachectl configtest

# Verificar módulos carregados
apache2ctl -M
```

## ✅ Checklist Final

- [ ] Backup realizado
- [ ] Arquivos enviados
- [ ] PHP version verificada
- [ ] Banco de dados configurado
- [ ] HTTPS configurado (opcional)
- [ ] Testes básicos realizados
- [ ] Testes CSRF realizados
- [ ] Performance verificada
- [ ] Logs monitorados

---

**Data de Criação**: $(date)
**Versão**: 1.0
**Status**: Pronto para produção

> **Nota**: Mantenha este arquivo como referência para futuras atualizações e troubleshooting.