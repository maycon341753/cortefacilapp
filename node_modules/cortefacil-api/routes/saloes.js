const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const { authenticateToken, requireParceiro, optionalAuth } = require('../middleware/auth');
const { validateId } = require('../middleware/validation');

const db = Database.getInstance();

// GET /api/saloes - Listar salões
router.get('/', optionalAuth, async (req, res) => {
    try {
        const { nome, cidade, ativo } = req.query;
        
        let query = `
            SELECT 
                s.id,
                s.nome,
                s.endereco,
                s.telefone,
                s.descricao,
                s.ativo,
                s.created_at,
                s.updated_at,
                u.nome as proprietario_nome,
                u.email as proprietario_email
            FROM saloes s
            INNER JOIN usuarios u ON s.id_dono = u.id
        `;
        
        const params = [];
        const conditions = [];
        
        if (nome) {
            conditions.push('s.nome LIKE ?');
            params.push(`%${nome}%`);
        }
        
        if (cidade) {
            conditions.push('s.endereco LIKE ?');
            params.push(`%${cidade}%`);
        }
        
        if (ativo !== undefined) {
            conditions.push('s.ativo = ?');
            params.push(ativo === 'true' ? 1 : 0);
        }
        
        if (conditions.length > 0) {
            query += ' WHERE ' + conditions.join(' AND ');
        }
        
        query += ' ORDER BY s.nome';
        
        const saloes = await db.query(query, params);
        
        res.json({
            success: true,
            data: saloes,
            total: saloes.length
        });
        
    } catch (error) {
        console.error('Erro ao listar salões:', error);
        res.status(500).json({
            success: false,
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/saloes/:id - Buscar salão específico
router.get('/:id', validateId, optionalAuth, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                success: false,
                error: 'ID do salão inválido'
            });
        }
        
        const salao = await db.queryOne(`
            SELECT 
                s.id,
                s.nome,
                s.endereco,
                s.telefone,
                s.descricao,
                s.ativo,
                s.created_at,
                s.updated_at,
                u.nome as proprietario_nome,
                u.email as proprietario_email
            FROM saloes s
            INNER JOIN usuarios u ON s.id_dono = u.id
            WHERE s.id = ?
        `, [id]);
        
        if (!salao) {
            return res.status(404).json({
                success: false,
                error: 'Salão não encontrado'
            });
        }
        
        res.json({
            success: true,
            data: salao
        });
        
    } catch (error) {
        console.error('Erro ao buscar salão:', error);
        res.status(500).json({
            success: false,
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// PUT /api/saloes/:id - Atualizar salão
router.put('/:id', validateId, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        const { nome, endereco, telefone, descricao, ativo } = req.body;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                success: false,
                error: 'ID do salão inválido'
            });
        }
        
        // Verificar se o salão existe e pertence ao usuário
        const salao = await db.queryOne('SELECT id, id_dono FROM saloes WHERE id = ?', [id]);
        if (!salao) {
            return res.status(404).json({
                success: false,
                error: 'Salão não encontrado'
            });
        }
        
        if (salao.id_dono !== req.user.id && req.user.tipo !== 'admin') {
            return res.status(403).json({
                success: false,
                error: 'Acesso negado'
            });
        }
        
        // Atualizar salão
        const updateFields = [];
        const updateParams = [];
        
        if (nome !== undefined) {
            updateFields.push('nome = ?');
            updateParams.push(nome);
        }
        
        if (endereco !== undefined) {
            updateFields.push('endereco = ?');
            updateParams.push(endereco);
        }
        
        if (telefone !== undefined) {
            updateFields.push('telefone = ?');
            updateParams.push(telefone);
        }
        
        if (descricao !== undefined) {
            updateFields.push('descricao = ?');
            updateParams.push(descricao);
        }
        
        if (ativo !== undefined) {
            updateFields.push('ativo = ?');
            updateParams.push(ativo ? 1 : 0);
        }
        
        if (updateFields.length === 0) {
            return res.status(400).json({
                success: false,
                error: 'Nenhum campo para atualizar'
            });
        }
        
        updateFields.push('updated_at = NOW()');
        updateParams.push(id);
        
        const updateQuery = `UPDATE saloes SET ${updateFields.join(', ')} WHERE id = ?`;
        await db.query(updateQuery, updateParams);
        
        // Buscar salão atualizado
        const salaoAtualizado = await db.queryOne(`
            SELECT 
                s.id,
                s.nome,
                s.endereco,
                s.telefone,
                s.descricao,
                s.ativo,
                s.created_at,
                s.updated_at,
                u.nome as proprietario_nome,
                u.email as proprietario_email
            FROM saloes s
            INNER JOIN usuarios u ON s.id_dono = u.id
            WHERE s.id = ?
        `, [id]);
        
        res.json({
            success: true,
            message: 'Salão atualizado com sucesso',
            data: salaoAtualizado
        });
        
    } catch (error) {
        console.error('Erro ao atualizar salão:', error);
        res.status(500).json({
            success: false,
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;