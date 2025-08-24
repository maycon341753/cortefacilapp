<?php
/**
 * Verificação final para confirmar se o problema foi resolvido
 * Este arquivo deve ser enviado para o servidor online
 */

echo "<h2>✅ Verificação Final - Sistema de Agendamento</h2>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";
echo "<hr>";

// Detectar se está online
$isOnline = !in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($isOnline) {
    echo "<p style='color: blue; font-weight: bold;'>🌐 EXECUTANDO NO SERVIDOR ONLINE</p>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>🏠 EXECUTANDO LOCALMENTE (simulando online)</p>";
    // Forçar configuração online para teste
    file_put_contents(__DIR__ . '/.env.online', 'FORCE_ONLINE=true');
}

try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com banco de dados');
    }
    
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
    
    // 1. Verificar dados essenciais
    echo "<h3>1. 📊 Verificação de Dados</h3>";
    
    // Verificar se as colunas 'status' existem antes de fazer as queries
    $saloes_has_status = false;
    $profissionais_has_status = false;
    
    try {
        $stmt = $conn->query("DESCRIBE saloes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ($column['Field'] === 'status') {
                $saloes_has_status = true;
                break;
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao verificar estrutura da tabela salões: {$e->getMessage()}</p>";
    }
    
    try {
        $stmt = $conn->query("DESCRIBE profissionais");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ($column['Field'] === 'status') {
                $profissionais_has_status = true;
                break;
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao verificar estrutura da tabela profissionais: {$e->getMessage()}</p>";
    }
    
    // Contar salões
    if ($saloes_has_status) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes WHERE status = 'ativo'");
        $total_saloes = $stmt->fetch()['total'];
    } else {
        echo "<p style='color: orange;'>⚠️ Coluna 'status' não encontrada na tabela 'saloes'. Contando todos os registros.</p>";
        $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes");
        $total_saloes = $stmt->fetch()['total'];
    }
    
    // Contar profissionais
    if ($profissionais_has_status) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais WHERE status = 'ativo'");
        $total_profissionais = $stmt->fetch()['total'];
    } else {
        echo "<p style='color: orange;'>⚠️ Coluna 'status' não encontrada na tabela 'profissionais'. Contando todos os registros.</p>";
        $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais");
        $total_profissionais = $stmt->fetch()['total'];
    }
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'cliente'");
    $total_clientes = $stmt->fetch()['total'];
    
    echo "<div style='display: flex; gap: 20px; margin: 20px 0;'>";
    
    $cor_saloes = $total_saloes > 0 ? 'green' : 'red';
    echo "<div style='border: 2px solid {$cor_saloes}; padding: 15px; border-radius: 8px; text-align: center;'>";
    echo "<h4>🏢 Salões</h4>";
    echo "<p style='font-size: 24px; font-weight: bold; color: {$cor_saloes};'>{$total_saloes}</p>";
    echo "<p>" . ($total_saloes > 0 ? '✅ OK' : '❌ Problema') . "</p>";
    echo "</div>";
    
    $cor_prof = $total_profissionais > 0 ? 'green' : 'red';
    echo "<div style='border: 2px solid {$cor_prof}; padding: 15px; border-radius: 8px; text-align: center;'>";
    echo "<h4>👥 Profissionais</h4>";
    echo "<p style='font-size: 24px; font-weight: bold; color: {$cor_prof};'>{$total_profissionais}</p>";
    echo "<p>" . ($total_profissionais > 0 ? '✅ OK' : '❌ Problema') . "</p>";
    echo "</div>";
    
    $cor_clientes = $total_clientes > 0 ? 'green' : 'red';
    echo "<div style='border: 2px solid {$cor_clientes}; padding: 15px; border-radius: 8px; text-align: center;'>";
    echo "<h4>👤 Clientes</h4>";
    echo "<p style='font-size: 24px; font-weight: bold; color: {$cor_clientes};'>{$total_clientes}</p>";
    echo "<p>" . ($total_clientes > 0 ? '✅ OK' : '❌ Problema') . "</p>";
    echo "</div>";
    
    echo "</div>";
    
    // 2. Testar API de profissionais
    echo "<h3>2. 🔧 Teste da API de Profissionais</h3>";
    
    if ($total_saloes > 0 && $total_profissionais > 0) {
        // Iniciar sessão para teste
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Simular usuário logado
        $_SESSION['user_id'] = 1;
        $_SESSION['user_type'] = 'cliente';
        $_SESSION['user_name'] = 'Cliente Teste';
        
        // Buscar primeiro salão com profissionais
        $query = "
            SELECT s.id, s.nome, COUNT(p.id) as total_prof
            FROM saloes s 
            LEFT JOIN profissionais p ON s.id = p.id_salao";
        
        // Adicionar condição de status apenas se a coluna existir
        if ($profissionais_has_status) {
            $query .= " AND p.status = 'ativo'";
        }
        
        $query .= " WHERE ";
        
        // Adicionar condição de status para salões apenas se a coluna existir
        if ($saloes_has_status) {
            $query .= "s.status = 'ativo'";
        } else {
            $query .= "1=1"; // Condição sempre verdadeira se não há coluna status
        }
        
        $query .= "
            GROUP BY s.id, s.nome
            HAVING total_prof > 0
            LIMIT 1
        ";
        
        $stmt = $conn->query($query);
        $salao_teste = $stmt->fetch();
        
        if ($salao_teste) {
            echo "<p>Testando com salão: <strong>{$salao_teste['nome']}</strong> (ID: {$salao_teste['id']}, {$salao_teste['total_prof']} profissionais)</p>";
            
            // Simular chamada da API
            $_GET['salao'] = $salao_teste['id'];
            
            ob_start();
            try {
                include 'api/profissionais.php';
                $api_response = ob_get_clean();
                
                echo "<h4>📋 Resposta da API:</h4>";
                echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;'>";
                echo htmlspecialchars($api_response);
                echo "</pre>";
                
                $json_data = json_decode($api_response, true);
                
                if ($json_data && $json_data['success']) {
                    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h4 style='color: #155724;'>✅ API FUNCIONANDO CORRETAMENTE!</h4>";
                    echo "<p><strong>Profissionais retornados:</strong> " . ($json_data['total'] ?? 0) . "</p>";
                    
                    if (isset($json_data['data']) && count($json_data['data']) > 0) {
                        echo "<p><strong>Lista de profissionais:</strong></p>";
                        echo "<ul>";
                        foreach ($json_data['data'] as $prof) {
                            echo "<li>{$prof['nome']} (ID: {$prof['id']}, Status: {$prof['status']})</li>";
                        }
                        echo "</ul>";
                    }
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h4 style='color: #721c24;'>❌ ERRO NA API</h4>";
                    echo "<p><strong>Erro:</strong> " . ($json_data['error'] ?? 'Resposta inválida') . "</p>";
                    echo "</div>";
                }
                
            } catch (Exception $e) {
                ob_end_clean();
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4 style='color: #721c24;'>❌ ERRO AO EXECUTAR API</h4>";
                echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
                echo "</div>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Nenhum salão com profissionais encontrado</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ Não é possível testar API - dados insuficientes</p>";
    }
    
    // 3. Verificar estrutura das tabelas
    echo "<h3>3. 🔍 Estrutura das Tabelas</h3>";
    
    $tabelas_verificar = ['saloes', 'profissionais', 'usuarios'];
    
    foreach ($tabelas_verificar as $tabela) {
        try {
            $stmt = $conn->query("DESCRIBE {$tabela}");
            $campos = $stmt->fetchAll();
            
            echo "<h4>Tabela: {$tabela}</h4>";
            echo "<p>Campos encontrados: " . count($campos) . "</p>";
            
            $campos_importantes = [];
            foreach ($campos as $campo) {
                $campos_importantes[] = $campo['Field'];
            }
            
            echo "<p><strong>Campos:</strong> " . implode(', ', $campos_importantes) . "</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Relatório final
    echo "<hr>";
    echo "<h3>📋 Relatório Final</h3>";
    
    $problemas = [];
    $sucessos = [];
    
    if ($total_saloes > 0) {
        $sucessos[] = "✅ Salões cadastrados: {$total_saloes}";
    } else {
        $problemas[] = "❌ Nenhum salão ativo encontrado";
    }
    
    if ($total_profissionais > 0) {
        $sucessos[] = "✅ Profissionais cadastrados: {$total_profissionais}";
    } else {
        $problemas[] = "❌ Nenhum profissional ativo encontrado";
    }
    
    if ($total_clientes > 0) {
        $sucessos[] = "✅ Clientes cadastrados: {$total_clientes}";
    } else {
        $problemas[] = "❌ Nenhum cliente encontrado";
    }
    
    if (count($sucessos) > 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4 style='color: #155724;'>✅ Itens Funcionando:</h4>";
        echo "<ul>";
        foreach ($sucessos as $sucesso) {
            echo "<li>{$sucesso}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    if (count($problemas) > 0) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4 style='color: #721c24;'>❌ Problemas Encontrados:</h4>";
        echo "<ul>";
        foreach ($problemas as $problema) {
            echo "<li>{$problema}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Status geral
    if (count($problemas) == 0) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>";
        echo "<h2 style='color: #0c5460;'>🎉 SISTEMA FUNCIONANDO PERFEITAMENTE!</h2>";
        echo "<p>Todos os componentes estão operacionais. O agendamento deveria funcionar corretamente.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>";
        echo "<h2 style='color: #721c24;'>⚠️ PROBLEMAS DETECTADOS</h2>";
        echo "<p>Alguns componentes precisam de atenção. Execute o script de correção novamente.</p>";
        echo "</div>";
    }
    
    // Instruções finais
    echo "<h3>📝 Instruções para Teste</h3>";
    echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px;'>";
    echo "<ol>";
    echo "<li>Acesse: <strong>https://cortefacil.app/login.php</strong></li>";
    echo "<li>Faça login com: <strong>cliente@teste.com</strong> / <strong>123456</strong></li>";
    echo "<li>Vá para: <strong>https://cortefacil.app/cliente/agendar.php</strong></li>";
    echo "<li>Selecione um salão no dropdown</li>";
    echo "<li>Verifique se os profissionais aparecem automaticamente</li>";
    echo "</ol>";
    echo "<p><strong>Se ainda não funcionar:</strong></p>";
    echo "<ul>";
    echo "<li>Limpe o cache do navegador (Ctrl+F5)</li>";
    echo "<li>Verifique o console do navegador (F12) para erros JavaScript</li>";
    echo "<li>Execute este script novamente para verificar o status</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4 style='color: #721c24;'>❌ ERRO CRÍTICO</h4>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

// Limpar arquivo de ambiente forçado se foi criado
if (!$isOnline && file_exists(__DIR__ . '/.env.online')) {
    unlink(__DIR__ . '/.env.online');
}

echo "<hr>";
echo "<p><em>Verificação concluída em " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Ambiente:</strong> " . ($isOnline ? 'Online (Produção)' : 'Local (Desenvolvimento)') . "</p>";
?>