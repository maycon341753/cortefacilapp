<?php
/**
 * Debug para verificar se há múltiplas tabelas agendamentos ou problemas de conexão
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>🔍 Debug Múltiplas Tabelas/Conexões</h2>";

// 1. Verificar todas as tabelas que contêm 'agendamento'
echo "<h3>1. Tabelas relacionadas a 'agendamento'</h3>";

$database = Database::getInstance();
$conn = $database->connect();

try {
    $stmt = $conn->query("SHOW TABLES LIKE '%agendamento%'");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='info'>📊 Tabelas encontradas: " . count($tabelas) . "</p>";
    
    foreach ($tabelas as $tabela) {
        echo "<h4>Tabela: {$tabela}</h4>";
        
        // Verificar estrutura de cada tabela
        $stmt_desc = $conn->query("DESCRIBE {$tabela}");
        $colunas = $stmt_desc->fetchAll(PDO::FETCH_ASSOC);
        
        $tem_status = false;
        $tem_id_profissional = false;
        
        echo "<p class='info'>Colunas: ";
        foreach ($colunas as $coluna) {
            echo $coluna['Field'] . ' ';
            if ($coluna['Field'] === 'status') $tem_status = true;
            if ($coluna['Field'] === 'id_profissional') $tem_id_profissional = true;
        }
        echo "</p>";
        
        if ($tem_status) {
            echo "<p class='success'>✅ Tem coluna 'status'</p>";
        } else {
            echo "<p class='error'>❌ NÃO tem coluna 'status'</p>";
        }
        
        if ($tem_id_profissional) {
            echo "<p class='success'>✅ Tem coluna 'id_profissional'</p>";
        } else {
            echo "<p class='error'>❌ NÃO tem coluna 'id_profissional'</p>";
        }
        
        // Contar registros
        $stmt_count = $conn->query("SELECT COUNT(*) FROM {$tabela}");
        $total = $stmt_count->fetchColumn();
        echo "<p class='info'>📊 Total de registros: {$total}</p>";
        
        echo "<hr>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro ao verificar tabelas: {$e->getMessage()}</p>";
}

// 2. Verificar qual banco está sendo usado
echo "<h3>2. Informações da Conexão</h3>";

try {
    $stmt = $conn->query("SELECT DATABASE() as current_db");
    $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p class='info'>🗄️ Banco atual: {$db_info['current_db']}</p>";
    
    // Verificar configuração da conexão
    $stmt = $conn->query("SELECT USER() as current_user");
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p class='info'>👤 Usuário atual: {$user_info['current_user']}</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro ao verificar conexão: {$e->getMessage()}</p>";
}

// 3. Testar query direta na tabela agendamentos
echo "<h3>3. Teste de Query Direta</h3>";

try {
    // Query que funciona
    echo "<h4>Query que deveria funcionar:</h4>";
    $sql_ok = "SELECT hora FROM agendamentos WHERE id_profissional = 1 AND data = '2025-08-23' AND status != 'cancelado' ORDER BY hora";
    echo "<p class='info'>SQL: {$sql_ok}</p>";
    
    $stmt = $conn->prepare($sql_ok);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='success'>✅ Query executada com sucesso</p>";
    echo "<p class='info'>📊 Resultados: " . count($resultados) . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro na query direta: {$e->getMessage()}</p>";
    echo "<p class='error'>Código do erro: {$e->getCode()}</p>";
}

// 4. Verificar se há views ou tabelas temporárias
echo "<h3>4. Verificar Views</h3>";

try {
    $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($views)) {
        echo "<p class='info'>📋 Nenhuma view encontrada</p>";
    } else {
        echo "<p class='info'>📋 Views encontradas:</p>";
        foreach ($views as $view) {
            echo "<p>- {$view['Tables_in_' . $db_info['current_db']]}</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro ao verificar views: {$e->getMessage()}</p>";
}

// 5. Testar instância da classe Agendamento
echo "<h3>5. Teste da Classe Agendamento</h3>";

try {
    $agendamento = new Agendamento();
    echo "<p class='success'>✅ Classe Agendamento instanciada</p>";
    
    // Usar reflexão para verificar a propriedade table
    $reflection = new ReflectionClass($agendamento);
    $table_property = $reflection->getProperty('table');
    $table_property->setAccessible(true);
    $table_name = $table_property->getValue($agendamento);
    
    echo "<p class='info'>📋 Nome da tabela na classe: {$table_name}</p>";
    
    // Verificar a conexão da classe
    $conn_property = $reflection->getProperty('conn');
    $conn_property->setAccessible(true);
    $class_conn = $conn_property->getValue($agendamento);
    
    if ($class_conn === $conn) {
        echo "<p class='success'>✅ Mesma conexão</p>";
    } else {
        echo "<p class='error'>❌ Conexões diferentes!</p>";
        
        // Testar query na conexão da classe
        try {
            $stmt_class = $class_conn->query("SELECT DATABASE() as db_class");
            $db_class = $stmt_class->fetch(PDO::FETCH_ASSOC);
            echo "<p class='error'>🗄️ Banco da classe: {$db_class['db_class']}</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao verificar banco da classe: {$e->getMessage()}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao instanciar classe: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>