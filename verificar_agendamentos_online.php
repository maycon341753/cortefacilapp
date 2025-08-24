<?php
require_once 'config/database.php';

// Forçar ambiente online
$_ENV['ENVIRONMENT'] = 'online';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

echo "<h2>Verificação da Tabela Agendamentos - Banco Online</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    // Conectar ao banco online
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<p class='success'>✅ Conectado ao banco online</p>";
    
    // Verificar se a tabela agendamentos existe
    $stmt = $conn->query("SHOW TABLES LIKE 'agendamentos'");
    $tabela_existe = $stmt->rowCount() > 0;
    
    if (!$tabela_existe) {
        echo "<p class='error'>❌ Tabela 'agendamentos' não existe!</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Tabela 'agendamentos' existe</p>";
    
    // Verificar estrutura da tabela
    echo "<h3>Estrutura da tabela agendamentos:</h3>";
    $stmt = $conn->query("DESCRIBE agendamentos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    $campos_encontrados = [];
    foreach ($colunas as $coluna) {
        $campos_encontrados[] = $coluna['Field'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar se a coluna 'status' existe
    $tem_status = in_array('status', $campos_encontrados);
    
    if ($tem_status) {
        echo "<p class='success'>✅ Coluna 'status' existe na tabela</p>";
    } else {
        echo "<p class='error'>❌ Coluna 'status' NÃO existe na tabela</p>";
        echo "<p class='info'>📝 Campos encontrados: " . implode(', ', $campos_encontrados) . "</p>";
    }
    
    // Contar registros
    $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos");
    $total_registros = $stmt->fetchColumn();
    echo "<p class='info'>📊 Total de registros na tabela: $total_registros</p>";
    
    // Se houver registros, mostrar alguns exemplos
    if ($total_registros > 0) {
        echo "<h3>Exemplos de registros (últimos 5):</h3>";
        $stmt = $conn->query("SELECT * FROM agendamentos ORDER BY id DESC LIMIT 5");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($registros)) {
            echo "<table>";
            // Cabeçalho
            echo "<tr>";
            foreach (array_keys($registros[0]) as $campo) {
                echo "<th>" . htmlspecialchars($campo) . "</th>";
            }
            echo "</tr>";
            
            // Dados
            foreach ($registros as $registro) {
                echo "<tr>";
                foreach ($registro as $valor) {
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";
?>