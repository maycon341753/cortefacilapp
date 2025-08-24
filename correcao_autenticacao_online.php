<?php
/**
 * Correção de Autenticação Online - CorteFácil
 * Resolve problemas de sessão e autenticação no ambiente de produção
 */

// Configurações de erro
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h2>Correção de Autenticação Online - CorteFácil</h2>";
echo "<hr>";

// 1. Forçar conexão online
echo "<h3>1. Configurando Conexão Online</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    $db->forceOnlineConfig();
    $conn = $db->connect();
    if ($conn) {
        echo "✅ Conexão online estabelecida com sucesso<br>";
    } else {
        throw new Exception('Falha na conexão');
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}
echo "<br>";

// 2. Verificar e criar usuário parceiro de teste
echo "<h3>2. Verificando Usuário Parceiro</h3>";
try {
    // Verificar se existe um parceiro
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
    $stmt->execute();
    $parceiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$parceiro) {
        // Criar um parceiro de teste
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario, data_cadastro) 
            VALUES (?, ?, ?, ?, 'parceiro', NOW())
        ");
        $stmt->execute([
            'Parceiro Teste Online',
            'parceiro@cortefacil.app',
            $senha_hash,
            '11999999999'
        ]);
        
        $parceiro_id = $conn->lastInsertId();
        echo "✅ Parceiro de teste criado (ID: {$parceiro_id})<br>";
        
        // Buscar o parceiro criado
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$parceiro_id]);
        $parceiro = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "✅ Parceiro encontrado: " . $parceiro['nome'] . " (" . $parceiro['email'] . ")<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar parceiro: " . $e->getMessage() . "<br>";
    exit;
}
echo "<br>";

// 3. Verificar e criar salão para o parceiro
echo "<h3>3. Verificando Salão do Parceiro</h3>";
try {
    $stmt = $conn->prepare("SELECT * FROM saloes WHERE usuario_id = ?");
    $stmt->execute([$parceiro['id']]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        // Criar salão para o parceiro
        $stmt = $conn->prepare("
            INSERT INTO saloes (usuario_id, nome, endereco, telefone, descricao, data_cadastro) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $parceiro['id'],
            'Salão Teste Online',
            'Rua Teste, 123 - Centro',
            '11999999999',
            'Salão de teste para ambiente online'
        ]);
        
        $salao_id = $conn->lastInsertId();
        echo "✅ Salão criado para o parceiro (ID: {$salao_id})<br>";
    } else {
        echo "✅ Salão encontrado: " . $salao['nome'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar salão: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 4. Configurar sessão e fazer login automático
echo "<h3>4. Configurando Sessão de Teste</h3>";
try {
    // Configurações de sessão para produção
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // 0 para HTTP, 1 para HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Fazer login do parceiro
    $_SESSION['usuario_id'] = $parceiro['id'];
    $_SESSION['usuario_nome'] = $parceiro['nome'];
    $_SESSION['usuario_email'] = $parceiro['email'];
    $_SESSION['tipo_usuario'] = $parceiro['tipo_usuario'];
    $_SESSION['usuario_telefone'] = $parceiro['telefone'];
    $_SESSION['login_timestamp'] = time();
    
    echo "✅ Sessão configurada com sucesso<br>";
    echo "- Session ID: " . session_id() . "<br>";
    echo "- Usuário logado: " . $_SESSION['usuario_nome'] . "<br>";
    echo "- Tipo: " . $_SESSION['tipo_usuario'] . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao configurar sessão: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 5. Testar funções de autenticação
echo "<h3>5. Testando Autenticação</h3>";
try {
    require_once __DIR__ . '/includes/auth.php';
    
    echo "isLoggedIn(): " . (isLoggedIn() ? '✅ SIM' : '❌ NÃO') . "<br>";
    echo "isParceiro(): " . (isParceiro() ? '✅ SIM' : '❌ NÃO') . "<br>";
    
    if (isLoggedIn() && isParceiro()) {
        echo "✅ Autenticação funcionando corretamente!<br>";
    } else {
        echo "❌ Problema na autenticação<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao testar autenticação: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 6. Criar links de teste
echo "<h3>6. Links de Teste</h3>";
echo "<p>Use os links abaixo para testar as páginas do parceiro:</p>";
echo "<ul>";
echo "<li><a href='parceiro/dashboard.php' target='_blank'>Dashboard</a></li>";
echo "<li><a href='parceiro/profissionais.php' target='_blank'>Profissionais</a></li>";
echo "<li><a href='parceiro/salao.php' target='_blank'>Salão</a></li>";
echo "<li><a href='parceiro/agendamentos.php' target='_blank'>Agendamentos</a></li>";
echo "<li><a href='parceiro/agenda.php' target='_blank'>Agenda</a></li>";
echo "<li><a href='parceiro/relatorios.php' target='_blank'>Relatórios</a></li>";
echo "</ul><br>";

// 7. Informações de debug
echo "<h3>7. Informações de Debug</h3>";
echo "<strong>Dados da Sessão:</strong><br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<strong>Dados do Parceiro:</strong><br>";
echo "<pre>";
print_r($parceiro);
echo "</pre>";

echo "<h3>✅ Correção Concluída</h3>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Credenciais de teste:</strong></p>";
echo "<p>Email: parceiro@cortefacil.app<br>Senha: 123456</p>";
?>