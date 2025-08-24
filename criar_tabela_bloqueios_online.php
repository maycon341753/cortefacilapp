<?php
/**
 * Script para criar tabela bloqueios_temporarios no banco ONLINE (Hostinger)
 * Força a criação da tabela no ambiente de produção
 */

require_once 'config/database.php';

echo "<h2>Criando Tabela bloqueios_temporarios no Banco ONLINE</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Forçar ambiente online criando arquivo .env.online
    $env_file = __DIR__ . '/.env.online';
    if (!file_exists($env_file)) {
        file_put_contents($env_file, 'FORCE_ONLINE=true');
        echo "<p class='info'>📁 Arquivo .env.online criado para forçar ambiente online</p>";
    }
    
    // Conectar ao banco (agora será online)
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados online');
    }
    
    echo "<p class='success'>✅ Conexão com banco ONLINE estabelecida</p>";
    
    // Verificar se já existe
    $stmt = $conn->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='info'>⚠️ Tabela 'bloqueios_temporarios' já existe no banco online</p>";
        
        // Verificar estrutura
        $stmt = $conn->query("DESCRIBE bloqueios_temporarios");
        $campos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p class='info'>📋 Campos existentes: " . implode(', ', $campos) . "</p>";
        
        $campos_esperados = ['id', 'id_profissional', 'data', 'hora', 'session_id', 'ip_cliente', 'created_at', 'expires_at'];
        $campos_faltando = array_diff($campos_esperados, $campos);
        
        if (empty($campos_faltando)) {
            echo "<p class='success'>✅ Estrutura da tabela online está correta</p>";
        } else {
            echo "<p class='error'>❌ Campos faltando: " . implode(', ', $campos_faltando) . "</p>";
        }
        
    } else {
        echo "<p class='info'>🔧 Criando tabela 'bloqueios_temporarios' no banco online...</p>";
        
        // SQL para criar a tabela
        $sql_create_table = "
            CREATE TABLE IF NOT EXISTS bloqueios_temporarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_profissional INT NOT NULL,
                data DATE NOT NULL,
                hora TIME NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                ip_cliente VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                INDEX idx_profissional_data (id_profissional, data),
                INDEX idx_expires (expires_at),
                UNIQUE KEY unique_horario (id_profissional, data, hora)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql_create_table);
        echo "<p class='success'>✅ Tabela 'bloqueios_temporarios' criada no banco ONLINE!</p>";
        
        // Verificar estrutura criada
        echo "<h3>Estrutura da Tabela Online:</h3>";
        $result = $conn->query("DESCRIBE bloqueios_temporarios");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Testar inserção e remoção
    echo "<h3>Teste de Funcionalidade Online</h3>";
    
    $teste_session = 'teste_online_' . time();
    $sql_insert = "INSERT INTO bloqueios_temporarios 
                   (id_profissional, data, hora, session_id, ip_cliente, expires_at) 
                   VALUES (1, CURDATE(), '10:00:00', ?, '127.0.0.1', DATE_ADD(NOW(), INTERVAL 1 MINUTE))";
    
    $stmt = $conn->prepare($sql_insert);
    if ($stmt->execute([$teste_session])) {
        echo "<p class='success'>✅ Teste de inserção no banco online: OK</p>";
        
        // Remover teste
        $sql_delete = "DELETE FROM bloqueios_temporarios WHERE session_id = ?";
        $stmt_del = $conn->prepare($sql_delete);
        if ($stmt_del->execute([$teste_session])) {
            echo "<p class='success'>✅ Teste de remoção no banco online: OK</p>";
        }
    } else {
        echo "<p class='error'>❌ Erro no teste de inserção</p>";
    }
    
    // Verificar contagem atual
    $stmt = $conn->query("SELECT COUNT(*) FROM bloqueios_temporarios");
    $total = $stmt->fetchColumn();
    
    echo "<h3>Status Final do Banco Online</h3>";
    echo "<p class='info'>📊 Total de bloqueios na tabela online: $total</p>";
    
    echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;margin:20px 0;'>";
    echo "<h4>✅ Tabela bloqueios_temporarios Configurada no Banco Online!</h4>";
    echo "<p>A tabela está pronta para uso no ambiente de produção (Hostinger).</p>";
    echo "<p><strong>Ambiente:</strong> Online (srv486.hstgr.io)</p>";
    echo "<p><strong>Banco:</strong> u690889028_cortefacil</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

echo "<br><a href='teste_bloqueios_funcionando.php' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔗 Testar Sistema Local</a>";
echo "<a href='cliente/agendar.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🌐 Testar Interface</a>";
?>