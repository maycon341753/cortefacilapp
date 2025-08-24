<?php
require_once 'config/database.php';

echo "<h2>Verificar e Corrigir Coluna Status - Banco Online</h2>";
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
    
    // Verificar estrutura atual da tabela agendamentos
    echo "<h3>1. Verificando estrutura atual da tabela agendamentos</h3>";
    $stmt = $conn->query("DESCRIBE agendamentos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tem_status = false;
    echo "<div style='background:#f8f9fa;padding:10px;margin:10px 0;border:1px solid #ddd;'>";
    echo "<strong>Colunas existentes:</strong><br>";
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})";
        if ($coluna['Default']) echo " DEFAULT {$coluna['Default']}";
        echo "<br>";
        
        if ($coluna['Field'] === 'status') {
            $tem_status = true;
        }
    }
    echo "</div>";
    
    if ($tem_status) {
        echo "<p class='success'>✅ Coluna 'status' já existe!</p>";
        
        // Testar uma consulta simples
        echo "<h3>2. Testando consulta com coluna status</h3>";
        try {
            $stmt_test = $conn->query("SELECT COUNT(*) as total FROM agendamentos WHERE status != 'cancelado'");
            $resultado = $stmt_test->fetch(PDO::FETCH_ASSOC);
            echo "<p class='success'>✅ Consulta funcionou! Total de agendamentos não cancelados: {$resultado['total']}</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro na consulta: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Coluna 'status' NÃO existe!</p>";
        
        echo "<h3>2. Adicionando coluna status</h3>";
        try {
            $sql_add_status = "ALTER TABLE agendamentos ADD COLUMN status ENUM('pendente','confirmado','cancelado','concluido') DEFAULT 'pendente' AFTER hora";
            $conn->exec($sql_add_status);
            echo "<p class='success'>✅ Coluna 'status' adicionada com sucesso!</p>";
            
            // Atualizar registros existentes
            $sql_update = "UPDATE agendamentos SET status = 'confirmado' WHERE status IS NULL";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute();
            $affected = $stmt_update->rowCount();
            echo "<p class='info'>📊 {$affected} registros atualizados com status 'confirmado'</p>";
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao adicionar coluna: " . $e->getMessage() . "</p>";
        }
    }
    
    // Verificar estrutura final
    echo "<h3>3. Estrutura final da tabela</h3>";
    $stmt = $conn->query("DESCRIBE agendamentos");
    $colunas_final = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background:#e8f5e8;padding:10px;margin:10px 0;border:1px solid #4caf50;'>";
    echo "<strong>Estrutura atualizada:</strong><br>";
    foreach ($colunas_final as $coluna) {
        $destaque = ($coluna['Field'] === 'status') ? 'style="background:yellow;font-weight:bold;"' : '';
        echo "<span $destaque>- {$coluna['Field']} ({$coluna['Type']})";
        if ($coluna['Default']) echo " DEFAULT {$coluna['Default']}";
        echo "</span><br>";
    }
    echo "</div>";
    
    // Teste final
    echo "<h3>4. Teste final da funcionalidade</h3>";
    try {
        // Buscar um profissional para teste
        $stmt_prof = $conn->query("SELECT id FROM profissionais WHERE ativo = 1 LIMIT 1");
        $profissional = $stmt_prof->fetch(PDO::FETCH_ASSOC);
        
        if ($profissional) {
            $data_teste = date('Y-m-d');
            $stmt_test = $conn->prepare("SELECT hora FROM agendamentos WHERE id_profissional = ? AND data = ? AND status != 'cancelado' ORDER BY hora");
            $stmt_test->execute([$profissional['id'], $data_teste]);
            $horarios = $stmt_test->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p class='success'>✅ Consulta de horários ocupados funcionou!</p>";
            echo "<p class='info'>📊 Horários ocupados para profissional {$profissional['id']} em {$data_teste}: " . count($horarios) . "</p>";
            if (!empty($horarios)) {
                echo "<p class='info'>🕐 Horários: " . implode(', ', $horarios) . "</p>";
            }
        } else {
            echo "<p class='info'>ℹ️ Nenhum profissional ativo encontrado para teste</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no teste final: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";
echo "<p><a href='teste_horarios_direto.php'>🔄 Testar horários novamente</a></p>";
?>