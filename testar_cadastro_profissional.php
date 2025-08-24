<?php
/**
 * Script para testar o cadastro de profissionais com sessão simulada
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste de Cadastro de Profissionais</h1>";

try {
    // Incluir arquivos necessários
    require_once 'config/database.php';
    require_once 'models/profissional.php';
    require_once 'models/salao.php';
    
    // Conectar ao banco
    $conn = getConnection();
    echo "<p>Conexão com banco estabelecida</p>";
    
    // Verificar tabela especialidades
    $stmt = $conn->query("SHOW TABLES LIKE 'especialidades'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p style='color: red;'>Tabela 'especialidades' não existe. Execute o script criar_tabela_especialidades_online.php primeiro.</p>";
        exit;
    }
    
    // Verificar se há especialidades cadastradas
    $stmt = $conn->query("SELECT COUNT(*) FROM especialidades");
    $count = $stmt->fetchColumn();
    
    echo "<p>Total de especialidades: {$count}</p>";
    
    if ($count == 0) {
        echo "<p style='color: red;'>Nenhuma especialidade cadastrada. Execute o script criar_tabela_especialidades_online.php primeiro.</p>";
        exit;
    }
    
    // Listar especialidades
    $stmt = $conn->query("SELECT id, nome FROM especialidades ORDER BY nome");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Especialidades disponíveis:</h2>";
    echo "<ul>";
    foreach ($especialidades as $esp) {
        echo "<li>{$esp['id']} - {$esp['nome']}</li>";
    }
    echo "</ul>";
    
    // Verificar se há salões cadastrados
    $stmt = $conn->query("SELECT COUNT(*) FROM saloes");
    $count = $stmt->fetchColumn();
    
    echo "<p>Total de salões: {$count}</p>";
    
    if ($count == 0) {
        echo "<p style='color: red;'>Nenhum salão cadastrado. Cadastre um salão primeiro.</p>";
        exit;
    }
    
    // Obter um salão para teste
    $stmt = $conn->query("SELECT id, nome FROM saloes LIMIT 1");
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Salão para teste: {$salao['id']} - {$salao['nome']}</p>";
    
    // Simular sessão de parceiro logado
    session_start();
    $_SESSION['usuario_id'] = 1; // ID do parceiro
    $_SESSION['usuario_tipo'] = 'parceiro';
    $_SESSION['salao_id'] = $salao['id']; // ID do salão
    
    echo "<p>Sessão simulada criada para parceiro com salão ID {$salao['id']}</p>";
    
    // Criar instância do modelo Profissional
    $profissional = new Profissional();
    
    // Dados para teste
    $nome = "Profissional Teste " . date('YmdHis');
    $especialidade = $especialidades[0]['nome']; // Primeira especialidade da lista
    $telefone = "(11) 99999-9999";
    $email = "teste" . date('YmdHis') . "@teste.com";
    $ativo = 1;
    
    echo "<h2>Dados para cadastro:</h2>";
    echo "<ul>";
    echo "<li>Nome: {$nome}</li>";
    echo "<li>Especialidade: {$especialidade}</li>";
    echo "<li>Telefone: {$telefone}</li>";
    echo "<li>Email: {$email}</li>";
    echo "<li>Ativo: {$ativo}</li>";
    echo "<li>Salão ID: {$salao['id']}</li>";
    echo "</ul>";
    
    // Tentar cadastrar
    $dados = [
        'nome' => $nome,
        'especialidade' => $especialidade,
        'telefone' => $telefone,
        'email' => $email,
        'ativo' => $ativo,
        'id_salao' => $salao['id']
    ];
    $resultado = $profissional->cadastrar($dados);
    
    if ($resultado) {
        echo "<p style='color: green;'>Profissional cadastrado com sucesso!</p>";
        
        // Verificar se foi realmente cadastrado
        $stmt = $conn->prepare("SELECT * FROM profissionais WHERE nome = ? AND email = ? AND id_salao = ?");
        $stmt->execute([$nome, $email, $salao['id']]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prof) {
            echo "<h2>Profissional cadastrado:</h2>";
            echo "<ul>";
            foreach ($prof as $key => $value) {
                echo "<li>{$key}: {$value}</li>";
            }
            echo "</ul>";
            
            // Limpar dados de teste
            $stmt = $conn->prepare("DELETE FROM profissionais WHERE id = ?");
            $stmt->execute([$prof['id']]);
            echo "<p>Dados de teste removidos.</p>";
        } else {
            echo "<p style='color: red;'>Erro: Profissional não encontrado após cadastro!</p>";
        }
    } else {
        echo "<p style='color: red;'>Erro ao cadastrar profissional!</p>";
        
        // Verificar erros no log
        $log_file = 'logs/error.log';
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            $lines = explode("\n", $log_content);
            $last_lines = array_slice($lines, -20); // Últimas 20 linhas
            
            echo "<h2>Últimos erros no log:</h2>";
            echo "<pre>" . implode("\n", $last_lines) . "</pre>";
        }
    }
    
    // Limpar sessão
    session_destroy();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " (linha " . $e->getLine() . ")</p>";
}
?>