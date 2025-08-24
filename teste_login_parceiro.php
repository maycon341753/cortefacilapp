<?php
/**
 * Teste de Login do Parceiro - Diagnóstico
 * Verifica se o usuário está logado e pode acessar o dashboard
 */

// Configurações de erro para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão
session_start();

echo "<h1>🔍 Teste de Login do Parceiro</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Verificar sessão
echo "<h2>1. 📋 Informações da Sessão</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Dados da Sessão:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "<hr>";

// 2. Incluir auth.php
echo "<h2>2. 🔧 Carregando Sistema de Autenticação</h2>";
try {
    require_once 'includes/auth.php';
    echo "<p>✅ auth.php carregado com sucesso</p>";
    
    // Verificar se funções existem
    $funcoes = ['isLoggedIn', 'hasUserType', 'isParceiro', 'requireParceiro', 'getLoggedUser'];
    foreach ($funcoes as $funcao) {
        if (function_exists($funcao)) {
            echo "<p>✅ Função $funcao existe</p>";
        } else {
            echo "<p>❌ Função $funcao NÃO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 3. Testar funções de autenticação
echo "<h2>3. 🔐 Teste de Autenticação</h2>";

if (function_exists('isLoggedIn')) {
    $logado = isLoggedIn();
    echo "<p><strong>isLoggedIn():</strong> " . ($logado ? '✅ SIM' : '❌ NÃO') . "</p>";
    
    if ($logado) {
        if (function_exists('getLoggedUser')) {
            $usuario = getLoggedUser();
            echo "<p><strong>Dados do usuário:</strong></p>";
            echo "<pre>" . print_r($usuario, true) . "</pre>";
            
            if (function_exists('isParceiro')) {
                $eh_parceiro = isParceiro();
                echo "<p><strong>isParceiro():</strong> " . ($eh_parceiro ? '✅ SIM' : '❌ NÃO') . "</p>";
                
                if ($eh_parceiro) {
                    echo "<p>🎉 <strong>USUÁRIO PODE ACESSAR DASHBOARD DO PARCEIRO!</strong></p>";
                    echo "<p><a href='parceiro/dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir para Dashboard</a></p>";
                } else {
                    echo "<p>⚠️ Usuário logado mas não é parceiro</p>";
                    if (isset($usuario['tipo_usuario'])) {
                        echo "<p>Tipo atual: " . $usuario['tipo_usuario'] . "</p>";
                    }
                }
            }
        }
    } else {
        echo "<p>⚠️ Usuário não está logado</p>";
        echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Fazer Login</a></p>";
    }
} else {
    echo "<p>❌ Função isLoggedIn não encontrada</p>";
}

echo "<hr>";

// 4. Teste de requireParceiro (simulado)
echo "<h2>4. 🛡️ Teste de Proteção (requireParceiro)</h2>";
if (function_exists('requireParceiro')) {
    echo "<p>ℹ️ Função requireParceiro existe. Testando...</p>";
    
    // Capturar se haveria redirecionamento
    ob_start();
    try {
        // Não executar realmente, apenas verificar lógica
        if (function_exists('isLoggedIn') && function_exists('hasUserType')) {
            if (!isLoggedIn()) {
                echo "<p>❌ Seria redirecionado para login (usuário não logado)</p>";
            } elseif (!hasUserType('parceiro')) {
                echo "<p>❌ Seria redirecionado para index (não é parceiro)</p>";
            } else {
                echo "<p>✅ Passaria na verificação requireParceiro</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro na verificação: " . $e->getMessage() . "</p>";
    }
    $output = ob_get_clean();
    echo $output;
} else {
    echo "<p>❌ Função requireParceiro não encontrada</p>";
}

echo "<hr>";

// 5. Links úteis
echo "<h2>5. 🔗 Links Úteis</h2>";
echo "<p><a href='index.php'>🏠 Página Inicial</a></p>";
echo "<p><a href='login.php'>🔑 Login</a></p>";
echo "<p><a href='cadastro.php'>📝 Cadastro</a></p>";
echo "<p><a href='parceiro/dashboard.php'>📊 Dashboard Parceiro</a></p>";

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>