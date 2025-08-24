<?php
/**
 * Teste de conexão com MySQL
 */

echo "<h2>🔌 Teste de Conexão com MySQL</h2>";
echo "<hr>";

// Teste 1: Conexão direta com PDO
echo "<h3>📡 Teste 1: Conexão Direta PDO</h3>";

try {
    $host = 'localhost';
    $dbname = 'cortefacil';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    
    echo "<p><strong>DSN:</strong> {$dsn}</p>";
    echo "<p><strong>Usuário:</strong> {$username}</p>";
    echo "<p><strong>Senha:</strong> " . (empty($password) ? '(vazia)' : '(definida)') . "</p>";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<p>✅ <strong>Conexão PDO bem-sucedida!</strong></p>";
    
    // Testar uma consulta simples
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    
    echo "<p><strong>Versão do MySQL:</strong> {$result['version']}</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>Erro na conexão PDO:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código do erro:</strong> " . $e->getCode() . "</p>";
}

echo "<hr>";

// Teste 2: Usando a classe Database do projeto
echo "<h3>🏗️ Teste 2: Classe Database do Projeto</h3>";

try {
    require_once 'config/database.php';
    
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<p>✅ <strong>Conexão via classe Database bem-sucedida!</strong></p>";
        
        // Testar consulta
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        
        echo "<p><strong>Total de usuários:</strong> {$result['total']}</p>";
        
        // Testar tabelas importantes
        $tabelas = ['usuarios', 'saloes', 'profissionais', 'agendamentos', 'bloqueios_temporarios'];
        
        echo "<h4>📋 Verificação de Tabelas:</h4>";
        echo "<ul>";
        
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM {$tabela}");
                $result = $stmt->fetch();
                echo "<li>✅ <strong>{$tabela}:</strong> {$result['total']} registros</li>";
            } catch (Exception $e) {
                echo "<li>❌ <strong>{$tabela}:</strong> Erro - " . $e->getMessage() . "</li>";
            }
        }
        
        echo "</ul>";
        
    } else {
        echo "<p>❌ <strong>Falha na conexão via classe Database</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro na classe Database:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Teste 3: Verificar se MySQL está rodando
echo "<h3>🔍 Teste 3: Status do MySQL</h3>";

$mysql_running = false;

// Tentar conectar na porta padrão do MySQL
$connection = @fsockopen('localhost', 3306, $errno, $errstr, 5);
if ($connection) {
    echo "<p>✅ <strong>MySQL está rodando na porta 3306</strong></p>";
    fclose($connection);
    $mysql_running = true;
} else {
    echo "<p>❌ <strong>MySQL não está respondendo na porta 3306</strong></p>";
    echo "<p><strong>Erro:</strong> {$errstr} (Código: {$errno})</p>";
}

echo "<hr>";

// Instruções para resolver
echo "<h3>🛠️ Como Resolver</h3>";

if (!$mysql_running) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ MySQL não está rodando</h4>";
    echo "<p><strong>Soluções:</strong></p>";
    echo "<ol>";
    echo "<li>Abra o <strong>XAMPP Control Panel</strong></li>";
    echo "<li>Clique em <strong>Start</strong> ao lado de <strong>MySQL</strong></li>";
    echo "<li>Aguarde até aparecer <strong>Running</strong> em verde</li>";
    echo "<li>Recarregue esta página para testar novamente</li>";
    echo "</ol>";
    echo "<p><strong>Alternativa:</strong> Execute o comando <code>net start mysql</code> como administrador</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ MySQL está funcionando</h4>";
    echo "<p>O problema pode estar em:</p>";
    echo "<ul>";
    echo "<li>Configurações de conexão no arquivo <code>config/database.php</code></li>";
    echo "<li>Permissões do banco de dados</li>";
    echo "<li>Nome do banco de dados incorreto</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='cliente/agendar.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📅 Testar Agendamento</a>";
echo "<a href='teste_api_original.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Teste API</a></p>";

echo "<p><strong>Recarregue esta página após iniciar o MySQL</strong></p>";
?>