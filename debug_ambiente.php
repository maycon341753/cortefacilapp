<?php
/**
 * Debug da detecção de ambiente
 */

require_once 'config/database.php';

echo "<h2>🔍 Debug Detecção de Ambiente</h2>";

// Verificar variáveis de servidor
echo "<h3>1. Variáveis de Servidor</h3>";
echo "<p class='info'>SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "</p>";
echo "<p class='info'>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "</p>";
echo "<p class='info'>DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "</p>";
echo "<p class='info'>SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'não definido') . "</p>";
echo "<p class='info'>SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'não definido') . "</p>";
echo "<p class='info'>PHP SAPI: " . php_sapi_name() . "</p>";

// Verificar arquivo .env.online
echo "<h3>2. Verificação de Arquivos</h3>";
$env_online_path = __DIR__ . '/.env.online';
echo "<p class='info'>Caminho .env.online: {$env_online_path}</p>";
if (file_exists($env_online_path)) {
    echo "<p class='error'>❌ Arquivo .env.online EXISTE</p>";
} else {
    echo "<p class='success'>✅ Arquivo .env.online NÃO existe</p>";
}

// Simular a lógica de detecção
echo "<h3>3. Simulação da Lógica de Detecção</h3>";

// Passo 1: Verificar .env.online
if (file_exists($env_online_path)) {
    echo "<p class='error'>❌ FORÇADO ONLINE: Arquivo .env.online existe</p>";
    $ambiente = 'online';
} else {
    echo "<p class='success'>✅ Passo 1 OK: Arquivo .env.online não existe</p>";
    
    // Passo 2: Verificar SERVER_NAME
    $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    echo "<p class='info'>Server name detectado: '{$serverName}'</p>";
    
    if (strpos($serverName, 'cortefacil.app') !== false) {
        echo "<p class='error'>❌ FORÇADO ONLINE: Domínio cortefacil.app detectado</p>";
        $ambiente = 'online';
    } else {
        echo "<p class='success'>✅ Passo 2 OK: Não é domínio cortefacil.app</p>";
        
        // Passo 3: Verificar se não é localhost mas tem domínio
        if (!empty($serverName) && !in_array($serverName, ['localhost', '127.0.0.1', '::1'])) {
            echo "<p class='error'>❌ FORÇADO ONLINE: Domínio real detectado (não localhost)</p>";
            $ambiente = 'online';
        } else {
            echo "<p class='success'>✅ Passo 3 OK: É localhost ou sem domínio</p>";
            
            // Passo 4: Verificar CLI
            if (php_sapi_name() === 'cli') {
                echo "<p class='info'>🖥️ Executando via CLI</p>";
                if (file_exists($env_online_path)) {
                    echo "<p class='error'>❌ FORÇADO ONLINE: CLI com arquivo .env.online</p>";
                    $ambiente = 'online';
                } else {
                    echo "<p class='success'>✅ CLI SEM .env.online = LOCAL</p>";
                    $ambiente = 'local';
                }
            } else {
                echo "<p class='info'>🌐 Executando via web</p>";
                
                // Passo 5: Verificar hosts locais
                $localHosts = ['localhost', '127.0.0.1', '::1'];
                if (in_array($serverName, $localHosts)) {
                    echo "<p class='success'>✅ Host local detectado = LOCAL</p>";
                    $ambiente = 'local';
                } else {
                    // Passo 6: Verificar XAMPP/WAMP
                    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
                    if (strpos($docRoot, 'xampp') !== false || strpos($docRoot, 'wamp') !== false) {
                        echo "<p class='success'>✅ XAMPP/WAMP detectado = LOCAL</p>";
                        $ambiente = 'local';
                    } else {
                        // Passo 7: Verificar servidor PHP built-in
                        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
                        if (strpos($serverSoftware, 'PHP') !== false) {
                            echo "<p class='success'>✅ Servidor PHP built-in = LOCAL</p>";
                            $ambiente = 'local';
                        } else {
                            // Passo 8: Verificar porta de desenvolvimento
                            $port = $_SERVER['SERVER_PORT'] ?? 80;
                            if (in_array($port, [3000, 8000, 8080, 8888, 9000])) {
                                echo "<p class='success'>✅ Porta de desenvolvimento ({$port}) = LOCAL</p>";
                                $ambiente = 'local';
                            } else {
                                echo "<p class='error'>❌ Nenhuma condição local atendida = ONLINE</p>";
                                $ambiente = 'online';
                            }
                        }
                    }
                }
            }
        }
    }
}

echo "<h3>4. Resultado Final</h3>";
if ($ambiente === 'local') {
    echo "<p class='success'>✅ AMBIENTE DETECTADO: LOCAL</p>";
} else {
    echo "<p class='error'>❌ AMBIENTE DETECTADO: ONLINE</p>";
}

// Testar a classe Database
echo "<h3>5. Teste da Classe Database</h3>";
$database = Database::getInstance();
$conn = $database->connect();

$stmt = $conn->query("SELECT DATABASE() as current_db");
$db_info = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p class='info'>🗄️ Banco conectado: {$db_info['current_db']}</p>";

// Verificar se é o banco local ou online
if ($db_info['current_db'] === 'u690889028_cortefacil') {
    // Verificar se é local ou online testando uma query específica
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM agendamentos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p class='info'>📊 Registros na tabela agendamentos: {$result['total']}</p>";
        
        // Se conseguiu conectar no banco u690889028_cortefacil, pode ser local ou online
        // Vamos verificar o host da conexão
        $stmt = $conn->query("SELECT @@hostname as host");
        $host_info = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p class='info'>🖥️ Host do banco: {$host_info['host']}</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao testar banco: {$e->getMessage()}</p>";
    }
}

echo "<hr>";
echo "<p><strong>Debug de ambiente concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>