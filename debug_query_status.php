<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>Debug Query Status - Teste Direto</h2>";

try {
    // Conectar ao banco usando Singleton
    $db = Database::getInstance();
    $conn = $db->connect();
    
    echo "<h3>1. Informações da Conexão</h3>";
    echo "<p>Banco conectado: " . $conn->query("SELECT DATABASE()")->fetchColumn() . "</p>";
    echo "<p>Host: " . $conn->query("SELECT @@hostname")->fetchColumn() . "</p>";
    
    echo "<h3>2. Testando Queries Diretas</h3>";
    
    // Teste 1: Query simples na tabela agendamentos
    echo "<h4>Teste 1: SELECT básico</h4>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✅ Total de agendamentos: $count</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    // Teste 2: Query com coluna status
    echo "<h4>Teste 2: SELECT com coluna status</h4>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos WHERE status != 'cancelado'");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✅ Agendamentos não cancelados: $count</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    // Teste 3: Query com id_profissional
    echo "<h4>Teste 3: SELECT com id_profissional</h4>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos WHERE id_profissional = 1");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✅ Agendamentos do profissional 1: $count</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    // Teste 4: Query completa como no método listarHorariosOcupados
    echo "<h4>Teste 4: Query completa do método</h4>";
    try {
        $sql = "SELECT data, hora FROM agendamentos WHERE id_profissional = ? AND data = ? AND status != 'cancelado'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([1, '2024-01-15']);
        $result = $stmt->fetchAll();
        echo "<p class='success'>✅ Query preparada funcionou: " . count($result) . " resultados</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>3. Testando Métodos da Classe Agendamento</h3>";
    
    // Instanciar classe
    $agendamento = new Agendamento();
    
    // Teste do método listarHorariosOcupados
    echo "<h4>Teste do método listarHorariosOcupados</h4>";
    try {
        $horarios = $agendamento->listarHorariosOcupados(1, '2024-01-15');
        echo "<p class='success'>✅ listarHorariosOcupados: " . count($horarios) . " horários</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no listarHorariosOcupados: " . $e->getMessage() . "</p>";
        echo "<p class='error'>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    }
    
    // Teste do método gerarHorariosDisponiveis
    echo "<h4>Teste do método gerarHorariosDisponiveis</h4>";
    try {
        $horarios = $agendamento->gerarHorariosDisponiveis(1, '2024-01-15');
        echo "<p class='success'>✅ gerarHorariosDisponiveis: " . count($horarios) . " horários</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no gerarHorariosDisponiveis: " . $e->getMessage() . "</p>";
        echo "<p class='error'>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    }
    
    echo "<h3>4. Verificando Estrutura da Tabela</h3>";
    try {
        $stmt = $conn->query("DESCRIBE agendamentos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Colunas da tabela agendamentos:</strong></p>";
        echo "<ul>";
        foreach ($columns as $column) {
            $highlight = ($column['Field'] == 'status' || $column['Field'] == 'id_profissional') ? ' style="background-color: yellow;"' : '';
            echo "<li$highlight>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Debug concluído!</strong></p>";
?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>