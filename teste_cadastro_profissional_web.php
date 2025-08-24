<?php
/**
 * Script para testar o cadastro de profissionais diretamente na página web
 * Simula uma sessão de parceiro logado e acessa a página profissionais.php
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Teste de Acesso à Página de Profissionais</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
echo ".info { color: blue; background: #e6f3ff; padding: 15px; border: 1px solid blue; margin: 15px 0; }\n";
echo "iframe { border: 1px solid #ddd; width: 100%; height: 600px; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🧪 Teste de Acesso à Página de Profissionais</h1>\n";

try {
    // Incluir arquivos necessários
    require_once 'config/database.php';
    
    // Conectar ao banco
    $conn = getConnection();
    echo "<div class='success'>\n";
    echo "<p>✅ Conexão com banco estabelecida</p>\n";
    echo "</div>\n";
    
    // Verificar se há salões cadastrados
    $stmt = $conn->query("SELECT COUNT(*) FROM saloes");
    $count = $stmt->fetchColumn();
    
    echo "<div class='info'>\n";
    echo "<p>Total de salões: {$count}</p>\n";
    echo "</div>\n";
    
    if ($count == 0) {
        echo "<div class='error'>\n";
        echo "<p>❌ Nenhum salão cadastrado. Cadastre um salão primeiro.</p>\n";
        echo "</div>\n";
        exit;
    }
    
    // Obter um salão para teste
    $stmt = $conn->query("SELECT id, nome FROM saloes LIMIT 1");
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>\n";
    echo "<p>Salão para teste: {$salao['id']} - {$salao['nome']}</p>\n";
    echo "</div>\n";
    
    // Verificar se há usuários parceiros
    $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'parceiro'");
    $count = $stmt->fetchColumn();
    
    echo "<div class='info'>\n";
    echo "<p>Total de parceiros: {$count}</p>\n";
    echo "</div>\n";
    
    if ($count == 0) {
        echo "<div class='error'>\n";
        echo "<p>❌ Nenhum parceiro cadastrado. Cadastre um parceiro primeiro.</p>\n";
        echo "</div>\n";
        exit;
    }
    
    // Obter um parceiro para teste
    $stmt = $conn->query("SELECT id, nome, email FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
    $parceiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>\n";
    echo "<p>Parceiro para teste: {$parceiro['id']} - {$parceiro['nome']} ({$parceiro['email']})</p>\n";
    echo "</div>\n";
    
    // Verificar se o salão está associado ao parceiro
    $stmt = $conn->prepare("SELECT COUNT(*) FROM saloes WHERE id = ? AND id_dono = ?");
    $stmt->execute([$salao['id'], $parceiro['id']]);
    $associado = $stmt->fetchColumn() > 0;
    
    if (!$associado) {
        // Associar o salão ao parceiro
        $stmt = $conn->prepare("UPDATE saloes SET id_dono = ? WHERE id = ?");
        $stmt->execute([$parceiro['id'], $salao['id']]);
        
        echo "<div class='success'>\n";
        echo "<p>✅ Salão associado ao parceiro para teste</p>\n";
        echo "</div>\n";
    } else {
        echo "<div class='info'>\n";
        echo "<p>ℹ️ Salão já está associado ao parceiro</p>\n";
        echo "</div>\n";
    }
    
    // Criar arquivo temporário para simular sessão
    $temp_file = __DIR__ . '/parceiro/teste_sessao_temp.php';
    $session_code = "<?php\n";
    $session_code .= "// Arquivo temporário para teste - será excluído automaticamente\n";
    $session_code .= "session_start();\n";
    $session_code .= "\$_SESSION['usuario_id'] = {$parceiro['id']};\n";
    $session_code .= "\$_SESSION['usuario_tipo'] = 'parceiro';\n";
    $session_code .= "\$_SESSION['usuario_nome'] = '{$parceiro['nome']}';\n";
    $session_code .= "\$_SESSION['usuario_email'] = '{$parceiro['email']}';\n";
    $session_code .= "\$_SESSION['salao_id'] = {$salao['id']};\n";
    $session_code .= "\$_SESSION['salao_nome'] = '{$salao['nome']}';\n";
    $session_code .= "header('Location: profissionais.php');\n";
    $session_code .= "?>\n";
    
    file_put_contents($temp_file, $session_code);
    
    echo "<div class='success'>\n";
    echo "<p>✅ Arquivo temporário de sessão criado</p>\n";
    echo "</div>\n";
    
    // Gerar URL para teste
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://");
    $base_url .= $_SERVER['HTTP_HOST'];
    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    
    $test_url = $base_url . 'parceiro/teste_sessao_temp.php';
    
    echo "<div class='info'>\n";
    echo "<p>🔗 URL para teste: <a href='{$test_url}' target='_blank'>{$test_url}</a></p>\n";
    echo "</div>\n";
    
    echo "<h2>📋 Instruções para Teste</h2>\n";
    echo "<ol>\n";
    echo "<li>Clique no link acima ou no botão abaixo para acessar a página de profissionais com sessão simulada</li>\n";
    echo "<li>Tente cadastrar um novo profissional usando o botão '+ Novo Profissional'</li>\n";
    echo "<li>Verifique se o dropdown de especialidades está carregando corretamente</li>\n";
    echo "<li>Complete o cadastro e verifique se o profissional aparece na lista</li>\n";
    echo "</ol>\n";
    
    echo "<div style='margin: 20px 0;'>\n";
    echo "<a href='{$test_url}' target='_blank' style='display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Acessar Página de Profissionais</a>\n";
    echo "</div>\n";
    
    echo "<h2>🖥️ Visualização da Página</h2>\n";
    echo "<iframe src='{$test_url}' title='Teste de Profissionais'></iframe>\n";
    
    // Configurar exclusão automática do arquivo temporário após 5 minutos
    echo "<script>\n";
    echo "setTimeout(function() {\n";
    echo "    fetch('limpar_teste_sessao.php');\n";
    echo "    alert('O arquivo de teste temporário foi excluído automaticamente.');\n";
    echo "}, 300000); // 5 minutos\n";
    echo "</script>\n";
    
    // Criar arquivo para limpar o arquivo temporário
    $cleanup_file = __DIR__ . '/limpar_teste_sessao.php';
    $cleanup_code = "<?php\n";
    $cleanup_code .= "// Excluir arquivo temporário de teste\n";
    $cleanup_code .= "\$temp_file = __DIR__ . '/parceiro/teste_sessao_temp.php';\n";
    $cleanup_code .= "if (file_exists(\$temp_file)) {\n";
    $cleanup_code .= "    unlink(\$temp_file);\n";
    $cleanup_code .= "    echo 'Arquivo temporário excluído';\n";
    $cleanup_code .= "} else {\n";
    $cleanup_code .= "    echo 'Arquivo temporário não encontrado';\n";
    $cleanup_code .= "}\n";
    $cleanup_code .= "?>\n";
    
    file_put_contents($cleanup_file, $cleanup_code);
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>