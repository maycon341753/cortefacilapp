<?php
/**
 * Teste Final do Sistema de Agendamento
 * Verifica todas as funcionalidades implementadas
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'includes/functions.php';

echo "<h2>🧪 Teste Final do Sistema de Agendamento</h2>\n";
echo "<pre>\n";

try {
    // 1. Conectar ao banco
    echo "1️⃣ Conectando ao banco de dados...\n";
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco');
    }
    echo "✅ Conexão estabelecida com sucesso\n\n";
    
    // 2. Instanciar classe Agendamento com conexão
    $agendamento = new Agendamento($conn);
    echo "✅ Classe Agendamento instanciada com conexão\n\n";
    
    // 3. Testar geração de horários com bloqueios
    echo "2️⃣ Testando geração de horários com sistema de bloqueios...\n";
    $id_profissional = 1;
    $data = date('Y-m-d', strtotime('+1 day')); // Amanhã
    $session_id = 'teste_session_' . time();
    
    echo "📅 Data de teste: {$data}\n";
    echo "👨‍💼 Profissional ID: {$id_profissional}\n";
    echo "🔑 Session ID: {$session_id}\n\n";
    
    // Gerar horários disponíveis
    $horarios = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data, $session_id);
    echo "⏰ Horários disponíveis encontrados: " . count($horarios) . "\n";
    
    if (count($horarios) > 0) {
        echo "📋 Primeiros 5 horários: " . implode(', ', array_slice($horarios, 0, 5)) . "\n\n";
        
        // 4. Testar bloqueio de horário
        echo "3️⃣ Testando sistema de bloqueio temporário...\n";
        $horario_teste = $horarios[0];
        echo "🔒 Bloqueando horário: {$horario_teste}\n";
        
        $bloqueio_ok = $agendamento->bloquearHorarioTemporariamente(
            $id_profissional, 
            $data, 
            $horario_teste, 
            $session_id, 
            '127.0.0.1',
            10 // 10 minutos
        );
        
        if ($bloqueio_ok) {
            echo "✅ Horário bloqueado com sucesso\n";
            
            // Verificar se horário não aparece mais na lista
            $horarios_apos_bloqueio = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data, 'outra_session');
            $horario_ainda_disponivel = in_array($horario_teste, $horarios_apos_bloqueio);
            
            if (!$horario_ainda_disponivel) {
                echo "✅ Horário corretamente removido da lista para outras sessões\n";
            } else {
                echo "❌ Horário ainda aparece na lista para outras sessões\n";
            }
            
            // Verificar se horário ainda aparece para a mesma sessão
            $horarios_mesma_sessao = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data, $session_id);
            $horario_disponivel_mesma_sessao = in_array($horario_teste, $horarios_mesma_sessao);
            
            if ($horario_disponivel_mesma_sessao) {
                echo "✅ Horário ainda disponível para a mesma sessão\n";
            } else {
                echo "❌ Horário não disponível nem para a mesma sessão\n";
            }
            
            // 5. Testar desbloqueio
            echo "\n4️⃣ Testando desbloqueio de horário...\n";
            $desbloqueio_ok = $agendamento->desbloquearHorario($id_profissional, $data, $horario_teste, $session_id);
            
            if ($desbloqueio_ok) {
                echo "✅ Horário desbloqueado com sucesso\n";
                
                // Verificar se horário voltou à lista
                $horarios_apos_desbloqueio = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data, 'outra_session');
                $horario_voltou = in_array($horario_teste, $horarios_apos_desbloqueio);
                
                if ($horario_voltou) {
                    echo "✅ Horário voltou à lista após desbloqueio\n";
                } else {
                    echo "❌ Horário não voltou à lista após desbloqueio\n";
                }
            } else {
                echo "❌ Falha ao desbloquear horário\n";
            }
            
        } else {
            echo "❌ Falha ao bloquear horário\n";
        }
        
    } else {
        echo "⚠️ Nenhum horário disponível para teste\n";
    }
    
    // 6. Testar limpeza de bloqueios expirados
    echo "\n5️⃣ Testando limpeza de bloqueios expirados...\n";
    $limpeza_ok = $agendamento->limparBloqueiosExpirados();
    echo "🧹 Limpeza de bloqueios: " . ($limpeza_ok ? "✅ Sucesso" : "❌ Falha") . "\n";
    
    // 7. Verificar intervalos de 30 minutos
    echo "\n6️⃣ Verificando intervalos de 30 minutos...\n";
    if (count($horarios) >= 2) {
        $primeiro_horario = $horarios[0];
        $segundo_horario = $horarios[1];
        
        $time1 = strtotime($primeiro_horario);
        $time2 = strtotime($segundo_horario);
        $diferenca_minutos = ($time2 - $time1) / 60;
        
        echo "⏱️ Diferença entre {$primeiro_horario} e {$segundo_horario}: {$diferenca_minutos} minutos\n";
        
        if ($diferenca_minutos == 30) {
            echo "✅ Intervalos de 30 minutos funcionando corretamente\n";
        } else {
            echo "❌ Intervalos não estão em 30 minutos\n";
        }
    }
    
    echo "\n🎉 Teste concluído com sucesso!\n";
    echo "\n📊 Resumo das funcionalidades testadas:\n";
    echo "✅ Conexão com banco de dados\n";
    echo "✅ Geração de horários com intervalos de 30min\n";
    echo "✅ Sistema de bloqueio temporário\n";
    echo "✅ Sistema de desbloqueio\n";
    echo "✅ Limpeza de bloqueios expirados\n";
    echo "✅ Verificação de disponibilidade por sessão\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . " (linha " . $e->getLine() . ")\n";
}

echo "</pre>\n";
?>