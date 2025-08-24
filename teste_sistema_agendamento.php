<?php
// Teste do Sistema de Agendamento - Intervalos de 30min e Bloqueios
require_once '../config/database.php';
require_once '../models/agendamento.php';

echo "<h2>🧪 Teste do Sistema de Agendamento</h2>";
echo "<hr>";

// Inicializar classe de agendamento
$agendamento = new Agendamento();

// Teste 1: Verificar intervalos de 30 minutos
echo "<h3>📅 Teste 1: Intervalos de 30 minutos</h3>";
$profissional_id = 1; // Assumindo que existe um profissional com ID 1
$data = date('Y-m-d', strtotime('+1 day')); // Amanhã

try {
    $horarios = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data, session_id());
    
    if (!empty($horarios)) {
        echo "✅ <strong>Sucesso!</strong> Encontrados " . count($horarios) . " horários disponíveis:<br>";
        echo "<div style='margin: 10px 0; padding: 10px; background: #f0f8ff; border-left: 4px solid #007cba;'>";
        foreach (array_slice($horarios, 0, 10) as $hora) { // Mostrar apenas os primeiros 10
            echo "🕐 $hora<br>";
        }
        if (count($horarios) > 10) {
            echo "... e mais " . (count($horarios) - 10) . " horários";
        }
        echo "</div>";
        
        // Verificar se os intervalos são de 30 minutos
        $intervalo_correto = true;
        for ($i = 1; $i < min(5, count($horarios)); $i++) {
            $hora_anterior = strtotime($horarios[$i-1]);
            $hora_atual = strtotime($horarios[$i]);
            $diferenca = ($hora_atual - $hora_anterior) / 60; // em minutos
            
            if ($diferenca != 30) {
                $intervalo_correto = false;
                echo "❌ Intervalo incorreto entre {$horarios[$i-1]} e {$horarios[$i]}: {$diferenca} minutos<br>";
                break;
            }
        }
        
        if ($intervalo_correto) {
            echo "✅ <strong>Intervalos de 30 minutos confirmados!</strong><br>";
        }
    } else {
        echo "⚠️ Nenhum horário disponível encontrado para o profissional $profissional_id na data $data<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao gerar horários: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 2: Sistema de bloqueio temporário
echo "<h3>🔒 Teste 2: Sistema de Bloqueio Temporário</h3>";

if (!empty($horarios)) {
    $hora_teste = $horarios[0]; // Primeiro horário disponível
    $session_teste = 'teste_' . uniqid();
    
    try {
        // Bloquear horário
        $resultado_bloqueio = $agendamento->bloquearHorarioTemporariamente($profissional_id, $data, $hora_teste, $session_teste);
        
        if ($resultado_bloqueio) {
            echo "✅ <strong>Horário bloqueado com sucesso:</strong> $hora_teste<br>";
            
            // Verificar se o horário foi removido da lista
            $horarios_apos_bloqueio = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data, 'outra_sessao');
            
            if (!in_array($hora_teste, $horarios_apos_bloqueio)) {
                echo "✅ <strong>Bloqueio funcionando:</strong> Horário $hora_teste não aparece mais na lista para outras sessões<br>";
            } else {
                echo "❌ <strong>Erro no bloqueio:</strong> Horário $hora_teste ainda aparece na lista<br>";
            }
            
            // Verificar se o horário ainda aparece para a mesma sessão
            $horarios_mesma_sessao = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data, $session_teste);
            
            if (in_array($hora_teste, $horarios_mesma_sessao)) {
                echo "✅ <strong>Sessão própria:</strong> Horário $hora_teste ainda disponível para a sessão que bloqueou<br>";
            } else {
                echo "⚠️ <strong>Atenção:</strong> Horário $hora_teste não disponível nem para a sessão que bloqueou<br>";
            }
            
            // Desbloquear horário
            $resultado_desbloqueio = $agendamento->desbloquearHorario($profissional_id, $data, $hora_teste, $session_teste);
            
            if ($resultado_desbloqueio) {
                echo "✅ <strong>Horário desbloqueado com sucesso</strong><br>";
                
                // Verificar se voltou à lista
                $horarios_apos_desbloqueio = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data, 'outra_sessao');
                
                if (in_array($hora_teste, $horarios_apos_desbloqueio)) {
                    echo "✅ <strong>Desbloqueio funcionando:</strong> Horário $hora_teste voltou à lista<br>";
                } else {
                    echo "❌ <strong>Erro no desbloqueio:</strong> Horário $hora_teste não voltou à lista<br>";
                }
            } else {
                echo "❌ Erro ao desbloquear horário<br>";
            }
            
        } else {
            echo "❌ Erro ao bloquear horário<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro no teste de bloqueio: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Não foi possível testar bloqueios - nenhum horário disponível<br>";
}

echo "<hr>";

// Teste 3: Verificar tabela de bloqueios
echo "<h3>🗃️ Teste 3: Tabela de Bloqueios Temporários</h3>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
    if ($stmt->rowCount() > 0) {
        echo "✅ <strong>Tabela 'bloqueios_temporarios' existe</strong><br>";
        
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE bloqueios_temporarios");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $colunas_esperadas = ['id', 'id_profissional', 'data', 'hora', 'session_id', 'ip_cliente', 'created_at', 'expires_at'];
        $colunas_faltando = array_diff($colunas_esperadas, $colunas);
        
        if (empty($colunas_faltando)) {
            echo "✅ <strong>Estrutura da tabela correta</strong><br>";
        } else {
            echo "❌ <strong>Colunas faltando:</strong> " . implode(', ', $colunas_faltando) . "<br>";
        }
        
        // Verificar registros ativos
        $stmt = $pdo->query("SELECT COUNT(*) FROM bloqueios_temporarios WHERE expires_at > NOW()");
        $bloqueios_ativos = $stmt->fetchColumn();
        
        echo "📊 <strong>Bloqueios ativos:</strong> $bloqueios_ativos<br>";
        
    } else {
        echo "❌ <strong>Tabela 'bloqueios_temporarios' não existe</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabela: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📋 Resumo dos Testes</h3>";
echo "<p>✅ = Funcionando corretamente</p>";
echo "<p>❌ = Erro encontrado</p>";
echo "<p>⚠️ = Atenção necessária</p>";
echo "<br><a href='cliente/agendar.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Testar Interface de Agendamento</a>";
?>