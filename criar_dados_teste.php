<?php
/**
 * Script para criar dados de teste
 * Cria salão e profissional de teste se não existirem
 */

require_once 'config/database.php';

echo "<h2>Criação de Dados de Teste</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se existe usuário parceiro
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'parceiro'");
    $parceiros = $stmt->fetch()['total'];
    
    $id_parceiro = 1;
    if ($parceiros == 0) {
        echo "<h3>1. Criando usuário parceiro de teste</h3>";
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, telefone, tipo, status) VALUES (?, ?, ?, ?, ?, ?)");
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt->execute(['Parceiro Teste', 'parceiro@teste.com', $senha_hash, '(11) 99999-9999', 'parceiro', 'ativo']);
        $id_parceiro = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ Usuário parceiro criado com ID: {$id_parceiro}</p>";
    } else {
        $stmt = $pdo->query("SELECT id FROM usuarios WHERE tipo = 'parceiro' LIMIT 1");
        $id_parceiro = $stmt->fetch()['id'];
        echo "<p>✓ Usando parceiro existente ID: {$id_parceiro}</p>";
    }
    
    // Verificar se existe salão
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes WHERE status = 'ativo'");
    $saloes = $stmt->fetch()['total'];
    
    $id_salao = null;
    if ($saloes == 0) {
        echo "<h3>2. Criando salão de teste</h3>";
        $stmt = $pdo->prepare("INSERT INTO saloes (nome, endereco, telefone, horario_funcionamento, status, id_parceiro) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Salão CorteFácil Teste',
            'Rua das Flores, 123 - Centro',
            '(11) 3333-4444',
            '08:00-18:00',
            'ativo',
            $id_parceiro
        ]);
        $id_salao = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ Salão criado com ID: {$id_salao}</p>";
    } else {
        $stmt = $pdo->query("SELECT id FROM saloes WHERE status = 'ativo' LIMIT 1");
        $id_salao = $stmt->fetch()['id'];
        echo "<p>✓ Usando salão existente ID: {$id_salao}</p>";
    }
    
    // Verificar se existem profissionais
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM profissionais WHERE id_salao = ? AND status = 'ativo'");
    $stmt->execute([$id_salao]);
    $profissionais = $stmt->fetch()['total'];
    
    if ($profissionais == 0) {
        echo "<h3>3. Criando profissionais de teste</h3>";
        
        $profissionais_teste = [
            ['João Silva', 'Corte Masculino e Barba', '(11) 98888-1111'],
            ['Maria Santos', 'Corte Feminino e Escova', '(11) 98888-2222'],
            ['Pedro Costa', 'Corte e Coloração', '(11) 98888-3333']
        ];
        
        foreach ($profissionais_teste as $prof) {
            $stmt = $pdo->prepare("INSERT INTO profissionais (nome, especialidade, telefone, status, id_salao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$prof[0], $prof[1], $prof[2], 'ativo', $id_salao]);
            $id_prof = $pdo->lastInsertId();
            echo "<p style='color: green;'>✓ Profissional '{$prof[0]}' criado com ID: {$id_prof}</p>";
        }
    } else {
        echo "<p>✓ Já existem {$profissionais} profissionais ativos no salão</p>";
    }
    
    // Verificar se existe cliente de teste
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'");
    $clientes = $stmt->fetch()['total'];
    
    if ($clientes == 0) {
        echo "<h3>4. Criando cliente de teste</h3>";
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, telefone, tipo, status) VALUES (?, ?, ?, ?, ?, ?)");
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt->execute(['Cliente Teste', 'cliente@teste.com', $senha_hash, '(11) 97777-8888', 'cliente', 'ativo']);
        $id_cliente = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ Cliente de teste criado com ID: {$id_cliente}</p>";
    } else {
        echo "<p>✓ Já existem {$clientes} clientes cadastrados</p>";
    }
    
    echo "<hr>";
    echo "<h3>5. Resumo dos dados criados</h3>";
    
    // Listar salões ativos
    $stmt = $pdo->query("SELECT id, nome, status FROM saloes WHERE status = 'ativo'");
    $saloes_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>Salões ativos:</strong></p>";
    foreach ($saloes_ativos as $salao) {
        echo "<p>• ID: {$salao['id']} - {$salao['nome']}</p>";
    }
    
    // Listar profissionais ativos
    $stmt = $pdo->query("SELECT p.id, p.nome, p.especialidade, s.nome as salao_nome 
                        FROM profissionais p 
                        JOIN saloes s ON p.id_salao = s.id 
                        WHERE p.status = 'ativo'");
    $profissionais_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>Profissionais ativos:</strong></p>";
    foreach ($profissionais_ativos as $prof) {
        echo "<p>• ID: {$prof['id']} - {$prof['nome']} ({$prof['especialidade']}) - Salão: {$prof['salao_nome']}</p>";
    }
    
    echo "<hr>";
    echo "<h3>6. Teste da API de horários</h3>";
    
    if (!empty($profissionais_ativos)) {
        $prof_teste = $profissionais_ativos[0];
        $data_teste = date('Y-m-d', strtotime('+1 day'));
        
        echo "<p>Testando API com:</p>";
        echo "<ul>";
        echo "<li>Profissional: {$prof_teste['nome']} (ID: {$prof_teste['id']})</li>";
        echo "<li>Data: {$data_teste}</li>";
        echo "</ul>";
        
        $url_teste = "http://localhost/cortefacil/cortefacilapp/api/horarios.php?profissional={$prof_teste['id']}&data={$data_teste}";
        echo "<p><a href='{$url_teste}' target='_blank'>🔗 Testar API de horários</a></p>";
        
        echo "<p><a href='cliente/agendar.php' target='_blank'>🔗 Ir para página de agendamento</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Criação de dados de teste concluída!</strong></p>";
?>