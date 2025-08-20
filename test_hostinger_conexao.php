<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Conexão IP Hostinger - CorteFácil</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #28a745;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .content {
            padding: 40px;
        }
        .test-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #28a745;
        }
        .success {
            color: #28a745;
            font-weight: bold;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin: 10px 0;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin: 10px 0;
        }
        .warning {
            color: #856404;
            font-weight: bold;
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin: 10px 0;
        }
        .info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #6c757d;
        }
        .config-box {
            background: #343a40;
            color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
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
        .loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌐 Teste Conexão IP Hostinger</h1>
            <p>Testando com o IP direto: 31.170.167.153</p>
        </div>
        
        <div class="content">
            <?php
            echo "<div class='test-section'>";
            echo "<h2>🔧 Novas Configurações</h2>";
            
            // Configurações atualizadas da Hostinger
            $host_hostinger = '31.170.167.153';  // IP direto
            $username_hostinger = 'u690889028_cortefacil';
            $password_hostinger = 'Cortefacil2024@';
            $database_hostinger = 'u690889028_cortefacil';
            $port_hostinger = 3306;
            
            echo "<div class='config-box'>";
            echo "<strong>Configurações Atualizadas:</strong><br>";
            echo "Host: $host_hostinger (IP direto)<br>";
            echo "Usuário: $username_hostinger<br>";
            echo "Senha: " . str_repeat('*', strlen($password_hostinger)) . "<br>";
            echo "Banco: $database_hostinger<br>";
            echo "Porta: $port_hostinger<br>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🔍 Teste 1: Verificação de Extensões</h2>";
            
            $mysqli_loaded = extension_loaded('mysqli');
            $pdo_loaded = extension_loaded('pdo_mysql');
            
            if ($mysqli_loaded) {
                echo "<div class='success'>✅ MySQLi: Disponível</div>";
            } else {
                echo "<div class='error'>❌ MySQLi: Não disponível</div>";
            }
            
            if ($pdo_loaded) {
                echo "<div class='success'>✅ PDO MySQL: Disponível</div>";
            } else {
                echo "<div class='error'>❌ PDO MySQL: Não disponível</div>";
            }
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🌐 Teste 2: Conexão MySQLi com IP</h2>";
            
            if ($mysqli_loaded) {
                echo "<div class='loading'>";
                echo "<div class='spinner'></div>";
                echo "<p>Testando conexão com IP direto...</p>";
                echo "</div>";
                
                // Aumentar timeout
                ini_set('default_socket_timeout', 10);
                
                $start_time = microtime(true);
                $mysqli_conn = @mysqli_connect($host_hostinger, $username_hostinger, $password_hostinger, $database_hostinger, $port_hostinger);
                $end_time = microtime(true);
                $connection_time = round(($end_time - $start_time) * 1000, 2);
                
                if ($mysqli_conn) {
                    echo "<div class='success'>🎉 CONEXÃO ESTABELECIDA COM SUCESSO!</div>";
                    echo "<div class='info'>⏱️ Tempo de conexão: {$connection_time}ms</div>";
                    
                    // Informações do servidor
                    $server_info = mysqli_get_server_info($mysqli_conn);
                    $host_info = mysqli_get_host_info($mysqli_conn);
                    echo "<div class='info'>📋 Versão MySQL: $server_info</div>";
                    echo "<div class='info'>🔗 Info do Host: $host_info</div>";
                    
                    // Teste de query básica
                    $result = mysqli_query($mysqli_conn, "SELECT 1 as teste, NOW() as agora, DATABASE() as banco_atual");
                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        echo "<div class='success'>✅ Query executada com sucesso!</div>";
                        echo "<div class='info'>🕒 Hora do servidor: " . $row['agora'] . "</div>";
                        echo "<div class='info'>🗄️ Banco atual: " . $row['banco_atual'] . "</div>";
                    } else {
                        echo "<div class='error'>❌ Erro na query: " . mysqli_error($mysqli_conn) . "</div>";
                    }
                    
                    // Verificar tabelas existentes
                    $tables_result = mysqli_query($mysqli_conn, "SHOW TABLES");
                    if ($tables_result) {
                        $table_count = mysqli_num_rows($tables_result);
                        echo "<div class='info'>📊 Número de tabelas: $table_count</div>";
                        
                        if ($table_count > 0) {
                            echo "<div class='info'><strong>Tabelas encontradas:</strong><br>";
                            while ($table = mysqli_fetch_array($tables_result)) {
                                echo "• " . $table[0] . "<br>";
                            }
                            echo "</div>";
                        } else {
                            echo "<div class='warning'>⚠️ Nenhuma tabela encontrada no banco</div>";
                        }
                    }
                    
                    mysqli_close($mysqli_conn);
                    
                } else {
                    $error = mysqli_connect_error();
                    echo "<div class='error'>❌ FALHA NA CONEXÃO!</div>";
                    echo "<div class='error'>Erro: $error</div>";
                    echo "<div class='info'>⏱️ Tempo tentativa: {$connection_time}ms</div>";
                    
                    // Análise do erro
                    if (strpos($error, 'Access denied') !== false) {
                        echo "<div class='warning'>🔍 Análise: Problema de autenticação - credenciais incorretas ou acesso negado</div>";
                    } elseif (strpos($error, 'Connection refused') !== false) {
                        echo "<div class='warning'>🔍 Análise: Conexão recusada - servidor pode estar offline ou porta bloqueada</div>";
                    } elseif (strpos($error, 'timed out') !== false) {
                        echo "<div class='warning'>🔍 Análise: Timeout - servidor demorou para responder</div>";
                    } else {
                        echo "<div class='warning'>🔍 Análise: Erro desconhecido - verifique configurações de rede</div>";
                    }
                }
            } else {
                echo "<div class='error'>❌ MySQLi não está disponível</div>";
            }
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🔗 Teste 3: Conexão PDO com IP</h2>";
            
            if ($pdo_loaded) {
                echo "<p>Testando PDO com IP direto...</p>";
                
                try {
                    $dsn = "mysql:host=$host_hostinger;port=$port_hostinger;dbname=$database_hostinger;charset=utf8mb4";
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                        PDO::ATTR_TIMEOUT => 10
                    ];
                    
                    $start_time = microtime(true);
                    $pdo_conn = new PDO($dsn, $username_hostinger, $password_hostinger, $options);
                    $end_time = microtime(true);
                    $pdo_time = round(($end_time - $start_time) * 1000, 2);
                    
                    echo "<div class='success'>🎉 CONEXÃO PDO ESTABELECIDA!</div>";
                    echo "<div class='info'>⏱️ Tempo PDO: {$pdo_time}ms</div>";
                    
                    // Teste de query PDO
                    $stmt = $pdo_conn->prepare("SELECT 1 as teste, NOW() as agora, VERSION() as versao");
                    $stmt->execute();
                    $result = $stmt->fetch();
                    
                    if ($result) {
                        echo "<div class='success'>✅ Query PDO executada!</div>";
                        echo "<div class='info'>🕒 Hora: " . $result['agora'] . "</div>";
                        echo "<div class='info'>📋 Versão: " . $result['versao'] . "</div>";
                    }
                    
                    // Verificar tabela usuarios
                    $stmt = $pdo_conn->prepare("SHOW TABLES LIKE 'usuarios'");
                    $stmt->execute();
                    $table_exists = $stmt->fetch();
                    
                    if ($table_exists) {
                        echo "<div class='success'>✅ Tabela 'usuarios' encontrada!</div>";
                        
                        // Contar registros
                        $stmt = $pdo_conn->prepare("SELECT COUNT(*) as total FROM usuarios");
                        $stmt->execute();
                        $count = $stmt->fetch();
                        echo "<div class='info'>👥 Usuários cadastrados: " . $count['total'] . "</div>";
                    } else {
                        echo "<div class='warning'>⚠️ Tabela 'usuarios' não encontrada</div>";
                    }
                    
                } catch (PDOException $e) {
                    echo "<div class='error'>❌ Erro PDO: " . $e->getMessage() . "</div>";
                    
                    // Análise do erro PDO
                    $error_msg = $e->getMessage();
                    if (strpos($error_msg, 'Access denied') !== false) {
                        echo "<div class='warning'>🔍 PDO: Problema de autenticação</div>";
                    } elseif (strpos($error_msg, 'Connection refused') !== false) {
                        echo "<div class='warning'>🔍 PDO: Conexão recusada pelo servidor</div>";
                    } elseif (strpos($error_msg, 'timed out') !== false) {
                        echo "<div class='warning'>🔍 PDO: Timeout na conexão</div>";
                    }
                }
            } else {
                echo "<div class='error'>❌ PDO MySQL não disponível</div>";
            }
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🎯 Teste 4: Classe Database com IP</h2>";
            
            if (file_exists('config/database.php')) {
                // Backup das variáveis de servidor originais
                $original_server_name = $_SERVER['SERVER_NAME'] ?? '';
                $original_server_port = $_SERVER['SERVER_PORT'] ?? '';
                $original_https = $_SERVER['HTTPS'] ?? '';
                
                try {
                    // Simular ambiente online para forçar uso das credenciais da Hostinger
                    $_SERVER['SERVER_NAME'] = 'cortefacil.com';
                    $_SERVER['SERVER_PORT'] = '443';
                    $_SERVER['HTTPS'] = 'on';
                    
                    require_once 'config/database.php';
                    $database = new Database();
                    echo "<div class='success'>✅ Classe Database carregada</div>";
                    
                    $conn = $database->connect();
                    
                    if ($conn) {
                        echo "<div class='success'>🎉 CONEXÃO VIA CLASSE DATABASE OK!</div>";
                        
                        // Teste final integrado
                        $stmt = $conn->prepare("SELECT 'Sistema CorteFácil Online!' as mensagem, NOW() as timestamp");
                        $stmt->execute();
                        $result = $stmt->fetch();
                        
                        if ($result) {
                            echo "<div class='success'>🚀 " . $result['mensagem'] . "</div>";
                            echo "<div class='info'>🕒 Timestamp: " . $result['timestamp'] . "</div>";
                        }
                    } else {
                        echo "<div class='error'>❌ Falha na classe Database</div>";
                    }
                    
                } catch (Exception $e) {
                    echo "<div class='error'>❌ Erro na classe Database: " . $e->getMessage() . "</div>";
                } finally {
                    // Restaurar variáveis originais
                    $_SERVER['SERVER_NAME'] = $original_server_name;
                    $_SERVER['SERVER_PORT'] = $original_server_port;
                    $_SERVER['HTTPS'] = $original_https;
                }
            } else {
                echo "<div class='error'>❌ Arquivo config/database.php não encontrado</div>";
            }
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>📊 Resultado Final</h2>";
            echo "<div class='info'>";
            echo "<h4>✅ Se todos os testes passaram:</h4>";
            echo "<p>• A conexão com a Hostinger está funcionando perfeitamente!</p>";
            echo "<p>• Você pode fazer o deploy da aplicação com segurança</p>";
            echo "<p>• O sistema está pronto para produção</p>";
            echo "<br>";
            echo "<h4>❌ Se algum teste falhou:</h4>";
            echo "<p>• Verifique as credenciais no painel da Hostinger</p>";
            echo "<p>• Confirme se o acesso remoto está habilitado</p>";
            echo "<p>• Verifique se seu IP está autorizado</p>";
            echo "</div>";
            echo "</div>";
            ?>
            
            <div class="text-center">
                <a href="login.php" class="btn btn-success">🏠 Ir para o Login</a>
                <a href="debug_conexao.php" class="btn">🔍 Teste Local</a>
                <a href="DEPLOY_ONLINE.md" class="btn">📋 Guia de Deploy</a>
            </div>
        </div>
    </div>
    
    <script>
        // Remover spinner após carregamento
        window.addEventListener('load', function() {
            const spinners = document.querySelectorAll('.loading');
            spinners.forEach(spinner => {
                spinner.style.display = 'none';
            });
        });
    </script>
</body>
</html>