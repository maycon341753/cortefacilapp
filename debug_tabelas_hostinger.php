<?php
/**
 * Debug específico para verificar tabelas no Hostinger
 * Testa conexão e estrutura do banco de dados online
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug - Tabelas Hostinger</h2>";
echo "<p>Ambiente detectado: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";

try {
    // Configurações diretas para Hostinger
    $host = 'srv486.hstgr.io';
    $db_name = 'u690889028_cortefacil';
    $username = 'u690889028_mayconwender';
    $password = 'Maycon341753';
    
    echo "<p>Tentando conectar com:</p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Database: $db_name</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Password: [OCULTA]</li>";
    echo "</ul>";
    
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Verificar tabelas existentes
    echo "<h3>Tabelas existentes no banco:</h3>";
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tabelas)) {
        echo "<p style='color: red;'>❌ Nenhuma tabela encontrada no banco!</p>";
    } else {
        echo "<ul>";
        foreach ($tabelas as $tabela) {
            echo "<li>" . htmlspecialchars($tabela) . "</li>";
        }
        echo "</ul>";
    }
    
    // Tabelas necessárias
    $tabelas_necessarias = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
    $tabelas_faltantes = array_diff($tabelas_necessarias, $tabelas);
    
    if (!empty($tabelas_faltantes)) {
        echo "<h3 style='color: red;'>Tabelas faltantes:</h3>";
        echo "<ul>";
        foreach ($tabelas_faltantes as $faltante) {
            echo "<li style='color: red;'>$faltante</li>";
        }
        echo "</ul>";
        
        echo "<h3>Criando tabelas faltantes:</h3>";
        
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
                    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
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
    } else {
        echo "<p style='color: green;'>✅ Todas as tabelas necessárias existem!</p>";
    }
    
    // Verificar estrutura das tabelas principais
    echo "<h3>Estrutura das tabelas:</h3>";
    
    foreach (['usuarios', 'saloes', 'profissionais'] as $tabela) {
        if (in_array($tabela, $tabelas) || in_array($tabela, $tabelas_faltantes)) {
            try {
                $stmt = $conn->query("DESCRIBE $tabela");
                $colunas = $stmt->fetchAll();
                
                echo "<h4>$tabela:</h4>";
                echo "<table border='1' style='border-collapse: collapse; margin-bottom: 15px;'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th></tr>";
                
                foreach ($colunas as $coluna) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao verificar '$tabela': " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Testar inserção de dados de teste
    echo "<h3>Teste de funcionalidade:</h3>";
    
    // Verificar se existe usuário de teste
    $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = 'teste@profissional.com'");
    $stmt->execute();
    $existe_teste = $stmt->fetchColumn() > 0;
    
    if (!$existe_teste) {
        echo "<p>Criando usuário de teste...</p>";
        try {
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) 
                VALUES ('Teste Parceiro', 'teste@profissional.com', :senha, 'parceiro', '11999999999')
            ");
            $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->execute();
            echo "<p style='color: green;'>✅ Usuário de teste criado</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro ao criar usuário: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuário de teste já existe</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ ERRO DE CONEXÃO: " . $e->getMessage() . "</p>";
    echo "<p>Código: " . $e->getCode() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO GERAL: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";
echo "<p><a href='https://cortefacil.app/parceiro/profissionais.php'>Testar Profissionais</a> | ";
echo "<a href='https://cortefacil.app/parceiro/salao.php'>Testar Salão</a></p>";
?>