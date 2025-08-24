<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->connect();
    
    echo "=== VERIFICAÇÃO DOS DADOS DO BANCO ===\n";
    
    // Verificar salões
    $stmt = $conn->query('SELECT COUNT(*) as total FROM saloes WHERE ativo = 1');
    $result = $stmt->fetch();
    echo "Salões ativos: " . $result['total'] . "\n";
    
    // Verificar profissionais
    $stmt = $conn->query('SELECT COUNT(*) as total FROM profissionais WHERE ativo = 1');
    $result = $stmt->fetch();
    echo "Profissionais ativos: " . $result['total'] . "\n";
    
    // Verificar horários de funcionamento
    $stmt = $conn->query('SELECT COUNT(*) as total FROM horarios_funcionamento WHERE ativo = 1');
    $result = $stmt->fetch();
    echo "Horários de funcionamento: " . $result['total'] . "\n";
    
    // Listar alguns salões e profissionais para teste
    echo "\n=== DADOS PARA TESTE ===\n";
    
    $stmt = $conn->query('SELECT id, nome FROM saloes WHERE ativo = 1 LIMIT 3');
    $saloes = $stmt->fetchAll();
    echo "Salões disponíveis:\n";
    foreach ($saloes as $salao) {
        echo "- ID: {$salao['id']}, Nome: {$salao['nome']}\n";
    }
    
    $stmt = $conn->query('SELECT id, nome, id_salao FROM profissionais WHERE ativo = 1 LIMIT 3');
    $profissionais = $stmt->fetchAll();
    echo "\nProfissionais disponíveis:\n";
    foreach ($profissionais as $prof) {
        echo "- ID: {$prof['id']}, Nome: {$prof['nome']}, Salão ID: {$prof['id_salao']}\n";
    }
    
    // Verificar horários de funcionamento para um salão
    if (!empty($saloes)) {
        $salao_id = $saloes[0]['id'];
        $stmt = $conn->prepare('SELECT dia_semana, hora_abertura, hora_fechamento FROM horarios_funcionamento WHERE id_salao = ? AND ativo = 1');
        $stmt->execute([$salao_id]);
        $horarios_func = $stmt->fetchAll();
        
        echo "\nHorários de funcionamento do salão {$saloes[0]['nome']} (ID: {$salao_id}):\n";
        foreach ($horarios_func as $hf) {
            $dias = ['', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
            $dia_nome = $hf['dia_semana'] == 7 ? 'Domingo' : $dias[$hf['dia_semana']];
            echo "- {$dia_nome}: {$hf['hora_abertura']} às {$hf['hora_fechamento']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>