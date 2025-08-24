<?php
// Teste direto dos horários sem passar pela API
require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'includes/functions.php';

echo "<h2>Teste Direto dos Horários - Banco Online</h2>";
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
    
    // Buscar um profissional ativo
    $stmt = $conn->query("SELECT p.id, p.nome, p.especialidade, s.nome as salao_nome FROM profissionais p JOIN saloes s ON p.id_salao = s.id WHERE p.ativo = 1 LIMIT 1");
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profissional) {
        echo "<p class='error'>❌ Nenhum profissional ativo encontrado</p>";
        exit;
    }
    
    echo "<p class='info'>👨‍💼 Profissional encontrado: {$profissional['nome']} - {$profissional['especialidade']} (Salão: {$profissional['salao_nome']})</p>";
    
    $profissional_id = $profissional['id'];
    $data = date('Y-m-d');
    
    echo "<p class='info'>📅 Testando para data: $data</p>";
    
    // Criar instância do modelo de agendamento
    $agendamento = new Agendamento($conn);
    
    echo "<h3>1. Testando método listarHorariosOcupados</h3>";
    try {
        $horarios_ocupados = $agendamento->listarHorariosOcupados($profissional_id, $data);
        echo "<p class='success'>✅ Método funcionou</p>";
        echo "<p class='info'>📊 Horários ocupados: " . count($horarios_ocupados) . "</p>";
        if (!empty($horarios_ocupados)) {
            echo "<p class='info'>🕐 Horários: " . implode(', ', $horarios_ocupados) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>2. Testando método gerarHorariosDisponiveis</h3>";
    try {
        $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis($profissional_id, $data);
        echo "<p class='success'>✅ Método funcionou</p>";
        echo "<p class='info'>📊 Horários disponíveis: " . count($horarios_disponiveis) . "</p>";
        if (!empty($horarios_disponiveis)) {
            echo "<p class='info'>🕐 Horários: " . implode(', ', array_slice($horarios_disponiveis, 0, 10)) . (count($horarios_disponiveis) > 10 ? '...' : '') . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>3. Testando método gerarHorariosDisponiveisComBloqueios</h3>";
    try {
        $horarios_com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data, 'test_session');
        echo "<p class='success'>✅ Método funcionou</p>";
        echo "<p class='info'>📊 Horários com bloqueios: " . count($horarios_com_bloqueios) . "</p>";
        if (!empty($horarios_com_bloqueios)) {
            echo "<p class='info'>🕐 Horários: " . implode(', ', array_slice($horarios_com_bloqueios, 0, 10)) . (count($horarios_com_bloqueios) > 10 ? '...' : '') . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>4. Verificando estrutura da tabela agendamentos</h3>";
    $stmt = $conn->query("DESCRIBE agendamentos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tem_status = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'status') {
            $tem_status = true;
            break;
        }
    }
    
    if ($tem_status) {
        echo "<p class='success'>✅ Coluna 'status' existe na tabela agendamentos</p>";
    } else {
        echo "<p class='error'>❌ Coluna 'status' NÃO existe na tabela agendamentos</p>";
    }
    
    echo "<h3>5. Verificando horários de funcionamento</h3>";
    $stmt = $conn->prepare("SELECT * FROM horarios_funcionamento WHERE id_salao = (SELECT id_salao FROM profissionais WHERE id = ?) LIMIT 5");
    $stmt->execute([$profissional_id]);
    $horarios_funcionamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($horarios_funcionamento)) {
        echo "<p class='success'>✅ Horários de funcionamento encontrados: " . count($horarios_funcionamento) . "</p>";
    } else {
        echo "<p class='error'>❌ Nenhum horário de funcionamento encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Teste concluído!</strong></p>";
?>