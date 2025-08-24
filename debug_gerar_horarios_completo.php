<?php
/**
 * Debug completo do método gerarHorariosDisponiveis
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>🔍 Debug Completo gerarHorariosDisponiveis</h2>";

// Parâmetros de teste
$id_profissional = 1;
$data = '2025-08-23';

echo "<p class='info'>Testando com profissional ID: {$id_profissional}, data: {$data}</p>";

// 1. Instanciar Agendamento
echo "<h3>1. Instanciando Agendamento</h3>";
$agendamento = new Agendamento();
echo "<p class='success'>✅ Agendamento instanciado</p>";

// 2. Adicionar debug temporário ao método
echo "<h3>2. Executando gerarHorariosDisponiveis com Debug</h3>";

// Usar reflexão para acessar métodos privados se necessário
$reflection = new ReflectionClass($agendamento);

// Verificar se o método existe
if ($reflection->hasMethod('gerarHorariosDisponiveis')) {
    echo "<p class='success'>✅ Método gerarHorariosDisponiveis encontrado</p>";
    
    try {
        // Capturar output e erros
        ob_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Executar o método
        $horarios = $agendamento->gerarHorariosDisponiveis($id_profissional, $data);
        
        $output = ob_get_clean();
        
        if ($output) {
            echo "<p class='info'>Output capturado: <pre>{$output}</pre></p>";
        }
        
        echo "<p class='success'>✅ Método executado com sucesso</p>";
        echo "<p class='info'>Horários retornados: " . count($horarios) . "</p>";
        
        if (!empty($horarios)) {
            echo "<p class='info'>Primeiros horários: " . implode(', ', array_slice($horarios, 0, 5)) . "</p>";
        }
        
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo "<p class='error'>❌ Erro no método: {$e->getMessage()}</p>";
        echo "<p class='error'>Arquivo: {$e->getFile()}, Linha: {$e->getLine()}</p>";
        echo "<p class='error'>Stack trace: <pre>{$e->getTraceAsString()}</pre></p>";
        
        if ($output) {
            echo "<p class='info'>Output antes do erro: <pre>{$output}</pre></p>";
        }
    }
} else {
    echo "<p class='error'>❌ Método gerarHorariosDisponiveis não encontrado</p>";
}

// 3. Verificar logs de erro recentes
echo "<h3>3. Verificando Logs de Erro</h3>";

$error_log_paths = [
    'C:\\xampp\\apache\\logs\\error.log',
    'C:\\xampp\\php\\logs\\php_error_log',
    './error.log',
    '../error.log'
];

$found_logs = false;
foreach ($error_log_paths as $log_path) {
    if (file_exists($log_path)) {
        $found_logs = true;
        echo "<p class='success'>✅ Log encontrado: {$log_path}</p>";
        
        // Ler últimas 10 linhas
        $lines = file($log_path);
        if ($lines) {
            $recent_lines = array_slice($lines, -10);
            echo "<p class='info'>Últimas 10 linhas:</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
            foreach ($recent_lines as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        }
        break;
    }
}

if (!$found_logs) {
    echo "<p class='error'>❌ Nenhum log de erro encontrado</p>";
}

// 4. Testar método gerarHorariosDisponiveisComBloqueios também
echo "<h3>4. Testando gerarHorariosDisponiveisComBloqueios</h3>";

if ($reflection->hasMethod('gerarHorariosDisponiveisComBloqueios')) {
    try {
        ob_start();
        $horarios_com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data);
        $output2 = ob_get_clean();
        
        if ($output2) {
            echo "<p class='info'>Output capturado: <pre>{$output2}</pre></p>";
        }
        
        echo "<p class='success'>✅ Método gerarHorariosDisponiveisComBloqueios executado</p>";
        echo "<p class='info'>Horários com bloqueios: " . count($horarios_com_bloqueios) . "</p>";
        
    } catch (Exception $e) {
        $output2 = ob_get_clean();
        echo "<p class='error'>❌ Erro no método com bloqueios: {$e->getMessage()}</p>";
        
        if ($output2) {
            echo "<p class='info'>Output antes do erro: <pre>{$output2}</pre></p>";
        }
    }
} else {
    echo "<p class='error'>❌ Método gerarHorariosDisponiveisComBloqueios não encontrado</p>";
}

echo "<hr>";
echo "<p><strong>Debug completo concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
pre { white-space: pre-wrap; word-wrap: break-word; }
</style>