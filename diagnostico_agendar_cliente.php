<?php
/**
 * Diagnóstico específico para a página cliente/agendar.php
 * Identifica por que está redirecionando para login
 */

// Configurar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_agendar.log');

echo "<h1>🔍 Diagnóstico da Página de Agendamento</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";

// 1. Verificar sessão
echo "<h2>1. 📋 Verificação de Sessão</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p class='info'>✓ Sessão iniciada</p>";
} else {
    echo "<p class='success'>✓ Sessão já estava ativa</p>";
}

echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Dados da sessão:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// 2. Verificar autenticação
echo "<h2>2. 🔐 Verificação de Autenticação</h2>";

// Incluir arquivo de autenticação
try {
    require_once __DIR__ . '/includes/auth.php';
    echo "<p class='success'>✓ Arquivo auth.php carregado</p>";
    
    // Testar função isLoggedIn
    if (function_exists('isLoggedIn')) {
        $loggedIn = isLoggedIn();
        echo "<p><strong>isLoggedIn():</strong> " . ($loggedIn ? 'true' : 'false') . "</p>";
        
        if ($loggedIn) {
            echo "<p class='success'>✓ Usuário está logado</p>";
            
            // Verificar tipo de usuário
            if (function_exists('isCliente')) {
                $isCliente = isCliente();
                echo "<p><strong>isCliente():</strong> " . ($isCliente ? 'true' : 'false') . "</p>";
                
                if ($isCliente) {
                    echo "<p class='success'>✓ Usuário é cliente</p>";
                } else {
                    echo "<p class='error'>❌ Usuário NÃO é cliente</p>";
                    echo "<p class='info'>Tipo de usuário: " . ($_SESSION['tipo_usuario'] ?? 'não definido') . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Função isCliente() não encontrada</p>";
            }
            
            // Mostrar dados do usuário
            if (function_exists('getLoggedUser')) {
                $user = getLoggedUser();
                echo "<p><strong>Dados do usuário:</strong></p>";
                echo "<pre>" . print_r($user, true) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ Usuário NÃO está logado</p>";
        }
    } else {
        echo "<p class='error'>❌ Função isLoggedIn() não encontrada</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
}

// 3. Testar requireCliente diretamente
echo "<h2>3. 🎯 Teste da Função requireCliente</h2>";
try {
    if (function_exists('requireCliente')) {
        echo "<p class='info'>Testando requireCliente() (pode redirecionar)...</p>";
        
        // Capturar headers antes de chamar requireCliente
        ob_start();
        
        // Tentar chamar requireCliente sem redirecionar
        // Vamos simular o que ela faz
        if (!isLoggedIn()) {
            echo "<p class='error'>❌ requireCliente falharia: usuário não logado</p>";
        } elseif (!isCliente()) {
            echo "<p class='error'>❌ requireCliente falharia: usuário não é cliente</p>";
        } else {
            echo "<p class='success'>✓ requireCliente passaria</p>";
        }
        
        ob_end_flush();
    } else {
        echo "<p class='error'>❌ Função requireCliente() não encontrada</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao testar requireCliente: " . $e->getMessage() . "</p>";
}

// 4. Verificar banco de dados
echo "<h2>4. 🗄️ Verificação do Banco de Dados</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    echo "<p class='success'>✓ Arquivo database.php carregado</p>";
    
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<p class='success'>✓ Conexão com banco estabelecida</p>";
        
        // Verificar qual banco está sendo usado
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Banco atual:</strong> " . $result['db_name'] . "</p>";
        
        // Verificar tabelas essenciais
        $tabelas = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
        foreach ($tabelas as $tabela) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
            $stmt->execute([$tabela]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                echo "<p class='success'>✓ Tabela '$tabela' existe</p>";
            } else {
                echo "<p class='error'>❌ Tabela '$tabela' não existe</p>";
            }
        }
        
    } else {
        echo "<p class='error'>❌ Falha na conexão com banco</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

// 5. Simular acesso à página de agendamento
echo "<h2>5. 🎯 Simulação da Página de Agendamento</h2>";

echo "<p><strong>Simulando o que acontece em cliente/agendar.php:</strong></p>";

// Verificar se todos os arquivos necessários existem
$arquivos_necessarios = [
    'includes/auth.php',
    'includes/functions.php', 
    'models/agendamento.php',
    'models/salao.php',
    'models/profissional.php'
];

foreach ($arquivos_necessarios as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo "<p class='success'>✓ $arquivo existe</p>";
    } else {
        echo "<p class='error'>❌ $arquivo não existe</p>";
    }
}

// 6. Teste de login manual
echo "<h2>6. 🔑 Teste de Login Manual</h2>";
echo "<p><strong>Para testar, vamos criar uma sessão de cliente:</strong></p>";

// Criar sessão de teste
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Cliente Teste';
$_SESSION['usuario_email'] = 'cliente@teste.com';
$_SESSION['tipo_usuario'] = 'cliente';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<p class='info'>Sessão de teste criada:</p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Testar novamente as funções de autenticação
if (function_exists('isLoggedIn') && function_exists('isCliente')) {
    echo "<p><strong>Após criar sessão:</strong></p>";
    echo "<p>isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "</p>";
    echo "<p>isCliente(): " . (isCliente() ? 'true' : 'false') . "</p>";
    
    if (isLoggedIn() && isCliente()) {
        echo "<p class='success'>✅ Agora o usuário deveria conseguir acessar a página de agendamento!</p>";
    } else {
        echo "<p class='error'>❌ Ainda há problemas com a autenticação</p>";
    }
}

// 7. Links para teste
echo "<h2>7. 🔗 Links para Teste</h2>";
echo "<p><a href='cliente/agendar.php' target='_blank'>🎯 Testar página de agendamento</a></p>";
echo "<p><a href='login.php' target='_blank'>🔑 Página de login</a></p>";
echo "<p><a href='cadastro.php' target='_blank'>📝 Página de cadastro</a></p>";

echo "<h2>8. 📋 Resumo do Diagnóstico</h2>";
echo "<div style='background:#f0f0f0;padding:15px;border-radius:5px;'>";
echo "<p><strong>Problemas identificados:</strong></p>";
echo "<ul>";
echo "<li>Verificar se o usuário está realmente logado</li>";
echo "<li>Verificar se o tipo de usuário está correto (cliente)</li>";
echo "<li>Verificar se a sessão está sendo mantida entre páginas</li>";
echo "<li>Verificar se os arquivos de modelo existem</li>";
echo "</ul>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>1. Fazer login como cliente</li>";
echo "<li>2. Verificar se a sessão persiste</li>";
echo "<li>3. Testar acesso à página de agendamento</li>";
echo "</ul>";
echo "</div>";

?>