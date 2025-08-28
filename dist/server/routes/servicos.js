const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const { authenticateToken, requireParceiro, requireSalaoAccess, optionalAuth } = require('../middleware/auth');
const { validateServicoCreate, validateServicoUpdate, validateServicoQuery, validateId } = require('../middleware/validation');

const db = Database.getInstance();

// GET /api/servicos - Listar serviços
router.get('/', validateServicoQuery, optionalAuth, async (req, res) => {
    try {
        const { salao_id, ativo } = req.query;
        
        let query = `
            SELECT 
                s.id,
                s.nome,
                s.descricao,
                s.preco,
                s.duracao,
                s.categoria,
                s.ativo,
                s.created_at,
                s.updated_at,
                sal.nome as salao_nome
            FROM servicos s
            INNER JOIN saloes sal ON s.id_salao = sal.id
        `;
        
        const params = [];
        const conditions = [];
        
        if (salao_id) {
            conditions.push('s.id_salao = ?');
            params.push(salao_id);
        }
        
        if (ativo !== undefined) {
            conditions.push('s.ativo = ?');
            params.push(ativo === 'true' ? 1 : 0);
        }
        
        if (conditions.length > 0) {
            query += ' WHERE ' + conditions.join(' AND ');
        }
        
        query += ' ORDER BY s.categoria, s.nome';
        
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
router.get('/:id', validateId, optionalAuth, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do serviço inválido'
            });
        }
        
        const servico = await db.queryOne(`
            SELECT 
                s.id,
                s.nome,
                s.descricao,
                s.preco,
                s.duracao,
                s.categoria,
                s.ativo,
                s.created_at,
                s.updated_at,
                sal.nome as salao_nome,
                sal.id as salao_id
            FROM servicos s
            INNER JOIN saloes sal ON s.id_salao = sal.id
            WHERE s.id = ?
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

// POST /api/servicos - Criar novo serviço
router.post('/', validateServicoCreate, authenticateToken, requireParceiro, requireSalaoAccess, async (req, res) => {
    try {
        const { nome, descricao, preco, duracao, categoria, id_salao } = req.body;
        
        // Validações
        if (!nome || !preco || !duracao || !id_salao) {
            return res.status(400).json({
                error: 'Campos obrigatórios: nome, preco, duracao, id_salao'
            });
        }
        
        if (isNaN(preco) || parseFloat(preco) <= 0) {
            return res.status(400).json({
                error: 'Preço deve ser um número válido maior que zero'
            });
        }
        
        if (isNaN(duracao) || parseInt(duracao) <= 0) {
            return res.status(400).json({
                error: 'Duração deve ser um número válido maior que zero (em minutos)'
            });
        }
        
        // Verificar se o salão existe
        const salao = await db.queryOne('SELECT id FROM saloes WHERE id = ?', [id_salao]);
        if (!salao) {
            return res.status(404).json({
                error: 'Salão não encontrado'
            });
        }
        
        // Inserir serviço
        const result = await db.query(`
            INSERT INTO servicos (nome, descricao, preco, duracao, categoria, id_salao, ativo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        `, [nome, descricao, parseFloat(preco), parseInt(duracao), categoria, id_salao]);
        
        const novoServico = await db.queryOne(`
            SELECT 
                s.id,
                s.nome,
                s.descricao,
                s.preco,
                s.duracao,
                s.categoria,
                s.ativo,
                s.created_at,
                sal.nome as salao_nome
            FROM servicos s
            INNER JOIN saloes sal ON s.id_salao = sal.id
            WHERE s.id = ?
        `, [result.insertId]);
        
        res.status(201).json({
            success: true,
            message: 'Serviço criado com sucesso',
            data: novoServico
        });
        
    } catch (error) {
        console.error('Erro ao criar serviço:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// PUT /api/servicos/:id - Atualizar serviço
router.put('/:id', validateId, validateServicoUpdate, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        const { nome, descricao, preco, duracao, categoria, ativo } = req.body;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do serviço inválido'
            });
        }
        
        // Verificar se serviço existe
        const servicoExiste = await db.queryOne('SELECT id FROM servicos WHERE id = ?', [id]);
        if (!servicoExiste) {
            return res.status(404).json({
                error: 'Serviço não encontrado'
            });
        }
        
        // Validações
        if (preco && (isNaN(preco) || parseFloat(preco) <= 0)) {
            return res.status(400).json({
                error: 'Preço deve ser um número válido maior que zero'
            });
        }
        
        if (duracao && (isNaN(duracao) || parseInt(duracao) <= 0)) {
            return res.status(400).json({
                error: 'Duração deve ser um número válido maior que zero (em minutos)'
            });
        }
        
        // Atualizar serviço
        await db.query(`
            UPDATE servicos 
            SET nome = ?, descricao = ?, preco = ?, duracao = ?, categoria = ?, ativo = ?, updated_at = NOW()
            WHERE id = ?
        `, [
            nome, 
            descricao, 
            preco ? parseFloat(preco) : null, 
            duracao ? parseInt(duracao) : null, 
            categoria, 
            ativo !== undefined ? (ativo ? 1 : 0) : null, 
            id
        ]);
        
        // Buscar serviço atualizado
        const servicoAtualizado = await db.queryOne(`
            SELECT 
                s.id,
                s.nome,
                s.descricao,
                s.preco,
                s.duracao,
                s.categoria,
                s.ativo,
                s.created_at,
                s.updated_at,
                sal.nome as salao_nome
            FROM servicos s
            INNER JOIN saloes sal ON s.id_salao = sal.id
            WHERE s.id = ?
        `, [id]);
        
        res.json({
            success: true,
            message: 'Serviço atualizado com sucesso',
            data: servicoAtualizado
        });
        
    } catch (error) {
        console.error('Erro ao atualizar serviço:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// DELETE /api/servicos/:id - Desativar serviço
router.delete('/:id', validateId, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do serviço inválido'
            });
        }
        
        // Verificar se serviço existe
        const servico = await db.queryOne('SELECT id, nome FROM servicos WHERE id = ?', [id]);
        if (!servico) {
            return res.status(404).json({
                error: 'Serviço não encontrado'
            });
        }
        
        // Desativar ao invés de deletar
        await db.query('UPDATE servicos SET ativo = 0, updated_at = NOW() WHERE id = ?', [id]);
        
        res.json({
            success: true,
            message: `Serviço ${servico.nome} desativado com sucesso`
        });
        
    } catch (error) {
        console.error('Erro ao desativar serviço:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/servicos/categorias - Listar categorias de serviços
router.get('/meta/categorias', async (req, res) => {
    try {
        const { salao_id } = req.query;
        
        let query = 'SELECT DISTINCT categoria FROM servicos WHERE categoria IS NOT NULL AND categoria != ""';
        const params = [];
        
        if (salao_id) {
            query += ' AND id_salao = ?';
            params.push(salao_id);
        }
        
        query += ' ORDER BY categoria';
        
        const result = await db.query(query, params);
        const categorias = result.map(row => row.categoria);
        
        res.json({
            success: true,
            data: categorias,
            total: categorias.length
        });
        
    } catch (error) {
        console.error('Erro ao listar categorias:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;