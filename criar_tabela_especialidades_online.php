<?php
/**
 * Script para criar tabela de especialidades no banco online
 * E inserir especialidades padrão para salões de beleza
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configuração do banco
require_once 'config/database.php';

echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Criar Tabela Especialidades</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
echo ".info { color: blue; background: #e6f3ff; padding: 15px; border: 1px solid blue; margin: 15px 0; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔧 Criando Tabela de Especialidades no Banco Online</h1>\n";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<div class='success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // SQL para criar tabela especialidades
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS especialidades (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(100) NOT NULL,
            descricao TEXT,
            ativo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_nome (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    echo "<h2>📋 Criando Tabela Especialidades</h2>\n";
    
    $pdo->exec($createTableSQL);
    
    echo "<div class='success'>\n";
    echo "<p>✅ Tabela 'especialidades' criada com sucesso!</p>\n";
    echo "</div>\n";
    
    // Especialidades padrão para salões de beleza
    $especialidades = [
        ['nome' => 'Corte Masculino', 'descricao' => 'Cortes de cabelo masculinos tradicionais e modernos'],
        ['nome' => 'Corte Feminino', 'descricao' => 'Cortes de cabelo femininos, chanel, long bob, etc.'],
        ['nome' => 'Barba', 'descricao' => 'Aparar, modelar e cuidar da barba'],
        ['nome' => 'Coloração', 'descricao' => 'Tintura, mechas, luzes e outros procedimentos de coloração'],
        ['nome' => 'Escova', 'descricao' => 'Escova modeladora, escova lisa, escova cachos'],
        ['nome' => 'Hidratação', 'descricao' => 'Tratamentos hidratantes para cabelos ressecados'],
        ['nome' => 'Progressiva', 'descricao' => 'Alisamento e redução de volume'],
        ['nome' => 'Manicure', 'descricao' => 'Cuidados com as unhas das mãos'],
        ['nome' => 'Pedicure', 'descricao' => 'Cuidados com as unhas dos pés'],
        ['nome' => 'Sobrancelha', 'descricao' => 'Design e modelagem de sobrancelhas'],
        ['nome' => 'Maquiagem', 'descricao' => 'Maquiagem para eventos, casamentos, etc.'],
        ['nome' => 'Depilação', 'descricao' => 'Remoção de pelos com cera, linha, etc.'],
        ['nome' => 'Massagem', 'descricao' => 'Massagens relaxantes e terapêuticas'],
        ['nome' => 'Limpeza de Pele', 'descricao' => 'Tratamentos faciais e limpeza profunda'],
        ['nome' => 'Penteado', 'descricao' => 'Penteados para eventos especiais']
    ];
    
    echo "<h2>📝 Inserindo Especialidades Padrão</h2>\n";
    
    $insertSQL = "INSERT IGNORE INTO especialidades (nome, descricao) VALUES (?, ?)";
    $stmt = $pdo->prepare($insertSQL);
    
    $inserted = 0;
    foreach ($especialidades as $esp) {
        try {
            $stmt->execute([$esp['nome'], $esp['descricao']]);
            if ($stmt->rowCount() > 0) {
                $inserted++;
                echo "<div class='success'>\n";
                echo "<p>✅ Especialidade '{$esp['nome']}' inserida</p>\n";
                echo "</div>\n";
            } else {
                echo "<div class='info'>\n";
                echo "<p>ℹ️ Especialidade '{$esp['nome']}' já existe</p>\n";
                echo "</div>\n";
            }
        } catch (Exception $e) {
            echo "<div class='error'>\n";
            echo "<p>❌ Erro ao inserir '{$esp['nome']}': " . $e->getMessage() . "</p>\n";
            echo "</div>\n";
        }
    }
    
    echo "<div class='success'>\n";
    echo "<h3>🎉 Processo Concluído!</h3>\n";
    echo "<p><strong>Especialidades inseridas:</strong> {$inserted}</p>\n";
    echo "</div>\n";
    
    // Verificar resultado final
    echo "<h2>📊 Verificação Final</h2>\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM especialidades WHERE ativo = 1");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<div class='info'>\n";
    echo "<p><strong>Total de especialidades ativas:</strong> {$result['total']}</p>\n";
    echo "</div>\n";
    
    // Mostrar todas as especialidades
    $stmt = $pdo->prepare("SELECT id, nome, descricao FROM especialidades WHERE ativo = 1 ORDER BY nome");
    $stmt->execute();
    $especialidades_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Lista de Especialidades Disponíveis:</h3>\n";
    echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>\n";
    echo "<tr style='background-color: #f2f2f2;'>\n";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>\n";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Nome</th>\n";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Descrição</th>\n";
    echo "</tr>\n";
    
    foreach ($especialidades_db as $esp) {
        echo "<tr>\n";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($esp['id']) . "</td>\n";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'><strong>" . htmlspecialchars($esp['nome']) . "</strong></td>\n";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($esp['descricao']) . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>