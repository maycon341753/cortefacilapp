const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const { optionalAuth } = require('../middleware/auth');

const db = Database.getInstance();

// GET /api/servicos - Listar serviços (especialidades)
router.get('/', optionalAuth, async (req, res) => {
    try {
        const { ativo } = req.query;
        
        let query = `
            SELECT 
                e.id,
                e.nome,
                e.descricao,
                50.00 as preco,
                30 as duracao,
                'Geral' as categoria,
                e.ativo,
                e.data_cadastro as created_at,
                e.data_cadastro as updated_at,
                'Todos os Salões' as salao_nome
            FROM especialidades e
        `;
        
        const params = [];
        const conditions = [];
        
        if (ativo !== undefined) {
            conditions.push('e.ativo = ?');
            params.push(ativo === 'true' ? 1 : 0);
        }
        
        if (conditions.length > 0) {
            query += ' WHERE ' + conditions.join(' AND ');
        }
        
        query += ' ORDER BY e.nome';
        
        const servicos = await db.query(query, params);
        
        res.json({
            success: true,
            data: servicos,
            total: servicos.length
        });
        
    } catch (error) {
        console.error('Erro ao listar serviços:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/servicos/:id - Buscar serviço específico
router.get('/:id', optionalAuth, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do serviço inválido'
            });
        }
        
        const servico = await db.queryOne(`
            SELECT 
                e.id,
                e.nome,
                e.descricao,
                50.00 as preco,
                30 as duracao,
                'Geral' as categoria,
                e.ativo,
                e.data_cadastro as created_at,
                e.data_cadastro as updated_at,
                'Todos os Salões' as salao_nome,
                0 as salao_id
            FROM especialidades e
            WHERE e.id = ?
        `, [id]);
        
        if (!servico) {
            return res.status(404).json({
                error: 'Serviço não encontrado'
            });
        }
        
        res.json({
            success: true,
            data: servico
        });
        
    } catch (error) {
        console.error('Erro ao buscar serviço:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/servicos/salao/:id - Buscar serviços por salão
router.get('/salao/:id', optionalAuth, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do salão inválido'
            });
        }
        
        // Por enquanto, retornamos todas as especialidades como serviços disponíveis
        // Em uma implementação futura, poderia haver uma tabela de relacionamento salao_servicos
        const servicos = await db.query(`
            SELECT 
                e.id,
                e.nome,
                e.descricao,
                50.00 as preco,
                30 as duracao,
                'Geral' as categoria,
                e.ativo,
                e.data_cadastro as created_at,
                e.data_cadastro as updated_at,
                ? as salao_id
            FROM especialidades e
            WHERE e.ativo = 1
            ORDER BY e.nome
        `, [id]);
        
        res.json({
            success: true,
            data: servicos,
            total: servicos.length
        });
        
    } catch (error) {
        console.error('Erro ao buscar serviços por salão:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;