<?php
/**
 * Teste Completo do Dashboard - Simula exatamente o que o dashboard faz
 */

echo "<h2>🧪 Teste Completo do Dashboard</h2>";

// ===== CONFIGURAÇÕES DE ERRO E DEBUG =====
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

echo "<p>✅ Configurações de erro definidas</p>";

// ===== INÍCIO SEGURO DE SESSÃO =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p>✅ Sessão iniciada</p>";

// Simular usuário parceiro logado
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'parceiro@teste.com';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<p>✅ Sessão de parceiro simulada</p>";

// ===== VERIFICAÇÃO DE SAÚDE DO SISTEMA =====
// Desabilitar health check automático para permitir acesso ao dashboard
define('HEALTH_CHECK_DISABLED', true);

echo "<p>✅ Health check desabilitado</p>";

try {
    if (file_exists('includes/health_check.php')) {
        require_once 'includes/health_check.php';
        echo "<p>✅ health_check.php incluído</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange'>⚠️ Erro no health check: " . $e->getMessage() . "</p>";
}

// ===== VERIFICAÇÃO E INCLUSÃO DE ARQUIVOS CRÍTICOS =====
try {
    echo "<h3>Verificando arquivos críticos:</h3>";
    
    // Verificar se arquivos críticos existem
    $arquivos_criticos = [
        'includes/auth.php',
        'includes/functions.php',
        'models/salao.php',
        'models/profissional.php',
        'models/agendamento.php'
    ];
    
    foreach ($arquivos_criticos as $arquivo) {
        if (!file_exists($arquivo)) {
            throw new Exception("Arquivo crítico não encontrado: $arquivo");
        }
        echo "<p>✅ $arquivo existe</p>";
    }
    
    // Incluir arquivos com tratamento de erro
    require_once 'includes/auth.php';
    echo "<p>✅ auth.php incluído</p>";
    
    require_once 'includes/functions.php';
    echo "<p>✅ functions.php incluído</p>";
    
    require_once 'models/salao.php';
    echo "<p>✅ salao.php incluído</p>";
    
    require_once 'models/profissional.php';
    echo "<p>✅ profissional.php incluído</p>";
    
    require_once 'models/agendamento.php';
    echo "<p>✅ agendamento.php incluído</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ ERRO na inclusão de arquivos: " . $e->getMessage() . "</p>";
    echo "<p>Este é o ponto onde o dashboard falha!</p>";
    exit;
}

// ===== VERIFICAÇÕES DE SEGURANÇA =====
try {
    echo "<h3>Verificações de segurança:</h3>";
    
    // Verificar se funções necessárias existem
    if (!function_exists('requireParceiro')) {
        throw new Exception('Função requireParceiro não encontrada');
    }
    echo "<p>✅ Função requireParceiro existe</p>";
    
    if (!function_exists('getLoggedUser')) {
        throw new Exception('Função getLoggedUser não encontrada');
    }
    echo "<p>✅ Função getLoggedUser existe</p>";
    
    // Verificar se é parceiro (SEM chamar requireParceiro para evitar redirecionamento)
    if (!isLoggedIn()) {
        throw new Exception('Usuário não está logado');
    }
    echo "<p>✅ Usuário está logado</p>";
    
    if (!isParceiro()) {
        throw new Exception('Usuário não é parceiro');
    }
    echo "<p>✅ Usuário é parceiro</p>";
    
    $usuario = getLoggedUser();
    
    if (!$usuario) {
        throw new Exception('Não foi possível obter dados do usuário');
    }
    echo "<p>✅ Dados do usuário obtidos: " . $usuario['nome'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ ERRO de autenticação: " . $e->getMessage() . "</p>";
    echo "<p>Este é o ponto onde o dashboard falha!</p>";
    exit;
}

echo "<h3>🎉 SUCESSO!</h3>";
echo "<p style='color: green'><strong>Todas as verificações passaram! O dashboard deveria funcionar.</strong></p>";
echo "<p>Se chegamos até aqui, o problema não está nas verificações iniciais.</p>";

// Continuar com o resto do dashboard...
echo "<h3>Continuando com o dashboard...</h3>";

try {
    // Verificar conexão com banco de dados
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        echo "<p>✅ database.php incluído</p>";
        
        if (function_exists('getConnection')) {
            $pdo = getConnection();
            if ($pdo) {
                echo "<p>✅ Conexão com banco de dados estabelecida</p>";
            } else {
                echo "<p style='color: red'>❌ Falha na conexão com banco de dados</p>";
            }
        }
    }
    
    // Tentar instanciar modelos
    if (class_exists('Salao')) {
        $salaoModel = new Salao();
        echo "<p>✅ Modelo Salao instanciado</p>";
    }
    
    if (class_exists('Profissional')) {
        $profissionalModel = new Profissional();
        echo "<p>✅ Modelo Profissional instanciado</p>";
    }
    
    if (class_exists('Agendamento')) {
        $agendamentoModel = new Agendamento();
        echo "<p>✅ Modelo Agendamento instanciado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ ERRO nos modelos: " . $e->getMessage() . "</p>";
}

echo "<h3>✅ Teste completo finalizado!</h3>";
?>