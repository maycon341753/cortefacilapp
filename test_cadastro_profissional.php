<?php
/**
 * Teste específico para cadastro de profissionais
 * Simula o processo completo de cadastro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste - Cadastro de Profissionais</h2>";

// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

echo "<h3>1. Verificando autenticação...</h3>";

// Simular usuário logado (para teste)
$_SESSION['user_id'] = 1;
$_SESSION['user_tipo'] = 'parceiro';
$_SESSION['user_nome'] = 'Teste Parceiro';
$_SESSION['user_email'] = 'teste@parceiro.com';

echo "✅ Usuário simulado logado<br>";

// Verificar se tem salão
echo "<h3>2. Verificando salão...</h3>";
$salao = new Salao();
$meu_salao = $salao->buscarPorDono(1);

if (!$meu_salao) {
    echo "❌ Nenhum salão encontrado para o usuário<br>";
    echo "<strong>Criando salão de teste...</strong><br>";
    
    // Criar salão de teste
    $dados_salao = [
        'nome' => 'Salão Teste Debug',
        'endereco' => 'Rua Teste, 123',
        'telefone' => '(11) 99999-9999',
        'id_dono' => 1
    ];
    
    $resultado_salao = $salao->cadastrar($dados_salao);
    if ($resultado_salao) {
        echo "✅ Salão de teste criado<br>";
        $meu_salao = $salao->buscarPorDono(1);
    } else {
        echo "❌ Erro ao criar salão de teste<br>";
        exit;
    }
} else {
    echo "✅ Salão encontrado: {$meu_salao['nome']}<br>";
}

echo "<h3>3. Testando geração de token CSRF...</h3>";
$csrf_token = generateCSRFToken();
echo "✅ Token CSRF gerado: " . substr($csrf_token, 0, 20) . "...<br>";

echo "<h3>4. Simulando POST de cadastro...</h3>";

// Simular dados POST
$_POST = [
    'acao' => 'cadastrar',
    'csrf_token' => $csrf_token,
    'nome' => 'João Silva Teste',
    'especialidade' => 'Corte e Barba',
    'telefone' => '(11) 98765-4321',
    'email' => 'joao@teste.com',
    'ativo' => '1'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<strong>Dados POST simulados:</strong><br>";
foreach ($_POST as $key => $value) {
    if ($key !== 'csrf_token') {
        echo "- {$key}: {$value}<br>";
    } else {
        echo "- {$key}: " . substr($value, 0, 20) . "...<br>";
    }
}

echo "<h3>5. Executando validações...</h3>";

$erro = '';
$sucesso = '';

try {
    // Validar CSRF
    echo "Validando CSRF...<br>";
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Token de segurança inválido.');
    }
    echo "✅ CSRF válido<br>";
    
    $acao = $_POST['acao'] ?? '';
    echo "Ação: {$acao}<br>";
    
    if ($acao === 'cadastrar') {
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        $especialidade = trim($_POST['especialidade'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        echo "Validando nome...<br>";
        if (empty($nome)) {
            throw new Exception('Nome do profissional é obrigatório.');
        }
        
        if (strlen($nome) < 3) {
            throw new Exception('Nome deve ter pelo menos 3 caracteres.');
        }
        echo "✅ Nome válido<br>";
        
        echo "Validando especialidade...<br>";
        if (empty($especialidade)) {
            throw new Exception('Especialidade é obrigatória.');
        }
        echo "✅ Especialidade válida<br>";
        
        // Validar email se fornecido
        if (!empty($email)) {
            echo "Validando email...<br>";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }
            echo "✅ Email válido<br>";
        }
        
        // Preparar dados
        $dados = [
            'nome' => $nome,
            'especialidade' => $especialidade,
            'telefone' => $telefone,
            'email' => $email,
            'ativo' => $ativo,
            'id_salao' => $meu_salao['id']
        ];
        
        echo "<strong>Dados preparados para cadastro:</strong><br>";
        foreach ($dados as $key => $value) {
            echo "- {$key}: {$value}<br>";
        }
        
        echo "Executando cadastro...<br>";
        $profissional = new Profissional();
        $resultado = $profissional->cadastrar($dados);
        
        if ($resultado) {
            $sucesso = 'Profissional cadastrado com sucesso!';
            echo "✅ {$sucesso}<br>";
            
            // Buscar o profissional recém-cadastrado
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT * FROM profissionais WHERE nome = ? AND id_salao = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$nome, $meu_salao['id']]);
            $prof_cadastrado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prof_cadastrado) {
                echo "<strong>Profissional cadastrado no banco:</strong><br>";
                foreach ($prof_cadastrado as $key => $value) {
                    echo "- {$key}: {$value}<br>";
                }
                
                // Limpar dados de teste
                $stmt = $conn->prepare("DELETE FROM profissionais WHERE id = ?");
                $stmt->execute([$prof_cadastrado['id']]);
                echo "🧹 Dados de teste removidos<br>";
            }
        } else {
            throw new Exception('Erro ao salvar dados do profissional.');
        }
    }
    
} catch (Exception $e) {
    $erro = $e->getMessage();
    echo "❌ Erro: {$erro}<br>";
}

echo "<h3>Resultado do teste:</h3>";
if ($sucesso) {
    echo "<div style='color: green; font-weight: bold;'>✅ SUCESSO: {$sucesso}</div>";
} elseif ($erro) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERRO: {$erro}</div>";
} else {
    echo "<div style='color: orange; font-weight: bold;'>⚠️ Teste incompleto</div>";
}

echo "<br><a href='parceiro/profissionais.php'>← Voltar para Profissionais</a>";
?>