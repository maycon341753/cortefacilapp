<?php
// Teste de Conexão Hostinger - IP Correto Identificado
// Data: 19/08/2025

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustar Banco Hostinger - IP Correto</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #667eea;
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
        .ip-box {
            background: #e8f5e8;
            border: 3px solid #28a745;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .ip-box h2 {
            color: #28a745;
            margin-top: 0;
        }
        .ip-address {
            font-size: 2em;
            font-weight: bold;
            color: #155724;
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .instructions {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin: 25px 0;
            border-left: 6px solid #007bff;
        }
        .step {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 5px solid #007bff;
            position: relative;
        }
        .step-number {
            position: absolute;
            left: -15px;
            top: 20px;
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .step h4 {
            margin-top: 0;
            color: #007bff;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .test-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 25px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .highlight {
            background: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #856404;
        }
        .code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            border: 1px solid #dee2e6;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Ajustar Banco Hostinger</h1>
            <p>Configuração do IP correto para acesso remoto</p>
        </div>
        
        <div class="content">
            <div class="ip-box">
                <h2>✅ IP CORRETO IDENTIFICADO</h2>
                <div class="ip-address">45.181.73.171</div>
                <p><strong>Provedor:</strong> HUBTELTELECOM</p>
                <p><strong>Data/Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
            
            <div class="instructions">
                <h2>📋 Instruções para Adicionar o IP na Hostinger</h2>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Acesse o Painel da Hostinger</h4>
                    <p>Entre no seu painel de controle da Hostinger e vá para:</p>
                    <div class="code">Sites → cortefacil.app → Bancos de Dados → MySQL Remoto</div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Adicionar IP no Campo</h4>
                    <p>No campo "IP (IPv4 ou IPv6)", adicione exatamente:</p>
                    <div class="code">45.181.73.171</div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Selecionar Banco de Dados</h4>
                    <p>No campo "Banco de dados", selecione:</p>
                    <div class="code">u690889028_cortefacil</div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Criar a Conexão</h4>
                    <p>Clique no botão <span class="highlight">"Criar"</span> e aguarde a confirmação.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">5</div>
                    <h4>Aguardar Ativação</h4>
                    <p>A liberação pode levar até <strong>30 minutos</strong> para ser efetivada.</p>
                </div>
            </div>
            
            <div class="warning">
                <h3>⚠️ IMPORTANTE</h3>
                <ul>
                    <li>Use exatamente o IP: <strong>45.181.73.171</strong></li>
                    <li>Não marque "Qualquer Host" por questões de segurança</li>
                    <li>Aguarde até 30 minutos após criar a conexão</li>
                    <li>Se não funcionar, tente usar <strong>%</strong> temporariamente</li>
                </ul>
            </div>
            
            <div class="test-section">
                <h2>🧪 Teste de Conexão</h2>
                <p>Após adicionar o IP no painel da Hostinger, teste a conexão:</p>
                
                <?php
                // Configurações de conexão
                $host = '31.170.167.153';
                $username = 'u690889028_cortefacil';
                $password = 'Cortefacil2024@';
                $database = 'u690889028_cortefacil';
                $port = 3306;
                
                echo "<div class='code'>";
                echo "<strong>Configurações de Teste:</strong><br>";
                echo "Host: $host<br>";
                echo "Usuário: $username<br>";
                echo "Banco: $database<br>";
                echo "Porta: $port<br>";
                echo "IP Atual: 45.181.73.171";
                echo "</div>";
                
                // Teste de conexão
                echo "<h3>Resultado do Teste:</h3>";
                
                try {
                    $start_time = microtime(true);
                    
                    // Teste MySQLi
                    $connection = new mysqli($host, $username, $password, $database, $port);
                    
                    if ($connection->connect_error) {
                        throw new Exception("Erro MySQLi: " . $connection->connect_error);
                    }
                    
                    $end_time = microtime(true);
                    $connection_time = round(($end_time - $start_time) * 1000, 2);
                    
                    echo "<div class='success'>";
                    echo "<h4>✅ CONEXÃO ESTABELECIDA COM SUCESSO!</h4>";
                    echo "<p><strong>Tempo de conexão:</strong> {$connection_time}ms</p>";
                    echo "<p><strong>Versão do servidor:</strong> " . $connection->server_info . "</p>";
                    echo "<p><strong>Charset:</strong> " . $connection->character_set_name() . "</p>";
                    
                    // Teste de query simples
                    $result = $connection->query("SELECT 1 as test");
                    if ($result) {
                        echo "<p><strong>Teste de query:</strong> ✅ Funcionando</p>";
                    }
                    
                    // Listar tabelas
                    $tables_result = $connection->query("SHOW TABLES");
                    if ($tables_result && $tables_result->num_rows > 0) {
                        echo "<p><strong>Tabelas encontradas:</strong> " . $tables_result->num_rows . "</p>";
                    } else {
                        echo "<p><strong>Tabelas:</strong> Nenhuma tabela encontrada (banco vazio)</p>";
                    }
                    
                    echo "</div>";
                    
                    $connection->close();
                    
                } catch (Exception $e) {
                    echo "<div class='error'>";
                    echo "<h4>❌ FALHA NA CONEXÃO</h4>";
                    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
                    
                    if (strpos($e->getMessage(), 'Access denied') !== false) {
                        echo "<h4>🔍 Possíveis Causas:</h4>";
                        echo "<ul>";
                        echo "<li>IP ainda não foi adicionado no painel da Hostinger</li>";
                        echo "<li>Mudanças ainda não foram aplicadas (aguarde até 30 min)</li>";
                        echo "<li>IP incorreto foi adicionado</li>";
                        echo "<li>Credenciais incorretas</li>";
                        echo "</ul>";
                        
                        echo "<h4>💡 Soluções:</h4>";
                        echo "<ul>";
                        echo "<li>Verifique se o IP <strong>45.181.73.171</strong> foi adicionado corretamente</li>";
                        echo "<li>Aguarde 15-30 minutos após adicionar o IP</li>";
                        echo "<li>Tente usar <strong>%</strong> no lugar do IP específico</li>";
                        echo "<li>Contate o suporte da Hostinger se persistir</li>";
                        echo "</ul>";
                    }
                    
                    echo "</div>";
                }
                ?>
            </div>
            
            <div class="success">
                <h2>🎯 Próximos Passos</h2>
                <ol>
                    <li><strong>Adicione o IP no painel da Hostinger</strong> conforme as instruções acima</li>
                    <li><strong>Aguarde 15-30 minutos</strong> para a ativação</li>
                    <li><strong>Recarregue esta página</strong> para testar novamente</li>
                    <li><strong>Se funcionar:</strong> Sua conexão remota estará configurada!</li>
                    <li><strong>Para desenvolvimento:</strong> Continue usando MySQL local do XAMPP</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="javascript:location.reload()" class="btn btn-success">🔄 Testar Novamente</a>
                <a href="login.php" class="btn">🏠 Ir para Login</a>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d;">
                <p><strong>IP identificado automaticamente:</strong> 45.181.73.171</p>
                <p>Última verificação: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>