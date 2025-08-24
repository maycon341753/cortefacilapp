<?php
/**
 * Script para verificar serviços no banco de dados
 */

require_once 'config/database.php';

echo "<h2>Verificação de Serviços</h2>";

try {
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela servicos existe
    $stmt = $conn->query("SHOW TABLES LIKE 'servicos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'servicos' não existe!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Tabela 'servicos' existe</p>";
    
    // Buscar todos os serviços
    $stmt = $conn->query('SELECT id, nome, id_salao, ativo, preco FROM servicos ORDER BY id_salao, id');
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Serviços encontrados: " . count($servicos) . "</h3>";
    
    if (count($servicos) == 0) {
        echo "<p style='color: red;'>❌ Nenhum serviço encontrado no banco de dados!</p>";
        echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
        echo "<p><strong>SOLUÇÃO:</strong> É necessário cadastrar serviços para o salão.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>ID Salão</th><th>Preço</th><th>Status</th></tr>";
        
        foreach($servicos as $servico) {
            $status = $servico['ativo'] ? 'ATIVO' : 'INATIVO';
            $cor = $servico['ativo'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$servico['id']}</td>";
            echo "<td>{$servico['nome']}</td>";
            echo "<td>{$servico['id_salao']}</td>";
            echo "<td>R$ " . number_format($servico['preco'], 2, ',', '.') . "</td>";
            echo "<td style='color: {$cor}; font-weight: bold;'>{$status}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Verificar serviços do salão 11
        $servicos_salao_11 = array_filter($servicos, function($s) { return $s['id_salao'] == 11; });
        echo "<h4>Serviços do Salão 11: " . count($servicos_salao_11) . "</h4>";
        
        if (count($servicos_salao_11) == 0) {
            echo "<p style='color: red;'>❌ Nenhum serviço cadastrado para o salão ID 11!</p>";
            echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
            echo "<p><strong>SOLUÇÃO:</strong> É necessário cadastrar serviços para o salão ID 11.</p>";
        } else {
            $servicos_ativos_salao_11 = array_filter($servicos_salao_11, function($s) { return $s['ativo']; });
            echo "<p>Serviços ativos do salão 11: " . count($servicos_ativos_salao_11) . "</p>";
            
            if (count($servicos_ativos_salao_11) == 0) {
                echo "<p style='color: red;'>❌ Nenhum serviço ativo para o salão ID 11!</p>";
                echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
                echo "<p><strong>SOLUÇÃO:</strong> É necessário ativar os serviços do salão ID 11.</p>";
            }
        }
    }
    
    // Verificar também a tabela profissional_servicos se existir
    $stmt = $conn->query("SHOW TABLES LIKE 'profissional_servicos'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>Verificando Associação Profissional-Serviços</h3>";
        
        $stmt = $conn->query('SELECT ps.*, p.nome as profissional_nome, s.nome as servico_nome FROM profissional_servicos ps LEFT JOIN profissionais p ON ps.id_profissional = p.id LEFT JOIN servicos s ON ps.id_servico = s.id');
        $associacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Associações profissional-serviço: " . count($associacoes) . "</p>";
        
        if (count($associacoes) == 0) {
            echo "<p style='color: red;'>❌ Nenhuma associação entre profissionais e serviços!</p>";
            echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
            echo "<p><strong>SOLUÇÃO:</strong> É necessário associar os profissionais aos serviços.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID Profissional</th><th>Profissional</th><th>ID Serviço</th><th>Serviço</th></tr>";
            
            foreach($associacoes as $assoc) {
                echo "<tr>";
                echo "<td>{$assoc['id_profissional']}</td>";
                echo "<td>{$assoc['profissional_nome']}</td>";
                echo "<td>{$assoc['id_servico']}</td>";
                echo "<td>{$assoc['servico_nome']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Verificar associações do profissional Pedro (ID 21)
            $associacoes_pedro = array_filter($associacoes, function($a) { return $a['id_profissional'] == 21; });
            echo "<p>Associações do profissional Pedro (ID 21): " . count($associacoes_pedro) . "</p>";
            
            if (count($associacoes_pedro) == 0) {
                echo "<p style='color: red;'>❌ O profissional Pedro não está associado a nenhum serviço!</p>";
                echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
                echo "<p><strong>SOLUÇÃO:</strong> É necessário associar o profissional Pedro aos serviços.</p>";
            }
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>