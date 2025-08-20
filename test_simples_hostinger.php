<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Simples Hostinger - Pós Desbloqueio</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.2em;
        }
        .content {
            padding: 40px;
        }
        .test-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #4CAF50;
        }
        .success {
            color: #28a745;
            font-weight: bold;
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin: 15px 0;
            font-size: 1.1em;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
            background: #f8d7da;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin: 15px 0;
            font-size: 1.1em;
        }
        .info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #6c757d;
        }
        .config-display {
            background: #343a40;
            color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            font-size: 0.95em;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .text-center {
            text-align: center;
        }
        .timestamp {
            font-size: 0.9em;
            color: #6c757d;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Teste Pós-Desbloqueio</h1>
            <p>Verificando conexão após liberação do IP pela Hostinger</p>
        </div>
        
        <div class="content">
            <?php
            echo "<div class='test-box'>";
            echo "<h2>🔧 Configurações de Teste</h2>";
            
            // Configurações da Hostinger
            $host = '31.170.167.153';
            $username = 'u690889028_cortefacil';
            $password = 'Cortefacil2024@';
            $database = 'u690889028_cortefacil';
            $port = 3306;
            
            echo "<div class='config-display'>";
            echo "<strong>Testando com:</strong><br>";
            echo "Host: $host<br>";
            echo "Usuário: $username<br>";
            echo "Senha: " . str_repeat('*', strlen($password)) . "<br>";
            echo "Banco: $database<br>";
            echo "Porta: $port<br>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='test-box'>";
            echo "<h2>🌐 Teste de Conexão Simples</h2>";
            
            // Configurar timeout
            ini_set('default_socket_timeout', 15);
            
            $start_time = microtime(true);
            
            // Tentar conexão MySQLi
            $connection = @mysqli_connect($host, $username, $password, $database, $port);
            
            $end_time = microtime(true);
            $connection_time = round(($end_time - $start_time) * 1000, 2);
            
            if ($connection) {
                echo "<div class='success'>";
                echo "🎉 SUCESSO! Conexão estabelecida com a Hostinger!<br>";
                echo "⏱️ Tempo de conexão: {$connection_time}ms";
                echo "</div>";
                
                // Informações do servidor
                $server_info = mysqli_get_server_info($connection);
                $host_info = mysqli_get_host_info($connection);
                
                echo "<div class='info'>";
                echo "<strong>Informações do Servidor:</strong><br>";
                echo "📋 Versão MySQL: $server_info<br>";
                echo "🔗 Host Info: $host_info";
                echo "</div>";
                
                // Teste de query simples
                $query_result = mysqli_query($connection, "SELECT 'Conexão OK!' as status, NOW() as timestamp, DATABASE() as banco_atual");
                
                if ($query_result) {
                    $row = mysqli_fetch_assoc($query_result);
                    echo "<div class='success'>";
                    echo "✅ Query executada: " . $row['status'] . "<br>";
                    echo "🕒 Timestamp servidor: " . $row['timestamp'] . "<br>";
                    echo "🗄️ Banco atual: " . $row['banco_atual'];
                    echo "</div>";
                } else {
                    echo "<div class='error'>❌ Erro na query: " . mysqli_error($connection) . "</div>";
                }
                
                // Verificar tabelas
                $tables_query = mysqli_query($connection, "SHOW TABLES");
                if ($tables_query) {
                    $table_count = mysqli_num_rows($tables_query);
                    echo "<div class='info'>";
                    echo "📊 Tabelas no banco: $table_count";
                    
                    if ($table_count > 0) {
                        echo "<br><strong>Lista de tabelas:</strong><br>";
                        while ($table = mysqli_fetch_array($tables_query)) {
                            echo "• " . $table[0] . "<br>";
                        }
                    }
                    echo "</div>";
                }
                
                mysqli_close($connection);
                
                echo "<div class='success'>";
                echo "🚀 <strong>RESULTADO FINAL: CONEXÃO FUNCIONANDO PERFEITAMENTE!</strong><br>";
                echo "✅ O IP foi desbloqueado com sucesso pela Hostinger<br>";
                echo "✅ Todas as credenciais estão corretas<br>";
                echo "✅ O sistema está pronto para produção";
                echo "</div>";
                
            } else {
                $error = mysqli_connect_error();
                echo "<div class='error'>";
                echo "❌ FALHA NA CONEXÃO!<br>";
                echo "Erro: $error<br>";
                echo "⏱️ Tempo de tentativa: {$connection_time}ms";
                echo "</div>";
                
                echo "<div class='info'>";
                echo "<strong>Possíveis causas:</strong><br>";
                if (strpos($error, 'Access denied') !== false) {
                    echo "🔍 O IP ainda pode não estar liberado ou as credenciais estão incorretas<br>";
                    echo "💡 Aguarde alguns minutos e tente novamente<br>";
                    echo "💡 Verifique se o IP correto foi adicionado no painel da Hostinger";
                } elseif (strpos($error, 'Connection refused') !== false) {
                    echo "🔍 Servidor MySQL pode estar offline<br>";
                    echo "💡 Contate o suporte da Hostinger";
                } elseif (strpos($error, 'timed out') !== false) {
                    echo "🔍 Timeout na conexão<br>";
                    echo "💡 Verifique sua conexão com a internet";
                } else {
                    echo "🔍 Erro desconhecido<br>";
                    echo "💡 Verifique todas as configurações";
                }
                echo "</div>";
            }
            
            echo "</div>";
            
            // Mostrar IP atual
            echo "<div class='test-box'>";
            echo "<h2>🌍 Informações de Rede</h2>";
            echo "<div class='info'>";
            echo "<strong>Seu IP atual:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Não detectado');
            echo "<br><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Não detectado');
            echo "</div>";
            echo "</div>";
            ?>
            
            <div class="text-center">
                <a href="login.php" class="btn btn-success">🏠 Ir para o Login</a>
                <a href="test_simples_hostinger.php" class="btn">🔄 Testar Novamente</a>
            </div>
            
            <div class="timestamp">
                Teste executado em: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
</body>
</html>