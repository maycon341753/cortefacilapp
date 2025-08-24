<?php
/**
 * Teste de Autenticação da API de Profissionais
 * Verifica se o problema está na autenticação
 */

echo "<h2>🔐 Teste de Autenticação da API</h2>";

// Teste 1: Sem sessão
echo "<h3>1. Teste sem sessão ativa</h3>";
ob_start();
$_GET['salao'] = 1;
include 'api/profissionais.php';
$response1 = ob_get_contents();
ob_end_clean();

echo "<p><strong>Resposta:</strong> <code>" . htmlspecialchars($response1) . "</code></p>";

// Teste 2: Com sessão de cliente
echo "<h3>2. Teste com sessão de cliente</h3>";
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'cliente';
$_SESSION['nome'] = 'Cliente Teste';

ob_start();
$_GET['salao'] = 1;
include 'api/profissionais.php';
$response2 = ob_get_contents();
ob_end_clean();

echo "<p><strong>Resposta:</strong> <code>" . htmlspecialchars($response2) . "</code></p>";

// Teste 3: Verificar dados da sessão
echo "<h3>3. Dados da sessão atual</h3>";
echo "<p>Status da sessão: " . session_status() . " (" . (session_status() === PHP_SESSION_ACTIVE ? 'ATIVA' : 'INATIVA') . ")</p>";
echo "<p>ID da sessão: " . session_id() . "</p>";
echo "<p>Dados da sessão:</p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Teste 4: Verificar função isLoggedIn
echo "<h3>4. Teste da função isLoggedIn</h3>";
require_once 'includes/auth.php';
echo "<p>isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";
echo "<p>isCliente(): " . (isCliente() ? 'TRUE' : 'FALSE') . "</p>";

// Teste 5: Verificar se existe salão com ID 1
echo "<h3>5. Verificar salão ID 1</h3>";
require_once 'config/database.php';
try {
    $conn = connectWithFallback();
    $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = 1");
    $stmt->execute();
    $salao = $stmt->fetch();
    
    if ($salao) {
        echo "<p style='color: green;'>✅ Salão encontrado: {$salao['nome']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Salão com ID 1 não encontrado</p>";
        
        // Listar salões disponíveis
        $stmt = $conn->query("SELECT id, nome FROM saloes LIMIT 5");
        $saloes = $stmt->fetchAll();
        echo "<p>Salões disponíveis:</p>";
        foreach ($saloes as $s) {
            echo "<p>- ID {$s['id']}: {$s['nome']}</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar salão: {$e->getMessage()}</p>";
}

// Teste 6: Verificar profissionais do salão
echo "<h3>6. Verificar profissionais diretamente</h3>";
try {
    $stmt = $conn->prepare("SELECT * FROM profissionais WHERE id_salao = 1");
    $stmt->execute();
    $profissionais = $stmt->fetchAll();
    
    if (empty($profissionais)) {
        echo "<p style='color: orange;'>⚠️ Nenhum profissional encontrado para salão ID 1</p>";
        
        // Verificar se há profissionais em outros salões
        $stmt = $conn->query("SELECT id_salao, COUNT(*) as total FROM profissionais GROUP BY id_salao");
        $counts = $stmt->fetchAll();
        echo "<p>Profissionais por salão:</p>";
        foreach ($counts as $count) {
            echo "<p>- Salão ID {$count['id_salao']}: {$count['total']} profissional(is)</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ {count($profissionais)} profissional(is) encontrado(s)</p>";
        foreach ($profissionais as $prof) {
            $status = $prof['status'] ?? 'ativo';
            echo "<p>👤 {$prof['nome']} - {$prof['especialidade']} (Status: {$status})</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar profissionais: {$e->getMessage()}</p>";
}

// Teste 7: Testar com salão que tem profissionais
echo "<h3>7. Teste com primeiro salão que tem profissionais</h3>";
try {
    $stmt = $conn->query("SELECT DISTINCT id_salao FROM profissionais LIMIT 1");
    $result = $stmt->fetch();
    
    if ($result) {
        $salao_com_prof = $result['id_salao'];
        echo "<p>Testando com salão ID: {$salao_com_prof}</p>";
        
        ob_start();
        $_GET['salao'] = $salao_com_prof;
        include 'api/profissionais.php';
        $response3 = ob_get_contents();
        ob_end_clean();
        
        echo "<p><strong>Resposta:</strong> <code>" . htmlspecialchars($response3) . "</code></p>";
        
        // Tentar decodificar JSON
        $json_data = json_decode($response3, true);
        if ($json_data) {
            echo "<p><strong>JSON decodificado:</strong></p>";
            echo "<pre>" . print_r($json_data, true) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ Nenhum salão com profissionais encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no teste: {$e->getMessage()}</p>";
}
?>