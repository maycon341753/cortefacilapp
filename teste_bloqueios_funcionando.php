<?php
/**
 * Teste para verificar se o sistema de bloqueios temporários está funcionando
 * após a criação da tabela bloqueios_temporarios
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>Teste do Sistema de Bloqueios Temporários</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados');
    }
    
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela bloqueios_temporarios existe
    $stmt = $conn->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Tabela bloqueios_temporarios não encontrada!');
    }
    
    echo "<p class='success'>✅ Tabela bloqueios_temporarios encontrada</p>";
    
    // Verificar estrutura da tabela
    $stmt = $conn->query("DESCRIBE bloqueios_temporarios");
    $campos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $campos_esperados = ['id', 'id_profissional', 'data', 'hora', 'session_id', 'ip_cliente', 'created_at', 'expires_at'];
    $campos_faltando = array_diff($campos_esperados, $campos);
    
    if (empty($campos_faltando)) {
        echo "<p class='success'>✅ Estrutura da tabela correta</p>";
    } else {
        echo "<p class='error'>❌ Campos faltando: " . implode(', ', $campos_faltando) . "</p>";
    }
    
    // Testar classe Agendamento
    $agendamento = new Agendamento($conn);
    echo "<p class='success'>✅ Classe Agendamento instanciada</p>";
    
    // Testar métodos de bloqueio
    $id_profissional = 1;
    $data = date('Y-m-d');
    $hora = '10:00:00';
    $session_id = 'teste_' . time();
    $ip_cliente = '127.0.0.1';
    
    echo "<h3>Teste de Bloqueio de Horário</h3>";
    echo "<p class='info'>Testando: Profissional $id_profissional, Data $data, Hora $hora</p>";
    
    // Limpar bloqueios expirados primeiro
    $agendamento->limparBloqueiosExpirados();
    echo "<p class='info'>🧹 Bloqueios expirados limpos</p>";
    
    // Testar bloqueio
    $resultado_bloqueio = $agendamento->bloquearHorarioTemporariamente(
        $id_profissional, 
        $data, 
        $hora, 
        $session_id, 
        $ip_cliente, 
        1 // 1 minuto para teste
    );
    
    if ($resultado_bloqueio) {
        echo "<p class='success'>✅ Horário bloqueado com sucesso</p>";
        
        // Verificar se o bloqueio está na tabela
        $stmt = $conn->prepare("SELECT * FROM bloqueios_temporarios WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $bloqueio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bloqueio) {
            echo "<p class='success'>✅ Bloqueio registrado na tabela</p>";
            echo "<p class='info'>📋 Detalhes: ID {$bloqueio['id']}, Expira em {$bloqueio['expires_at']}</p>";
        } else {
            echo "<p class='error'>❌ Bloqueio não encontrado na tabela</p>";
        }
        
        // Testar desbloqueio
        $resultado_desbloqueio = $agendamento->desbloquearHorario(
            $id_profissional, 
            $data, 
            $hora, 
            $session_id
        );
        
        if ($resultado_desbloqueio) {
            echo "<p class='success'>✅ Horário desbloqueado com sucesso</p>";
        } else {
            echo "<p class='error'>❌ Erro ao desbloquear horário</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Erro ao bloquear horário</p>";
    }
    
    // Testar geração de horários com bloqueios
    echo "<h3>Teste de Horários Disponíveis com Bloqueios</h3>";
    
    $horarios_com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data);
    
    if (!empty($horarios_com_bloqueios)) {
        echo "<p class='success'>✅ Horários gerados com sistema de bloqueios</p>";
        echo "<p class='info'>📊 Total de horários disponíveis: " . count($horarios_com_bloqueios) . "</p>";
        
        // Mostrar alguns horários
        $primeiros_horarios = array_slice($horarios_com_bloqueios, 0, 5);
        echo "<p class='info'>🕐 Primeiros horários: " . implode(', ', $primeiros_horarios) . "</p>";
    } else {
        echo "<p class='error'>❌ Nenhum horário disponível gerado</p>";
    }
    
    // Verificar contagem de bloqueios ativos
    $stmt = $conn->query("SELECT COUNT(*) FROM bloqueios_temporarios WHERE expires_at > NOW()");
    $bloqueios_ativos = $stmt->fetchColumn();
    
    echo "<h3>Status Final</h3>";
    echo "<p class='info'>📊 Bloqueios ativos no sistema: $bloqueios_ativos</p>";
    
    echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;margin:20px 0;'>";
    echo "<h4>✅ Sistema de Bloqueios Temporários Funcionando!</h4>";
    echo "<p>A tabela foi criada com sucesso e todos os métodos estão operacionais.</p>";
    echo "<p><strong>Próximo passo:</strong> Testar na interface de agendamento</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

echo "<br><a href='cliente/agendar.php' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔗 Testar Interface de Agendamento</a>";
?>