<?php
/**
 * Correção global para o erro net::ERR_ABORTED @vite
 * Inclua este arquivo no início de suas páginas PHP
 */

// Aplicar cabeçalhos de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Vite-Ignore: true');
}

// Função para incluir o script bloqueador
function includeViteBlocker() {
    $scriptPath = '/assets/js/vite-blocker-safe.js';
    echo '<script src="' . $scriptPath . '" defer></script>' . "\n";
}

// Função para incluir proteção inline (caso o arquivo JS não esteja disponível)
function includeInlineViteProtection() {
    echo '<script>
(function(){
    if(window.fetch){
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const url = args[0];
            if (typeof url === "string" && url.includes("@vite")) {
                console.warn("Vite request blocked:", url);
                return Promise.reject(new Error("Vite blocked"));
            }
            return originalFetch.apply(this, args);
        };
    }
    console.log("🛡️ Vite protection active");
})();
</script>' . "\n";
}

// Auto-incluir proteção se não estiver em modo de produção
if (!defined('VITE_FIX_MANUAL') || !VITE_FIX_MANUAL) {
    // Verificar se estamos em uma requisição HTTP normal
    if (isset($_SERVER['HTTP_HOST']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // Adicionar ao buffer de saída
        ob_start(function($buffer) {
            // Inserir o script antes do fechamento do </head> ou no início do <body>
            if (strpos($buffer, '</head>') !== false) {
                $viteScript = '<script src="/assets/js/vite-blocker-safe.js" defer></script>';
                $buffer = str_replace('</head>', $viteScript . "\n</head>", $buffer);
            } elseif (strpos($buffer, '<body') !== false) {
                $viteScript = '<script src="/assets/js/vite-blocker-safe.js" defer></script>';
                $buffer = preg_replace('/(<body[^>]*>)/', '$1' . "\n" . $viteScript, $buffer);
            }
            return $buffer;
        });
    }
}

// Log para debug (apenas em desenvolvimento)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log('Vite Fix aplicado em: ' . $_SERVER['REQUEST_URI'] ?? 'CLI');
}

?>