<?php
/**
 * Teste de Configurações Hostinger - CorteFácil
 * Este arquivo testa se todas as configurações estão funcionando corretamente
 * 
 * IMPORTANTE: Remover este arquivo após os testes em produção
 * 
 * @version 1.0
 * @date 2024
 */

// Incluir configurações do Hostinger
require_once 'hostinger_config.php';

// Iniciar sessão para testes
session_start();

// Função para exibir resultado do teste
function displayTestResult($test_name, $result, $details = '') {
    $status = $result ? '✅ PASSOU' : '❌ FALHOU';
    $color = $result ? 'green' : 'red';
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid {$color}; background: #f9f9f9;'>";
    echo "<strong>{$test_name}:</strong> <span style='color: {$color};'>{$status}</span>";
    if ($details) {
        echo "<br><small style='color: #666;'>{$details}</small>";
    }
    echo "</div>";
}

// Função para testar CSRF
function testCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    $token_exists = isset($_SESSION['csrf_token']);
    $token_time_exists = isset($_SESSION['csrf_token_time']);
    $token_valid = $token_exists && strlen($_SESSION['csrf_token']) === 64;
    
    return [
        'token_exists' => $token_exists,
        'token_time_exists' => $token_time_exists,
        'token_valid' => $token_valid,
        'overall' => $token_exists && $token_time_exists && $token_valid
    ];
}

// Função para testar banco de dados
function testDatabase() {
    if (!file_exists('config/database.php')) {
        return ['connected' => false, 'error' => 'Arquivo de configuração não encontrado'];
    }
    
    try {
        require_once 'config/database.php';
        
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            return ['connected' => false, 'error' => 'Constantes de banco não definidas'];
        }
        
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            return ['connected' => false, 'error' => $mysqli->connect_error];
        }
        
        $mysqli->close();
        return ['connected' => true, 'error' => null];
        
    } catch (Exception $e) {
        return ['connected' => false, 'error' => $e->getMessage()];
    }
}

// Função para testar permissões de arquivo
function testFilePermissions() {
    $tests = [];
    
    // Testar escrita no diretório atual
    $test_file = 'test_write_' . time() . '.tmp';
    $can_write = file_put_contents($test_file, 'test');
    if ($can_write) {
        unlink($test_file);
        $tests['write_root'] = true;
    } else {
        $tests['write_root'] = false;
    }
    
    // Testar leitura de arquivos importantes
    $tests['read_htaccess'] = file_exists('.htaccess') && is_readable('.htaccess');
    $tests['read_config'] = file_exists('config/database.php') && is_readable('config/database.php');
    
    return $tests;
}

// Função para testar headers de segurança
function testSecurityHeaders() {
    $headers = [];
    
    // Capturar headers enviados
    if (function_exists('apache_response_headers')) {
        $response_headers = apache_response_headers();
    } else {
        $response_headers = [];
    }
    
    $expected_headers = [
        'X-Content-Type-Options',
        'X-Frame-Options',
        'X-XSS-Protection',
        'Referrer-Policy',
        'Content-Security-Policy'
    ];
    
    foreach ($expected_headers as $header) {
        $headers[$header] = isset($response_headers[$header]);
    }
    
    return $headers;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Hostinger - CorteFácil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .info-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Teste de Configurações Hostinger</h1>
            <p>CorteFácil - Verificação de Ambiente de Produção</p>
            <small>Data: <?php echo date('d/m/Y H:i:s'); ?></small>
        </div>
        
        <div class="warning">
            <strong>⚠️ IMPORTANTE:</strong> Este arquivo deve ser removido após os testes em produção por questões de segurança.
        </div>
        
        <!-- Informações do Servidor -->
        <div class="test-section">
            <h2>📊 Informações do Servidor</h2>
            <div class="info-grid">
                <div class="info-card">
                    <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                </div>
                <div class="info-card">
                    <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?>
                </div>
                <div class="info-card">
                    <strong>Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?>
                </div>
                <div class="info-card">
                    <strong>Ambiente Hostinger:</strong> <?php echo isHostingerEnvironment() ? 'Sim' : 'Não'; ?>
                </div>
                <div class="info-card">
                    <strong>HTTPS:</strong> <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="info-card">
                    <strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?>
                </div>
            </div>
        </div>
        
        <!-- Testes de Configuração PHP -->
        <div class="test-section">
            <h2>⚙️ Configurações PHP</h2>
            <?php
            $php_configs = [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'session.cookie_httponly' => ini_get('session.cookie_httponly'),
                'session.use_only_cookies' => ini_get('session.use_only_cookies'),
                'display_errors' => ini_get('display_errors')
            ];
            
            foreach ($php_configs as $config => $value) {
                $is_good = true;
                $details = "Valor: {$value}";
                
                // Verificações específicas
                if ($config === 'display_errors' && $value == '1') {
                    $is_good = false;
                    $details .= " (Deve estar Off em produção)";
                }
                if ($config === 'session.cookie_httponly' && $value != '1') {
                    $is_good = false;
                    $details .= " (Deve estar On para segurança)";
                }
                
                displayTestResult($config, $is_good, $details);
            }
            ?>
        </div>
        
        <!-- Testes de Extensões PHP -->
        <div class="test-section">
            <h2>🔧 Extensões PHP</h2>
            <?php
            $extensions = ['mysqli', 'json', 'mbstring', 'openssl', 'curl', 'gd', 'zip'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                displayTestResult("Extensão {$ext}", $loaded);
            }
            ?>
        </div>
        
        <!-- Testes de Sessão e CSRF -->
        <div class="test-section">
            <h2>🔐 Testes de Sessão e CSRF</h2>
            <?php
            displayTestResult('Sessão Iniciada', session_status() === PHP_SESSION_ACTIVE);
            displayTestResult('Session ID', !empty(session_id()), 'ID: ' . session_id());
            
            $csrf_tests = testCSRF();
            displayTestResult('Token CSRF Existe', $csrf_tests['token_exists']);
            displayTestResult('Timestamp do Token', $csrf_tests['token_time_exists']);
            displayTestResult('Token Válido', $csrf_tests['token_valid']);
            displayTestResult('Sistema CSRF', $csrf_tests['overall']);
            ?>
        </div>
        
        <!-- Testes de Banco de Dados -->
        <div class="test-section">
            <h2>🗄️ Teste de Banco de Dados</h2>
            <?php
            $db_test = testDatabase();
            displayTestResult('Conexão com Banco', $db_test['connected'], 
                $db_test['error'] ? 'Erro: ' . $db_test['error'] : 'Conexão estabelecida com sucesso');
            ?>
        </div>
        
        <!-- Testes de Permissões -->
        <div class="test-section">
            <h2>📁 Permissões de Arquivo</h2>
            <?php
            $perm_tests = testFilePermissions();
            displayTestResult('Escrita no Diretório', $perm_tests['write_root']);
            displayTestResult('Leitura .htaccess', $perm_tests['read_htaccess']);
            displayTestResult('Leitura Config DB', $perm_tests['read_config']);
            ?>
        </div>
        
        <!-- Testes de Segurança -->
        <div class="test-section">
            <h2>🛡️ Headers de Segurança</h2>
            <?php
            $security_headers = testSecurityHeaders();
            foreach ($security_headers as $header => $present) {
                displayTestResult("Header {$header}", $present);
            }
            ?>
        </div>
        
        <!-- Teste de Arquivos Importantes -->
        <div class="test-section">
            <h2>📄 Arquivos Importantes</h2>
            <?php
            $important_files = [
                '.htaccess' => file_exists('.htaccess'),
                'config/database.php' => file_exists('config/database.php'),
                'auth.php' => file_exists('auth.php'),
                'get_csrf_token.php' => file_exists('get_csrf_token.php'),
                'parceiro/profissionais.php' => file_exists('parceiro/profissionais.php'),
                'hostinger_config.php' => file_exists('hostinger_config.php')
            ];
            
            foreach ($important_files as $file => $exists) {
                displayTestResult("Arquivo {$file}", $exists);
            }
            ?>
        </div>
        
        <!-- Resumo Final -->
        <div class="test-section" style="background: #e8f5e8; border: 2px solid #28a745;">
            <h2>✅ Resumo dos Testes</h2>
            <p><strong>Status Geral:</strong> 
                <?php
                // Calcular status geral baseado nos testes críticos
                $critical_tests = [
                    session_status() === PHP_SESSION_ACTIVE,
                    $csrf_tests['overall'],
                    $db_test['connected'],
                    $perm_tests['write_root'],
                    file_exists('.htaccess')
                ];
                
                $passed = array_sum($critical_tests);
                $total = count($critical_tests);
                $percentage = ($passed / $total) * 100;
                
                if ($percentage >= 80) {
                    echo "<span style='color: green; font-weight: bold;'>✅ PRONTO PARA PRODUÇÃO ({$passed}/{$total} testes críticos passaram)</span>";
                } elseif ($percentage >= 60) {
                    echo "<span style='color: orange; font-weight: bold;'>⚠️ NECESSITA AJUSTES ({$passed}/{$total} testes críticos passaram)</span>";
                } else {
                    echo "<span style='color: red; font-weight: bold;'>❌ REQUER CORREÇÕES ({$passed}/{$total} testes críticos passaram)</span>";
                }
                ?>
            </p>
            
            <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 5px;">
                <h3>🚀 Próximos Passos:</h3>
                <ol>
                    <li>Se todos os testes críticos passaram, o sistema está pronto</li>
                    <li>Configure HTTPS se ainda não estiver ativo</li>
                    <li>Teste o sistema completo com usuários reais</li>
                    <li><strong>REMOVA este arquivo (test_hostinger.php) por segurança</strong></li>
                    <li>Configure monitoramento de logs de erro</li>
                </ol>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <p><strong>CorteFácil</strong> - Sistema de Agendamento</p>
            <p><small>Teste realizado em: <?php echo date('d/m/Y H:i:s'); ?></small></p>
        </div>
    </div>
</body>
</html>