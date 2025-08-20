<?php
/**
 * Script para verificar se o cadastro está funcionando no banco local
 * e mostrar os dados cadastrados
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação de Cadastros - CorteFácil</h1>";
echo "<hr>";

try {
    // Conectar ao banco local
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
    echo "✅ <strong>Conexão com banco estabelecida com sucesso!</strong>";
    echo "</div>";
    
    // Verificar usuários cadastrados
    echo "<h2>👥 Usuários Cadastrados</h2>";
    $stmt = $conn->prepare("SELECT id, nome, email, tipo_usuario, telefone FROM usuarios ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nome</th>";
        echo "<th style='padding: 8px;'>Email</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Telefone</th>";
        echo "</tr>";
        
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['id']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['nome']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['tipo_usuario']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['telefone'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #17a2b8;'>";
        echo "📊 <strong>Total de usuários encontrados:</strong> " . count($usuarios);
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
        echo "⚠️ <strong>Nenhum usuário encontrado no banco de dados</strong>";
        echo "</div>";
    }
    
    // Verificar salões cadastrados
    echo "<h2>🏪 Salões Cadastrados</h2>";
    $stmt = $conn->prepare("SELECT id, nome, endereco, telefone, descricao FROM saloes ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $saloes = $stmt->fetchAll();
    
    if (count($saloes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nome do Salão</th>";
        echo "<th style='padding: 8px;'>Endereço</th>";
        echo "<th style='padding: 8px;'>Telefone</th>";
        echo "<th style='padding: 8px;'>Descrição</th>";
        echo "</tr>";
        
        foreach ($saloes as $salao) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($salao['id']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($salao['nome']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($salao['endereco']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($salao['telefone']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($salao['descricao']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #17a2b8;'>";
        echo "📊 <strong>Total de salões encontrados:</strong> " . count($saloes);
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
        echo "⚠️ <strong>Nenhum salão encontrado no banco de dados</strong>";
        echo "</div>";
    }
    
    // Teste de cadastro rápido
    echo "<h2>🧪 Teste de Cadastro Rápido</h2>";
    echo "<div style='background: #e2e3e5; padding: 10px; margin: 10px 0; border-left: 4px solid #6c757d;'>";
    echo "<strong>Para testar um novo cadastro:</strong><br>";
    echo "1. Acesse: <a href='cadastro.php?tipo=parceiro' target='_blank'>cadastro.php?tipo=parceiro</a><br>";
    echo "2. Preencha o formulário com dados únicos<br>";
    echo "3. Recarregue esta página para ver o novo cadastro<br>";
    echo "</div>";
    
    echo "<h2>🌐 Verificação no Banco Online (Hostinger)</h2>";
    echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
    echo "⚠️ <strong>Acesso remoto ao banco da Hostinger está bloqueado</strong><br><br>";
    echo "<strong>Para verificar o cadastro no banco online:</strong><br>";
    echo "1. Acesse o painel da Hostinger<br>";
    echo "2. Vá em 'Databases' → 'Manage'<br>";
    echo "3. Clique em 'Enter phpMyAdmin'<br>";
    echo "4. Selecione o banco 'u690889028_cortefacil'<br>";
    echo "5. Verifique as tabelas 'usuarios' e 'saloes'<br><br>";
    echo "<strong>Ou faça o deploy do sistema na Hostinger para testar diretamente online.</strong>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
    echo "❌ <strong>Erro:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Ir para Login</a>";
echo "<a href='cadastro.php?tipo=parceiro' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>➕ Testar Cadastro</a>";
echo "</div>";
?>