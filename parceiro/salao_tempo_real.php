<?php
// Script para adicionar informações em tempo real na página do salão
require_once '../config/database.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';
require_once '../models/agendamento.php';

// Verificar se é uma requisição AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_stats') {
    header('Content-Type: application/json');
    
    try {
        $database = new Database();
        $db = $database->connect();
        
        $id_salao = (int)($_GET['id_salao'] ?? 0);
        
        if (!$id_salao) {
            throw new Exception('ID do salão não fornecido');
        }
        
        // Buscar estatísticas do salão
        $stats = [];
        
        // Total de profissionais
        $query = "SELECT COUNT(*) as total FROM profissionais WHERE id_salao = :id_salao";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['total_profissionais'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Profissionais ativos
        $query = "SELECT COUNT(*) as total FROM profissionais WHERE id_salao = :id_salao AND ativo = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['profissionais_ativos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Agendamentos hoje
        $query = "SELECT COUNT(*) as total FROM agendamentos a 
                  INNER JOIN profissionais p ON a.id_profissional = p.id 
                  WHERE p.id_salao = :id_salao AND DATE(a.data) = CURDATE()";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['agendamentos_hoje'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Agendamentos esta semana
        $query = "SELECT COUNT(*) as total FROM agendamentos a 
                  INNER JOIN profissionais p ON a.id_profissional = p.id 
                  WHERE p.id_salao = :id_salao AND YEARWEEK(a.data) = YEARWEEK(NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['agendamentos_semana'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Agendamentos este mês
        $query = "SELECT COUNT(*) as total FROM agendamentos a 
                  INNER JOIN profissionais p ON a.id_profissional = p.id 
                  WHERE p.id_salao = :id_salao AND MONTH(a.data) = MONTH(NOW()) AND YEAR(a.data) = YEAR(NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['agendamentos_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Últimos profissionais cadastrados
        $query = "SELECT nome, especialidade, DATE_FORMAT(data_cadastro, '%d/%m/%Y') as data_cadastro 
                  FROM profissionais 
                  WHERE id_salao = :id_salao 
                  ORDER BY data_cadastro DESC 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['ultimos_profissionais'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Próximos agendamentos
        $query = "SELECT a.data, a.hora, p.nome as profissional, 
                         CASE 
                             WHEN u.nome IS NOT NULL THEN u.nome
                             ELSE 'Cliente não identificado'
                         END as cliente
                  FROM agendamentos a 
                  INNER JOIN profissionais p ON a.id_profissional = p.id 
                  LEFT JOIN usuarios u ON a.id_cliente = u.id
                  WHERE p.id_salao = :id_salao AND a.data >= CURDATE() 
                  ORDER BY a.data ASC, a.hora ASC 
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_salao', $id_salao);
        $stmt->execute();
        $stats['proximos_agendamentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

// Se não for AJAX, retornar erro
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Requisição inválida']);
?>