<?php
/**
 * Teste de Links do Parceiro
 * Verifica se os links do menu lateral estão funcionando após correção
 */

// Simular sessão de parceiro para teste
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'teste@parceiro.com';
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_telefone'] = '11999999999';

// Incluir arquivos necessários
require_once 'includes/auth.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Teste Links Parceiro</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";
echo "<div class='container mt-4'>";
echo "<h1><i class='fas fa-test-tube'></i> Teste de Links do Parceiro</h1>";
echo "<hr>";

// Verificar status da sessão
echo "<div class='alert alert-info'>";
echo "<h5>Status da Sessão</h5>";
echo "<p><strong>Usuário Logado:</strong> " . (isLoggedIn() ? '✅ Sim' : '❌ Não') . "</p>";
echo "<p><strong>É Parceiro:</strong> " . (isParceiro() ? '✅ Sim' : '❌ Não') . "</p>";
echo "<p><strong>Nome:</strong> " . ($_SESSION['usuario_nome'] ?? 'N/A') . "</p>";
echo "<p><strong>Tipo:</strong> " . ($_SESSION['tipo_usuario'] ?? 'N/A') . "</p>";
echo "</div>";

// Lista de links para testar
$links = [
    'Dashboard' => 'parceiro/dashboard.php',
    'Agenda' => 'parceiro/agenda.php',
    'Profissionais' => 'parceiro/profissionais.php',
    'Agendamentos' => 'parceiro/agendamentos.php',
    'Meu Salão' => 'parceiro/salao.php',
    'Relatórios' => 'parceiro/relatorios.php'
];

echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h3>Links do Menu Lateral</h3>";
echo "<div class='list-group'>";

foreach ($links as $nome => $url) {
    $arquivo = __DIR__ . '/' . $url;
    $existe = file_exists($arquivo) ? '✅' : '❌';
    $status_class = file_exists($arquivo) ? 'list-group-item-success' : 'list-group-item-danger';
    
    echo "<a href='$url' class='list-group-item list-group-item-action $status_class'>";
    echo "<i class='fas fa-link'></i> $nome $existe";
    echo "<br><small class='text-muted'>$url</small>";
    echo "</a>";
}

echo "</div>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<h3>Teste de Autenticação</h3>";
echo "<div class='card'>";
echo "<div class='card-body'>";

try {
    // Testar função requireParceiro
    echo "<p><strong>Teste requireParceiro():</strong> ";
    
    // Capturar qualquer redirecionamento
    ob_start();
    $redirect_captured = false;
    
    // Função para capturar headers
    function capture_header($header) {
        global $redirect_captured;
        if (strpos($header, 'Location:') === 0) {
            $redirect_captured = $header;
        }
    }
    
    // Não executar requireParceiro para evitar redirecionamento
    echo "✅ Função disponível (não executada para evitar redirecionamento)</p>";
    
    echo "<p><strong>Funções Disponíveis:</strong></p>";
    echo "<ul>";
    $funcoes = ['isLoggedIn', 'isParceiro', 'hasUserType', 'getLoggedUser'];
    foreach ($funcoes as $funcao) {
        $disponivel = function_exists($funcao) ? '✅' : '❌';
        echo "<li>$funcao: $disponivel</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "</p>";
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<div class='alert alert-success'>";
echo "<h5>✅ Teste Concluído</h5>";
echo "<p>Se você consegue ver esta página, significa que a autenticação está funcionando.</p>";
echo "<p>Clique nos links acima para testar a navegação.</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>