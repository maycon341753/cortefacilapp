<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'models/profissional.php';
require_once 'models/salao.php';

echo "<h2>Teste do Sistema de Agendamento Completo</h2>";

try {
    // Conectar ao banco
    $db = Database::getInstance();
    $conn = $db->connect();
    
    echo "<h3>1. Verificação da Conexão</h3>";
    echo "<p class='success'>✅ Conectado ao banco: " . $conn->query("SELECT DATABASE()")->fetchColumn() . "</p>";
    
    // Instanciar classes
    $agendamento = new Agendamento();
    $profissional = new Profissional();
    $salao = new Salao();
    
    echo "<h3>2. Verificação de Dados Básicos</h3>";
    
    // Verificar se existem salões
    try {
        $saloes = $salao->listarTodos();
        echo "<p class='success'>✅ Salões encontrados: " . count($saloes) . "</p>";
        if (count($saloes) > 0) {
            $primeiro_salao = $saloes[0];
            echo "<p class='info'>📍 Primeiro salão: {$primeiro_salao['nome']} (ID: {$primeiro_salao['id']})</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao listar salões: " . $e->getMessage() . "</p>";
    }
    
    // Verificar se existem profissionais
    try {
        $profissionais = $profissional->listarTodos();
        echo "<p class='success'>✅ Profissionais encontrados: " . count($profissionais) . "</p>";
        if (count($profissionais) > 0) {
            $primeiro_prof = $profissionais[0];
            echo "<p class='info'>👨‍💼 Primeiro profissional: {$primeiro_prof['nome']} (ID: {$primeiro_prof['id']})</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao listar profissionais: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>3. Teste de Geração de Horários (Intervalos de 30min)</h3>";
    
    // Testar com dados reais se existirem, senão usar IDs fictícios
    $id_profissional = isset($primeiro_prof) ? $primeiro_prof['id'] : 1;
    $data_teste = date('Y-m-d', strtotime('+1 day')); // Amanhã
    
    echo "<p class='info'>🔍 Testando para profissional ID: $id_profissional, data: $data_teste</p>";
    
    // Teste 1: Horários ocupados
    try {
        $ocupados = $agendamento->listarHorariosOcupados($id_profissional, $data_teste);
        echo "<p class='success'>✅ Horários ocupados: " . count($ocupados) . "</p>";
        if (count($ocupados) > 0) {
            echo "<ul>";
            foreach ($ocupados as $horario) {
                echo "<li>⏰ {$horario}</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao listar horários ocupados: " . $e->getMessage() . "</p>";
    }
    
    // Teste 2: Horários disponíveis (30min)
    try {
        $disponiveis = $agendamento->gerarHorariosDisponiveis($id_profissional, $data_teste);
        echo "<p class='success'>✅ Horários disponíveis (30min): " . count($disponiveis) . "</p>";
        if (count($disponiveis) > 0) {
            echo "<p class='info'>📋 Primeiros 10 horários:</p>";
            echo "<ul>";
            for ($i = 0; $i < min(10, count($disponiveis)); $i++) {
                echo "<li>🕐 {$disponiveis[$i]}</li>";
            }
            if (count($disponiveis) > 10) {
                echo "<li>... e mais " . (count($disponiveis) - 10) . " horários</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao gerar horários disponíveis: " . $e->getMessage() . "</p>";
    }
    
    // Teste 3: Horários com bloqueios
    try {
        $com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data_teste);
        echo "<p class='success'>✅ Horários disponíveis com bloqueios: " . count($com_bloqueios) . "</p>";
        if (count($com_bloqueios) > 0) {
            echo "<p class='info'>📋 Primeiros 10 horários (com verificação de bloqueios):</p>";
            echo "<ul>";
            for ($i = 0; $i < min(10, count($com_bloqueios)); $i++) {
                echo "<li>🕐 {$com_bloqueios[$i]}</li>";
            }
            if (count($com_bloqueios) > 10) {
                echo "<li>... e mais " . (count($com_bloqueios) - 10) . " horários</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao gerar horários com bloqueios: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>4. Teste de Bloqueio de Horários</h3>";
    
    // Testar bloqueio de horário
    $horario_teste = '14:30';
    try {
        $resultado = $agendamento->bloquearHorarioTemporariamente($id_profissional, $data_teste, $horario_teste, 'test_session', '127.0.0.1');
        if ($resultado) {
            echo "<p class='success'>✅ Horário $horario_teste bloqueado com sucesso</p>";
            
            // Verificar se o horário foi realmente bloqueado
            $com_bloqueios_apos = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data_teste);
            $horario_bloqueado = !in_array($horario_teste, $com_bloqueios_apos);
            
            if ($horario_bloqueado) {
                echo "<p class='success'>✅ Verificação: Horário $horario_teste não está mais disponível</p>";
            } else {
                echo "<p class='warning'>⚠️ Verificação: Horário $horario_teste ainda está disponível</p>";
            }
            
            // Desbloquear o horário
            $desbloqueio = $agendamento->desbloquearHorario($id_profissional, $data_teste, $horario_teste, 'test_session');
            if ($desbloqueio) {
                echo "<p class='success'>✅ Horário $horario_teste desbloqueado com sucesso</p>";
            }
        } else {
            echo "<p class='error'>❌ Falha ao bloquear horário $horario_teste</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no teste de bloqueio: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>5. Verificação de Intervalos</h3>";
    
    // Verificar se os intervalos são realmente de 30 minutos
    if (isset($disponiveis) && count($disponiveis) >= 2) {
        $primeiro = $disponiveis[0];
        $segundo = $disponiveis[1];
        
        $time1 = strtotime($primeiro);
        $time2 = strtotime($segundo);
        $diferenca = ($time2 - $time1) / 60; // em minutos
        
        echo "<p class='info'>🔍 Verificação de intervalo:</p>";
        echo "<p class='info'>• Primeiro horário: $primeiro</p>";
        echo "<p class='info'>• Segundo horário: $segundo</p>";
        echo "<p class='info'>• Diferença: $diferenca minutos</p>";
        
        if ($diferenca == 30) {
            echo "<p class='success'>✅ Intervalos de 30 minutos confirmados!</p>";
        } else {
            echo "<p class='error'>❌ Intervalos incorretos! Esperado: 30min, Encontrado: {$diferenca}min</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
    echo "<p class='error'>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
}

echo "<hr><p><strong>Teste do sistema completo finalizado!</strong></p>";
?>

<style>
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
ul { margin-left: 20px; }
li { margin: 5px 0; }
</style>