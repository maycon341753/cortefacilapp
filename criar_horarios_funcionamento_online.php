<?php
require_once 'config/database.php';

echo "<h2>Criar Horários de Funcionamento - Banco Online</h2>";
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
    
    // Buscar salões ativos
    $stmt = $conn->query("SELECT id, nome FROM saloes WHERE ativo = 1");
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($saloes)) {
        echo "<p class='error'>❌ Nenhum salão ativo encontrado</p>";
        exit;
    }
    
    echo "<p class='info'>🏪 Salões encontrados: " . count($saloes) . "</p>";
    
    foreach ($saloes as $salao) {
        echo "<h3>Configurando horários para: {$salao['nome']} (ID: {$salao['id']})</h3>";
        
        // Verificar se já tem horários
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM horarios_funcionamento WHERE id_salao = ?");
        $stmt_check->execute([$salao['id']]);
        $tem_horarios = $stmt_check->fetchColumn() > 0;
        
        if ($tem_horarios) {
            echo "<p class='info'>ℹ️ Salão já possui horários cadastrados</p>";
            continue;
        }
        
        // Criar horários padrão (Segunda a Sábado: 8h às 18h, Domingo: 9h às 17h)
        $horarios_padrao = [
            1 => ['08:00:00', '18:00:00'], // Segunda
            2 => ['08:00:00', '18:00:00'], // Terça
            3 => ['08:00:00', '18:00:00'], // Quarta
            4 => ['08:00:00', '18:00:00'], // Quinta
            5 => ['08:00:00', '18:00:00'], // Sexta
            6 => ['08:00:00', '17:00:00'], // Sábado
            0 => ['09:00:00', '16:00:00']  // Domingo
        ];
        
        $stmt_insert = $conn->prepare("
            INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento, ativo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        
        $inseridos = 0;
        foreach ($horarios_padrao as $dia => $horarios) {
            try {
                $stmt_insert->execute([$salao['id'], $dia, $horarios[0], $horarios[1]]);
                $inseridos++;
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro ao inserir dia $dia: " . $e->getMessage() . "</p>";
            }
        }
        
        if ($inseridos > 0) {
            echo "<p class='success'>✅ Inseridos $inseridos horários de funcionamento</p>";
        }
    }
    
    // Verificar resultado final
    echo "<h3>📊 Resumo Final</h3>";
    $stmt = $conn->query("
        SELECT s.nome as salao_nome, 
               COUNT(hf.id) as total_horarios,
               GROUP_CONCAT(CONCAT('Dia ', hf.dia_semana, ': ', hf.hora_abertura, '-', hf.hora_fechamento) SEPARATOR ', ') as horarios
        FROM saloes s 
        LEFT JOIN horarios_funcionamento hf ON s.id = hf.id_salao 
        WHERE s.ativo = 1 
        GROUP BY s.id, s.nome
    ");
    
    $resumo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($resumo as $item) {
        echo "<div style='background:#f8f9fa;padding:10px;margin:10px 0;border-left:4px solid #007bff;'>";
        echo "<strong>{$item['salao_nome']}</strong><br>";
        echo "Horários cadastrados: {$item['total_horarios']}<br>";
        if ($item['horarios']) {
            echo "Detalhes: {$item['horarios']}";
        } else {
            echo "<span style='color:red;'>Nenhum horário cadastrado</span>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Configuração concluída!</strong></p>";
echo "<p><a href='teste_horarios_direto.php'>🔄 Testar horários novamente</a></p>";
?>