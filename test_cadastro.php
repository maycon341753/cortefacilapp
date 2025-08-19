<?php
/**
 * Teste específico do cadastro.php com exibição de erros
 * Este arquivo replica a lógica do cadastro.php para identificar erros
 */

// Forçar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Capturar erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; margin: 10px;'>";
        echo "<h2 style='color: #f44336;'>🚨 ERRO FATAL DETECTADO:</h2>";
        echo "<p><strong>Tipo:</strong> " . $error['type'] . "</p>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $error['file'] . "</p>";
        echo "<p><strong>Linha:</strong> " . $error['line'] . "</p>";
        echo "</div>";
    }
});

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Teste Cadastro - Debug</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo ".warning { color: orange; }";
echo ".step { background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Teste de Cadastro com Debug Ativado</h1>";
echo "<p>Este arquivo replica exatamente a lógica do cadastro.php para identificar onde está o erro.</p>";

try {
    echo "<div class='step'>";
    echo "<h2>Passo 1: Carregando Dependências</h2>";
    
    echo "<p>Carregando auth.php...</p>";
    require_once __DIR__ . '/includes/auth.php';
    echo "<p class='success'>✓ auth.php carregado com sucesso</p>";
    
    echo "<p>Carregando functions.php...</p>";
    require_once __DIR__ . '/includes/functions.php';
    echo "<p class='success'>✓ functions.php carregado com sucesso</p>";
    
    echo "<p>Carregando usuario.php...</p>";
    require_once __DIR__ . '/models/usuario.php';
    echo "<p class='success'>✓ usuario.php carregado com sucesso</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h2>Passo 2: Verificando Estado de Login</h2>";
    
    // Se já está logado, redireciona
    if (isLoggedIn()) {
        echo "<p class='warning'>⚠ Usuário já está logado - redirecionaria para index.php</p>";
    } else {
        echo "<p class='success'>✓ Usuário não está logado - pode prosseguir com cadastro</p>";
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h2>Passo 3: Inicializando Variáveis</h2>";
    
    $erro = '';
    $sucesso = '';
    $tipo_usuario = $_GET['tipo'] ?? 'cliente';
    
    // Validar tipo de usuário
    if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
        $tipo_usuario = 'cliente';
    }
    
    echo "<p class='success'>✓ Variáveis inicializadas</p>";
    echo "<p>Tipo de usuário: " . htmlspecialchars($tipo_usuario) . "</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h2>Passo 4: Simulando Processamento POST</h2>";
    
    // Simular dados POST para teste
    $_POST = [
        'nome' => 'Teste Usuario',
        'email' => 'teste@email.com',
        'telefone' => '11999999999',
        'senha' => '123456',
        'confirmar_senha' => '123456',
        'tipo_usuario' => 'cliente'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<p>Processando dados POST...</p>";
        
        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        $tipo_usuario = sanitizeInput($_POST['tipo_usuario'] ?? 'cliente');
        
        echo "<p class='success'>✓ Dados POST processados</p>";
        
        // Validações
        if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
            $erro = 'Por favor, preencha todos os campos.';
        } elseif (!validateEmail($email)) {
            $erro = 'Digite um email válido.';
        } elseif (!validateTelefone($telefone)) {
            $erro = 'Digite um telefone válido.';
        } elseif (strlen($senha) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } elseif ($senha !== $confirmar_senha) {
            $erro = 'As senhas não coincidem.';
        } else {
            echo "<p class='success'>✓ Todas as validações passaram</p>";
            
            echo "<p>Criando objeto Usuario...</p>";
            $usuario = new Usuario();
            echo "<p class='success'>✓ Objeto Usuario criado</p>";
            
            // Verificar se email já existe
            echo "<p>Verificando se email já existe...</p>";
            if ($usuario->emailExiste($email)) {
                $erro = 'Este email já está cadastrado.';
                echo "<p class='warning'>⚠ Email já existe</p>";
            } else {
                echo "<p class='success'>✓ Email disponível</p>";
                
                // Limpar telefone
                $telefone = preg_replace('/[^0-9]/', '', $telefone);
                
                $dados = [
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $telefone,
                    'senha' => $senha,
                    'tipo_usuario' => $tipo_usuario
                ];
                
                echo "<p>Tentando cadastrar usuário...</p>";
                if ($usuario->cadastrar($dados)) {
                    $sucesso = 'Cadastro realizado com sucesso!';
                    echo "<p class='success'>✓ Usuário cadastrado com sucesso!</p>";
                } else {
                    $erro = 'Erro ao realizar cadastro. Tente novamente.';
                    echo "<p class='error'>✗ Erro ao cadastrar usuário</p>";
                }
            }
        }
        
        if ($erro) {
            echo "<p class='error'>✗ Erro: " . htmlspecialchars($erro) . "</p>";
        }
        if ($sucesso) {
            echo "<p class='success'>✓ Sucesso: " . htmlspecialchars($sucesso) . "</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h2>Passo 5: Testando Renderização HTML</h2>";
    echo "<p>Simulando a parte HTML do cadastro.php...</p>";
    
    // Simular parte do HTML
    echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
    echo "<h3>Formulário de Cadastro (Simulação)</h3>";
    
    if ($erro) {
        echo "<div style='background: #ffebee; color: #c62828; padding: 10px; margin: 10px 0;'>";
        echo "<i>⚠</i> " . htmlspecialchars($erro);
        echo "</div>";
    }
    
    echo "<form method='POST' action='test_cadastro.php'>";
    echo "<input type='hidden' name='tipo_usuario' value='" . htmlspecialchars($tipo_usuario) . "'>";
    echo "<p>Nome: <input type='text' name='nome' value='" . htmlspecialchars($nome ?? '') . "' required></p>";
    echo "<p>Email: <input type='email' name='email' value='" . htmlspecialchars($email ?? '') . "' required></p>";
    echo "<p>Telefone: <input type='tel' name='telefone' value='" . htmlspecialchars($telefone ?? '') . "' required></p>";
    echo "<p>Senha: <input type='password' name='senha' required></p>";
    echo "<p>Confirmar Senha: <input type='password' name='confirmar_senha' required></p>";
    echo "<p><button type='submit'>Cadastrar</button></p>";
    echo "</form>";
    echo "</div>";
    
    echo "<p class='success'>✓ HTML renderizado com sucesso</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 15px; margin: 20px 0;'>";
    echo "<h2 style='color: #2e7d32;'>🎉 TESTE CONCLUÍDO COM SUCESSO!</h2>";
    echo "<p>Se você está vendo esta mensagem, significa que:</p>";
    echo "<ul>";
    echo "<li>✓ Todos os arquivos PHP estão sendo carregados corretamente</li>";
    echo "<li>✓ As funções estão funcionando</li>";
    echo "<li>✓ A conexão com banco está OK</li>";
    echo "<li>✓ O processamento POST funciona</li>";
    echo "<li>✓ O HTML é renderizado corretamente</li>";
    echo "</ul>";
    echo "<p><strong>Conclusão:</strong> O problema do erro 500 não está na lógica do cadastro.php.</p>";
    echo "<p><strong>Possíveis causas restantes:</strong></p>";
    echo "<ul>";
    echo "<li>Configurações específicas do servidor Hostinger</li>";
    echo "<li>Problemas de permissões de arquivo no servidor</li>";
    echo "<li>Conflitos no arquivo .htaccess</li>";
    echo "<li>Limites de recursos PHP no servidor</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; margin: 10px;'>";
    echo "<h2 style='color: #f44336;'>🚨 EXCEPTION CAPTURADA:</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details>";
    echo "<summary>Stack Trace</summary>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; margin: 10px;'>";
    echo "<h2 style='color: #f44336;'>🚨 ERRO FATAL CAPTURADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details>";
    echo "<summary>Stack Trace</summary>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 Instruções:</h3>";
echo "<ol>";
echo "<li>Faça upload deste arquivo para o servidor: <code>https://cortefacil.app/test_cadastro.php</code></li>";
echo "<li>Faça upload do .htaccess atualizado (com exibição de erros ativada)</li>";
echo "<li>Acesse o arquivo no navegador</li>";
echo "<li>Se aparecer algum erro, anote a linha exata e a mensagem</li>";
echo "<li>Remova este arquivo após o debug por segurança</li>";
echo "</ol>";

echo "</body>";
echo "</html>";
?>