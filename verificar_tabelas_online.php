<?php
/**
 * Script para verificar estrutura completa das tabelas no banco online
 * Compara com a estrutura esperada do projeto
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Verificação Completa das Tabelas - Banco Online</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    // Forçar ambiente online
    $env_file = __DIR__ . '/.env.online';
    if (!file_exists($env_file)) {
        file_put_contents($env_file, 'FORCE_ONLINE=true');
    }
    
    require_once 'config/database.php';
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco online');
    }
    
    echo "<p class='success'>✅ Conectado ao banco online: u690889028_cortefacil</p>";
    
    // Definir estrutura esperada das tabelas principais
    $tabelas_esperadas = [
        'usuarios' => [
            'campos' => ['id', 'nome', 'email', 'senha', 'tipo_usuario', 'telefone', 'cpf', 'data_cadastro', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'nome', 'email', 'senha', 'tipo_usuario']
        ],
        'saloes' => [
            'campos' => ['id', 'id_dono', 'nome', 'endereco', 'telefone', 'documento', 'tipo_documento', 'descricao', 'ativo', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_dono', 'nome', 'endereco', 'telefone']
        ],
        'profissionais' => [
            'campos' => ['id', 'id_salao', 'nome', 'especialidade', 'ativo', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_salao', 'nome', 'especialidade']
        ],
        'agendamentos' => [
            'campos' => ['id', 'id_cliente', 'id_salao', 'id_profissional', 'data', 'hora', 'status', 'valor_taxa', 'observacoes', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_cliente', 'id_salao', 'id_profissional', 'data', 'hora']
        ],
        'bloqueios_temporarios' => [
            'campos' => ['id', 'id_profissional', 'data', 'hora', 'session_id', 'ip_cliente', 'created_at', 'expires_at'],
            'obrigatorios' => ['id', 'id_profissional', 'data', 'hora', 'session_id']
        ],
        'horarios_funcionamento' => [
            'campos' => ['id', 'id_salao', 'dia_semana', 'hora_abertura', 'hora_fechamento', 'ativo', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_salao', 'dia_semana', 'hora_abertura', 'hora_fechamento']
        ],
        'servicos' => [
            'campos' => ['id', 'id_salao', 'nome', 'descricao', 'preco', 'duracao_minutos', 'ativo', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_salao', 'nome', 'preco']
        ],
        'pagamentos' => [
            'campos' => ['id', 'id_agendamento', 'valor', 'status', 'metodo_pagamento', 'transaction_id', 'data_pagamento', 'created_at', 'updated_at'],
            'obrigatorios' => ['id', 'id_agendamento', 'valor', 'status']
        ]
    ];
    
    // Verificar tabelas existentes
    $stmt = $conn->query("SHOW TABLES");
    $tabelas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Tabelas Encontradas no Banco Online</h3>";
    echo "<p class='info'>Total de tabelas: " . count($tabelas_existentes) . "</p>";
    echo "<p><strong>Tabelas:</strong> " . implode(', ', $tabelas_existentes) . "</p>";
    
    echo "<hr>";
    
    // Verificar cada tabela esperada
    $tabelas_faltantes = [];
    $tabelas_com_problemas = [];
    $tabelas_ok = [];
    
    foreach ($tabelas_esperadas as $nome_tabela => $estrutura) {
        echo "<h3>🔍 Verificando tabela: <strong>$nome_tabela</strong></h3>";
        
        if (in_array($nome_tabela, $tabelas_existentes)) {
            echo "<p class='success'>✅ Tabela '$nome_tabela' existe</p>";
            
            // Verificar estrutura
            $stmt = $conn->query("DESCRIBE $nome_tabela");
            $colunas_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nomes_colunas = array_column($colunas_existentes, 'Field');
            
            echo "<h4>Estrutura da tabela:</h4>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
            foreach ($colunas_existentes as $coluna) {
                echo "<tr>";
                echo "<td>{$coluna['Field']}</td>";
                echo "<td>{$coluna['Type']}</td>";
                echo "<td>{$coluna['Null']}</td>";
                echo "<td>{$coluna['Key']}</td>";
                echo "<td>{$coluna['Default']}</td>";
                echo "<td>{$coluna['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar campos obrigatórios
            $campos_faltando = array_diff($estrutura['obrigatorios'], $nomes_colunas);
            $campos_extras = array_diff($nomes_colunas, $estrutura['campos']);
            
            if (empty($campos_faltando)) {
                echo "<p class='success'>✅ Todos os campos obrigatórios estão presentes</p>";
            } else {
                echo "<p class='error'>❌ Campos obrigatórios faltando: " . implode(', ', $campos_faltando) . "</p>";
                $tabelas_com_problemas[] = $nome_tabela;
            }
            
            if (!empty($campos_extras)) {
                echo "<p class='info'>ℹ️ Campos extras encontrados: " . implode(', ', $campos_extras) . "</p>";
            }
            
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) FROM $nome_tabela");
            $total_registros = $stmt->fetchColumn();
            echo "<p class='info'>📊 Total de registros: $total_registros</p>";
            
            if (empty($campos_faltando)) {
                $tabelas_ok[] = $nome_tabela;
            }
            
        } else {
            echo "<p class='error'>❌ Tabela '$nome_tabela' NÃO EXISTE</p>";
            $tabelas_faltantes[] = $nome_tabela;
        }
        
        echo "<hr>";
    }
    
    // Resumo final
    echo "<h3>📊 Resumo da Verificação</h3>";
    echo "<p class='success'>✅ Tabelas OK: " . count($tabelas_ok) . " (" . implode(', ', $tabelas_ok) . ")</p>";
    
    if (!empty($tabelas_com_problemas)) {
        echo "<p class='warning'>⚠️ Tabelas com problemas: " . count($tabelas_com_problemas) . " (" . implode(', ', $tabelas_com_problemas) . ")</p>";
    }
    
    if (!empty($tabelas_faltantes)) {
        echo "<p class='error'>❌ Tabelas faltantes: " . count($tabelas_faltantes) . " (" . implode(', ', $tabelas_faltantes) . ")</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";
?>