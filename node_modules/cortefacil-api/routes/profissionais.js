const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const { authenticateToken, requireParceiro, requireSalaoAccess, optionalAuth } = require('../middleware/auth');
const { validateProfissionalCreate, validateProfissionalUpdate, validateId } = require('../middleware/validation');

const db = Database.getInstance();

// Middleware de autenticação (simplificado por enquanto)
const requireAuth = (req, res, next) => {
    // TODO: Implementar verificação de JWT ou sessão
    // Por enquanto, apenas continua
    next();
};

// GET /api/profissionais - Buscar profissionais por salão
router.get('/', optionalAuth, async (req, res) => {
    try {
        const { salao_id, salao } = req.query;
        const salaoParam = salao_id || salao;
        
        if (!salaoParam) {
            return res.status(400).json({
                error: 'ID do salão é obrigatório'
            });
        }
        
        const idSalao = parseInt(salaoParam);
        
        if (idSalao <= 0) {
            return res.status(400).json({
                error: 'ID do salão inválido'
            });
        }
        
        // Buscar profissionais do salão
        const profissionais = await db.query(`
            SELECT 
                p.id,
                p.nome,
                p.especialidade,
                p.telefone,
                p.email,
                p.ativo,
                p.created_at,
                s.nome as salao_nome
            FROM profissionais p
            INNER JOIN saloes s ON p.id_salao = s.id
            WHERE p.id_salao = ?
            ORDER BY p.nome ASC
        `, [idSalao]);
        
        // Filtrar apenas profissionais ativos
        const profissionaisAtivos = profissionais.filter(prof => prof.ativo === 1);
        
        res.json({
            success: true,
            data: profissionaisAtivos,
            total: profissionaisAtivos.length,
            salao_id: idSalao
        });
        
    } catch (error) {
        console.error('Erro ao buscar profissionais:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/profissionais/:id - Buscar profissional específico
router.get('/:id', validateId, authenticateToken, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do profissional inválido'
            });
        }
        
        const profissional = await db.queryOne(`
            SELECT 
                p.id,
                p.nome,
                p.especialidade,
                p.telefone,
                p.email,
                p.status,
                p.foto,
                p.descricao,
                p.created_at,
                s.nome as salao_nome,
                s.id as salao_id
            FROM profissionais p
            INNER JOIN saloes s ON p.id_salao = s.id
            WHERE p.id = ? AND p.status = 'ativo'
        `, [id]);
        
        if (!profissional) {
            return res.status(404).json({
                error: 'Profissional não encontrado'
            });
        }
        
        res.json({
            success: true,
            data: profissional
        });
        
    } catch (error) {
        console.error('Erro ao buscar profissional:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// POST /api/profissionais - Criar novo profissional
router.post('/', validateProfissionalCreate, authenticateToken, requireParceiro, requireSalaoAccess, async (req, res) => {
    try {
        const { nome, especialidade, telefone, email, id_salao, descricao } = req.body;
        
        // Validações
        if (!nome || !especialidade || !id_salao) {
            return res.status(400).json({
                error: 'Campos obrigatórios: nome, especialidade, id_salao'
            });
        }
        
        // Verificar se o salão existe
        const salao = await db.queryOne('SELECT id FROM saloes WHERE id = ?', [id_salao]);
        if (!salao) {
            return res.status(404).json({
                error: 'Salão não encontrado'
            });
        }
        
        // Inserir profissional
        const result = await db.query(`
            INSERT INTO profissionais (nome, especialidade, telefone, email, id_salao, descricao, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'ativo', NOW())
        `, [nome, especialidade, telefone, email, id_salao, descricao]);
        
        const novoProfissional = await db.queryOne(`
            SELECT 
                p.id,
                p.nome,
                p.especialidade,
                p.telefone,
                p.email,
                p.status,
                p.descricao,
                p.created_at,
                s.nome as salao_nome
            FROM profissionais p
            INNER JOIN saloes s ON p.id_salao = s.id
            WHERE p.id = ?
        `, [result.insertId]);
        
        res.status(201).json({
            success: true,
            message: 'Profissional criado com sucesso',
            data: novoProfissional
        });
        
    } catch (error) {
        console.error('Erro ao criar profissional:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// PUT /api/profissionais/:id - Atualizar profissional
router.put('/:id', validateId, validateProfissionalUpdate, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        const { nome, especialidade, telefone, email, descricao, status } = req.body;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do profissional inválido'
            });
        }
        
        // Verificar se profissional existe
        const profissionalExiste = await db.queryOne('SELECT id FROM profissionais WHERE id = ?', [id]);
        if (!profissionalExiste) {
            return res.status(404).json({
                error: 'Profissional não encontrado'
            });
        }
        
        // Atualizar profissional
        await db.query(`
            UPDATE profissionais 
            SET nome = ?, especialidade = ?, telefone = ?, email = ?, descricao = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        `, [nome, especialidade, telefone, email, descricao, status || 'ativo', id]);
        
        // Buscar profissional atualizado
        const profissionalAtualizado = await db.queryOne(`
            SELECT 
                p.id,
                p.nome,
                p.especialidade,
                p.telefone,
                p.email,
                p.status,
                p.descricao,
                p.created_at,
                s.nome as salao_nome
            FROM profissionais p
            INNER JOIN saloes s ON p.id_salao = s.id
            WHERE p.id = ?
        `, [id]);
        
        res.json({
            success: true,
            message: 'Profissional atualizado com sucesso',
            data: profissionalAtualizado
        });
        
    } catch (error) {
        console.error('Erro ao atualizar profissional:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// DELETE /api/profissionais/:id - Desativar profissional
router.delete('/:id', validateId, authenticateToken, requireParceiro, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                error: 'ID do profissional inválido'
            });
        }
        
        // Verificar se profissional existe
        const profissional = await db.queryOne('SELECT id, nome FROM profissionais WHERE id = ?', [id]);
        if (!profissional) {
            return res.status(404).json({
                error: 'Profissional não encontrado'
            });
        }
        
        // Desativar ao invés de deletar
        await db.query('UPDATE profissionais SET status = "inativo", updated_at = NOW() WHERE id = ?', [id]);
        
        res.json({
            success: true,
            message: `Profissional ${profissional.nome} desativado com sucesso`
        });
        
    } catch (error) {
        console.error('Erro ao desativar profissional:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

// GET /api/profissionais/salao/:id - Buscar profissionais por salão
router.get('/salao/:id', optionalAuth, async (req, res) => {
    try {
        const { id } = req.params;
        
        if (!id || isNaN(id)) {
            return res.status(400).json({
                success: false,
                error: 'ID do salão inválido'
            });
        }
        
        const idSalao = parseInt(id);
        
        // Buscar profissionais do salão
        const profissionais = await db.query(`
            SELECT 
                p.id,
                p.nome,
                p.especialidade,
                p.telefone,
                p.email,
                p.ativo,
                p.created_at,
                s.nome as salao_nome
            FROM profissionais p
            INNER JOIN saloes s ON p.id_salao = s.id
            WHERE p.id_salao = ? AND p.ativo = 1
            ORDER BY p.nome ASC
        `, [idSalao]);
        
        res.json({
            success: true,
            data: profissionais,
            total: profissionais.length,
            salao_id: idSalao
        });
        
    } catch (error) {
        console.error('Erro ao buscar profissionais por salão:', error);
        res.status(500).json({
            success: false,
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;