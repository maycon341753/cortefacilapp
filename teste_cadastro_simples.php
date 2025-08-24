<?php
// Teste simples do cadastro de profissionais
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cortefacil;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão estabelecida\n";
    
    // Verificar tabela especialidades
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM especialidades");
    $count = $stmt->fetch()['total'];
    echo "✅ Especialidades: {$count} registros\n";
    
    // Verificar tabela profissionais
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM profissionais");
    $count = $stmt->fetch()['total'];
    echo "✅ Profissionais: {$count} registros\n";
    
    // Verificar tabela salões
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes");
    $count = $stmt->fetch()['total'];
    echo "✅ Salões: {$count} registros\n";
    
    // Teste de cadastro
    $nome = "Teste Prof " . date('His');
    $stmt = $pdo->prepare("INSERT INTO profissionais (nome, especialidade_id, telefone, email, id_salao, data_cadastro) VALUES (?, 1, '11999999999', ?, 1, NOW())");
    $email = "teste" . date('His') . "@teste.com";
    
    if ($stmt->execute([$nome, $email])) {
        $id = $pdo->lastInsertId();
        echo "✅ SUCESSO! Profissional cadastrado com ID: {$id}\n";
        
        // Verificar se foi inserido
        $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE id = ?");
        $stmt->execute([$id]);
        $prof = $stmt->fetch();
        
        if ($prof) {
            echo "✅ Confirmado: {$prof['nome']} - {$prof['email']}\n";
        }
    } else {
        echo "❌ ERRO no cadastro\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n🎉 TESTE CONCLUÍDO - " . date('d/m/Y H:i:s') . "\n";
?>