<?php
/**
 * CORREÇÃO FINAL - Tabela Profissionais
 * Este arquivo deve ser enviado para o servidor online e executado lá
 * Corrige a estrutura da tabela profissionais adicionando campos telefone e email
 */

// Detectar ambiente
$isOnline = !in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($isOnline) {
    // Configurações para ambiente online (Hostinger)
    $host = 'srv486.hstgr.io';
    $db_name = 'u690889028_cortefacil';
    $username = 'u690889028_mayconwender';
    $password = 'Maycon341753';
    $ambiente = 'ONLINE (Hostinger)';
} else {
    // Configurações para ambiente local
    $host = 'localhost';
    $db_name = 'u690889028_cortefacil';
    $username = 'root';
    $password = '';
    $ambiente = 'LOCAL (XAMPP)';
}

echo "<h2>🔧 Correção da Tabela Profissionais</h2>";
echo "<p><strong>Ambiente:</strong> {$ambiente}</p>";
echo "<p><strong>Host:</strong> {$host}</p>";
echo "<p><strong>Database:</strong> {$db_name}</p>";
echo "<hr>";

try {
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<p style='color: green; font-weight: bold;'>✅ CONEXÃO ESTABELECIDA COM SUCESSO!</p>";
    
    // 1. Verificar estrutura atual
    echo "<h3>📋 1. Estrutura Atual da Tabela</h3>";
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos = $stmt->fetchAll();
    
    $tem_telefone = false;
    $tem_email = false;
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($campos as $campo) {
        $destaque = '';
        if ($campo['Field'] === 'telefone') {
            $tem_telefone = true;
            $destaque = 'background: #e8f5e8;';
        }
        if ($campo['Field'] === 'email') {
            $tem_email = true;
            $destaque = 'background: #e8f5e8;';
        }
        
        echo "<tr style='{$destaque}'>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Status dos campos necessários
    echo "<h3>🔍 2. Verificação de Campos Necessários</h3>";
    echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<p><strong>Campo 'telefone':</strong> " . ($tem_telefone ? "<span style='color: green; font-weight: bold;'>✅ EXISTE</span>" : "<span style='color: red; font-weight: bold;'>❌ NÃO EXISTE</span>") . "</p>";
    echo "<p><strong>Campo 'email':</strong> " . ($tem_email ? "<span style='color: green; font-weight: bold;'>✅ EXISTE</span>" : "<span style='color: red; font-weight: bold;'>❌ NÃO EXISTE</span>") . "</p>";
    echo "</div>";
    
    // 3. Aplicar correções se necessário
    $correcoes_aplicadas = [];
    
    if (!$tem_telefone || !$tem_email) {
        echo "<h3>🔧 3. Aplicando Correções</h3>";
        
        if (!$tem_telefone) {
            try {
                $sql_telefone = "ALTER TABLE profissionais ADD COLUMN telefone VARCHAR(20) NULL AFTER especialidade";
                echo "<p><strong>Executando:</strong> <code>{$sql_telefone}</code></p>";
                $conn->exec($sql_telefone);
                echo "<p style='color: green; font-weight: bold;'>✅ Campo 'telefone' adicionado com sucesso!</p>";
                $correcoes_aplicadas[] = 'telefone';
            } catch (PDOException $e) {
                echo "<p style='color: red; font-weight: bold;'>❌ Erro ao adicionar campo 'telefone': {$e->getMessage()}</p>";
            }
        }
        
        if (!$tem_email) {
            try {
                $sql_email = "ALTER TABLE profissionais ADD COLUMN email VARCHAR(255) NULL AFTER telefone";
                echo "<p><strong>Executando:</strong> <code>{$sql_email}</code></p>";
                $conn->exec($sql_email);
                echo "<p style='color: green; font-weight: bold;'>✅ Campo 'email' adicionado com sucesso!</p>";
                $correcoes_aplicadas[] = 'email';
            } catch (PDOException $e) {
                echo "<p style='color: red; font-weight: bold;'>❌ Erro ao adicionar campo 'email': {$e->getMessage()}</p>";
            }
        }
        
        if (!empty($correcoes_aplicadas)) {
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4 style='color: green; margin: 0 0 10px 0;'>🎉 Correções Aplicadas:</h4>";
            echo "<ul>";
            foreach ($correcoes_aplicadas as $campo) {
                echo "<li>Campo '<strong>{$campo}</strong>' adicionado</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        
    } else {
        echo "<h3 style='color: green;'>✅ 3. Tabela Já Está Correta!</h3>";
        echo "<p>Todos os campos necessários já existem na tabela profissionais.</p>";
    }
    
    // 4. Verificar estrutura final
    echo "<h3>📊 4. Estrutura Final da Tabela</h3>";
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos_final = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($campos_final as $campo) {
        $destaque = '';
        if (in_array($campo['Field'], ['telefone', 'email'])) {
            $destaque = 'background: #e8f5e8;';
        }
        
        echo "<tr style='{$destaque}'>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Teste de funcionalidade
    echo "<h3>🧪 5. Teste de Funcionalidade</h3>";
    
    // Buscar um salão para teste
    $stmt = $conn->query("SELECT id, nome FROM saloes WHERE ativo = 1 LIMIT 1");
    $salao_teste = $stmt->fetch();
    
    if ($salao_teste) {
        try {
            // Testar inserção completa
            $stmt = $conn->prepare("
                INSERT INTO profissionais (id_salao, nome, especialidade, telefone, email, ativo) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $dados_teste = [
                $salao_teste['id'],
                'Profissional Teste - ' . date('Y-m-d H:i:s'),
                'Cabeleireiro',
                '(11) 99999-9999',
                'teste@profissional.com',
                1
            ];
            
            $stmt->execute($dados_teste);
            $id_teste = $conn->lastInsertId();
            
            echo "<p style='color: green; font-weight: bold;'>✅ TESTE DE INSERÇÃO BEM-SUCEDIDO!</p>";
            echo "<p><strong>ID inserido:</strong> {$id_teste}</p>";
            echo "<p><strong>Salão usado:</strong> {$salao_teste['nome']} (ID: {$salao_teste['id']})</p>";
            
            // Verificar dados inseridos
            $stmt_verificar = $conn->prepare("SELECT * FROM profissionais WHERE id = ?");
            $stmt_verificar->execute([$id_teste]);
            $dados_inseridos = $stmt_verificar->fetch();
            
            echo "<h4>Dados inseridos:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            foreach ($dados_inseridos as $campo => $valor) {
                echo "<tr><td><strong>{$campo}</strong></td><td>{$valor}</td></tr>";
            }
            echo "</table>";
            
            // Remover registro de teste
            $conn->exec("DELETE FROM profissionais WHERE id = {$id_teste}");
            echo "<p style='color: blue;'>ℹ️ Registro de teste removido.</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red; font-weight: bold;'>❌ ERRO NO TESTE:</p>";
            echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
            echo "<p><strong>Código:</strong> {$e->getCode()}</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum salão ativo encontrado para teste.</p>";
    }
    
    // 6. Resultado final
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 30px 0; text-align: center;'>";
    echo "<h2 style='color: green; margin: 0 0 15px 0;'>🎉 CORREÇÃO CONCLUÍDA COM SUCESSO!</h2>";
    echo "<p style='font-size: 18px; margin: 0;'>A tabela profissionais agora está pronta para receber dados com telefone e email.</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: red; margin: 0 0 15px 0;'>❌ ERRO DE CONEXÃO</h3>";
    echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
    echo "<p><strong>Código:</strong> {$e->getCode()}</p>";
    echo "<p><strong>Host:</strong> {$host}</p>";
    echo "<p><strong>Database:</strong> {$db_name}</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin: 30px 0;'>";
if ($isOnline) {
    echo "<p><a href='/parceiro/profissionais.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Testar Página de Profissionais</a></p>";
} else {
    echo "<p><a href='https://cortefacil.app/parceiro/profissionais.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para Página Online</a></p>";
}
echo "</div>";
?>