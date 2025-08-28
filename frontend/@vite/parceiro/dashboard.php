<?php
/**
 * Interceptador para requisições @vite
 * Este arquivo bloqueia as tentativas de carregamento de recursos @vite
 * causadas por extensões do navegador
 */

// Definir cabeçalhos para bloquear a requisição
header('HTTP/1.1 204 No Content');
header('Content-Length: 0');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Finalizar a execução sem retornar conteúdo
exit();
?>