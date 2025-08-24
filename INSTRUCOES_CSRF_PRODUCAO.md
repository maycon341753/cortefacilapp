# Instruções para Correção do CSRF em Produção

## Problema Identificado
O erro "Token de segurança não encontrado" persiste no ambiente de produção online (`https://cortefacil.app/parceiro/profissionais.php`), mesmo após as correções implementadas localmente.

## Possíveis Causas em Produção

### 1. Configurações de Sessão do Servidor
- **Problema**: Servidor pode ter configurações restritivas de sessão
- **Solução**: Verificar e ajustar configurações no `.htaccess` ou `php.ini`

### 2. Cache do Navegador/CDN
- **Problema**: Arquivos em cache podem estar desatualizados
- **Solução**: Limpar cache e forçar atualização

### 3. Diferenças entre Ambiente Local e Produção
- **Problema**: Configurações de PHP diferentes
- **Solução**: Verificar versão do PHP e extensões disponíveis

## Correções Implementadas

### Arquivo: `parceiro/profissionais.php`
```php
// Configurações de sessão seguras para produção
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 7200); // 2 horas
    
    // HTTPS apenas se disponível
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // SameSite para proteção adicional
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    session_start();
}
```

### Arquivo: `includes/auth.php`
- Atualizada função `generateCSRFToken()` para usar chave padrão `csrf_token`
- Reduzido tempo de expiração para 2 horas (produção)
- Adicionado tratamento de exceções robusto
- Implementado fallback de emergência

### Arquivo: `parceiro/get_csrf_token.php` (NOVO)
- Endpoint AJAX para regeneração de token
- Configurações de segurança para produção
- Validação de autenticação

## Passos para Implementar em Produção

### 1. Upload dos Arquivos Atualizados
```bash
# Fazer backup dos arquivos originais
cp parceiro/profissionais.php parceiro/profissionais.php.backup
cp includes/auth.php includes/auth.php.backup

# Fazer upload dos arquivos corrigidos
# - parceiro/profissionais.php
# - includes/auth.php
# - parceiro/get_csrf_token.php (novo arquivo)
```

### 2. Verificar Configurações do Servidor

#### Criar arquivo `.htaccess` na raiz (se não existir):
```apache
# Configurações de sessão
php_value session.cookie_httponly 1
php_value session.use_only_cookies 1
php_value session.gc_maxlifetime 7200
php_value session.cookie_lifetime 0

# Cache control para arquivos PHP
<FilesMatch "\.(php)$">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
    Header set Pragma "no-cache"
    Header set Expires 0
</FilesMatch>

# Segurança adicional
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### 3. Teste de Funcionalidade

#### Verificar se o problema foi resolvido:
1. Acessar `https://cortefacil.app/parceiro/profissionais.php`
2. Tentar cadastrar um novo profissional
3. Verificar se o erro "Token de segurança não encontrado" desapareceu

#### Se o problema persistir:
1. Verificar logs de erro do servidor
2. Testar com diferentes navegadores
3. Limpar cache do navegador e cookies
4. Verificar se a sessão está sendo mantida

### 4. Debug Adicional (se necessário)

#### Criar arquivo de debug temporário:
```php
<?php
// debug_session.php - REMOVER APÓS TESTE
session_start();
echo "<h3>Debug de Sessão</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>CSRF Token: " . ($_SESSION['csrf_token'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>Token Time: " . ($_SESSION['csrf_token_time'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>Timestamp Atual: " . time() . "</p>";
echo "<h4>Todas as Variáveis de Sessão:</h4>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>
```

## Melhorias Implementadas

### 1. Validação Robusta de CSRF
- Verificação de token vazio
- Verificação de expiração (2 horas)
- Comparação segura com `hash_equals()`
- Regeneração automática após operações
- Logs detalhados para debug

### 2. Tratamento de Erros
- Mensagens específicas para cada tipo de erro
- Recarregamento automático da página em caso de erro
- Fallback de emergência para geração de token

### 3. Configurações de Produção
- Cookies seguros para HTTPS
- HttpOnly para prevenir XSS
- SameSite para proteção CSRF adicional
- Tempo de vida adequado para produção

## Monitoramento

Após a implementação, monitorar:
- Logs de erro do servidor
- Feedback dos usuários
- Métricas de sessão
- Performance da aplicação

## Rollback (se necessário)

Em caso de problemas:
```bash
# Restaurar arquivos originais
cp parceiro/profissionais.php.backup parceiro/profissionais.php
cp includes/auth.php.backup includes/auth.php
rm parceiro/get_csrf_token.php
```

---

**Nota**: Estas correções foram testadas localmente e devem resolver o problema de CSRF em produção. Se o problema persistir, pode ser necessário investigar configurações específicas do servidor de hospedagem.