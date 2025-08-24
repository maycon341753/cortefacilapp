<?php
/**
 * Teste específico para Dashboard do Parceiro
 * Identifica problemas de acesso e autenticação
 */

// Configurações de erro
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Teste Dashboard Parceiro</h1>";
echo "<hr>";

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. 📁 Verificação de Arquivos</h2>";

$arquivos_criticos = [
    'includes/auth.php',
    'includes/functions.php',
    'models/salao.php',
    'models/profissional.php',
    'models/agendamento.php'
];

foreach ($arquivos_criticos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p>✅ $arquivo existe</p>";
    } else {
        echo "<p>❌ $arquivo NÃO existe</p>";
    }
}

echo "<hr><h2>2. 🔧 Carregando Arquivos</h2>";

try {
    require_once 'includes/auth.php';
    echo "<p>✅ auth.php carregado</p>";
    
    require_once 'includes/functions.php';
    echo "<p>✅ functions.php carregado</p>";
    
    require_once 'models/salao.php';
    echo "<p>✅ salao.php carregado</p>";
    
    require_once 'models/profissional.php';
    echo "<p>✅ profissional.php carregado</p>";
    
    require_once 'models/agendamento.php';
    echo "<p>✅ agendamento.php carregado</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar arquivos: " . $e->getMessage() . "</p>";
    exit;
}

echo "<hr><h2>3. 🔐 Verificação de Funções</h2>";

if (function_exists('requireParceiro')) {
    echo "<p>✅ Função requireParceiro existe</p>";
} else {
    echo "<p>❌ Função requireParceiro NÃO existe</p>";
}

if (function_exists('getLoggedUser')) {
    echo "<p>✅ Função getLoggedUser existe</p>";
} else {
    echo "<p>❌ Função getLoggedUser NÃO existe</p>";
}

if (function_exists('isLoggedIn')) {
    echo "<p>✅ Função isLoggedIn existe</p>";
} else {
    echo "<p>❌ Função isLoggedIn NÃO existe</p>";
}

echo "<hr><h2>4. 👤 Status do Usuário</h2>";

if (function_exists('isLoggedIn')) {
    if (isLoggedIn()) {
        echo "<p>✅ Usuário está logado</p>";
        
        if (function_exists('getLoggedUser')) {
            $usuario = getLoggedUser();
            if ($usuario) {
                echo "<p><strong>Nome:</strong> " . htmlspecialchars($usuario['nome']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($usuario['email']) . "</p>";
                echo "<p><strong>Tipo:</strong> " . htmlspecialchars($usuario['tipo_usuario']) . "</p>";
                
                if ($usuario['tipo_usuario'] === 'parceiro') {
                    echo "<p>✅ É parceiro - pode acessar dashboard</p>";
                } else {
                    echo "<p>❌ NÃO é parceiro - tipo: " . $usuario['tipo_usuario'] . "</p>";
                }
            } else {
                echo "<p>❌ Erro ao obter dados do usuário</p>";
            }
        }
    } else {
        echo "<p>❌ Usuário NÃO está logado</p>";
        echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Fazer Login</a></p>";
    }
} else {
    echo "<p>❌ Função isLoggedIn não disponível</p>";
}

echo "<hr><h2>5. 🏢 Teste de Classes</h2>";

try {
    if (class_exists('Salao')) {
        echo "<p>✅ Classe Salao existe</p>";
        $salao = new Salao();
        echo "<p>✅ Instância de Salao criada</p>";
    } else {
        echo "<p>❌ Classe Salao NÃO existe</p>";
    }
    
    if (class_exists('Profissional')) {
        echo "<p>✅ Classe Profissional existe</p>";
        $profissional = new Profissional();
        echo "<p>✅ Instância de Profissional criada</p>";
    } else {
        echo "<p>❌ Classe Profissional NÃO existe</p>";
    }
    
    if (class_exists('Agendamento')) {
        echo "<p>✅ Classe Agendamento existe</p>";
        $agendamento = new Agendamento();
        echo "<p>✅ Instância de Agendamento criada</p>";
    } else {
        echo "<p>❌ Classe Agendamento NÃO existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao testar classes: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>6. 🔗 Links de Teste</h2>";
echo "<p><a href='login.php'>🔑 Login</a></p>";
echo "<p><a href='parceiro/dashboard.php'>📊 Dashboard Parceiro</a></p>";
echo "<p><a href='index.php'>🏠 Página Inicial</a></p>";

echo "<hr><p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>