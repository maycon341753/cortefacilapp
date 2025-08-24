<?php
/**
 * Script para testar se a mensagem de primeiro acesso foi resolvida
 */

require_once 'config/database.php';
require_once 'models/salao.php';

try {
    $conn = getConnection();
    $salao = new Salao();
    
    echo "<h2>Teste: Verificação de Primeiro Acesso</h2>";
    
    // Buscar um parceiro para teste
    $stmt = $conn->query("SELECT id, nome, email FROM usuarios WHERE tipo = 'Parceiro' LIMIT 1");
    $parceiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$parceiro) {
        echo "<p style='color: red;'>❌ Nenhum parceiro encontrado no banco de dados.</p>";
        exit;
    }
    
    echo "<p>Testando com o parceiro: <strong>{$parceiro['nome']}</strong> (ID: {$parceiro['id']})</p>";
    
    // Verificar se o parceiro tem salão
    $meu_salao = $salao->buscarPorDono($parceiro['id']);
    $editando = !empty($meu_salao);
    
    echo "<h3>Resultado do teste:</h3>";
    
    if ($editando) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; color: #155724;'>";
        echo "<h4>✅ SUCESSO!</h4>";
        echo "<p><strong>O parceiro TEM salão cadastrado!</strong></p>";
        echo "<p>Nome do salão: <strong>{$meu_salao['nome']}</strong></p>";
        echo "<p>ID do salão: <strong>{$meu_salao['id']}</strong></p>";
        echo "<p>📝 <strong>A mensagem 'Primeiro Acesso' NÃO será exibida.</strong></p>";
        echo "</div>";
        
        echo "<h4>Detalhes do salão:</h4>";
        echo "<ul>";
        echo "<li><strong>Nome:</strong> {$meu_salao['nome']}</li>";
        echo "<li><strong>Endereço:</strong> {$meu_salao['endereco']}</li>";
        echo "<li><strong>Telefone:</strong> {$meu_salao['telefone']}</li>";
        echo "<li><strong>Data de cadastro:</strong> {$meu_salao['data_cadastro']}</li>";
        echo "<li><strong>Status:</strong> " . ($meu_salao['ativo'] ? 'Ativo' : 'Inativo') . "</li>";
        echo "</ul>";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; color: #721c24;'>";
        echo "<h4>❌ PROBLEMA!</h4>";
        echo "<p><strong>O parceiro NÃO tem salão cadastrado!</strong></p>";
        echo "<p>🚨 <strong>A mensagem 'Primeiro Acesso' SERÁ exibida.</strong></p>";
        echo "</div>";
    }
    
    echo "<hr>";
    
    // Estatísticas gerais
    echo "<h3>Estatísticas gerais:</h3>";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'Parceiro'");
    $total_parceiros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT COUNT(DISTINCT s.id_dono) as total 
                         FROM saloes s 
                         INNER JOIN usuarios u ON s.id_dono = u.id 
                         WHERE u.tipo = 'Parceiro'");
    $parceiros_com_salao = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $parceiros_sem_salao = $total_parceiros - $parceiros_com_salao;
    
    echo "<ul>";
    echo "<li><strong>Total de parceiros:</strong> {$total_parceiros}</li>";
    echo "<li><strong>Parceiros com salão:</strong> {$parceiros_com_salao}</li>";
    echo "<li><strong>Parceiros sem salão:</strong> {$parceiros_sem_salao}</li>";
    echo "</ul>";
    
    if ($parceiros_sem_salao == 0) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; color: #0c5460;'>";
        echo "<h4>🎉 PERFEITO!</h4>";
        echo "<p><strong>Todos os parceiros têm salão cadastrado!</strong></p>";
        echo "<p>A mensagem de 'Primeiro Acesso' não aparecerá para nenhum parceiro.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; color: #856404;'>";
        echo "<h4>⚠️ ATENÇÃO!</h4>";
        echo "<p><strong>Ainda existem {$parceiros_sem_salao} parceiros sem salão.</strong></p>";
        echo "<p>Execute novamente o script 'criar_saloes_automaticos.php' para resolver.</p>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<p><a href='criar_saloes_automaticos.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 Executar correção automática</a></p>";
    echo "<p><a href='parceiro/salao.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏪 Ir para página do salão</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>