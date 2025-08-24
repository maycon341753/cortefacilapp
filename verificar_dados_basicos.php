<?php
/**
 * Verificação de dados básicos do sistema
 * Verifica se existem salões, profissionais e usuários cadastrados
 */

require_once 'config/database.php';

echo "<h2>Verificação de Dados Básicos do Sistema</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar usuários
    echo "<h3>1. Usuários</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total, tipo FROM usuarios GROUP BY tipo");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "<p style='color: red;'>❌ Nenhum usuário encontrado!</p>";
    } else {
        foreach ($usuarios as $user) {
            echo "<p>✓ {$user['tipo']}: {$user['total']} usuários</p>";
        }
    }
    
    // Verificar salões
    echo "<h3>2. Salões</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes");
    $total_saloes = $stmt->fetch()['total'];
    echo "<p>Total de salões: {$total_saloes}</p>";
    
    if ($total_saloes > 0) {
        $stmt = $pdo->query("SELECT id, nome, status, horario_funcionamento FROM saloes LIMIT 5");
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Status</th><th>Horário Funcionamento</th></tr>";
        foreach ($saloes as $salao) {
            $status_color = $salao['status'] === 'ativo' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$salao['id']}</td>";
            echo "<td>{$salao['nome']}</td>";
            echo "<td style='color: {$status_color};'>{$salao['status']}</td>";
            echo "<td>{$salao['horario_funcionamento']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum salão encontrado!</p>";
    }
    
    // Verificar profissionais
    echo "<h3>3. Profissionais</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM profissionais");
    $total_profissionais = $stmt->fetch()['total'];
    echo "<p>Total de profissionais: {$total_profissionais}</p>";
    
    if ($total_profissionais > 0) {
        $stmt = $pdo->query("SELECT p.id, p.nome, p.status, p.especialidade, s.nome as salao_nome 
                            FROM profissionais p 
                            LEFT JOIN saloes s ON p.id_salao = s.id 
                            LIMIT 5");
        $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Status</th><th>Especialidade</th><th>Salão</th></tr>";
        foreach ($profissionais as $prof) {
            $status_color = $prof['status'] === 'ativo' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$prof['id']}</td>";
            echo "<td>{$prof['nome']}</td>";
            echo "<td style='color: {$status_color};'>{$prof['status']}</td>";
            echo "<td>{$prof['especialidade']}</td>";
            echo "<td>{$prof['salao_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum profissional encontrado!</p>";
    }
    
    // Verificar agendamentos
    echo "<h3>4. Agendamentos</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
    $total_agendamentos = $stmt->fetch()['total'];
    echo "<p>Total de agendamentos: {$total_agendamentos}</p>";
    
    if ($total_agendamentos > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) as total, status FROM agendamentos GROUP BY status");
        $agendamentos_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($agendamentos_status as $status) {
            echo "<p>✓ {$status['status']}: {$status['total']} agendamentos</p>";
        }
    }
    
    // Verificar estrutura das tabelas
    echo "<h3>5. Estrutura das Tabelas</h3>";
    
    $tabelas = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
    
    foreach ($tabelas as $tabela) {
        echo "<h4>Tabela: {$tabela}</h4>";
        try {
            $stmt = $pdo->query("DESCRIBE {$tabela}");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
            foreach ($colunas as $coluna) {
                echo "<tr>";
                echo "<td>{$coluna['Field']}</td>";
                echo "<td>{$coluna['Type']}</td>";
                echo "<td>{$coluna['Null']}</td>";
                echo "<td>{$coluna['Key']}</td>";
                echo "<td>{$coluna['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "</p>";
        }
    }
    
    // Criar dados de teste se necessário
    echo "<h3>6. Sugestões de Correção</h3>";
    
    if ($total_saloes == 0) {
        echo "<p style='color: orange;'>⚠️ Recomendação: Criar salão de teste</p>";
        echo "<pre>INSERT INTO saloes (nome, endereco, telefone, horario_funcionamento, status, id_parceiro) 
VALUES ('Salão Teste', 'Rua Teste, 123', '(11) 99999-9999', '08:00-18:00', 'ativo', 1);</pre>";
    }
    
    if ($total_profissionais == 0) {
        echo "<p style='color: orange;'>⚠️ Recomendação: Criar profissional de teste</p>";
        echo "<pre>INSERT INTO profissionais (nome, especialidade, telefone, status, id_salao) 
VALUES ('Profissional Teste', 'Corte e Barba', '(11) 88888-8888', 'ativo', 1);</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";
?>