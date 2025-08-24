<?php
/**
 * Verificação da tabela de especialidades
 * Verifica se a tabela existe e cria se necessário
 */

header('Content-Type: text/html; charset=utf-8');

// Iniciar buffer de saída
ob_start();

echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Verificação de Especialidades</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }\n";
echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
echo "table { border-collapse: collapse; width: 100%; margin: 20px 0; }\n";
echo "table, th, td { border: 1px solid #ddd; }\n";
echo "th, td { padding: 10px; text-align: left; }\n";
echo "th { background-color: #f2f2f2; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔍 Verificação da Tabela de Especialidades</h1>\n";

try {
    require_once 'config/database.php';
    
    // Verificar conexão
    echo "<h2>1. Verificando Conexão com o Banco de Dados</h2>\n";
    $conn = getConnection();
    echo "<div class='success'>✅ Conexão estabelecida com sucesso!</div>\n";
    
    // Verificar tabela de especialidades
    echo "<h2>2. Verificando Tabela 'especialidades'</h2>\n";
    $stmt = $conn->query("SHOW TABLES LIKE 'especialidades'");
    $especialidadesExists = $stmt->rowCount() > 0;
    
    if (!$especialidadesExists) {
        echo "<div class='error'>❌ Tabela 'especialidades' não existe!</div>\n";
        
        // Criar tabela
        echo "<h3>Criando tabela 'especialidades'...</h3>\n";
        $sql = "CREATE TABLE especialidades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            ativo TINYINT(1) DEFAULT 1,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "<div class='success'>✅ Tabela 'especialidades' criada com sucesso!</div>\n";
        
        // Inserir especialidades padrão
        echo "<h3>Inserindo especialidades padrão...</h3>\n";
        $especialidades = [
            'Cabeleireiro',
            'Barbeiro',
            'Manicure',
            'Pedicure',
            'Esteticista',
            'Maquiador',
            'Depilador',
            'Massagista',
            'Designer de Sobrancelhas',
            'Colorista',
            'Podólogo',
            'Micropigmentador',
            'Alongamento de Cílios',
            'Lash Designer',
            'Terapeuta Capilar'
        ];
        
        $sql = "INSERT INTO especialidades (nome) VALUES (:nome)";
        $stmt = $conn->prepare($sql);
        
        $count = 0;
        foreach ($especialidades as $esp) {
            $stmt->bindParam(':nome', $esp);
            $stmt->execute();
            $count++;
        }
        
        echo "<div class='success'>✅ {$count} especialidades inseridas com sucesso!</div>\n";
    } else {
        echo "<div class='success'>✅ Tabela 'especialidades' existe!</div>\n";
        
        // Listar especialidades
        $stmt = $conn->query("SELECT * FROM especialidades WHERE ativo = 1 ORDER BY nome");
        $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Especialidades cadastradas (". count($especialidades) ."):</h3>\n";
        
        if (count($especialidades) > 0) {
            echo "<table>\n";
            echo "<tr><th>ID</th><th>Nome</th><th>Status</th></tr>\n";
            
            foreach ($especialidades as $esp) {
                echo "<tr>";
                echo "<td>" . $esp['id'] . "</td>";
                echo "<td>" . htmlspecialchars($esp['nome']) . "</td>";
                echo "<td>" . ($esp['ativo'] ? 'Ativo' : 'Inativo') . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        } else {
            echo "<div class='error'>❌ Nenhuma especialidade cadastrada ou ativa!</div>\n";
            
            // Inserir especialidades padrão
            echo "<h3>Inserindo especialidades padrão...</h3>\n";
            $especialidades = [
                'Cabeleireiro',
                'Barbeiro',
                'Manicure',
                'Pedicure',
                'Esteticista',
                'Maquiador',
                'Depilador',
                'Massagista',
                'Designer de Sobrancelhas',
                'Colorista',
                'Podólogo',
                'Micropigmentador',
                'Alongamento de Cílios',
                'Lash Designer',
                'Terapeuta Capilar'
            ];
            
            $sql = "INSERT INTO especialidades (nome) VALUES (:nome)";
            $stmt = $conn->prepare($sql);
            
            $count = 0;
            foreach ($especialidades as $esp) {
                $stmt->bindParam(':nome', $esp);
                $stmt->execute();
                $count++;
            }
            
            echo "<div class='success'>✅ {$count} especialidades inseridas com sucesso!</div>\n";
        }
    }
    
    // Verificar dropdown de especialidades na página profissionais.php
    echo "<h2>3. Verificando Dropdown de Especialidades</h2>\n";
    
    $arquivo_profissionais = file_get_contents('parceiro/profissionais.php');
    
    if (strpos($arquivo_profissionais, 'option value="Cabeleireiro"') !== false) {
        echo "<div class='error'>❌ Dropdown de especialidades está com valores fixos!</div>\n";
        
        // Criar script para corrigir
        echo "<h3>Criando script para corrigir dropdown...</h3>\n";
        
        $script_correcao = "<?php
/**
 * Correção do Dropdown de Especialidades
 * Substitui o dropdown hardcoded por um dinâmico que carrega do banco
 */

require_once 'config/database.php';

try {
    // Conectar ao banco
    \$conn = getConnection();
    echo \"<p>✅ Conexão estabelecida com sucesso!</p>\";
    
    // Verificar se tabela especialidades existe
    \$stmt = \$conn->query(\"SHOW TABLES LIKE 'especialidades'\");
    \$especialidadesExists = \$stmt->rowCount() > 0;
    
    if (!\$especialidadesExists) {
        echo \"<p>❌ Tabela 'especialidades' não existe!</p>\";
        exit;
    }
    
    // Buscar especialidades
    \$stmt = \$conn->query(\"SELECT * FROM especialidades WHERE ativo = 1 ORDER BY nome\");
    \$especialidades = \$stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo \"<p>✅ \" . count(\$especialidades) . \" especialidades encontradas</p>\";
    
    if (count(\$especialidades) === 0) {
        echo \"<p>❌ Nenhuma especialidade cadastrada ou ativa!</p>\";
        exit;
    }
    
    // Listar especialidades encontradas
    echo \"<p>Especialidades encontradas:</p>\";
    echo \"<ul>\";
    foreach (\$especialidades as \$esp) {
        echo \"<li>\" . \$esp['id'] . \" - \" . \$esp['nome'] . \"</li>\";
    }
    echo \"</ul>\";
    
    // Ler arquivo profissionais.php
    \$arquivo = file_get_contents('parceiro/profissionais.php');
    
    if (!\$arquivo) {
        echo \"<p>❌ Não foi possível ler o arquivo profissionais.php</p>\";
        exit;
    }
    
    // Inserir código para carregar especialidades
    \$codigo_carregar_especialidades = \"\n// Carregar especialidades do banco\n\\$stmt = \\$conn->query(\\\"SELECT * FROM especialidades WHERE ativo = 1 ORDER BY nome\\\");\n\\$especialidades = \\$stmt->fetchAll(PDO::FETCH_ASSOC);\n\";
    
    // Procurar posição para inserir (após a conexão com o banco)
    \$posicao = strpos(\$arquivo, '\\$profissional = new Profissional();');
    
    if (\$posicao !== false) {
        \$arquivo = substr_replace(\$arquivo, \$codigo_carregar_especialidades, \$posicao + strlen('\\$profissional = new Profissional();'), 0);
        echo \"<p>✅ Código de carregamento de especialidades inserido</p>\";
    } else {
        echo \"<p>❌ Não foi possível encontrar posição para inserir código</p>\";
        exit;
    }
    
    // Substituir dropdown hardcoded por dinâmico
    \$dropdown_hardcoded = '<select class=\\\"form-select\\\" id=\\\"especialidade\\\" name=\\\"especialidade\\\" required>\\n' .
                        '                                <option value=\\\"\\\">Selecione...</option>\\n' .
                        '                                <option value=\\\"Cabeleireiro\\\">Cabeleireiro</option>\\n' .
                        '                                <option value=\\\"Barbeiro\\\">Barbeiro</option>\\n' .
                        '                                <option value=\\\"Manicure\\\">Manicure</option>\\n' .
                        '                                <option value=\\\"Pedicure\\\">Pedicure</option>\\n' .
                        '                                <option value=\\\"Esteticista\\\">Esteticista</option>\\n' .
                        '                                <option value=\\\"Maquiador\\\">Maquiador</option>\\n' .
                        '                                <option value=\\\"Depilador\\\">Depilador</option>\\n' .
                        '                            </select>';
    
    \$dropdown_dinamico = '<select class=\\\"form-select\\\" id=\\\"especialidade\\\" name=\\\"especialidade\\\" required>\\n' .
                       '                                <option value=\\\"\\\">Selecione...</option>\\n' .
                       '                                <?php foreach (\\$especialidades as \\$esp): ?>\\n' .
                       '                                <option value=\\\"<?php echo \\$esp[\'nome\']; ?>\\\"><?php echo \\$esp[\'nome\']; ?></option>\\n' .
                       '                                <?php endforeach; ?>\\n' .
                       '                            </select>';
    
    \$arquivo_modificado = str_replace(\$dropdown_hardcoded, \$dropdown_dinamico, \$arquivo);
    
    if (\$arquivo_modificado !== \$arquivo) {
        // Fazer backup do arquivo original
        file_put_contents('parceiro/profissionais.php.bak', \$arquivo);
        echo \"<p>✅ Backup do arquivo original criado</p>\";
        
        // Salvar arquivo modificado
        file_put_contents('parceiro/profissionais.php', \$arquivo_modificado);
        echo \"<p>✅ Arquivo profissionais.php corrigido com sucesso!</p>\";
    } else {
        echo \"<p>❌ Não foi possível encontrar o dropdown para substituir</p>\";
    }
    
    echo \"<p>✅ Correção concluída com sucesso!</p>\";
    
} catch (Exception \$e) {
    echo \"<p>❌ Erro: \" . \$e->getMessage() . \"</p>\";
}\n";
        
        file_put_contents('corrigir_dropdown_especialidades.php', $script_correcao);
        echo "<div class='success'>✅ Script de correção criado: corrigir_dropdown_especialidades.php</div>\n";
        
        echo "<h3>Execute o script de correção para resolver o problema:</h3>\n";
        echo "<code>php corrigir_dropdown_especialidades.php</code>\n";
    } else {
        echo "<div class='success'>✅ Dropdown de especialidades parece estar correto!</div>\n";
    }
    
    echo "<h2>4. Conclusão</h2>\n";
    echo "<p>Com base na análise realizada, o problema de cadastro de profissionais pode ser:</p>\n";
    echo "<ol>\n";
    echo "<li>Tabela 'especialidades' inexistente ou sem dados</li>\n";
    echo "<li>Dropdown de especialidades com valores fixos em vez de dinâmicos</li>\n";
    echo "</ol>\n";
    
    echo "<p><strong>Recomendação:</strong> Execute o script de correção criado para resolver o problema do dropdown de especialidades.</p>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>\n";
}

$output = ob_get_clean();
echo $output;
?>