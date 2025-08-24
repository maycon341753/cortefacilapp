<?php
/**
 * Script para testar o dashboard simulando acesso online real
 * Simula as condições exatas do ambiente de produção
 */

// Simular ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['DOCUMENT_ROOT'] = '/home/u690889028/public_html';
$_SERVER['REQUEST_URI'] = '/parceiro/dashboard.php';

echo "<h2>Teste do Dashboard - Simulação Ambiente Online</h2>";
echo "<p><strong>Simulando acesso via:</strong> https://cortefacil.app/parceiro/dashboard.php</p>";

try {
    // 1. Testar detecção de ambiente
    echo "<h3>1. Detecção de Ambiente:</h3>";
    echo "<p>SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "</p>";
    echo "<p>HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "</p>";
    
    require_once __DIR__ . '/config/database.php';
    
    $db = Database::getInstance();
    $debugInfo = $db->getDebugInfo();
    echo "<p>Ambiente detectado: <strong>" . $debugInfo['environment'] . "</strong></p>";
    
    if ($debugInfo['environment'] === 'online') {
        echo "<p style='color: green;'>✅ Ambiente online detectado corretamente!</p>";
    } else {
        echo "<p style='color: red;'>❌ Ambiente local detectado incorretamente!</p>";
        throw new Exception('Detecção de ambiente falhou');
    }
    
    // 2. Testar conexão
    echo "<h3>2. Teste de Conexão:</h3>";
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com banco online');
    }
    
    echo "<p style='color: green;'>✅ Conexão online estabelecida!</p>";
    echo "<p>Host: {$debugInfo['host']}</p>";
    echo "<p>Database: {$debugInfo['database']}</p>";
    echo "<p>Username: {$debugInfo['username']}</p>";
    
    // 3. Simular sessão de usuário parceiro
    echo "<h3>3. Simulação de Sessão:</h3>";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simular usuário parceiro logado
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'parceiro';
    $_SESSION['user_name'] = 'Parceiro Teste';
    $_SESSION['logged_in'] = true;
    
    echo "<p>Usuário simulado: ID=1, Tipo=parceiro</p>";
    
    // 4. Incluir arquivos do dashboard
    echo "<h3>4. Inclusão de Arquivos Críticos:</h3>";
    
    $arquivos_criticos = [
        __DIR__ . '/includes/auth.php',
        __DIR__ . '/includes/functions.php',
        __DIR__ . '/models/salao.php',
        __DIR__ . '/models/profissional.php',
        __DIR__ . '/models/agendamento.php'
    ];
    
    foreach ($arquivos_criticos as $arquivo) {
        if (!file_exists($arquivo)) {
            echo "<p style='color: red;'>❌ Arquivo não encontrado: $arquivo</p>";
            throw new Exception("Arquivo crítico não encontrado: $arquivo");
        } else {
            echo "<p style='color: green;'>✅ " . basename($arquivo) . "</p>";
        }
    }
    
    // Incluir arquivos
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/models/salao.php';
    require_once __DIR__ . '/models/profissional.php';
    require_once __DIR__ . '/models/agendamento.php';
    
    echo "<p style='color: green;'>✅ Todos os arquivos incluídos com sucesso!</p>";
    
    // 5. Testar funções de autenticação
    echo "<h3>5. Teste de Autenticação:</h3>";
    
    if (function_exists('isLoggedIn')) {
        $logged = isLoggedIn();
        echo "<p>isLoggedIn(): " . ($logged ? 'true' : 'false') . "</p>";
    }
    
    if (function_exists('isParceiro')) {
        $parceiro = isParceiro();
        echo "<p>isParceiro(): " . ($parceiro ? 'true' : 'false') . "</p>";
    }
    
    if (function_exists('getLoggedUser')) {
        $usuario = getLoggedUser();
        if ($usuario) {
            echo "<p>getLoggedUser(): ID={$usuario['id']}, Nome={$usuario['nome']}</p>";
        } else {
            echo "<p style='color: orange;'>getLoggedUser(): null</p>";
        }
    }
    
    // 6. Testar busca de salão
    echo "<h3>6. Teste de Busca de Salão:</h3>";
    
    $salao = new Salao();
    $meu_salao = $salao->buscarPorDono(1); // ID do usuário simulado
    
    if ($meu_salao) {
        echo "<p style='color: green;'>✅ Salão encontrado: {$meu_salao['nome']}</p>";
        echo "<p>ID: {$meu_salao['id']}</p>";
        echo "<p>Endereço: {$meu_salao['endereco']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum salão encontrado para o usuário (redirecionaria para cadastro)</p>";
    }
    
    // 7. Resultado final
    echo "<h3>7. Resultado Final:</h3>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #155724;'>✅ Dashboard Online Funcionando!</h4>";
    echo "<p>Todas as verificações passaram com sucesso. O dashboard deveria funcionar corretamente no ambiente online.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #721c24;'>❌ Erro Encontrado:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Próximos Passos:</strong></p>";
echo "<ul>";
echo "<li>Se este teste passou, o problema está resolvido</li>";
echo "<li>Se ainda há erros, verificar logs específicos</li>";
echo "<li>Testar acesso real via https://cortefacil.app/parceiro/dashboard.php</li>";
echo "</ul>";
?>