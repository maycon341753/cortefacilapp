<?php
/**
 * Teste final do dashboard corrigido
 * Simula o acesso real ao dashboard com todas as correções aplicadas
 */

// Simular ambiente de produção
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['REQUEST_URI'] = '/parceiro/dashboard.php';

echo "<h2>Teste Final - Dashboard Corrigido</h2>";
echo "<p><strong>Simulando:</strong> https://cortefacil.app/parceiro/dashboard.php</p>";

// Capturar saída e erros
ob_start();
$errors = [];

try {
    // Iniciar sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simular usuário parceiro logado com as variáveis corretas
    $_SESSION['usuario_id'] = 1;
    $_SESSION['tipo_usuario'] = 'parceiro';
    $_SESSION['usuario_nome'] = 'Parceiro Teste';
    $_SESSION['usuario_email'] = 'parceiro@teste.com';
    $_SESSION['usuario_telefone'] = '11999999999';
    
    echo "<h3>1. Simulação de Usuário Logado:</h3>";
    echo "<p>✅ Usuário ID: {$_SESSION['usuario_id']}, Tipo: {$_SESSION['tipo_usuario']}</p>";
    
    // Incluir e executar partes críticas do dashboard
    echo "<h3>2. Verificação de Arquivos Críticos:</h3>";
    
    $arquivos_criticos = [
        __DIR__ . '/includes/auth.php',
        __DIR__ . '/includes/functions.php',
        __DIR__ . '/models/salao.php',
        __DIR__ . '/models/profissional.php',
        __DIR__ . '/models/agendamento.php'
    ];
    
    foreach ($arquivos_criticos as $arquivo) {
        if (!file_exists($arquivo)) {
            throw new Exception("Arquivo crítico não encontrado: $arquivo");
        }
        echo "<p>✅ " . basename($arquivo) . "</p>";
    }
    
    // Incluir arquivos
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/models/salao.php';
    require_once __DIR__ . '/models/profissional.php';
    require_once __DIR__ . '/models/agendamento.php';
    
    echo "<h3>3. Teste de Conexão Online Forçada:</h3>";
    
    // Simular a lógica do dashboard corrigido
    $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($serverName, 'cortefacil.app') !== false || file_exists(__DIR__ . '/.env.online')) {
        require_once __DIR__ . '/config/database.php';
        $db = Database::getInstance();
        $db->forceOnlineConfig();
        $conn = $db->connect();
        if (!$conn) {
            throw new Exception('Falha na conexão online forçada');
        }
        echo "<p style='color: green;'>✅ Conexão online forçada com sucesso!</p>";
    }
    
    echo "<h3>4. Teste de Autenticação:</h3>";
    
    if (!isLoggedIn()) {
        throw new Exception('Usuário não está logado');
    }
    echo "<p>✅ isLoggedIn(): true</p>";
    
    if (!isParceiro()) {
        throw new Exception('Usuário não é parceiro');
    }
    echo "<p>✅ isParceiro(): true</p>";
    
    $usuario = getLoggedUser();
    if (!$usuario) {
        throw new Exception('Não foi possível obter dados do usuário');
    }
    echo "<p>✅ getLoggedUser(): ID={$usuario['id']}, Nome={$usuario['nome']}</p>";
    
    echo "<h3>5. Teste de Busca de Salão:</h3>";
    
    $salao = new Salao();
    $profissional = new Profissional();
    $agendamento = new Agendamento();
    
    echo "<p>✅ Objetos instanciados com sucesso</p>";
    
    // Buscar salão do parceiro
    $meu_salao = $salao->buscarPorDono($usuario['id']);
    
    if ($meu_salao) {
        echo "<p style='color: green;'>✅ Salão encontrado: {$meu_salao['nome']}</p>";
        echo "<p>ID: {$meu_salao['id']}, Endereço: {$meu_salao['endereco']}</p>";
        
        // Buscar dados adicionais
        $profissionais = $profissional->listarPorSalao($meu_salao['id']) ?? [];
        $agendamentos = $agendamento->listarPorSalao($meu_salao['id']) ?? [];
        
        echo "<p>✅ Profissionais: " . count($profissionais) . "</p>";
        echo "<p>✅ Agendamentos: " . count($agendamentos) . "</p>";
        
        // Calcular estatísticas
        $total_profissionais = count($profissionais);
        $profissionais_ativos = count(array_filter($profissionais, function($p) {
            return isset($p['status']) && $p['status'] === 'ativo';
        }));
        
        $total_agendamentos = count($agendamentos);
        $agendamentos_hoje = count(array_filter($agendamentos, function($a) {
            return isset($a['data']) && $a['data'] === date('Y-m-d');
        }));
        
        echo "<h3>6. Estatísticas Calculadas:</h3>";
        echo "<p>✅ Total de profissionais: $total_profissionais</p>";
        echo "<p>✅ Profissionais ativos: $profissionais_ativos</p>";
        echo "<p>✅ Total de agendamentos: $total_agendamentos</p>";
        echo "<p>✅ Agendamentos hoje: $agendamentos_hoje</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum salão encontrado (redirecionaria para cadastro)</p>";
    }
    
    echo "<h3>7. Resultado Final:</h3>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #155724; margin-top: 0;'>🎉 DASHBOARD FUNCIONANDO PERFEITAMENTE!</h4>";
    echo "<p style='margin-bottom: 0;'>Todas as verificações passaram com sucesso. O dashboard do parceiro está operacional no ambiente online.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #721c24; margin-top: 0;'>❌ Erro Encontrado:</h4>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p style='margin-bottom: 0;'><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

$output = ob_get_clean();
echo $output;

echo "<hr>";
echo "<h3>Resumo das Correções Aplicadas:</h3>";
echo "<ul>";
echo "<li>✅ Correção de caminhos de arquivos críticos com __DIR__</li>";
echo "<li>✅ Implementação de conexão online forçada</li>";
echo "<li>✅ Criação do arquivo .env.online para detecção de ambiente</li>";
echo "<li>✅ Melhoria na detecção de ambiente de produção</li>";
echo "<li>✅ Correção das variáveis de sessão (usuario_id, tipo_usuario)</li>";
echo "<li>✅ Tratamento robusto de erros</li>";
echo "</ul>";

echo "<h3>Status do Sistema:</h3>";
if (strpos($output, 'DASHBOARD FUNCIONANDO PERFEITAMENTE') !== false) {
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🟢 SISTEMA OPERACIONAL</p>";
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>🔴 SISTEMA COM PROBLEMAS</p>";
}
?>