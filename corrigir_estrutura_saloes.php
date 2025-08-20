<?php
/**
 * Script para corrigir a estrutura da tabela saloes
 * Adiciona a coluna id_dono se ela não existir
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    // Verificar se a coluna id_dono existe
    $stmt = $conn->query("SHOW COLUMNS FROM saloes LIKE 'id_dono'");
    $coluna_existe = $stmt->rowCount() > 0;
    
    if (!$coluna_existe) {
        echo "<h2>Corrigindo estrutura da tabela saloes...</h2>";
        
        // Adicionar a coluna id_dono
        $sql = "ALTER TABLE saloes ADD COLUMN id_dono INT NOT NULL AFTER id";
        $conn->exec($sql);
        echo "<p>✓ Coluna id_dono adicionada com sucesso!</p>";
        
        // Adicionar a chave estrangeira
        $sql = "ALTER TABLE saloes ADD FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE";
        $conn->exec($sql);
        echo "<p>✓ Chave estrangeira adicionada com sucesso!</p>";
        
        // Verificar se existem usuários e salões para associar
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'parceiro'");
        $parceiros = $stmt->fetch()['total'];
        
        $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes");
        $saloes = $stmt->fetch()['total'];
        
        if ($parceiros > 0 && $saloes > 0) {
            // Associar salões existentes ao primeiro parceiro encontrado
            $stmt = $conn->query("SELECT id FROM usuarios WHERE tipo = 'parceiro' LIMIT 1");
            $primeiro_parceiro = $stmt->fetch()['id'];
            
            $sql = "UPDATE saloes SET id_dono = :id_dono WHERE id_dono = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_dono', $primeiro_parceiro);
            $stmt->execute();
            
            echo "<p>✓ Salões existentes associados ao parceiro ID: {$primeiro_parceiro}</p>";
        }
        
        echo "<h3>Estrutura corrigida com sucesso!</h3>";
        
    } else {
        echo "<h2>Estrutura da tabela saloes</h2>";
        echo "<p>✓ A coluna id_dono já existe na tabela saloes.</p>";
    }
    
    // Mostrar estrutura atual da tabela
    echo "<h3>Estrutura atual da tabela saloes:</h3>";
    $stmt = $conn->query("DESCRIBE saloes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
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
    
    // Mostrar dados existentes
    $stmt = $conn->query("SELECT * FROM saloes");
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($saloes)) {
        echo "<h3>Dados existentes na tabela saloes:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr>";
        foreach (array_keys($saloes[0]) as $campo) {
            echo "<th>{$campo}</th>";
        }
        echo "</tr>";
        
        foreach ($saloes as $salao) {
            echo "<tr>";
            foreach ($salao as $valor) {
                echo "<td>" . htmlspecialchars($valor ?? '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum salão cadastrado ainda.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Erro ao corrigir estrutura:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>

<hr>
<p><a href="parceiro/salao.php">← Voltar para página do salão</a></p>