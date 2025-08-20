# 🔧 Instruções para Correção do CSRF Online

## Problema Identificado
O site `https://cortefacil.app/parceiro/salao.php` está apresentando erro "Token de segurança inválido" devido a problemas na implementação do CSRF no ambiente de produção.

## Arquivos para Upload

### 1. Arquivo Principal de Correção
📁 **Arquivo:** `fix_csrf_online.php`
📍 **Local:** Raiz do projeto (mesmo diretório do `index.php`)

### 2. Modificações no auth.php

**Localizar o arquivo:** `includes/auth.php`

**Substituir as funções existentes:**

```php
// SUBSTITUIR a função generateCSRFToken() por:
function generateCSRFToken() {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Gerar novo token se necessário
    $regenerate_token = false;
    
    if (!isset($_SESSION['csrf_token'])) {
        $regenerate_token = true;
    } elseif (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        // Token expira em 1 hora
        $regenerate_token = true;
    } elseif (!isset($_SESSION['csrf_token_time'])) {
        // Se não tem timestamp, regenerar
        $regenerate_token = true;
    }
    
    if ($regenerate_token) {
        // Usar método mais robusto para gerar token
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback para servidores mais antigos
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

// SUBSTITUIR a função verifyCSRFToken() por:
function verifyCSRFToken($token) {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Verificações básicas
    if (empty($token)) {
        return false;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Verificar se o token não expirou
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    // Comparação segura
    if (function_exists('hash_equals')) {
        return hash_equals($_SESSION['csrf_token'], $token);
    } else {
        // Fallback para servidores sem hash_equals
        return $_SESSION['csrf_token'] === $token;
    }
}
```

### 3. Configurações de Sessão Melhoradas

**No início do arquivo `includes/auth.php`, ADICIONAR após a abertura do PHP:**

```php
// Configurações robustas para servidor online
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Configurações específicas para HTTPS (ambiente online)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para compatibilidade com navegadores modernos
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    // Definir nome da sessão específico
    session_name('CORTEFACIL_SESSION');
}
```

## Passos para Implementação

### Passo 1: Backup
1. Fazer backup dos arquivos `includes/auth.php` e `parceiro/salao.php`

### Passo 2: Upload dos Arquivos
1. Fazer upload do arquivo `fix_csrf_online.php` para a raiz do projeto
2. Modificar o arquivo `includes/auth.php` conforme as instruções acima

### Passo 3: Teste
1. Acessar `https://cortefacil.app/fix_csrf_online.php` para verificar se as correções estão funcionando
2. Testar o formulário na página de teste
3. Se o teste passar, acessar `https://cortefacil.app/parceiro/salao.php` para verificar se o problema foi resolvido

### Passo 4: Limpeza (Opcional)
1. Após confirmar que tudo está funcionando, remover o arquivo `fix_csrf_online.php` por segurança

## Verificações Importantes

### ✅ Checklist de Verificação
- [ ] Arquivo `fix_csrf_online.php` foi enviado para o servidor
- [ ] Funções CSRF foram atualizadas no `auth.php`
- [ ] Configurações de sessão foram adicionadas
- [ ] Teste em `fix_csrf_online.php` passou com sucesso
- [ ] Página `parceiro/salao.php` não apresenta mais erro de CSRF
- [ ] Formulário do salão pode ser enviado sem erros

### 🔍 Diagnóstico de Problemas

Se ainda houver problemas:

1. **Verificar logs do servidor** para erros PHP
2. **Verificar se as sessões estão funcionando** (cookies habilitados)
3. **Verificar se o HTTPS está configurado corretamente**
4. **Verificar se não há cache interferindo** (limpar cache do navegador)

### 📞 Suporte

Se os problemas persistirem, verificar:
- Versão do PHP no servidor
- Configurações de sessão do servidor
- Logs de erro do Apache/Nginx
- Configurações de firewall/CDN que possam interferir com cookies

---

**Data da Correção:** $(date)
**Versão:** 1.0
**Status:** Pronto para implementação