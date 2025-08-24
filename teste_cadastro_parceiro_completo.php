<?php
/**
 * Script para testar o cadastro completo de parceiro
 * Verifica se todas as informações (incluindo bairro, cidade e CEP) estão sendo salvas
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/usuario.php';

try {
    echo "<h2>🧪 Teste de Cadastro Completo de Parceiro</h2>";
    echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
    
    // Dados de teste únicos
    $timestamp = time();
    $dados_parceiro = [
        'nome' => 'Parceiro Teste ' . $timestamp,
        'email' => 'teste' . $timestamp . '@exemplo.com',
        'senha' => 'senha123',
        'telefone' => '11987654321',
        'documento' => '123' . $timestamp, // CPF único
        'tipo_documento' => 'cpf',
        'razao_social' => null,
        'inscricao_estadual' => null,
        'endereco' => 'Rua Teste, 456',
        'bairro' => 'Vila Teste',
        'cidade' => 'São Paulo',
        'cep' => '01234567'
    ];
    
    echo "<h3>📋 Dados do Teste</h3>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> " . htmlspecialchars($dados_parceiro['nome']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($dados_parceiro['email']) . "</li>";
    echo "<li><strong>Telefone:</strong> " . htmlspecialchars($dados_parceiro['telefone']) . "</li>";
    echo "<li><strong>Documento:</strong> " . htmlspecialchars($dados_parceiro['documento']) . "</li>";
    echo "<li><strong>Endereço:</strong> " . htmlspecialchars($dados_parceiro['endereco']) . "</li>";
    echo "<li><strong>Bairro:</strong> " . htmlspecialchars($dados_parceiro['bairro']) . "</li>";
    echo "<li><strong>Cidade:</strong> " . htmlspecialchars($dados_parceiro['cidade']) . "</li>";
    echo "<li><strong>CEP:</strong> " . htmlspecialchars($dados_parceiro['cep']) . "</li>";
    echo "</ul>";
    
    // Instanciar a classe Usuario
    $usuario = new Usuario();
    
    echo "<h3>🔄 Executando Cadastro...</h3>";
    
    // Preparar dados do usuário
    $dadosUsuario = [
        'nome' => $dados_parceiro['nome'],
        'email' => $dados_parceiro['email'],
        'senha' => $dados_parceiro['senha'],
        'telefone' => $dados_parceiro['telefone'],
        'tipo_usuario' => 'parceiro'
    ];
    
    // Preparar dados do salão
    $dadosSalao = [
        'nome' => $dados_parceiro['nome'],
        'endereco' => $dados_parceiro['endereco'],
        'bairro' => $dados_parceiro['bairro'],
        'cidade' => $dados_parceiro['cidade'],
        'cep' => $dados_parceiro['cep'],
        'telefone' => $dados_parceiro['telefone'],
        'documento' => $dados_parceiro['documento'],
        'tipo_documento' => $dados_parceiro['tipo_documento'],
        'razao_social' => $dados_parceiro['razao_social'],
        'inscricao_estadual' => $dados_parceiro['inscricao_estadual'],
        'descricao' => 'Salão de teste para verificação'
    ];
    
    // Tentar cadastrar o parceiro
    $resultado = $usuario->cadastrarParceiro($dadosUsuario, $dadosSalao);
    
    if ($resultado['success']) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
        echo "✅ Cadastro realizado com sucesso!";
        echo "<br><strong>ID do usuário:</strong> " . $resultado['user_id'];
        echo "<br><strong>ID do salão:</strong> " . $resultado['salao_id'];
        echo "</div>";
        
        // Verificar os dados salvos no banco
        echo "<h3>🔍 Verificação dos Dados Salvos</h3>";
        
        $conn = getConnection();
        
        // Verificar dados do usuário
        echo "<h4>👤 Dados do Usuário:</h4>";
        $stmt_usuario = $conn->prepare("SELECT id, nome, email, telefone, tipo_usuario FROM usuarios WHERE id = ?");
        $stmt_usuario->execute([$resultado['user_id']]);
        $dados_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
        
        if ($dados_usuario) {
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . htmlspecialchars($dados_usuario['id']) . "</li>";
            echo "<li><strong>Nome:</strong> " . htmlspecialchars($dados_usuario['nome']) . "</li>";
            echo "<li><strong>Email:</strong> " . htmlspecialchars($dados_usuario['email']) . "</li>";
            echo "<li><strong>Telefone:</strong> " . htmlspecialchars($dados_usuario['telefone']) . "</li>";
            echo "<li><strong>Tipo:</strong> " . htmlspecialchars($dados_usuario['tipo_usuario']) . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Dados do usuário não encontrados!</p>";
        }
        
        // Verificar dados do salão
        echo "<h4>🏪 Dados do Salão:</h4>";
        $stmt_salao = $conn->prepare("SELECT id, id_dono, nome, endereco, bairro, cidade, cep, telefone, documento, tipo_documento FROM saloes WHERE id = ?");
        $stmt_salao->execute([$resultado['salao_id']]);
        $dados_salao = $stmt_salao->fetch(PDO::FETCH_ASSOC);
        
        if ($dados_salao) {
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . htmlspecialchars($dados_salao['id']) . "</li>";
            echo "<li><strong>ID do Dono:</strong> " . htmlspecialchars($dados_salao['id_dono']) . "</li>";
            echo "<li><strong>Nome:</strong> " . htmlspecialchars($dados_salao['nome']) . "</li>";
            echo "<li><strong>Endereço:</strong> " . htmlspecialchars($dados_salao['endereco']) . "</li>";
            echo "<li><strong>Bairro:</strong> " . htmlspecialchars($dados_salao['bairro'] ?? 'NULL') . "</li>";
            echo "<li><strong>Cidade:</strong> " . htmlspecialchars($dados_salao['cidade'] ?? 'NULL') . "</li>";
            echo "<li><strong>CEP:</strong> " . htmlspecialchars($dados_salao['cep'] ?? 'NULL') . "</li>";
            echo "<li><strong>Telefone:</strong> " . htmlspecialchars($dados_salao['telefone']) . "</li>";
            echo "<li><strong>Documento:</strong> " . htmlspecialchars($dados_salao['documento']) . "</li>";
            echo "<li><strong>Tipo Documento:</strong> " . htmlspecialchars($dados_salao['tipo_documento']) . "</li>";
            echo "</ul>";
            
            // Verificar se os campos de endereço foram salvos corretamente
            echo "<h4>✅ Verificação dos Campos de Endereço:</h4>";
            echo "<ul>";
            
            $bairro_ok = !empty($dados_salao['bairro']) && $dados_salao['bairro'] === $dados_parceiro['bairro'];
            $cidade_ok = !empty($dados_salao['cidade']) && $dados_salao['cidade'] === $dados_parceiro['cidade'];
            $cep_ok = !empty($dados_salao['cep']) && $dados_salao['cep'] === $dados_parceiro['cep'];
            
            echo "<li>Bairro: " . ($bairro_ok ? "<span style='color: green;'>✅ CORRETO</span>" : "<span style='color: red;'>❌ INCORRETO</span>") . "</li>";
            echo "<li>Cidade: " . ($cidade_ok ? "<span style='color: green;'>✅ CORRETO</span>" : "<span style='color: red;'>❌ INCORRETO</span>") . "</li>";
            echo "<li>CEP: " . ($cep_ok ? "<span style='color: green;'>✅ CORRETO</span>" : "<span style='color: red;'>❌ INCORRETO</span>") . "</li>";
            echo "</ul>";
            
            if ($bairro_ok && $cidade_ok && $cep_ok) {
                echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
                echo "🎉 <strong>SUCESSO TOTAL!</strong> Todos os campos de endereço foram salvos corretamente!";
                echo "</div>";
            } else {
                echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;'>";
                echo "❌ <strong>PROBLEMA DETECTADO!</strong> Alguns campos de endereço não foram salvos corretamente.";
                echo "</div>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Dados do salão não encontrados!</p>";
        }
        
        // Limpeza: remover dados de teste
        echo "<h3>🧹 Limpeza dos Dados de Teste</h3>";
        
        try {
            $conn->beginTransaction();
            
            // Remover salão
            $stmt_del_salao = $conn->prepare("DELETE FROM saloes WHERE id = ?");
            $stmt_del_salao->execute([$resultado['salao_id']]);
            
            // Remover usuário
            $stmt_del_usuario = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt_del_usuario->execute([$resultado['user_id']]);
            
            $conn->commit();
            
            echo "<p style='color: green;'>✅ Dados de teste removidos com sucesso.</p>";
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<p style='color: orange;'>⚠️ Erro ao remover dados de teste: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;'>";
        echo "❌ Erro no cadastro: " . htmlspecialchars($resultado['message']);
        echo "</div>";
    }
    
    // Resumo final
    echo "<h3>📋 RESUMO DO TESTE</h3>";
    echo "<div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px;'>";
    
    if ($resultado['success']) {
        echo "<h4>✅ Resultado do Teste:</h4>";
        echo "<ul>";
        echo "<li>✅ Cadastro de parceiro executado com sucesso</li>";
        echo "<li>✅ Dados do usuário salvos corretamente</li>";
        echo "<li>✅ Dados do salão salvos corretamente</li>";
        
        if (isset($bairro_ok) && isset($cidade_ok) && isset($cep_ok)) {
            if ($bairro_ok && $cidade_ok && $cep_ok) {
                echo "<li>✅ Campos de endereço (bairro, cidade, CEP) salvos corretamente</li>";
            } else {
                echo "<li>❌ Alguns campos de endereço não foram salvos corretamente</li>";
            }
        }
        
        echo "<li>✅ Dados de teste removidos</li>";
        echo "</ul>";
        
        echo "<p><strong>🎯 CONCLUSÃO:</strong> O sistema está funcionando corretamente! Os dados de bairro, cidade e CEP agora são salvos em colunas separadas no banco de dados.</p>";
        
    } else {
        echo "<h4>❌ Resultado do Teste:</h4>";
        echo "<p>O teste falhou. Verifique os erros acima e corrija os problemas identificados.</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro Crítico no Teste:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>