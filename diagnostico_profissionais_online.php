<?php
/**
 * Diagnóstico específico para página profissionais.php no ambiente online
 * Verifica problemas de permissões, configurações e dependências
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Diagnóstico Profissionais Online</title></head><body>";
echo "<h1>🔍 Diagnóstico da Página Profissionais - Ambiente Online</h1>";
echo "<hr>";

// 1. Verificar ambiente
echo "<h2>1. Verificação de Ambiente</h2>";
echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "</p>";
echo "<p><strong>PHP_VERSION:</strong> " . phpversion() . "</p>";

// 2. Verificar arquivo .env.online
echo "<h2>2. Verificação do Arquivo .env.online</h2>";
$envFile = __DIR__ . '/.env.online';
if (file_exists($envFile)) {
    echo "<p>✅ Arquivo .env.online existe</p>";
    echo "<p><strong>Conteúdo:</strong></p>";
    echo "<pre>" . htmlspecialchars(file_get_contents($envFile)) . "</pre>";
} else {
    echo "<p>❌ Arquivo .env.online não encontrado</p>";
}

// 3. Testar conexão de banco
echo "<h2>3. Teste de Conexão com Banco de Dados</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    
    // Forçar configuração online
    $db->forceOnlineConfig();
    echo "<p>✅ Configuração online forçada</p>";
    
    $conn = $db->connect();
    if ($conn) {
        echo "<p>✅ Conexão estabelecida com sucesso</p>";
        
        // Testar uma query simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "<p>✅ Query de teste executada: " . $result['test'] . "</p>";
        
        // Verificar tabelas necessárias
        $tables = ['usuarios', 'saloes', 'profissionais'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
                $count = $stmt->fetch()['count'];
                echo "<p>✅ Tabela '$table': $count registros</p>";
            } catch (Exception $e) {
                echo "<p>❌ Tabela '$table': " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p>❌ Falha na conexão</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

// 4. Verificar arquivos críticos
echo "<h2>4. Verificação de Arquivos Críticos</h2>";
$arquivos = [
    'config/database.php',
    'includes/auth.php',
    'includes/functions.php',
    'models/salao.php',
    'models/profissional.php',
    'parceiro/profissionais.php'
];

foreach ($arquivos as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        $perms = substr(sprintf('%o', fileperms($caminho)), -4);
        $size = filesize($caminho);
        echo "<p>✅ $arquivo - Permissões: $perms, Tamanho: $size bytes</p>";
    } else {
        echo "<p>❌ $arquivo - NÃO ENCONTRADO</p>";
    }
}

// 5. Testar includes
echo "<h2>5. Teste de Includes</h2>";
try {
    require_once __DIR__ . '/includes/auth.php';
    echo "<p>✅ auth.php carregado</p>";
    
    require_once __DIR__ . '/includes/functions.php';
    echo "<p>✅ functions.php carregado</p>";
    
    require_once __DIR__ . '/models/salao.php';
    echo "<p>✅ salao.php carregado</p>";
    
    require_once __DIR__ . '/models/profissional.php';
    echo "<p>✅ profissional.php carregado</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar includes: " . $e->getMessage() . "</p>";
}

// 6. Verificar funções necessárias
echo "<h2>6. Verificação de Funções</h2>";
$funcoes = ['isLoggedIn', 'isParceiro', 'getLoggedUser', 'requireParceiro'];
foreach ($funcoes as $funcao) {
    if (function_exists($funcao)) {
        echo "<p>✅ Função '$funcao' disponível</p>";
    } else {
        echo "<p>❌ Função '$funcao' não encontrada</p>";
    }
}

// 7. Verificar classes
echo "<h2>7. Verificação de Classes</h2>";
$classes = ['Database', 'Salao', 'Profissional'];
foreach ($classes as $classe) {
    if (class_exists($classe)) {
        echo "<p>✅ Classe '$classe' disponível</p>";
    } else {
        echo "<p>❌ Classe '$classe' não encontrada</p>";
    }
}

// 8. Simular acesso à página
echo "<h2>8. Simulação de Acesso à Página</h2>";
try {
    // Simular sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simular usuário logado
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'parceiro';
    
    echo "<p>✅ Sessão iniciada</p>";
    echo "<p>✅ Usuário simulado como parceiro</p>";
    
    // Testar autenticação
    if (function_exists('isLoggedIn') && isLoggedIn()) {
        echo "<p>✅ Autenticação funcionando</p>";
    } else {
        echo "<p>❌ Problema na autenticação</p>";
    }
    
    if (function_exists('isParceiro') && isParceiro()) {
        echo "<p>✅ Verificação de parceiro funcionando</p>";
    } else {
        echo "<p>❌ Problema na verificação de parceiro</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro na simulação: " . $e->getMessage() . "</p>";
}

// 9. Resultado final
echo "<h2>9. Diagnóstico Final</h2>";
echo "<div style='background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px;'>";
echo "<h3>📋 Resumo do Diagnóstico</h3>";
echo "<p>Este diagnóstico verificou todos os componentes necessários para o funcionamento da página profissionais.php</p>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";
echo "</div>";

echo "</body></html>";
?>