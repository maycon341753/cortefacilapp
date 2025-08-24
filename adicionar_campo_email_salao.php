<?php
/**
 * Script para adicionar campo email na tabela saloes
 * CorteFácil - Adicionar Email do Salão
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Campo Email - Tabela Salões</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .error { background: #f8d7da; border-left: 5px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .warning { background: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .info { background: #d1ecf1; border-left: 5px solid #17a2b8; padding: 15px; margin: 15px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #e9ecef; font-weight: bold; }
        h1 { color: #495057; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Adicionar Campo Email na Tabela Salões</h1>
        
        <div class="info">
            <strong>📅 Data/Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
            <strong>🎯 Objetivo:</strong> Adicionar campo 'email' na tabela 'saloes' para armazenar o email do estabelecimento
        </div>

        <?php
        try {
            $db = new Database();
            $conn = $db->connect();
            
            if (!$conn) {
                throw new Exception('Não foi possível conectar ao banco de dados.');
            }
            
            echo "<div class='success'>✅ Conexão com banco de dados estabelecida</div>";
            
            // Verificar se a coluna email já existe
            echo "<h2>🔍 1. Verificando se a coluna 'email' já existe</h2>";
            
            $stmt = $conn->prepare("SHOW COLUMNS FROM saloes LIKE 'email'");
            $stmt->execute();
            $emailExists = $stmt->fetch();
            
            if ($emailExists) {
                echo "<div class='warning'>⚠️ A coluna 'email' já existe na tabela 'saloes'</div>";
                
                // Mostrar estrutura atual da coluna
                echo "<div class='code-block'>";
                echo "<strong>Estrutura atual da coluna 'email':</strong><br>";
                echo "• Campo: {$emailExists['Field']}<br>";
                echo "• Tipo: {$emailExists['Type']}<br>";
                echo "• Nulo: {$emailExists['Null']}<br>";
                echo "• Chave: {$emailExists['Key']}<br>";
                echo "• Padrão: " . ($emailExists['Default'] ?? 'NULL');
                echo "</div>";
                
            } else {
                echo "<div class='info'>ℹ️ A coluna 'email' não existe. Procedendo com a criação...</div>";
                
                // Adicionar a coluna email
                echo "<h2>➕ 2. Adicionando coluna 'email'</h2>";
                
                $sql = "ALTER TABLE saloes ADD COLUMN email VARCHAR(100) NULL AFTER telefone";
                
                echo "<div class='code-block'>";
                echo "<strong>SQL a ser executado:</strong><br>";
                echo htmlspecialchars($sql);
                echo "</div>";
                
                if ($conn->exec($sql) !== false) {
                    echo "<div class='success'>✅ Coluna 'email' adicionada com sucesso!</div>";
                } else {
                    $errorInfo = $conn->errorInfo();
                    throw new Exception("Erro ao adicionar coluna: " . $errorInfo[2]);
                }
            }
            
            // Verificar estrutura final da tabela
            echo "<h2>📋 3. Estrutura final da tabela 'saloes'</h2>";
            
            $stmt = $conn->prepare("DESCRIBE saloes");
            $stmt->execute();
            $columns = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
            
            foreach ($columns as $column) {
                $isEmailField = ($column['Field'] === 'email');
                $rowClass = $isEmailField ? 'style="background-color: #d4edda;"' : '';
                
                echo "<tr {$rowClass}>";
                echo "<td>" . ($isEmailField ? '<strong>' : '') . htmlspecialchars($column['Field']) . ($isEmailField ? '</strong>' : '') . "</td>";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar se há salões existentes para popular o email
            echo "<h2>👥 4. Verificando salões existentes</h2>";
            
            $stmt = $conn->prepare("SELECT s.id, s.nome, s.telefone, s.email, u.email as email_usuario, u.nome as nome_usuario FROM saloes s LEFT JOIN usuarios u ON s.id_dono = u.id ORDER BY s.id DESC LIMIT 5");
            $stmt->execute();
            $saloes = $stmt->fetchAll();
            
            if (!empty($saloes)) {
                echo "<div class='info'>ℹ️ Encontrados " . count($saloes) . " salões (mostrando últimos 5)</div>";
                
                echo "<table>";
                echo "<tr><th>ID</th><th>Nome do Salão</th><th>Email do Salão</th><th>Email do Usuário</th><th>Nome do Usuário</th><th>Ação</th></tr>";
                
                foreach ($saloes as $salao) {
                    $temEmail = !empty($salao['email']);
                    $podePopular = !$temEmail && !empty($salao['email_usuario']);
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($salao['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($salao['nome']) . "</td>";
                    echo "<td>" . ($temEmail ? '<span style="color: green;">' . htmlspecialchars($salao['email']) . '</span>' : '<span style="color: #999;">Não informado</span>') . "</td>";
                    echo "<td>" . htmlspecialchars($salao['email_usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($salao['nome_usuario']) . "</td>";
                    echo "<td>" . ($podePopular ? '<span style="color: orange;">Pode popular</span>' : ($temEmail ? '<span style="color: green;">OK</span>' : '<span style="color: #999;">-</span>')) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Opção para popular emails automaticamente
                $saloesParaPopular = array_filter($saloes, function($salao) {
                    return empty($salao['email']) && !empty($salao['email_usuario']);
                });
                
                if (!empty($saloesParaPopular)) {
                    echo "<h3>🔄 5. Populando emails automaticamente</h3>";
                    echo "<div class='info'>ℹ️ Populando email dos salões com o email do usuário proprietário...</div>";
                    
                    $populados = 0;
                    foreach ($saloesParaPopular as $salao) {
                        $stmt = $conn->prepare("UPDATE saloes SET email = ? WHERE id = ?");
                        if ($stmt->execute([$salao['email_usuario'], $salao['id']])) {
                            echo "<div class='success'>✅ Salão '{$salao['nome']}' (ID: {$salao['id']}) - Email definido como: {$salao['email_usuario']}</div>";
                            $populados++;
                        } else {
                            echo "<div class='error'>❌ Erro ao atualizar salão '{$salao['nome']}' (ID: {$salao['id']})</div>";
                        }
                    }
                    
                    echo "<div class='success'><strong>📊 Resumo: {$populados} salões tiveram seus emails populados automaticamente</strong></div>";
                }
            } else {
                echo "<div class='warning'>⚠️ Nenhum salão encontrado na base de dados</div>";
            }
            
            // Instruções finais
            echo "<h2>✅ 6. Próximos Passos</h2>";
            echo "<div class='success'>";
            echo "<strong>Campo 'email' adicionado com sucesso!</strong><br><br>";
            echo "<strong>Próximos passos:</strong><br>";
            echo "• ✅ Campo 'email' criado na tabela 'saloes'<br>";
            echo "• ✅ Emails existentes populados automaticamente<br>";
            echo "• 🔄 Modificar o formulário em 'parceiro/salao.php' para incluir o campo email<br>";
            echo "• 🔄 Atualizar a validação e processamento do formulário<br>";
            echo "• 🔄 Testar o cadastro/edição de salões com o novo campo<br>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Erro:</strong><br>";
            echo "• Mensagem: " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "• Arquivo: " . htmlspecialchars($e->getFile()) . "<br>";
            echo "• Linha: " . htmlspecialchars($e->getLine());
            echo "</div>";
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <p><strong>🔗 Links Úteis:</strong></p>
            <a href="parceiro/salao.php" style="margin: 0 10px;">📝 Formulário do Salão</a> |
            <a href="verificar_estrutura_saloes_real.php" style="margin: 0 10px;">🔍 Verificar Estrutura</a> |
            <a href="index.php" style="margin: 0 10px;">🏠 Início</a>
        </div>
    </div>
</body>
</html>