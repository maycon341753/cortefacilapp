<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Salao.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar se é parceiro
verificarParceiro();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizarString($_POST['nome'] ?? '');
    $endereco = sanitizarString($_POST['endereco'] ?? '');
    $telefone = sanitizarString($_POST['telefone'] ?? '');
    $descricao = sanitizarString($_POST['descricao'] ?? '');
    $horario_funcionamento = sanitizarString($_POST['horario_funcionamento'] ?? '');
    
    // Validações
    if (empty($nome) || empty($endereco) || empty($telefone) || empty($horario_funcionamento)) {
        $erro = 'Todos os campos obrigatórios devem ser preenchidos.';
    } else {
        // Conectar ao banco
        $database = new Database();
        $db = $database->getConnection();
        
        // Verificar se o parceiro já tem um salão
        $salao = new Salao($db);
        $salao->id_dono = $_SESSION['usuario_id'];
        if ($salao->buscarPorDono()) {
            $erro = 'Você já possui um salão cadastrado.';
        } else {
            // Criar novo salão
            $salao->nome = $nome;
            $salao->endereco = $endereco;
            $salao->telefone = $telefone;
            $salao->descricao = $descricao;
            $salao->horario_funcionamento = $horario_funcionamento;
            $salao->id_dono = $_SESSION['usuario_id'];
            
            if ($salao->criar()) {
                $sucesso = 'Salão cadastrado com sucesso!';
                // Redirecionar após 2 segundos
                header("refresh:2;url=dashboard.php");
            } else {
                $erro = 'Erro ao cadastrar salão. Tente novamente.';
            }
        }
    }
}

// Se chegou aqui via GET, redirecionar para dashboard
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Salão - CorteFácil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <?php if ($erro): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($sucesso); ?>
                    <p>Redirecionando para o dashboard...</p>
                </div>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-outline">← Voltar ao Dashboard</a>
        </div>
    </div>
</body>
</html>