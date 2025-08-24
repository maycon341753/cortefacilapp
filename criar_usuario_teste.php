<?php
require_once 'config/database.php';

echo "=== CRIANDO USUÁRIO DE TESTE ===\n";

try {
    $conn = getConnection();
    
    // Dados do usuário de teste
    $nome = 'Cliente Teste';
    $email = 'cliente@teste.com';
    $senha = password_hash('123456', PASSWORD_DEFAULT);
    $tipo_usuario = 'cliente';
    $cpf = '12345678901';
    $telefone = '11999999999';
    
    // Verificar se usuário já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "⚠ Usuário já existe. Atualizando dados...\n";
        
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET nome = ?, senha = ?, tipo_usuario = ?, cpf = ?, telefone = ?
            WHERE email = ?
        ");
        
        $stmt->execute([$nome, $senha, $tipo_usuario, $cpf, $telefone, $email]);
        echo "✓ Usuário atualizado com sucesso\n";
    } else {
        echo "Criando novo usuário...\n";
        
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo_usuario, cpf, telefone) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$nome, $email, $senha, $tipo_usuario, $cpf, $telefone]);
        echo "✓ Usuário criado com sucesso\n";
    }
    
    // Buscar o usuário criado/atualizado
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n=== DADOS DO USUÁRIO ===\n";
    echo "ID: " . $usuario['id'] . "\n";
    echo "Nome: " . $usuario['nome'] . "\n";
    echo "Email: " . $usuario['email'] . "\n";
    echo "Tipo: " . $usuario['tipo_usuario'] . "\n";
    echo "CPF: " . $usuario['cpf'] . "\n";
    echo "Telefone: " . $usuario['telefone'] . "\n";
    
    echo "\n=== CREDENCIAIS PARA TESTE ===\n";
    echo "Email: $email\n";
    echo "Senha: 123456\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== USUÁRIO DE TESTE PRONTO ===\n";
?>