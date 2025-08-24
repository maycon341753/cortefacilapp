<?php
/**
 * Debug específico para cadastro de profissionais online
 * Verifica tabelas, conexão e testa cadastro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug - Cadastro de Profissionais Online</h2>";
echo "<p>Ambiente: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";

try {
    // Incluir arquivos necessários
    require_once 'config/database.php';
    
    echo "<h3>1. Teste de Conexão</h3>";
    
    $conn = getConnection();
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    } else {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    
    echo "<h3>2. Verificação de Tabelas</h3>";
    
    // Verificar tabelas existentes
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Tabelas encontradas:</strong></p>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>" . htmlspecialchars($tabela) . "</li>";
    }
    echo "</ul>";
    
    // Verificar tabelas necessárias
    $tabelas_necessarias = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
    $tabelas_faltantes = array_diff($tabelas_necessarias, $tabelas);
    
    if (!empty($tabelas_faltantes)) {
        echo "<h4 style='color: red;'>Tabelas faltantes:</h4>";
        echo "<ul>";
        foreach ($tabelas_faltantes as $faltante) {
            echo "<li style='color: red;'>$faltante</li>";
        }
        echo "</ul>";
        
        echo "<h4>Criando tabelas faltantes:</h4>";
        
        // SQL para criar tabelas
        $sqls = [
            'usuarios' => "
                CREATE TABLE usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    senha VARCHAR(255) NOT NULL,
                    tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL DEFAULT 'cliente',
                    telefone VARCHAR(20),
                    cpf VARCHAR(14),
                    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ativo BOOLEAN DEFAULT TRUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'saloes' => "
                CREATE TABLE saloes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_dono INT NOT NULL,
                    nome VARCHAR(255) NOT NULL,
                    endereco TEXT,
                    telefone VARCHAR(20),
                    documento VARCHAR(20),
                    tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf',
                    razao_social VARCHAR(255),
                    inscricao_estadual VARCHAR(50),
                    descricao TEXT,
                    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ativo BOOLEAN DEFAULT TRUE,
                    FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'profissionais' => "
                CREATE TABLE profissionais (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_salao INT NOT NULL,
                    nome VARCHAR(255) NOT NULL,
                    especialidade VARCHAR(255) NOT NULL,
                    telefone VARCHAR(20),
                    email VARCHAR(255),
                    ativo BOOLEAN DEFAULT TRUE,
                    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'agendamentos' => "
                CREATE TABLE agendamentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_cliente INT NOT NULL,
                    id_salao INT NOT NULL,
                    id_profissional INT NOT NULL,
                    data DATE NOT NULL,
                    hora TIME NOT NULL,
                    servico VARCHAR(255),
                    observacoes TEXT,
                    status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
                    data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
                    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
                    FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tabelas_faltantes as $tabela) {
            if (isset($sqls[$tabela])) {
                try {
                    $conn->exec($sqls[$tabela]);
                    echo "<p style='color: green;'>✅ Tabela '$tabela' criada com sucesso!</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>❌ Erro ao criar tabela '$tabela': " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Atualizar lista de tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo "<h3>3. Verificação de Dados de Teste</h3>";
    
    // Verificar se existe usuário parceiro de teste
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = 'teste@parceiro.com' AND tipo_usuario = 'parceiro'");
    $stmt->execute();
    $usuario_teste = $stmt->fetch();
    
    if (!$usuario_teste) {
        echo "<p>Criando usuário parceiro de teste...</p>";
        try {
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) 
                VALUES ('Parceiro Teste', 'teste@parceiro.com', :senha, 'parceiro', '11999999999')
            ");
            $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->execute();
            $usuario_id = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Usuário parceiro criado (ID: $usuario_id)</p>";
            
            // Buscar o usuário criado
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario_teste = $stmt->fetch();
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro ao criar usuário: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuário parceiro de teste já existe (ID: {$usuario_teste['id']})</p>";
    }
    
    // Verificar se existe salão de teste
    if ($usuario_teste) {
        $stmt = $conn->prepare("SELECT * FROM saloes WHERE id_dono = ?");
        $stmt->execute([$usuario_teste['id']]);
        $salao_teste = $stmt->fetch();
        
        if (!$salao_teste) {
            echo "<p>Criando salão de teste...</p>";
            try {
                $stmt = $conn->prepare("
                    INSERT INTO saloes (id_dono, nome, endereco, telefone, documento, descricao) 
                    VALUES (?, 'Salão Teste', 'Rua Teste, 123', '11999999999', '12345678901', 'Salão para testes')
                ");
                $stmt->execute([$usuario_teste['id']]);
                $salao_id = $conn->lastInsertId();
                echo "<p style='color: green;'>✅ Salão criado (ID: $salao_id)</p>";
                
                // Buscar o salão criado
                $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = ?");
                $stmt->execute([$salao_id]);
                $salao_teste = $stmt->fetch();
                
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao criar salão: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Salão de teste já existe (ID: {$salao_teste['id']})</p>";
        }
    }
    
    echo "<h3>4. Teste de Cadastro de Profissional</h3>";
    
    if ($salao_teste) {
        // Tentar cadastrar um profissional de teste
        $nome_prof = 'João Barbeiro ' . date('His');
        $especialidade = 'Corte e Barba';
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO profissionais (id_salao, nome, especialidade, telefone, email) 
                VALUES (?, ?, ?, '11888888888', 'joao@teste.com')
            ");
            $stmt->execute([$salao_teste['id'], $nome_prof, $especialidade]);
            $prof_id = $conn->lastInsertId();
            
            echo "<p style='color: green;'>✅ Profissional cadastrado com sucesso!</p>";
            echo "<ul>";
            echo "<li>ID: $prof_id</li>";
            echo "<li>Nome: $nome_prof</li>";
            echo "<li>Especialidade: $especialidade</li>";
            echo "<li>Salão: {$salao_teste['nome']}</li>";
            echo "</ul>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro ao cadastrar profissional: " . $e->getMessage() . "</p>";
        }
        
        // Listar profissionais do salão
        echo "<h4>Profissionais do salão:</h4>";
        $stmt = $conn->prepare("SELECT * FROM profissionais WHERE id_salao = ? ORDER BY nome");
        $stmt->execute([$salao_teste['id']]);
        $profissionais = $stmt->fetchAll();
        
        if (empty($profissionais)) {
            echo "<p>Nenhum profissional encontrado.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Especialidade</th><th>Status</th><th>Data Cadastro</th></tr>";
            foreach ($profissionais as $prof) {
                echo "<tr>";
                echo "<td>" . $prof['id'] . "</td>";
                echo "<td>" . htmlspecialchars($prof['nome']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['especialidade']) . "</td>";
                echo "<td>" . ($prof['ativo'] ? 'Ativo' : 'Inativo') . "</td>";
                echo "<td>" . $prof['data_cadastro'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h3>5. Estrutura das Tabelas</h3>";
    
    foreach (['usuarios', 'saloes', 'profissionais'] as $tabela) {
        if (in_array($tabela, $tabelas)) {
            echo "<h4>$tabela:</h4>";
            $stmt = $conn->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 15px;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
            
            foreach ($colunas as $coluna) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($coluna['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";
echo "<p><a href='parceiro/profissionais.php'>Ir para Profissionais</a> | ";
echo "<a href='parceiro/salao.php'>Ir para Salão</a></p>";
?>