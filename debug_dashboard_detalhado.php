<?php
/**
 * Debug detalhado do Dashboard do Parceiro
 * Identifica exatamente onde está o problema
 */

// Iniciar sessão
session_start();

// Simular usuário logado como parceiro
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'parceiro@teste.com';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<h2>🔍 Debug Detalhado do Dashboard</h2>";

// 1. Testar Health Check
echo "<h3>1. Testando Health Check</h3>";
try {
    if (file_exists('includes/health_check.php')) {
        echo "✅ Arquivo health_check.php existe<br>";
        
        // Desabilitar redirecionamento automático
        define('HEALTH_CHECK_DISABLED', true);
        
        require_once 'includes/health_check.php';
        
        $health = getSystemHealthStatus();
        
        if ($health['healthy']) {
            echo "✅ Sistema saudável segundo health check<br>";
        } else {
            echo "❌ Sistema não saudável: " . $health['message'] . "<br>";
        }
        
        // Testar função de redirecionamento
        echo "<strong>Testando isSystemHealthy():</strong><br>";
        if (isSystemHealthy()) {
            echo "✅ isSystemHealthy() retorna true<br>";
        } else {
            echo "❌ isSystemHealthy() retorna false<br>";
        }
        
    } else {
        echo "❌ Arquivo health_check.php não encontrado<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no health check: " . $e->getMessage() . "<br>";
}

// 2. Testar arquivos de autenticação
echo "<h3>2. Testando Arquivos de Autenticação</h3>";
$arquivos_auth = [
    'includes/auth.php',
    'includes/functions.php',
    'models/salao.php',
    'models/profissional.php',
    'models/agendamento.php'
];

foreach ($arquivos_auth as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo existe<br>";
    } else {
        echo "❌ $arquivo NÃO existe<br>";
    }
}

// 3. Testar funções de autenticação
echo "<h3>3. Testando Funções de Autenticação</h3>";
try {
    require_once 'includes/auth.php';
    
    echo "<strong>isLoggedIn():</strong> " . (isLoggedIn() ? "✅ true" : "❌ false") . "<br>";
    echo "<strong>isParceiro():</strong> " . (isParceiro() ? "✅ true" : "❌ false") . "<br>";
    echo "<strong>hasUserType('parceiro'):</strong> " . (hasUserType('parceiro') ? "✅ true" : "❌ false") . "<br>";
    
    $user = getLoggedUser();
    if ($user) {
        echo "<strong>getLoggedUser():</strong> ✅ Dados obtidos<br>";
        echo "&nbsp;&nbsp;• ID: " . $user['id'] . "<br>";
        echo "&nbsp;&nbsp;• Tipo: " . $user['tipo_usuario'] . "<br>";
    } else {
        echo "<strong>getLoggedUser():</strong> ❌ Nenhum dado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na autenticação: " . $e->getMessage() . "<br>";
}

// 4. Testar conexão com banco
echo "<h3>4. Testando Conexão com Banco</h3>";
try {
    require_once 'config/database.php';
    
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if ($conn) {
        echo "✅ Conexão com banco estabelecida<br>";
        
        // Testar query simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result && $result['test'] == 1) {
            echo "✅ Query de teste executada com sucesso<br>";
        } else {
            echo "❌ Falha na query de teste<br>";
        }
        
    } else {
        echo "❌ Falha na conexão com banco<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

// 5. Testar modelos
echo "<h3>5. Testando Modelos</h3>";
try {
    require_once 'models/salao.php';
    require_once 'models/profissional.php';
    require_once 'models/agendamento.php';
    
    $salao = new Salao();
    $profissional = new Profissional();
    $agendamento = new Agendamento();
    
    echo "✅ Modelos instanciados com sucesso<br>";
    
    // Testar busca de salão
    $meu_salao = $salao->buscarPorDono(1);
    
    if ($meu_salao) {
        echo "✅ Salão encontrado para o usuário<br>";
        echo "&nbsp;&nbsp;• Nome: " . $meu_salao['nome'] . "<br>";
        echo "&nbsp;&nbsp;• ID: " . $meu_salao['id'] . "<br>";
    } else {
        echo "⚠️ Nenhum salão encontrado para o usuário (pode ser normal)<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro nos modelos: " . $e->getMessage() . "<br>";
}

// 6. Simular execução do dashboard passo a passo
echo "<h3>6. Simulando Dashboard Passo a Passo</h3>";

echo "<strong>Passo 1:</strong> Verificação de saúde...<br>";
if (defined('HEALTH_CHECK_DISABLED')) {
    echo "✅ Health check desabilitado para teste<br>";
} else {
    echo "⚠️ Health check ativo - pode redirecionar<br>";
}

echo "<strong>Passo 2:</strong> Verificação de arquivos críticos...<br>";
$arquivos_criticos = [
    'includes/auth.php',
    'includes/functions.php',
    'models/salao.php',
    'models/profissional.php',
    'models/agendamento.php'
];

$arquivos_ok = true;
foreach ($arquivos_criticos as $arquivo) {
    if (!file_exists($arquivo)) {
        echo "❌ Arquivo crítico não encontrado: $arquivo<br>";
        $arquivos_ok = false;
    }
}

if ($arquivos_ok) {
    echo "✅ Todos os arquivos críticos encontrados<br>";
}

echo "<strong>Passo 3:</strong> Verificação de funções...<br>";
if (function_exists('requireParceiro')) {
    echo "✅ Função requireParceiro existe<br>";
} else {
    echo "❌ Função requireParceiro não encontrada<br>";
}

if (function_exists('getLoggedUser')) {
    echo "✅ Função getLoggedUser existe<br>";
} else {
    echo "❌ Função getLoggedUser não encontrada<br>";
}

echo "<hr>";
echo "<p><strong>Conclusão:</strong> Se todos os itens acima estão ✅, o problema pode estar na lógica específica do dashboard.</p>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
?>