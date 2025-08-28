const express = require('express');
const Database = require('../config/database');
const moment = require('moment-timezone');
const { authenticateToken, requireParceiro, requireSalaoAccess } = require('../middleware/auth');
const { validateBloqueioCreate, validateBloqueioQuery, validateId } = require('../middleware/validation');

const router = express.Router();

const db = Database.getInstance();

// Middleware de autenticação
const requireAuth = (req, res, next) => {
    // TODO: Implementar verificação de JWT ou sessão
    next();
};

// POST /api/bloqueios - Bloquear temporariamente um horário
router.post('/', validateBloqueioCreate, authenticateToken, requireParceiro, requireSalaoAccess, async (req, res) => {
    try {
        const { profissional_id, data, hora, salao_id } = req.body;
        
        // Validar parâmetros obrigatórios
        if (!profissional_id) {
            return res.status(400).json({
                error: 'ID do profissional é obrigatório'
            });
        }
        
        if (!data) {
            return res.status(400).json({
                error: 'Data é obrigatória'
            });
        }
        
        if (!hora) {
            return res.status(400).json({
                error: 'Hora é obrigatória'
            });
        }
        
        if (!salao_id) {
            return res.status(400).json({
                error: 'ID do salão é obrigatório'
            });
        }
        
        // Validar formato da data
        if (!/^\d{4}-\d{2}-\d{2}$/.test(data)) {
            return res.status(400).json({
                error: 'Formato de data inválido. Use YYYY-MM-DD'
            });
        }
        
        // Validar formato da hora
        if (!/^\d{2}:\d{2}(:\d{2})?$/.test(hora)) {
            return res.status(400).json({
                error: 'Formato de hora inválido. Use HH:MM ou HH:MM:SS'
            });
        }
        
        // Normalizar hora (adicionar segundos se não tiver)
        const horaFormatada = hora.length === 5 ? hora + ':00' : hora;
        
        // Verificar se o profissional existe
        const profissional = await db.queryOne(`
            SELECT id, nome FROM profissionais 
            WHERE id = ? AND id_salao = ? AND status = 'ativo'
        `, [profissional_id, salao_id]);
        
        if (!profissional) {
            return res.status(404).json({
                error: 'Profissional não encontrado ou inativo'
            });
        }
        
        // Verificar se o horário já está agendado
        const agendamentoExiste = await db.queryOne(`
            SELECT id FROM agendamentos 
            WHERE id_profissional = ? 
            AND id_salao = ? 
            AND data = ? 
            AND hora = ? 
            AND status IN ('confirmado', 'pendente')
        `, [profissional_id, salao_id, data, horaFormatada]);
        
        if (agendamentoExiste) {
            return res.status(409).json({
                error: 'Horário já está agendado'
            });
        }
        
        // Verificar se já existe um bloqueio ativo para este horário
        const bloqueioExiste = await db.queryOne(`
            SELECT id FROM bloqueios_temporarios 
            WHERE id_profissional = ? 
            AND id_salao = ? 
            AND data_bloqueio = ? 
            AND hora_inicio = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        `, [profissional_id, salao_id, data, horaFormatada]);
        
        if (bloqueioExiste) {
            return res.status(409).json({
                error: 'Horário já está bloqueado temporariamente'
            });
        }
        
        // Calcular hora fim (assumindo 30 minutos de duração padrão)
        const horaInicio = new Date(`2000-01-01T${horaFormatada}`);
        const horaFim = new Date(horaInicio.getTime() + 30 * 60000); // +30 minutos
        const horaFimFormatada = horaFim.toTimeString().substring(0, 8);
        
        // Criar bloqueio temporário
        const result = await db.query(`
            INSERT INTO bloqueios_temporarios 
            (id_profissional, id_salao, data_bloqueio, hora_inicio, hora_fim, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        `, [profissional_id, salao_id, data, horaFormatada, horaFimFormatada]);
        
        // Buscar o bloqueio criado
        const novoBloqueio = await db.queryOne(`
            SELECT 
                bt.id,
                bt.id_profissional,
                bt.id_salao,
                bt.data_bloqueio,
                bt.hora_inicio,
                bt.hora_fim,
                bt.created_at,
                p.nome as profissional_nome,
                s.nome as salao_nome
            FROM bloqueios_temporarios bt
            INNER JOIN profissionais p ON bt.id_profissional = p.id
            INNER JOIN saloes s ON bt.id_salao = s.id
            WHERE bt.id = ?
        `, [result.insertId]);
        
        res.status(201).json({
            success: true,
            message: 'Horário bloqueado temporariamente com sucesso',
            data: novoBloqueio,
            expires_in: '10 minutos'
        });
        
    } catch (error) {
        console.error('Erro ao bloquear horário:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/bloqueios - Listar bloqueios ativos
router.get('/', validateBloqueioQuery, authenticateToken, requireSalaoAccess, async (req, res) => {
    try {
        const { profissional_id, salao_id, data } = req.query;
        
        let query = `
            SELECT 
                bt.id,
                bt.id_profissional,
                bt.id_salao,
                bt.data_bloqueio,
                bt.hora_inicio,
                bt.hora_fim,
                bt.created_at,
                p.nome as profissional_nome,
                s.nome as salao_nome,
                TIMESTAMPDIFF(MINUTE, bt.created_at, NOW()) as minutos_ativo
            FROM bloqueios_temporarios bt
            INNER JOIN profissionais p ON bt.id_profissional = p.id
            INNER JOIN saloes s ON bt.id_salao = s.id
            WHERE bt.created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        `;
        
        const params = [];
        
        if (profissional_id) {
            query += ' AND bt.id_profissional = ?';
            params.push(profissional_id);
        }
        
        if (salao_id) {
            query += ' AND bt.id_salao = ?';
            params.push(salao_id);
        }
        
        if (data) {
            query += ' AND bt.data_bloqueio = ?';
            params.push(data);
        }
        
        query += ' ORDER BY bt.created_at DESC';
        
        const bloqueios = await db.query(query, params);
        
        res.json({
            success: true,
            data: bloqueios,
            total: bloqueios.length
        });
        
    } catch (error) {
        console.error('Erro ao listar bloqueios:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// DELETE /api/bloqueios/:id - Remover bloqueio específico
router.delete('/:id', validateId, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do bloqueio inválido'
            });
        }
        
        // Verificar se bloqueio existe
        const bloqueio = await db.queryOne(`
            SELECT 
                bt.id,
                bt.data_bloqueio,
                bt.hora_inicio,
                p.nome as profissional_nome
            FROM bloqueios_temporarios bt
            INNER JOIN profissionais p ON bt.id_profissional = p.id
            WHERE bt.id = ?
        `, [id]);
        
        if (!bloqueio) {
            return res.status(404).json({
                error: 'Bloqueio não encontrado'
            });
        }
        
        // Remover bloqueio
        await db.query('DELETE FROM bloqueios_temporarios WHERE id = ?', [id]);
        
        res.json({
            success: true,
            message: `Bloqueio removido com sucesso para ${bloqueio.profissional_nome} em ${bloqueio.data_bloqueio} às ${bloqueio.hora_inicio}`
        });
        
    } catch (error) {
        console.error('Erro ao remover bloqueio:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// DELETE /api/bloqueios/cleanup - Limpar bloqueios expirados
router.delete('/cleanup/expired', async (req, res) => {
    try {
        // Remover bloqueios com mais de 10 minutos
        const result = await db.query(`
            DELETE FROM bloqueios_temporarios 
            WHERE created_at <= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        `);
        
        res.json({
            success: true,
            message: `${result.affectedRows} bloqueios expirados removidos`,
            removed_count: result.affectedRows
        });
        
    } catch (error) {
        console.error('Erro ao limpar bloqueios expirados:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;