<?php
// Teste final do cadastro de profissionais após correções
require_once 'config/database.php';
require_once 'includes/functions.php';

// Garantir que temos a conexão PDO
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=cortefacil;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Conexão com banco de dados estabelecida<br><br>";
    } catch (PDOException $e) {
        die("❌ Erro de conexão: " . $e->getMessage());
    }
}

echo "<h2>Teste Final - Cadastro de Profissionais</h2>";

// 1. Verificar se a tabela especialidades existe e tem dados
echo "<h3>1. Verificando tabela especialidades:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM especialidades");
    $count = $stmt->fetch()['total'];
    echo "✅ Tabela especialidades existe com {$count} registros<br>";
    
    // Listar algumas especialidades
    $stmt = $pdo->query("SELECT id, nome FROM especialidades LIMIT 5");
    echo "Especialidades disponíveis:<br>";
    while ($esp = $stmt->fetch()) {
        echo "- ID {$esp['id']}: {$esp['nome']}<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro na tabela especialidades: " . $e->getMessage() . "<br>";
}

// 2. Verificar estrutura da tabela profissionais
echo "<h3>2. Verificando tabela profissionais:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE profissionais");
    echo "Estrutura da tabela profissionais:<br>";
    while ($col = $stmt->fetch()) {
        echo "- {$col['Field']} ({$col['Type']})";
        if ($col['Key'] == 'PRI') echo " [PRIMARY KEY]";
        if ($col['Key'] == 'MUL') echo " [FOREIGN KEY]";
        echo "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro na tabela profissionais: " . $e->getMessage() . "<br>";
}

// 3. Teste de cadastro simulado
echo "<h3>3. Teste de cadastro simulado:</h3>";
try {
    // Dados de teste
    $nome = "Profissional Teste " . date('His');
    $especialidade_id = 1; // Corte de Cabelo
    $telefone = "(11) 99999-9999";
    $email = "teste" . date('His') . "@teste.com";
    $salao_id = 1; // Assumindo que existe um salão com ID 1
    
    echo "Dados do teste:<br>";
    echo "- Nome: {$nome}<br>";
    echo "- Especialidade ID: {$especialidade_id}<br>";
    echo "- Telefone: {$telefone}<br>";
    echo "- Email: {$email}<br>";
    echo "- Salão ID: {$salao_id}<br><br>";
    
    // Verificar se o salão existe
    $stmt = $pdo->prepare("SELECT id, nome FROM saloes WHERE id = ?");
    $stmt->execute([$salao_id]);
    $salao = $stmt->fetch();
    
    if (!$salao) {
        echo "❌ Salão com ID {$salao_id} não encontrado. Criando salão de teste...<br>";
        
        // Criar salão de teste
        $stmt = $pdo->prepare("INSERT INTO saloes (nome, endereco, telefone, email, id_dono) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Salão Teste',
            'Rua Teste, 123',
            '(11) 1234-5678',
            'salao@teste.com',
            1 // Assumindo que existe um usuário com ID 1
        ]);
        $salao_id = $pdo->lastInsertId();
        echo "✅ Salão de teste criado com ID: {$salao_id}<br>";
    } else {
        echo "✅ Salão encontrado: {$salao['nome']}<br>";
    }
    
    // Tentar cadastrar o profissional
    $stmt = $pdo->prepare("
        INSERT INTO profissionais (nome, especialidade_id, telefone, email, salao_id, data_cadastro) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $resultado = $stmt->execute([$nome, $especialidade_id, $telefone, $email, $salao_id]);
    
    if ($resultado) {
        $profissional_id = $pdo->lastInsertId();
        echo "✅ SUCESSO! Profissional cadastrado com ID: {$profissional_id}<br>";
        
        // Verificar se foi realmente inserido
        $stmt = $pdo->prepare("
            SELECT p.*, e.nome as especialidade_nome, s.nome as salao_nome 
            FROM profissionais p 
            JOIN especialidades e ON p.especialidade_id = e.id 
            JOIN saloes s ON p.salao_id = s.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$profissional_id]);
        $prof = $stmt->fetch();
        
        if ($prof) {
            echo "<br><strong>Dados do profissional cadastrado:</strong><br>";
            echo "- ID: {$prof['id']}<br>";
            echo "- Nome: {$prof['nome']}<br>";
            echo "- Especialidade: {$prof['especialidade_nome']}<br>";
            echo "- Telefone: {$prof['telefone']}<br>";
            echo "- Email: {$prof['email']}<br>";
            echo "- Salão: {$prof['salao_nome']}<br>";
            echo "- Data de Cadastro: {$prof['data_cadastro']}<br>";
        }
    } else {
        echo "❌ ERRO ao cadastrar profissional<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Erro SQL: " . $errorInfo[2] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO no teste de cadastro: " . $e->getMessage() . "<br>";
}

// 4. Estatísticas finais
echo "<h3>4. Estatísticas do sistema:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM especialidades");
    $total_esp = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM profissionais");
    $total_prof = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes");
    $total_saloes = $stmt->fetch()['total'];
    
    echo "📊 <strong>Resumo do sistema:</strong><br>";
    echo "- Total de especialidades: {$total_esp}<br>";
    echo "- Total de profissionais: {$total_prof}<br>";
    echo "- Total de salões: {$total_saloes}<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao obter estatísticas: " . $e->getMessage() . "<br>";
}

echo "<br><hr><br>";
echo "<strong>🎉 TESTE CONCLUÍDO!</strong><br>";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "<br>";
?>