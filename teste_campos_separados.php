<?php
/**
 * Teste dos campos separados de endereço
 */

require_once 'includes/auth.php';

echo "<h2>Teste dos Campos Separados de Endereço</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Dados Recebidos:</h3>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> " . htmlspecialchars($_POST['nome'] ?? '') . "</li>";
    echo "<li><strong>Rua:</strong> " . htmlspecialchars($_POST['rua'] ?? '') . "</li>";
    echo "<li><strong>Número:</strong> " . htmlspecialchars($_POST['numero'] ?? '') . "</li>";
    echo "<li><strong>Complemento:</strong> " . htmlspecialchars($_POST['complemento'] ?? '') . "</li>";
    echo "<li><strong>Bairro:</strong> " . htmlspecialchars($_POST['bairro'] ?? '') . "</li>";
    echo "<li><strong>Cidade:</strong> " . htmlspecialchars($_POST['cidade'] ?? '') . "</li>";
    echo "<li><strong>Estado:</strong> " . htmlspecialchars($_POST['estado'] ?? '') . "</li>";
    echo "<li><strong>CEP:</strong> " . htmlspecialchars($_POST['cep'] ?? '') . "</li>";
    echo "<li><strong>Telefone:</strong> " . htmlspecialchars($_POST['telefone'] ?? '') . "</li>";
    echo "</ul>";
    
    // Montar endereço completo
    $endereco_partes = [];
    if (!empty($_POST['rua'])) $endereco_partes[] = $_POST['rua'];
    if (!empty($_POST['numero'])) $endereco_partes[] = $_POST['numero'];
    if (!empty($_POST['complemento'])) $endereco_partes[] = $_POST['complemento'];
    if (!empty($_POST['bairro'])) $endereco_partes[] = $_POST['bairro'];
    if (!empty($_POST['cidade'])) $endereco_partes[] = $_POST['cidade'];
    if (!empty($_POST['estado'])) $endereco_partes[] = $_POST['estado'];
    if (!empty($_POST['cep'])) $endereco_partes[] = $_POST['cep'];
    
    $endereco_completo = implode(', ', $endereco_partes);
    echo "<h3>Endereço Completo Montado:</h3>";
    echo "<p><strong>" . htmlspecialchars($endereco_completo) . "</strong></p>";
    
    echo "<h3>Dados para Banco Online:</h3>";
    echo "<pre>";
    echo json_encode([
        'nome' => $_POST['nome'] ?? '',
        'rua' => $_POST['rua'] ?? '',
        'numero' => $_POST['numero'] ?? '',
        'complemento' => $_POST['complemento'] ?? '',
        'bairro' => $_POST['bairro'] ?? '',
        'cidade' => $_POST['cidade'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'cep' => $_POST['cep'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'endereco_completo' => $endereco_completo
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Campos Separados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <form method="POST">
            <?php echo generateCsrfToken(); ?>
            
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Salão *</label>
                <input type="text" class="form-control" id="nome" name="nome" 
                       placeholder="Ex: Salão Beleza & Estilo" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="rua" class="form-label">Rua/Avenida *</label>
                    <input type="text" class="form-control" id="rua" name="rua" 
                           placeholder="Ex: Rua das Flores" required>
                </div>
                <div class="col-md-4">
                    <label for="numero" class="form-label">Número *</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           placeholder="123" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="bairro" class="form-label">Bairro *</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" 
                           placeholder="Ex: Centro" required>
                </div>
                <div class="col-md-6">
                    <label for="cidade" class="form-label">Cidade *</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" 
                           placeholder="Ex: São Paulo" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="cep" class="form-label">CEP *</label>
                    <input type="text" class="form-control" id="cep" name="cep" 
                           placeholder="00000-000" required>
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="">Selecione...</option>
                        <option value="SP">São Paulo</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="MG">Minas Gerais</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" 
                           placeholder="Apto, Sala, etc.">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="telefone" class="form-label">Telefone *</label>
                <input type="tel" class="form-control" id="telefone" name="telefone" 
                       placeholder="(11) 99999-9999" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Testar Envio</button>
        </form>
    </div>
</body>
</html>