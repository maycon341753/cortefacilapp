<?php
/**
 * Script para aplicar as correções no banco de dados online
 * Resolve o problema de "Primeiro Acesso" para todos os parceiros
 * 
 * INSTRUÇÕES PARA USO NO SERVIDOR ONLINE:
 * 1. Faça upload deste arquivo para o servidor
 * 2. Execute uma única vez via navegador
 * 3. Delete o arquivo após a execução por segurança
 */

require_once 'config/database.php';

// Verificação de segurança - só executa se não estiver em produção ou se tiver parâmetro especial
$executar = isset($_GET['executar']) && $_GET['executar'] === 'sim';

if (!$executar) {
    echo "<h2>⚠️ Script de Correção do Banco de Dados</h2>";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🚨 ATENÇÃO - LEIA ANTES DE EXECUTAR</h3>";
    echo "<p>Este script irá:</p>";
    echo "<ul>";
    echo "<li>✅ Corrigir a estrutura da tabela 'saloes' (usar 'id_dono' em vez de 'usuario_id')</li>";
    echo "<li>✅ Criar salões automáticos para todos os parceiros que não têm salão</li>";
    echo "<li>✅ Criar horários de funcionamento padrão para os novos salões</li>";
    echo "<li>✅ Resolver o problema da mensagem 'Primeiro Acesso'</li>";
    echo "</ul>";
    echo "<p><strong>⚠️ Execute apenas UMA vez no servidor de produção!</strong></p>";
    echo "</div>";
    echo "<p><a href='?executar=sim' style='background: #dc3545; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🚀 EXECUTAR CORREÇÕES</a></p>";
    echo "<p><small>Certifique-se de ter backup do banco de dados antes de executar.</small></p>";
    exit;
}

try {
    $conn = getConnection();
    
    echo "<h2>🔧 Executando Correções no Banco de Dados</h2>";
    echo "<div style='font-family: monospace; background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    
    $conn->beginTransaction();
    
    // ETAPA 1: Verificar estrutura atual
    echo "<h3>📋 ETAPA 1: Verificando estrutura atual</h3>";
    
    $stmt = $conn->query("SHOW COLUMNS FROM saloes LIKE 'id_dono'");
    $tem_id_dono = $stmt->rowCount() > 0;
    
    $stmt = $conn->query("SHOW COLUMNS FROM saloes LIKE 'usuario_id'");
    $tem_usuario_id = $stmt->rowCount() > 0;
    
    echo "<p>✓ Coluna 'id_dono' existe: " . ($tem_id_dono ? 'SIM' : 'NÃO') . "</p>";
    echo "<p>✓ Coluna 'usuario_id' existe: " . ($tem_usuario_id ? 'SIM' : 'NÃO') . "</p>";
    
    // ETAPA 2: Corrigir estrutura se necessário
    if ($tem_usuario_id && !$tem_id_dono) {
        echo "<h3>🔄 ETAPA 2: Corrigindo estrutura da tabela</h3>";
        
        // Renomear coluna usuario_id para id_dono
        $conn->exec("ALTER TABLE saloes CHANGE usuario_id id_dono INT(11) NOT NULL");
        echo "<p>✓ Coluna 'usuario_id' renomeada para 'id_dono'</p>";
        
        // Recriar chave estrangeira se necessário
        try {
            $conn->exec("ALTER TABLE saloes DROP FOREIGN KEY saloes_ibfk_1");
            echo "<p>✓ Chave estrangeira antiga removida</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Chave estrangeira antiga não encontrada (normal)</p>";
        }
        
        try {
            $conn->exec("ALTER TABLE saloes ADD CONSTRAINT fk_saloes_id_dono FOREIGN KEY (id_dono) REFERENCES usuarios(id)");
            echo "<p>✓ Nova chave estrangeira criada</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Erro ao criar chave estrangeira: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<h3>✅ ETAPA 2: Estrutura já está correta</h3>";
    }
    
    // ETAPA 3: Criar salões para parceiros sem salão
    echo "<h3>🏪 ETAPA 3: Criando salões automáticos</h3>";
    
    $stmt = $conn->query("SELECT u.id, u.nome, u.email 
                         FROM usuarios u 
                         LEFT JOIN saloes s ON u.id = s.id_dono 
                         WHERE u.tipo = 'Parceiro' AND s.id IS NULL");
    
    $parceiros_sem_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($parceiros_sem_salao)) {
        echo "<p>✅ Todos os parceiros já têm salão cadastrado!</p>";
    } else {
        echo "<p>📊 Encontrados " . count($parceiros_sem_salao) . " parceiros sem salão</p>";
        
        $saloes_criados = 0;
        
        foreach ($parceiros_sem_salao as $parceiro) {
            // Criar salão básico
            $nome_salao = "Salão " . $parceiro['nome'];
            $endereco = "Endereço a ser atualizado pelo parceiro";
            $telefone = "(00) 00000-0000";
            $descricao = "Salão criado automaticamente. Por favor, atualize as informações através do painel de controle.";
            
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
                echo "<p>✓ Salão criado: {$nome_salao} (ID: {$salao_id})</p>";
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
                
                if (!empty($horarios_values)) {
                    $horarios_sql .= implode(', ', $horarios_values);
                    $conn->exec($horarios_sql);
                }
            }
        }
        
        echo "<p>✅ Total de salões criados: {$saloes_criados}</p>";
    }
    
    // ETAPA 4: Verificação final
    echo "<h3>🔍 ETAPA 4: Verificação final</h3>";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'Parceiro'");
    $total_parceiros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT COUNT(DISTINCT s.id_dono) as total 
                         FROM saloes s 
                         INNER JOIN usuarios u ON s.id_dono = u.id 
                         WHERE u.tipo = 'Parceiro'");
    $parceiros_com_salao = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p>📊 Total de parceiros: {$total_parceiros}</p>";
    echo "<p>📊 Parceiros com salão: {$parceiros_com_salao}</p>";
    
    if ($total_parceiros == $parceiros_com_salao) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; color: #155724; margin: 20px 0;'>";
        echo "<h3>🎉 SUCESSO TOTAL!</h3>";
        echo "<p><strong>Todos os parceiros agora têm salão cadastrado!</strong></p>";
        echo "<p>✅ A mensagem 'Primeiro Acesso' não aparecerá mais para nenhum parceiro.</p>";
        echo "<p>✅ Todos os parceiros podem acessar diretamente suas funcionalidades.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; color: #721c24; margin: 20px 0;'>";
        echo "<h3>⚠️ ATENÇÃO!</h3>";
        echo "<p>Ainda existem parceiros sem salão. Verifique os logs acima.</p>";
        echo "</div>";
    }
    
    $conn->commit();
    
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; color: #0c5460; margin: 20px 0;'>";
    echo "<h3>🔒 IMPORTANTE - SEGURANÇA</h3>";
    echo "<p><strong>DELETE este arquivo do servidor após a execução!</strong></p>";
    echo "<p>Este script não deve ficar acessível publicamente por questões de segurança.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ ERRO!</h3>";
    echo "<p>Erro durante a execução: " . $e->getMessage() . "</p>";
    echo "<p>Todas as alterações foram revertidas.</p>";
    echo "</div>";
}
?>