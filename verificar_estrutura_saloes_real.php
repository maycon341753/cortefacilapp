<?php
/**
 * VERIFICAR ESTRUTURA REAL DA TABELA SALOES
 * Este arquivo verifica a estrutura exata da tabela saloes no banco online
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h1>🔍 Estrutura Real da Tabela SALOES</h1>";
    echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";
    
    // Verificar estrutura da tabela saloes
    echo "<h2>📋 DESCRIBE saloes:</h2>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $estrutura = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($estrutura as $campo) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($campo['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar constraints de chave estrangeira
    echo "<h2>🔗 FOREIGN KEY CONSTRAINTS:</h2>";
    $stmt = $conn->prepare("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'saloes' 
        AND TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute();
    $constraints = $stmt->fetchAll();
    
    if ($constraints) {
        echo "<table>";
        echo "<tr><th>Constraint</th><th>Coluna Local</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        foreach ($constraints as $constraint) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($constraint['CONSTRAINT_NAME']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($constraint['COLUMN_NAME']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($constraint['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($constraint['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhuma constraint de chave estrangeira encontrada.</p>";
    }
    
    // Verificar dados existentes
    echo "<h2>📊 DADOS EXISTENTES:</h2>";
    
    // Contar salões
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM saloes");
    $stmt->execute();
    $total_saloes = $stmt->fetch()['total'];
    echo "<p><strong>Total de salões:</strong> $total_saloes</p>";
    
    // Mostrar alguns salões se existirem
    if ($total_saloes > 0) {
        $stmt = $conn->prepare("SELECT * FROM saloes LIMIT 3");
        $stmt->execute();
        $saloes = $stmt->fetchAll();
        
        echo "<h3>📋 Primeiros salões (máximo 3):</h3>";
        echo "<table>";
        $first = true;
        foreach ($saloes as $salao) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($salao) as $key) {
                    if (!is_numeric($key)) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($salao as $key => $value) {
                if (!is_numeric($key)) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar usuários parceiros
    echo "<h2>👥 USUÁRIOS PARCEIROS:</h2>";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'parceiro'");
    $stmt->execute();
    $total_parceiros = $stmt->fetch()['total'];
    echo "<p><strong>Total de parceiros:</strong> $total_parceiros</p>";
    
    if ($total_parceiros > 0) {
        $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 3");
        $stmt->execute();
        $parceiros = $stmt->fetchAll();
        
        echo "<h3>📋 Primeiros parceiros (máximo 3):</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th></tr>";
        foreach ($parceiros as $parceiro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($parceiro['id']) . "</td>";
            echo "<td>" . htmlspecialchars($parceiro['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($parceiro['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar qual coluna usar para inserção
    echo "<h2>🎯 DIAGNÓSTICO:</h2>";
    $tem_id_dono = false;
    $tem_usuario_id = false;
    
    foreach ($estrutura as $campo) {
        if ($campo['Field'] === 'id_dono') {
            $tem_id_dono = true;
        }
        if ($campo['Field'] === 'usuario_id') {
            $tem_usuario_id = true;
        }
    }
    
    echo "<div style='background:#f8f9fa;padding:20px;border-radius:10px;margin:20px 0;'>";
    echo "<p><strong>✅ Coluna 'id_dono' existe:</strong> " . ($tem_id_dono ? 'SIM' : 'NÃO') . "</p>";
    echo "<p><strong>✅ Coluna 'usuario_id' existe:</strong> " . ($tem_usuario_id ? 'SIM' : 'NÃO') . "</p>";
    
    if ($tem_usuario_id && !$tem_id_dono) {
        echo "<p style='color:red;'><strong>🚨 PROBLEMA IDENTIFICADO:</strong> A tabela usa 'usuario_id' mas o código está tentando usar 'id_dono'!</p>";
        echo "<p><strong>💡 SOLUÇÃO:</strong> Atualizar o código para usar 'usuario_id' em vez de 'id_dono'.</p>";
    } elseif ($tem_id_dono && !$tem_usuario_id) {
        echo "<p style='color:green;'><strong>✅ ESTRUTURA OK:</strong> A tabela usa 'id_dono' como esperado.</p>";
    } elseif ($tem_id_dono && $tem_usuario_id) {
        echo "<p style='color:orange;'><strong>⚠️ AMBAS EXISTEM:</strong> A tabela tem tanto 'id_dono' quanto 'usuario_id'.</p>";
        echo "<p><strong>💡 VERIFICAR:</strong> Qual constraint está ativa?</p>";
    } else {
        echo "<p style='color:red;'><strong>❌ ERRO:</strong> Nenhuma das colunas esperadas foi encontrada!</p>";
    }
    echo "</div>";
    
    echo "<p style='text-align:center;margin-top:30px;'><a href='criar_parceiro_e_salao.php' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔙 Voltar</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:10px;margin:20px;'>";
    echo "<h2>❌ Erro ao verificar estrutura:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>