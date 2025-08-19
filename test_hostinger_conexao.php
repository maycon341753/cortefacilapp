<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Hostinger - CorteFácil</title>
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
            background: #dc3545;
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
            border-left: 5px solid #dc3545;
        }
        .success {
            color: #28a745;
            font-weight: bold;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .warning {
            color: #856404;
            font-weight: bold;
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
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
        .solution {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
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
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 Diagnóstico Hostinger</h1>
            <p>Análise detalhada da conexão com o banco de dados online</p>
        </div>
        
        <div class="content">
            <?php
            echo "<div class='test-section'>";
            echo "<h2>🔍 Problema Identificado</h2>";
            echo "<div class='error'>❌ Access denied for user 'u690889028_cortefacil'</div>";
            echo "<p>O erro indica que as credenciais ou configurações de acesso remoto estão incorretas.</p>";
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>📋 Possíveis Causas</h2>";
            echo "<div class='info'>";
            echo "<h4>1. Credenciais Incorretas</h4>";
            echo "<p>• Usuário, senha ou nome do banco podem estar errados</p>";
            echo "<p>• Verifique no painel da Hostinger se as credenciais estão corretas</p>";
            echo "<br>";
            echo "<h4>2. Acesso Remoto Não Habilitado</h4>";
            echo "<p>• Por padrão, a Hostinger bloqueia conexões externas ao MySQL</p>";
            echo "<p>• É necessário habilitar o acesso remoto no painel de controle</p>";
            echo "<br>";
            echo "<h4>3. IP Não Autorizado</h4>";
            echo "<p>• Seu IP atual pode não estar na lista de IPs autorizados</p>";
            echo "<p>• Verifique as configurações de segurança do banco</p>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='solution'>";
            echo "<h2>🔧 Soluções</h2>";
            echo "<h4>Opção 1: Verificar Credenciais</h4>";
            echo "<ol>";
            echo "<li>Acesse o painel da Hostinger</li>";
            echo "<li>Vá em 'Bancos de Dados' → 'Gerenciar'</li>";
            echo "<li>Verifique se as credenciais estão corretas:</li>";
            echo "<ul>";
            echo "<li>Host: srv1434.hstgr.io</li>";
            echo "<li>Usuário: u690889028_cortefacil</li>";
            echo "<li>Banco: u690889028_cortefacil</li>";
            echo "<li>Senha: Cortefacil2024@</li>";
            echo "</ul>";
            echo "</ol>";
            echo "<br>";
            echo "<h4>Opção 2: Habilitar Acesso Remoto</h4>";
            echo "<ol>";
            echo "<li>No painel da Hostinger, vá em 'Bancos de Dados'</li>";
            echo "<li>Clique em 'Acesso Remoto' ou 'Remote Access'</li>";
            echo "<li>Adicione seu IP atual ou use '%' para qualquer IP</li>";
            echo "<li>Salve as configurações</li>";
            echo "</ol>";
            echo "<br>";
            echo "<h4>Opção 3: Usar Localhost (Recomendado)</h4>";
            echo "<p><strong>IMPORTANTE:</strong> Para desenvolvimento local, use o MySQL local do XAMPP.</p>";
            echo "<p>A conexão com a Hostinger só deve ser testada quando o site estiver online.</p>";
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>💡 Informações Importantes</h2>";
            echo "<div class='info'>";
            echo "<h4>🏠 Desenvolvimento Local</h4>";
            echo "<p>• Use o MySQL do XAMPP (localhost)</p>";
            echo "<p>• Crie um banco local chamado 'u690889028_cortefacil'</p>";
            echo "<p>• Importe a estrutura das tabelas</p>";
            echo "<br>";
            echo "<h4>🌐 Produção Online</h4>";
            echo "<p>• A conexão com Hostinger só funciona quando o site está hospedado lá</p>";
            echo "<p>• Teste a conexão apenas após fazer o upload dos arquivos</p>";
            echo "<p>• Configure o acesso remoto se necessário para testes externos</p>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='test-section'>";
            echo "<h2>🎯 Próximos Passos</h2>";
            echo "<div class='warning'>";
            echo "<h4>Para Desenvolvimento:</h4>";
            echo "<p>1. Inicie o MySQL no XAMPP</p>";
            echo "<p>2. Use o teste de conexão local</p>";
            echo "<p>3. Desenvolva e teste localmente</p>";
            echo "<br>";
            echo "<h4>Para Deploy:</h4>";
            echo "<p>1. Finalize o desenvolvimento local</p>";
            echo "<p>2. Faça upload dos arquivos para a Hostinger</p>";
            echo "<p>3. Teste a conexão diretamente no servidor</p>";
            echo "</div>";
            echo "</div>";
            ?>
            
            <div class="text-center">
                <a href="debug_conexao.php" class="btn">🔍 Teste Conexão Local</a>
                <a href="SOLUCAO_MYSQL.html" class="btn">🔧 Iniciar MySQL XAMPP</a>
                <a href="login.php" class="btn">🏠 Ir para o Login</a>
            </div>
        </div>
    </div>
</body>
</html>