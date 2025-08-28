<?php
/**
 * Script para criar usuários de teste
 * Cria 1 ADM, 1 Cliente e 1 Parceiro para testes do sistema
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    echo "=== CRIANDO USUÁRIOS DE TESTE ===\n\n";
    
    // Senha padrão para todos os usuários de teste
    $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
    
    // 1. Criar usuário ADMIN
    echo "1. Criando usuário ADMIN...\n";
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES (?, ?, ?, ?, ?)");
    $admin_result = $stmt->execute([
        'Admin Teste',
        'admin@teste.com',
        $senha_hash,
        'admin',
        '(11) 99999-0001'
    ]);
    
    if ($admin_result) {
        $admin_id = $conn->lastInsertId();
        echo "✅ Admin criado com sucesso! ID: {$admin_id}\n";
        echo "   Email: admin@teste.com\n";
        echo "   Senha: 123456\n\n";
    } else {
        echo "❌ Erro ao criar admin\n\n";
    }
    
    // 2. Criar usuário CLIENTE
    echo "2. Criando usuário CLIENTE...\n";
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES (?, ?, ?, ?, ?)");
    $cliente_result = $stmt->execute([
        'Cliente Teste',
        'cliente@teste.com',
        $senha_hash,
        'cliente',
        '(11) 99999-0002'
    ]);
    
    if ($cliente_result) {
        $cliente_id = $conn->lastInsertId();
        echo "✅ Cliente criado com sucesso! ID: {$cliente_id}\n";
        echo "   Email: cliente@teste.com\n";
        echo "   Senha: 123456\n\n";
    } else {
        echo "❌ Erro ao criar cliente\n\n";
    }
    
    // 3. Criar usuário PARCEIRO
    echo "3. Criando usuário PARCEIRO...\n";
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES (?, ?, ?, ?, ?)");
    $parceiro_result = $stmt->execute([
        'Parceiro Teste',
        'parceiro@teste.com',
        $senha_hash,
        'parceiro',
        '(11) 99999-0003'
    ]);
    
    if ($parceiro_result) {
        $parceiro_id = $conn->lastInsertId();
        echo "✅ Parceiro criado com sucesso! ID: {$parceiro_id}\n";
        echo "   Email: parceiro@teste.com\n";
        echo "   Senha: 123456\n\n";
        
        // Criar um salão para o parceiro
        echo "4. Criando salão para o parceiro...\n";
        $stmt = $conn->prepare("INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao) VALUES (?, ?, ?, ?, ?)");
        $salao_result = $stmt->execute([
            $parceiro_id,
            'Salão Teste',
            'Rua das Flores, 123 - Centro - São Paulo/SP - CEP: 01234-567',
            '(11) 3333-4444',
            'Salão de beleza para testes do sistema CorteF\u00e1cil'
        ]);
        
        if ($salao_result) {
            $salao_id = $conn->lastInsertId();
            echo "✅ Salão criado com sucesso! ID: {$salao_id}\n";
            echo "   Nome: Salão Teste\n";
            echo "   Dono: Parceiro Teste (ID: {$parceiro_id})\n\n";
            
            // Criar profissionais para o salão
            echo "5. Criando profissionais para o salão...\n";
            $profissionais = [
                ['João Silva', 'Corte Masculino'],
                ['Maria Santos', 'Corte Feminino'],
                ['Pedro Costa', 'Barba e Bigode']
            ];
            
            foreach ($profissionais as $index => $prof) {
                $stmt = $conn->prepare("INSERT INTO profissionais (id_salao, nome, especialidade) VALUES (?, ?, ?)");
                $prof_result = $stmt->execute([$salao_id, $prof[0], $prof[1]]);
                
                if ($prof_result) {
                    $prof_id = $conn->lastInsertId();
                    echo "   ✅ {$prof[0]} - {$prof[1]} (ID: {$prof_id})\n";
                }
            }
        } else {
            echo "❌ Erro ao criar salão\n";
        }
    } else {
        echo "❌ Erro ao criar parceiro\n\n";
    }
    
    echo "\n=== RESUMO DOS USUÁRIOS CRIADOS ===\n";
    echo "1. ADMIN: admin@teste.com | Senha: 123456\n";
    echo "2. CLIENTE: cliente@teste.com | Senha: 123456\n";
    echo "3. PARCEIRO: parceiro@teste.com | Senha: 123456\n";
    echo "\n✅ Todos os usuários foram criados com sucesso!\n";
    echo "\n💡 Você pode usar essas credenciais para testar o sistema.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "\n🔍 Verifique se:\n";
    echo "- O banco de dados está rodando\n";
    echo "- As credenciais estão corretas\n";
    echo "- As tabelas foram criadas (execute schema.sql)\n";
}
?>