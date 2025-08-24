<?php
/**
 * Script para criar salões automaticamente para todos os parceiros que não têm salão
 * Resolve o problema de "Primeiro Acesso" para parceiros existentes
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Criando salões automáticos para parceiros</h2>";
    
    // Buscar parceiros sem salão
    $stmt = $conn->query("SELECT u.id, u.nome, u.email 
                         FROM usuarios u 
                         LEFT JOIN saloes s ON u.id = s.id_dono 
                         WHERE u.tipo = 'Parceiro' AND s.id IS NULL");
    
    $parceiros_sem_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($parceiros_sem_salao)) {
        echo "<p style='color: green;'>✅ Todos os parceiros já têm salão cadastrado!</p>";
    } else {
        echo "<p>Encontrados " . count($parceiros_sem_salao) . " parceiros sem salão.</p>";
        echo "<p>Criando salões automáticos...</p>";
        
        $conn->beginTransaction();
        
        $saloes_criados = 0;
        $erros = [];
        
        foreach ($parceiros_sem_salao as $parceiro) {
            try {
                // Criar salão básico para o parceiro
                $nome_salao = "Salão " . $parceiro['nome'];
                $endereco = "Endereço a ser atualizado";
                $telefone = "(00) 00000-0000";
                $descricao = "Salão criado automaticamente. Por favor, atualize as informações.";
                
                $sql = "INSERT INTO saloes (nome, endereco, telefone, descricao, id_dono, data_cadastro, ativo) 
                        VALUES (:nome, :endereco, :telefone, :descricao, :id_dono, NOW(), 1)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nome', $nome_salao);
                $stmt->bindParam(':endereco', $endereco);
                $stmt->bindParam(':telefone', $telefone);
                $stmt->bindParam(':descricao', $descricao);
                $stmt->bindParam(':id_dono', $parceiro['id']);
                
                if ($stmt->execute()) {
                    $salao_id = $conn->lastInsertId();
                    echo "<p style='color: green;'>✅ Salão criado para {$parceiro['nome']} (ID: {$salao_id})</p>";
                    $saloes_criados++;
                    
                    // Criar horários de funcionamento padrão
                    $horarios_sql = "INSERT INTO horarios_funcionamento (salao_id, dia_semana, hora_abertura, hora_fechamento, ativo) VALUES";
                    $horarios_values = [];
                    $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                    
                    foreach ($dias as $dia) {
                        $hora_abertura = ($dia == 'Sábado') ? '08:00' : '09:00';
                        $hora_fechamento = ($dia == 'Sábado') ? '17:00' : '18:00';
                        $horarios_values[] = "({$salao_id}, '{$dia}', '{$hora_abertura}', '{$hora_fechamento}', 1)";
                    }
                    
                    $horarios_sql .= implode(', ', $horarios_values);
                    $conn->exec($horarios_sql);
                    
                    echo "<p style='color: blue;'>📅 Horários de funcionamento padrão criados</p>";
                    
                } else {
                    $erros[] = "Erro ao criar salão para {$parceiro['nome']}";
                }
                
            } catch (Exception $e) {
                $erros[] = "Erro ao processar {$parceiro['nome']}: " . $e->getMessage();
            }
        }
        
        if (empty($erros)) {
            $conn->commit();
            echo "<h3 style='color: green;'>✅ Processo concluído com sucesso!</h3>";
            echo "<p>Total de salões criados: {$saloes_criados}</p>";
        } else {
            $conn->rollback();
            echo "<h3 style='color: red;'>❌ Erros encontrados:</h3>";
            foreach ($erros as $erro) {
                echo "<p style='color: red;'>• {$erro}</p>";
            }
        }
    }
    
    echo "<h3>Verificação final:</h3>";
    
    // Verificar novamente parceiros sem salão
    $stmt = $conn->query("SELECT COUNT(*) as total 
                         FROM usuarios u 
                         LEFT JOIN saloes s ON u.id = s.id_dono 
                         WHERE u.tipo = 'Parceiro' AND s.id IS NULL");
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] == 0) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Todos os parceiros agora têm salão cadastrado!</p>";
        echo "<p>A mensagem de 'Primeiro Acesso' não aparecerá mais.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Ainda existem {$resultado['total']} parceiros sem salão.</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='parceiro/salao.php'>🔗 Ir para página do salão</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>