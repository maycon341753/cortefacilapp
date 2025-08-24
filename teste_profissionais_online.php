<?php
/**
 * Script para testar o cadastro de profissionais no banco online
 * Simula o processo que acontece na página profissionais.php
 */

// Configuração direta para o banco online
$host = 'srv486.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

echo "<h2>Teste de Cadastro de Profissionais - Banco Online</h2>";

try {
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // 1. Verificar estrutura da tabela profissionais
    echo "<h3>1. Estrutura da tabela profissionais:</h3>";
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($campos as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar se existem salões
    echo "<h3>2. Verificando salões disponíveis:</h3>";
    $stmt = $conn->query("SELECT id, nome, id_dono FROM saloes WHERE ativo = 1 LIMIT 5");
    $saloes = $stmt->fetchAll();
    
    if (empty($saloes)) {
        echo "<p style='color: red;'>❌ Nenhum salão encontrado!</p>";
        echo "<p>É necessário ter pelo menos um salão cadastrado para testar profissionais.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>ID Dono</th></tr>";
        
        foreach ($saloes as $salao) {
            echo "<tr>";
            echo "<td>{$salao['id']}</td>";
            echo "<td>{$salao['nome']}</td>";
            echo "<td>{$salao['id_dono']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 3. Testar cadastro de profissional
        echo "<h3>3. Testando cadastro de profissional:</h3>";
        
        $salao_teste = $saloes[0]; // Usar o primeiro salão
        
        try {
            // Simular o mesmo SQL que está no modelo Profissional
            $sql = "INSERT INTO profissionais (id_salao, nome, especialidade, telefone, email, ativo) 
                    VALUES (:id_salao, :nome, :especialidade, :telefone, :email, :ativo)";
            
            $stmt = $conn->prepare($sql);
            
            $dados = [
                ':id_salao' => $salao_teste['id'],
                ':nome' => 'João Silva - Teste',
                ':especialidade' => 'Cabeleireiro',
                ':telefone' => '(11) 98765-4321',
                ':email' => 'joao.teste@email.com',
                ':ativo' => 1
            ];
            
            echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
            echo "<p><strong>Dados:</strong></p>";
            echo "<ul>";
            foreach ($dados as $key => $value) {
                echo "<li>{$key}: {$value}</li>";
            }
            echo "</ul>";
            
            $resultado = $stmt->execute($dados);
            
            if ($resultado) {
                $id_inserido = $conn->lastInsertId();
                echo "<p style='color: green;'>✅ Profissional cadastrado com sucesso! ID: {$id_inserido}</p>";
                
                // Verificar se foi inserido corretamente
                $stmt_verificar = $conn->prepare("SELECT * FROM profissionais WHERE id = ?");
                $stmt_verificar->execute([$id_inserido]);
                $profissional = $stmt_verificar->fetch();
                
                if ($profissional) {
                    echo "<p><strong>Dados inseridos:</strong></p>";
                    echo "<table border='1' style='border-collapse: collapse;'>";
                    foreach ($profissional as $campo => $valor) {
                        echo "<tr><td><strong>{$campo}</strong></td><td>{$valor}</td></tr>";
                    }
                    echo "</table>";
                }
                
                // Remover o registro de teste
                $conn->exec("DELETE FROM profissionais WHERE id = {$id_inserido}");
                echo "<p style='color: blue;'>ℹ️ Registro de teste removido.</p>";
                
            } else {
                echo "<p style='color: red;'>❌ Falha ao executar a inserção.</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro ao cadastrar profissional:</p>";
            echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
            echo "<p><strong>Código:</strong> {$e->getCode()}</p>";
        }
        
        // 4. Listar profissionais existentes
        echo "<h3>4. Profissionais existentes:</h3>";
        $stmt = $conn->query("SELECT p.*, s.nome as nome_salao FROM profissionais p INNER JOIN saloes s ON p.id_salao = s.id ORDER BY p.id DESC LIMIT 10");
        $profissionais = $stmt->fetchAll();
        
        if (empty($profissionais)) {
            echo "<p>Nenhum profissional cadastrado ainda.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Especialidade</th><th>Telefone</th><th>Email</th><th>Salão</th><th>Ativo</th></tr>";
            
            foreach ($profissionais as $prof) {
                echo "<tr>";
                echo "<td>{$prof['id']}</td>";
                echo "<td>{$prof['nome']}</td>";
                echo "<td>{$prof['especialidade']}</td>";
                echo "<td>" . ($prof['telefone'] ?? '-') . "</td>";
                echo "<td>" . ($prof['email'] ?? '-') . "</td>";
                echo "<td>{$prof['nome_salao']}</td>";
                echo "<td>" . ($prof['ativo'] ? 'Sim' : 'Não') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h3>✅ Teste concluído!</h3>";
    echo "<p>Se chegou até aqui sem erros, o sistema está funcionando corretamente.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão:</p>";
    echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
    echo "<p><strong>Código:</strong> {$e->getCode()}</p>";
}

echo "<hr>";
echo "<p><a href='https://cortefacil.app/parceiro/profissionais.php' target='_blank'>Ir para página de profissionais online</a></p>";
echo "<p><a href='https://cortefacil.app/login.php' target='_blank'>Fazer login no sistema</a></p>";
?>