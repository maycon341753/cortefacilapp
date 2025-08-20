<?php
/**
 * Script para configurar banco online e resetar senha do administrador
 * Execute este arquivo uma vez para configurar o sistema online
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔧 Configuração do Banco Online e Reset do Admin</h2>";

// Credenciais do banco online (Hostinger)
$host = 'srv486.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📊 Credenciais do Banco Online:</h3>";
echo "<strong>Host:</strong> $host<br>";
echo "<strong>Banco:</strong> $db_name<br>";
echo "<strong>Usuário:</strong> $username<br>";
echo "<strong>Senha:</strong> " . str_repeat('*', strlen($password)) . "<br>";
echo "</div>";

try {
    // Conectar ao banco online
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Conexão com banco online: SUCESSO!</h3>";
    echo "</div>";
    
    // Verificar se a tabela usuarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>⚠️ Tabela 'usuarios' não encontrada!</h3>";
        echo "<p>Você precisa importar o schema do banco primeiro.</p>";
        echo "</div>";
        exit;
    }
    
    // Verificar usuários existentes
    $stmt = $pdo->query("SELECT id, nome, email, tipo_usuario FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>👥 Usuários Existentes no Banco:</h3>";
    if (empty($usuarios)) {
        echo "<p>Nenhum usuário encontrado.</p>";
    } else {
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['tipo_usuario']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Criar/Atualizar usuário administrador
    $admin_email = 'admin@cortefacil.com';
    $admin_senha = 'password';
    $admin_senha_hash = password_hash($admin_senha, PASSWORD_DEFAULT);
    
    // Verificar se admin já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin_existente = $stmt->fetch();
    
    if ($admin_existente) {
        // Atualizar senha do admin existente
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$admin_senha_hash, $admin_email]);
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🔄 Senha do Administrador Atualizada!</h3>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<p><strong>Nova Senha:</strong> $admin_senha</p>";
        echo "</div>";
    } else {
        // Criar novo administrador
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) 
            VALUES (?, ?, ?, 'admin', '(11) 99999-0000', NOW())
        ");
        $stmt->execute(['Administrador', $admin_email, $admin_senha_hash]);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Administrador Criado com Sucesso!</h3>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<p><strong>Senha:</strong> $admin_senha</p>";
        echo "</div>";
    }
    
    // Criar usuários de teste se não existirem
    $usuarios_teste = [
        [
            'nome' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'senha' => '123456',
            'tipo' => 'cliente',
            'telefone' => '(11) 99999-1111'
        ],
        [
            'nome' => 'Parceiro Teste',
            'email' => 'parceiro@teste.com',
            'senha' => '123456',
            'tipo' => 'parceiro',
            'telefone' => '(11) 99999-2222'
        ]
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>👤 Criando Usuários de Teste:</h3>";
    
    foreach ($usuarios_teste as $usuario) {
        // Verificar se usuário já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$usuario['email']]);
        
        if (!$stmt->fetch()) {
            $senha_hash = password_hash($usuario['senha'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $usuario['nome'],
                $usuario['email'],
                $senha_hash,
                $usuario['tipo'],
                $usuario['telefone']
            ]);
            echo "✅ {$usuario['nome']} ({$usuario['email']}) criado<br>";
        } else {
            echo "ℹ️ {$usuario['nome']} ({$usuario['email']}) já existe<br>";
        }
    }
    echo "</div>";
    
    // Resumo final
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h2>🎉 Configuração Concluída com Sucesso!</h2>";
    echo "<h3>📋 Credenciais para Acesso:</h3>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>🔑 Administrador:</strong><br>";
    echo "Email: admin@cortefacil.com<br>";
    echo "Senha: password<br><br>";
    
    echo "<strong>👤 Cliente de Teste:</strong><br>";
    echo "Email: cliente@teste.com<br>";
    echo "Senha: 123456<br><br>";
    
    echo "<strong>🏪 Parceiro de Teste:</strong><br>";
    echo "Email: parceiro@teste.com<br>";
    echo "Senha: 123456<br>";
    echo "</div>";
    
    echo "<p><strong>🌐 Próximos Passos:</strong></p>";
    echo "<ol>";
    echo "<li>Acesse <a href='login.php' target='_blank'>login.php</a> para testar o login</li>";
    echo "<li>Use as credenciais acima para fazer login</li>";
    echo "<li>Verifique se o sistema está funcionando corretamente</li>";
    echo "<li><strong>IMPORTANTE:</strong> Delete este arquivo após o uso por segurança</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Erro de Conexão com o Banco:</h3>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🔍 Possíveis Soluções:</h4>";
        echo "<ul>";
        echo "<li>Verifique se as credenciais estão corretas no painel Hostinger</li>";
        echo "<li>Confirme se o usuário tem permissões para acessar o banco</li>";
        echo "<li>Verifique se o IP está liberado (se necessário)</li>";
        echo "<li>Tente aguardar alguns minutos e teste novamente</li>";
        echo "</ul>";
        echo "</div>";
    }
    echo "</div>";
}

echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Ir para Login</a>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
echo "<strong>⚠️ SEGURANÇA:</strong> Delete este arquivo após o uso!";
echo "</div>";
?>