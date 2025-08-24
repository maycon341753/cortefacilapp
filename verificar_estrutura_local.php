<?php
/**
 * Verificar e criar estrutura do banco local
 */

require_once 'config/database.php';

echo "<h2>🔍 Verificação da Estrutura do Banco Local</h2>";

$database = Database::getInstance();
$conn = $database->connect();

// Verificar qual banco estamos usando
$stmt = $conn->query("SELECT DATABASE() as current_db");
$db_info = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p class='info'>🗄️ Banco atual: {$db_info['current_db']}</p>";

// Verificar host
$stmt = $conn->query("SELECT @@hostname as host");
$host_info = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p class='info'>🖥️ Host: {$host_info['host']}</p>";

echo "<h3>1. Verificando Tabelas Existentes</h3>";

$stmt = $conn->query("SHOW TABLES");
$tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<p class='info'>📊 Total de tabelas: " . count($tabelas) . "</p>";

if (empty($tabelas)) {
    echo "<p class='error'>❌ Banco local está vazio!</p>";
} else {
    echo "<p class='success'>✅ Tabelas encontradas:</p>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>{$tabela}</li>";
    }
    echo "</ul>";
}

// Verificar tabelas essenciais
echo "<h3>2. Verificando Tabelas Essenciais</h3>";

$tabelas_essenciais = [
    'agendamentos',
    'profissionais', 
    'saloes',
    'clientes',
    'horarios_funcionamento',
    'bloqueios_temporarios'
];

$tabelas_faltando = [];

foreach ($tabelas_essenciais as $tabela_essencial) {
    if (in_array($tabela_essencial, $tabelas)) {
        echo "<p class='success'>✅ {$tabela_essencial}</p>";
        
        // Verificar estrutura da tabela agendamentos
        if ($tabela_essencial === 'agendamentos') {
            $stmt = $conn->query("DESCRIBE agendamentos");
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $tem_status = in_array('status', $colunas);
            $tem_id_profissional = in_array('id_profissional', $colunas);
            
            if ($tem_status && $tem_id_profissional) {
                echo "<p class='success'>  ✅ Estrutura OK (status e id_profissional presentes)</p>";
            } else {
                echo "<p class='error'>  ❌ Estrutura incompleta</p>";
                if (!$tem_status) echo "<p class='error'>    - Falta coluna 'status'</p>";
                if (!$tem_id_profissional) echo "<p class='error'>    - Falta coluna 'id_profissional'</p>";
            }
        }
        
    } else {
        echo "<p class='error'>❌ {$tabela_essencial} (FALTANDO)</p>";
        $tabelas_faltando[] = $tabela_essencial;
    }
}

if (!empty($tabelas_faltando)) {
    echo "<h3>3. Criando Tabelas Faltantes</h3>";
    
    // SQL para criar as tabelas essenciais
    $sqls_criacao = [
        'saloes' => "
            CREATE TABLE saloes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                endereco TEXT,
                telefone VARCHAR(20),
                email VARCHAR(255),
                ativo BOOLEAN DEFAULT TRUE,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'profissionais' => "
            CREATE TABLE profissionais (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_salao INT NOT NULL,
                nome VARCHAR(255) NOT NULL,
                especialidade VARCHAR(255),
                telefone VARCHAR(20),
                email VARCHAR(255),
                ativo BOOLEAN DEFAULT TRUE,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_salao) REFERENCES saloes(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'clientes' => "
            CREATE TABLE clientes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                telefone VARCHAR(20),
                email VARCHAR(255),
                data_nascimento DATE,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
                status ENUM('agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'agendado',
                data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_cliente) REFERENCES clientes(id),
                FOREIGN KEY (id_salao) REFERENCES saloes(id),
                FOREIGN KEY (id_profissional) REFERENCES profissionais(id),
                INDEX idx_profissional_data (id_profissional, data),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'horarios_funcionamento' => "
            CREATE TABLE horarios_funcionamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_salao INT NOT NULL,
                dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
                hora_abertura TIME NOT NULL,
                hora_fechamento TIME NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                FOREIGN KEY (id_salao) REFERENCES saloes(id),
                UNIQUE KEY unique_salao_dia (id_salao, dia_semana)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'bloqueios_temporarios' => "
            CREATE TABLE bloqueios_temporarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_profissional INT NOT NULL,
                data DATE NOT NULL,
                hora TIME NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                ip_cliente VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                INDEX idx_profissional_data (id_profissional, data),
                INDEX idx_expires (expires_at),
                UNIQUE KEY unique_horario_ativo (id_profissional, data, hora, session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tabelas_faltando as $tabela) {
        if (isset($sqls_criacao[$tabela])) {
            try {
                echo "<p class='info'>🔨 Criando tabela {$tabela}...</p>";
                $conn->exec($sqls_criacao[$tabela]);
                echo "<p class='success'>✅ Tabela {$tabela} criada com sucesso!</p>";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Erro ao criar tabela {$tabela}: {$e->getMessage()}</p>";
            }
        }
    }
    
    echo "<h3>4. Inserindo Dados de Teste</h3>";
    
    try {
        // Inserir salão de teste
        $conn->exec("INSERT INTO saloes (nome, endereco, telefone) VALUES ('Salão Teste Local', 'Rua Teste, 123', '(11) 99999-9999')");
        echo "<p class='success'>✅ Salão de teste inserido</p>";
        
        // Inserir profissional de teste
        $conn->exec("INSERT INTO profissionais (id_salao, nome, especialidade) VALUES (1, 'Profissional Teste Local', 'Corte e Escova')");
        echo "<p class='success'>✅ Profissional de teste inserido</p>";
        
        // Inserir horários de funcionamento
        $horarios = [
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 1, '08:00:00', '18:00:00')", // Segunda
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 2, '08:00:00', '18:00:00')", // Terça
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 3, '08:00:00', '18:00:00')", // Quarta
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 4, '08:00:00', '18:00:00')", // Quinta
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 5, '08:00:00', '18:00:00')", // Sexta
            "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (1, 6, '08:00:00', '16:00:00')"  // Sábado
        ];
        
        foreach ($horarios as $sql) {
            $conn->exec($sql);
        }
        echo "<p class='success'>✅ Horários de funcionamento inseridos</p>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao inserir dados de teste: {$e->getMessage()}</p>";
    }
}

echo "<h3>5. Teste Final</h3>";

try {
    // Testar a classe Agendamento
    require_once 'models/agendamento.php';
    $agendamento = new Agendamento();
    
    echo "<p class='success'>✅ Classe Agendamento instanciada</p>";
    
    // Testar método listarHorariosOcupados
    $horarios_ocupados = $agendamento->listarHorariosOcupados(1, '2025-08-23');
    echo "<p class='success'>✅ listarHorariosOcupados funcionou: " . count($horarios_ocupados) . " horários</p>";
    
    // Testar método gerarHorariosDisponiveis
    $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis(1, '2025-08-23');
    echo "<p class='success'>✅ gerarHorariosDisponiveis funcionou: " . count($horarios_disponiveis) . " horários</p>";
    
    // Testar método gerarHorariosDisponiveisComBloqueios
    $horarios_com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios(1, '2025-08-23', 'test_session');
    echo "<p class='success'>✅ gerarHorariosDisponiveisComBloqueios funcionou: " . count($horarios_com_bloqueios) . " horários</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro no teste final: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><strong>Verificação da estrutura local concluída!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>