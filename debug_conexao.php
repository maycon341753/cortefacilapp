<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico MySQL - CorteFácil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { background: #e9ecef; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .solution { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 10px 0; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h3 { color: #495057; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Diagnóstico MySQL - CorteFácil</h2>
        
        <?php
        echo "<h3>1. Status do MySQL</h3>";
        
        if (function_exists('mysqli_connect')) {
            echo "<p>Tentando conectar ao MySQL...</p>";
            
            $connection = @mysqli_connect('localhost', 'root', '');
            
            if ($connection) {
                echo "<p class='success'>✅ MYSQL ESTÁ RODANDO!</p>";
                
                // Verificar versão do MySQL
                $version = mysqli_get_server_info($connection);
                echo "<p class='info'>📋 Versão do MySQL: $version</p>";
                
                // Verificar se o banco existe
                $dbExists = mysqli_select_db($connection, 'u690889028_cortefacil');
                
                if ($dbExists) {
                    echo "<p class='success'>✅ Banco 'u690889028_cortefacil' existe!</p>";
                } else {
                    echo "<p class='warning'>⚠️ Banco 'u690889028_cortefacil' não existe</p>";
                    echo "<p>Criando banco...</p>";
                    
                    if (mysqli_query($connection, "CREATE DATABASE u690889028_cortefacil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                        echo "<p class='success'>✅ Banco criado com sucesso!</p>";
                    } else {
                        echo "<p class='error'>❌ Erro ao criar banco: " . mysqli_error($connection) . "</p>";
                    }
                }
                
                mysqli_close($connection);
                
            } else {
                echo "<p class='error'>❌ MYSQL NÃO ESTÁ RODANDO!</p>";
                echo "<p class='error'>Erro: " . mysqli_connect_error() . "</p>";
                
                echo "<div class='solution'>";
                echo "<h4>🔧 SOLUÇÃO URGENTE:</h4>";
                echo "<ol>";
                echo "<li><strong>Abra o XAMPP Control Panel</strong></li>";
                echo "<li><strong>Clique em 'Start' ao lado de 'MySQL'</strong></li>";
                echo "<li><strong>Aguarde até ficar verde</strong></li>";
                echo "<li><strong>Recarregue esta página</strong></li>";
                echo "</ol>";
                echo "<p><strong>IMPORTANTE:</strong> Sem o MySQL rodando, o sistema não funciona!</p>";
                echo "</div>";
            }
        } else {
            echo "<p class='error'>❌ Extensão mysqli não está instalada!</p>";
        }
        
        echo "<h3>2. Teste da Classe Database</h3>";
        
        if (file_exists('config/database.php')) {
            require_once 'config/database.php';
            
            try {
                $database = new Database();
                echo "<p class='success'>✅ Classe Database carregada</p>";
                
                $conn = $database->connect();
                
                if ($conn) {
                    echo "<p class='success'>✅ Conexão PDO estabelecida!</p>";
                    
                    // Teste simples
                    try {
                        $stmt = $conn->prepare("SELECT 1 as teste");
                        $stmt->execute();
                        $result = $stmt->fetch();
                        if ($result) {
                            echo "<p class='success'>✅ Query de teste funcionou perfeitamente!</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>❌ Erro na query: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p class='error'>❌ Falha na conexão PDO</p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro na classe Database: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Arquivo config/database.php não encontrado!</p>";
        }
        
        echo "<hr>";
        echo "<h3>🎯 Resultado Final</h3>";
        echo "<div class='info'>";
        echo "<p><strong>Se você vê ✅ em todos os itens:</strong> O sistema está funcionando!</p>";
        echo "<p><strong>Se você vê ❌:</strong> Siga as instruções para corrigir.</p>";
        echo "<p><strong>Problema mais comum:</strong> MySQL não está iniciado no XAMPP.</p>";
        echo "</div>";
        
        echo "<p style='text-align: center; margin-top: 20px;'>";
        echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Voltar para o Login</a>";
        echo "</p>";
        ?>
    </div>
</body>
</html>