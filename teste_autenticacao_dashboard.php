<?php
/**
 * Teste de Autenticação do Dashboard
 * Verifica se as funções de autenticação estão funcionando
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>🔐 Teste de Autenticação do Dashboard</h2>";

// Simular usuário parceiro logado
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'parceiro@teste.com';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<p><strong>Sessão simulada:</strong></p>";
echo "<ul>";
echo "<li>ID: " . $_SESSION['usuario_id'] . "</li>";
echo "<li>Tipo: " . $_SESSION['tipo_usuario'] . "</li>";
echo "<li>Nome: " . $_SESSION['usuario_nome'] . "</li>";
echo "<li>Email: " . $_SESSION['usuario_email'] . "</li>";
echo "</ul>";

// Incluir arquivos necessários
try {
    require_once 'includes/auth.php';
    echo "<p style='color: green'>✅ auth.php incluído com sucesso</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Erro ao incluir auth.php: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>Testando funções de autenticação:</h3>";
echo "<ul>";

// Testar isLoggedIn
echo "<li><strong>isLoggedIn():</strong> ";
if (function_exists('isLoggedIn')) {
    $result = isLoggedIn();
    echo $result ? "<span style='color: green'>✅ TRUE</span>" : "<span style='color: red'>❌ FALSE</span>";
} else {
    echo "<span style='color: red'>❌ Função não existe</span>";
}
echo "</li>";

// Testar isParceiro
echo "<li><strong>isParceiro():</strong> ";
if (function_exists('isParceiro')) {
    $result = isParceiro();
    echo $result ? "<span style='color: green'>✅ TRUE</span>" : "<span style='color: red'>❌ FALSE</span>";
} else {
    echo "<span style='color: red'>❌ Função não existe</span>";
}
echo "</li>";

// Testar hasUserType
echo "<li><strong>hasUserType('parceiro'):</strong> ";
if (function_exists('hasUserType')) {
    $result = hasUserType('parceiro');
    echo $result ? "<span style='color: green'>✅ TRUE</span>" : "<span style='color: red'>❌ FALSE</span>";
} else {
    echo "<span style='color: red'>❌ Função não existe</span>";
}
echo "</li>";

// Testar getLoggedUser
echo "<li><strong>getLoggedUser():</strong> ";
if (function_exists('getLoggedUser')) {
    $user = getLoggedUser();
    if ($user) {
        echo "<span style='color: green'>✅ Usuário encontrado</span>";
        echo " (ID: {$user['id']}, Tipo: {$user['tipo_usuario']})";
    } else {
        echo "<span style='color: red'>❌ Nenhum usuário encontrado</span>";
    }
} else {
    echo "<span style='color: red'>❌ Função não existe</span>";
}
echo "</li>";

echo "</ul>";

echo "<h3>Testando requireParceiro (sem redirecionamento):</h3>";
if (function_exists('requireParceiro')) {
    echo "<p>Função requireParceiro existe. Vamos verificar o que ela faz...</p>";
    
    // Capturar qualquer saída ou redirecionamento
    ob_start();
    
    try {
        // Não chamar requireParceiro diretamente pois pode redirecionar
        // Em vez disso, vamos verificar manualmente as condições
        
        if (function_exists('requireUserType')) {
            echo "<p>Função requireUserType existe. Testando condições...</p>";
            
            if (isLoggedIn() && hasUserType('parceiro')) {
                echo "<p style='color: green'>✅ Usuário está logado e é parceiro - requireParceiro deveria passar</p>";
            } else {
                echo "<p style='color: red'>❌ Usuário não está logado ou não é parceiro - requireParceiro redirecionaria</p>";
                echo "<ul>";
                echo "<li>isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "</li>";
                echo "<li>hasUserType('parceiro'): " . (hasUserType('parceiro') ? 'true' : 'false') . "</li>";
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red'>❌ Função requireUserType não existe</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    $output = ob_get_clean();
    echo $output;
    
} else {
    echo "<p style='color: red'>❌ Função requireParceiro não existe</p>";
}

echo "<h3>Informações da sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>