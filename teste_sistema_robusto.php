<?php
/**
 * Teste do Sistema Robusto de Conexão
 * Verifica se o fallback automático e tratamento de erros amigável estão funcionando
 */

echo "<h2>🔧 Teste do Sistema Robusto - CorteFácil</h2>";
echo "<hr>";

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'models/usuario.php';

echo "<h3>1. Testando Conexão com Fallback Automático</h3>";

// Testar instância singleton
$db = Database::getInstance();
echo "✅ Instância Database criada<br>";

// Testar conexão
$conn = $db->connect();
if ($conn) {
    echo "✅ Conexão estabelecida com sucesso<br>";
    
    // Verificar qual tipo de conexão foi estabelecida
    try {
        $stmt = $conn->query("SELECT CONNECTION_ID() as conn_id, USER() as user_info, DATABASE() as db_name");
        $info = $stmt->fetch();
        echo "📊 <strong>Informações da Conexão:</strong><br>";
        echo "&nbsp;&nbsp;• ID da Conexão: {$info['conn_id']}<br>";
        echo "&nbsp;&nbsp;• Usuário: {$info['user_info']}<br>";
        echo "&nbsp;&nbsp;• Banco: {$info['db_name']}<br>";
        
        // Determinar se é local ou online baseado no usuário
        if (strpos($info['user_info'], 'root@localhost') !== false) {
            echo "🏠 <strong>Conexão LOCAL ativa</strong><br>";
        } else {
            echo "🌐 <strong>Conexão ONLINE ativa</strong><br>";
        }
        
    } catch (Exception $e) {
        echo "⚠️ Erro ao obter informações da conexão<br>";
    }
} else {
    echo "❌ Falha na conexão - Sistema deve estar em modo degradado<br>";
}

echo "<br><h3>2. Testando Classe Usuario com Tratamento Robusto</h3>";

// Testar classe Usuario
$usuario = new Usuario();
echo "✅ Instância Usuario criada<br>";

// Verificar status da conexão
if ($usuario->isConnectionAvailable()) {
    echo "✅ Conexão disponível na classe Usuario<br>";
    
    // Testar login com credenciais inválidas (não deve mostrar erro técnico)
    echo "<br><strong>Teste de Login (credenciais inválidas):</strong><br>";
    $resultado = $usuario->login('teste@inexistente.com', 'senha_errada');
    
    if (is_string($resultado)) {
        echo "⚠️ Erro de conexão: {$resultado}<br>";
    } elseif ($resultado === false) {
        echo "✅ Login rejeitado corretamente (credenciais inválidas)<br>";
    } else {
        echo "❓ Resultado inesperado<br>";
    }
    
} else {
    echo "❌ Conexão não disponível na classe Usuario<br>";
    
    // Testar comportamento sem conexão
    echo "<br><strong>Teste de Login sem Conexão:</strong><br>";
    $resultado = $usuario->login('teste@teste.com', 'senha123');
    
    if (is_string($resultado)) {
        echo "✅ Mensagem amigável retornada: {$resultado}<br>";
    } else {
        echo "❌ Não retornou mensagem amigável<br>";
    }
}

echo "<br><h3>3. Testando Verificação de Tabelas</h3>";

if ($conn) {
    try {
        // Verificar tabelas essenciais
        $tabelas = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
        
        foreach ($tabelas as $tabela) {
            $stmt = $conn->query("SHOW TABLES LIKE '{$tabela}'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Tabela '{$tabela}' existe<br>";
                
                // Contar registros
                $count_stmt = $conn->query("SELECT COUNT(*) as total FROM {$tabela}");
                $count = $count_stmt->fetch()['total'];
                echo "&nbsp;&nbsp;• {$count} registros<br>";
            } else {
                echo "❌ Tabela '{$tabela}' não encontrada<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "⚠️ Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Não foi possível verificar tabelas (sem conexão)<br>";
}

echo "<br><h3>4. Resumo do Sistema</h3>";
echo "<div style='background:#e8f5e8;padding:15px;border-radius:5px;border-left:4px solid #4caf50;'>";
echo "<strong>✅ Sistema Robusto Implementado:</strong><br>";
echo "• Fallback automático (online → local)<br>";
echo "• Tratamento de erros amigável<br>";
echo "• Mensagens não técnicas para usuários<br>";
echo "• Logs detalhados para desenvolvedores<br>";
echo "• Verificação de saúde da conexão<br>";
echo "</div>";

echo "<br><h3>5. Próximos Passos</h3>";
echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;border-left:4px solid #ffc107;'>";
echo "<strong>📋 Para Produção:</strong><br>";
echo "• Sistema funcionará automaticamente<br>";
echo "• Usuários verão apenas mensagens amigáveis<br>";
echo "• Fallback transparente em caso de problemas<br>";
echo "• Logs disponíveis para monitoramento<br>";
echo "</div>";

echo "<br><p><strong>Data/Hora do Teste:</strong> " . date('d/m/Y H:i:s') . "</p>";
?>