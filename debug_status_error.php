<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>Debug do Erro da Coluna Status</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Forçar ambiente online
$_ENV['ENVIRONMENT'] = 'online';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

try {
    // Conectar ao banco online
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<p class='success'>✅ Conectado ao banco online</p>";
    
    // Testar consulta direta
    echo "<h3>1. Teste de consulta direta</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos WHERE status != 'cancelado'");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✅ Consulta direta funcionou! Count: $count</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro na consulta direta: " . $e->getMessage() . "</p>";
    }
    
    // Testar com prepared statement
    echo "<h3>2. Teste com prepared statement</h3>";
    try {
        $stmt = $conn->prepare("SELECT hora FROM agendamentos WHERE id_profissional = ? AND data = ? AND status != 'cancelado' ORDER BY hora");
        $stmt->execute([1, '2025-08-23']);
        $horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='success'>✅ Prepared statement funcionou! Horários: " . count($horarios) . "</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no prepared statement: " . $e->getMessage() . "</p>";
    }
    
    // Testar classe Agendamento
    echo "<h3>3. Teste da classe Agendamento</h3>";
    try {
        $agendamento = new Agendamento($conn);
        echo "<p class='success'>✅ Classe Agendamento instanciada</p>";
        
        // Usar reflexão para verificar a propriedade table
        $reflection = new ReflectionClass($agendamento);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableName = $tableProperty->getValue($agendamento);
        echo "<p class='info'>📊 Nome da tabela na classe: '$tableName'</p>";
        
        // Testar método listarHorariosOcupados com debug
        echo "<h4>3.1. Testando listarHorariosOcupados</h4>";
        
        // Capturar a query que será executada
        $id_profissional = 1;
        $data = '2025-08-23';
        
        $sql = "SELECT hora FROM {$tableName} 
                WHERE id_profissional = :id_profissional 
                AND data = :data 
                AND status != 'cancelado' 
                ORDER BY hora";
        
        echo "<p class='info'>🔍 SQL que será executado:</p>";
        echo "<pre style='background:#f8f9fa;padding:10px;border:1px solid #ddd;'>" . htmlspecialchars($sql) . "</pre>";
        echo "<p class='info'>📋 Parâmetros: id_profissional=$id_profissional, data=$data</p>";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':data', $data);
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='success'>✅ Query manual funcionou! Horários: " . count($horarios) . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro na query manual: " . $e->getMessage() . "</p>";
        }
        
        // Agora testar o método da classe
        try {
            $horarios_classe = $agendamento->listarHorariosOcupados($id_profissional, $data);
            echo "<p class='success'>✅ Método da classe funcionou! Horários: " . count($horarios_classe) . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro no método da classe: " . $e->getMessage() . "</p>";
            
            // Verificar se é problema de conexão
            $connProperty = $reflection->getProperty('conn');
            $connProperty->setAccessible(true);
            $classConn = $connProperty->getValue($agendamento);
            
            if ($classConn === $conn) {
                echo "<p class='info'>ℹ️ Conexão da classe é a mesma que a externa</p>";
            } else {
                echo "<p class='error'>⚠️ Conexão da classe é diferente da externa!</p>";
                
                // Testar a conexão da classe
                try {
                    $stmt_class = $classConn->query("SELECT COUNT(*) FROM agendamentos WHERE status != 'cancelado'");
                    $count_class = $stmt_class->fetchColumn();
                    echo "<p class='info'>📊 Conexão da classe funciona. Count: $count_class</p>";
                } catch (Exception $e2) {
                    echo "<p class='error'>❌ Conexão da classe com problema: " . $e2->getMessage() . "</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro na classe Agendamento: " . $e->getMessage() . "</p>";
    }
    
    // Verificar informações do banco
    echo "<h3>4. Informações do banco</h3>";
    try {
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p class='info'>🗄️ Banco atual: {$db_info['db_name']}</p>";
        
        $stmt = $conn->query("SHOW TABLES LIKE 'agendamentos'");
        $table_exists = $stmt->fetch();
        if ($table_exists) {
            echo "<p class='success'>✅ Tabela 'agendamentos' existe</p>";
        } else {
            echo "<p class='error'>❌ Tabela 'agendamentos' NÃO existe</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao verificar banco: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";
?>