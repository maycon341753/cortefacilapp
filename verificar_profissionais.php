<?php
/**
 * Script para verificar profissionais no banco de dados
 */

require_once 'config/database.php';

echo "<h2>Verificação de Profissionais</h2>";

try {
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela profissionais existe
    $stmt = $conn->query("SHOW TABLES LIKE 'profissionais'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'profissionais' não existe!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Tabela 'profissionais' existe</p>";
    
    // Buscar todos os profissionais
    $stmt = $conn->query('SELECT id, nome, id_salao, ativo FROM profissionais ORDER BY id_salao, id');
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Profissionais encontrados: " . count($profissionais) . "</h3>";
    
    if (count($profissionais) == 0) {
        echo "<p style='color: red;'>❌ Nenhum profissional encontrado no banco de dados!</p>";
        echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
        echo "<p><strong>SOLUÇÃO:</strong> É necessário cadastrar profissionais para o salão.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>ID Salão</th><th>Status</th></tr>";
        
        foreach($profissionais as $prof) {
            $status = $prof['ativo'] ? 'ATIVO' : 'INATIVO';
            $cor = $prof['ativo'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$prof['id']}</td>";
            echo "<td>{$prof['nome']}</td>";
            echo "<td>{$prof['id_salao']}</td>";
            echo "<td style='color: {$cor}; font-weight: bold;'>{$status}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Verificar profissionais do salão 11
        $profissionais_salao_11 = array_filter($profissionais, function($p) { return $p['id_salao'] == 11; });
        echo "<h4>Profissionais do Salão 11: " . count($profissionais_salao_11) . "</h4>";
        
        if (count($profissionais_salao_11) == 0) {
            echo "<p style='color: red;'>❌ Nenhum profissional cadastrado para o salão ID 11!</p>";
            echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
            echo "<p><strong>SOLUÇÃO:</strong> É necessário cadastrar profissionais para o salão ID 11.</p>";
        } else {
            $profissionais_ativos_salao_11 = array_filter($profissionais_salao_11, function($p) { return $p['ativo']; });
            echo "<p>Profissionais ativos do salão 11: " . count($profissionais_ativos_salao_11) . "</p>";
            
            if (count($profissionais_ativos_salao_11) == 0) {
                echo "<p style='color: red;'>❌ Nenhum profissional ativo para o salão ID 11!</p>";
                echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
                echo "<p><strong>SOLUÇÃO:</strong> É necessário ativar os profissionais do salão ID 11.</p>";
            }
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>