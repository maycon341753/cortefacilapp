<?php
/**
 * Teste para verificar se o salão está sendo salvo com as colunas separadas
 * de bairro, cidade e CEP
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/salao.php';

echo "<h2>Teste de Salão com Colunas Separadas</h2>";

try {
    $salao = new Salao();
    
    // Dados de teste
    $timestamp = time();
    $dados_teste = [
        'nome' => 'Salão Teste Atualizado ' . $timestamp,
        'endereco' => 'Rua das Flores, 123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'cep' => '01234567',
        'telefone' => '11999887766',
        'descricao' => 'Salão de teste para verificar colunas separadas',
        'documento' => '12345' . $timestamp, // Documento único
        'id_dono' => 1 // Assumindo que existe um usuário com ID 1
    ];
    
    echo "<h3>1. Cadastrando novo salão...</h3>";
    $salao_id = $salao->cadastrar($dados_teste);
    
    if ($salao_id) {
        echo "<p style='color: green;'>✅ Salão cadastrado com sucesso! ID: {$salao_id}</p>";
        
        // Buscar o salão recém-criado
        echo "<h3>2. Verificando dados salvos...</h3>";
        $salao_salvo = $salao->buscarPorId($salao_id);
        
        if ($salao_salvo) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Valor Salvo</th></tr>";
            echo "<tr><td>Nome</td><td>" . htmlspecialchars($salao_salvo['nome']) . "</td></tr>";
            echo "<tr><td>Endereço</td><td>" . htmlspecialchars($salao_salvo['endereco']) . "</td></tr>";
            echo "<tr><td>Bairro</td><td>" . htmlspecialchars($salao_salvo['bairro'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td>Cidade</td><td>" . htmlspecialchars($salao_salvo['cidade'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td>CEP</td><td>" . htmlspecialchars($salao_salvo['cep'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td>Telefone</td><td>" . htmlspecialchars($salao_salvo['telefone']) . "</td></tr>";
            echo "<tr><td>Descrição</td><td>" . htmlspecialchars($salao_salvo['descricao']) . "</td></tr>";
            echo "</table>";
            
            // Verificar se as colunas separadas foram salvas
            $sucesso_bairro = !empty($salao_salvo['bairro']);
            $sucesso_cidade = !empty($salao_salvo['cidade']);
            $sucesso_cep = !empty($salao_salvo['cep']);
            
            echo "<h3>3. Análise dos Resultados:</h3>";
            echo "<ul>";
            echo "<li>Bairro salvo: " . ($sucesso_bairro ? "<span style='color: green;'>✅ SIM</span>" : "<span style='color: red;'>❌ NÃO</span>") . "</li>";
            echo "<li>Cidade salva: " . ($sucesso_cidade ? "<span style='color: green;'>✅ SIM</span>" : "<span style='color: red;'>❌ NÃO</span>") . "</li>";
            echo "<li>CEP salvo: " . ($sucesso_cep ? "<span style='color: green;'>✅ SIM</span>" : "<span style='color: red;'>❌ NÃO</span>") . "</li>";
            echo "</ul>";
            
            if ($sucesso_bairro && $sucesso_cidade && $sucesso_cep) {
                echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
                echo "<h4>🎉 Sucesso Total!</h4>";
                echo "<p>Todas as informações de endereço foram salvas nas colunas separadas corretamente.</p>";
                echo "</div>";
            } else {
                echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
                echo "<h4>⚠️ Problema Parcial</h4>";
                echo "<p>Algumas informações de endereço não foram salvas nas colunas separadas.</p>";
                echo "</div>";
            }
            
            // Teste de atualização
            echo "<h3>4. Testando atualização...</h3>";
            $dados_atualizacao = [
                'nome' => $dados_teste['nome'] . ' (Atualizado)',
                'endereco' => 'Rua das Rosas, 456',
                'bairro' => 'Vila Nova',
                'cidade' => 'Rio de Janeiro',
                'cep' => '87654321',
                'telefone' => '21888776655',
                'descricao' => 'Salão atualizado para teste'
            ];
            
            $resultado_atualizacao = $salao->atualizar($salao_id, $dados_atualizacao);
            
            if ($resultado_atualizacao) {
                echo "<p style='color: green;'>✅ Salão atualizado com sucesso!</p>";
                
                // Verificar dados atualizados
                $salao_atualizado = $salao->buscarPorId($salao_id);
                echo "<p><strong>Dados após atualização:</strong></p>";
                echo "<ul>";
                echo "<li>Bairro: " . htmlspecialchars($salao_atualizado['bairro'] ?? 'NULL') . "</li>";
                echo "<li>Cidade: " . htmlspecialchars($salao_atualizado['cidade'] ?? 'NULL') . "</li>";
                echo "<li>CEP: " . htmlspecialchars($salao_atualizado['cep'] ?? 'NULL') . "</li>";
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao atualizar salão</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Erro ao buscar salão recém-criado</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao cadastrar salão</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>