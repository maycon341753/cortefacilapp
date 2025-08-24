const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const Database = require('../config/database');
const { authenticateToken } = require('../middleware/auth');
const { validate } = require('../middleware/validation');
const Joi = require('joi');

const router = express.Router();
const db = Database.getInstance();

// Schemas de validação para autenticação
const authSchemas = {
  login: Joi.object({
    email: Joi.string().email().required()
      .messages({
        'string.email': 'Email deve ter um formato válido',
        'any.required': 'Email é obrigatório'
      }),
    password: Joi.string().min(6).required()
      .messages({
        'string.min': 'Senha deve ter pelo menos 6 caracteres',
        'any.required': 'Senha é obrigatória'
      })
  }),
  
  register: Joi.object({
    nome: Joi.string().min(2).max(100).required()
      .messages({
        'string.min': 'Nome deve ter pelo menos 2 caracteres',
        'string.max': 'Nome deve ter no máximo 100 caracteres',
        'any.required': 'Nome é obrigatório'
      }),
    email: Joi.string().email().max(150).required()
      .messages({
        'string.email': 'Email deve ter um formato válido',
        'string.max': 'Email deve ter no máximo 150 caracteres',
        'any.required': 'Email é obrigatório'
      }),
    password: Joi.string().min(6).max(100).required()
      .messages({
        'string.min': 'Senha deve ter pelo menos 6 caracteres',
        'string.max': 'Senha deve ter no máximo 100 caracteres',
        'any.required': 'Senha é obrigatória'
      }),
    telefone: Joi.string().pattern(/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/).required()
      .messages({
        'string.pattern.base': 'Telefone deve ter um formato válido (ex: (11) 99999-9999)',
        'any.required': 'Telefone é obrigatório'
      }),
    tipo: Joi.string().valid('cliente', 'parceiro').default('cliente')
      .messages({
        'any.only': 'Tipo deve ser "cliente" ou "parceiro"'
      }),
    // Campos específicos para parceiros
    nome_salao: Joi.when('tipo', {
      is: 'parceiro',
      then: Joi.string().min(2).max(100).required()
        .messages({
          'string.min': 'Nome do salão deve ter pelo menos 2 caracteres',
          'string.max': 'Nome do salão deve ter no máximo 100 caracteres',
          'any.required': 'Nome do salão é obrigatório para parceiros'
        }),
      otherwise: Joi.optional()
    }),
    endereco: Joi.when('tipo', {
      is: 'parceiro',
      then: Joi.string().min(10).max(200).required()
        .messages({
          'string.min': 'Endereço deve ter pelo menos 10 caracteres',
          'string.max': 'Endereço deve ter no máximo 200 caracteres',
          'any.required': 'Endereço é obrigatório para parceiros'
        }),
      otherwise: Joi.optional()
    })
  }),
  
  changePassword: Joi.object({
    currentPassword: Joi.string().required()
      .messages({
        'any.required': 'Senha atual é obrigatória'
      }),
    newPassword: Joi.string().min(6).max(100).required()
      .messages({
        'string.min': 'Nova senha deve ter pelo menos 6 caracteres',
        'string.max': 'Nova senha deve ter no máximo 100 caracteres',
        'any.required': 'Nova senha é obrigatória'
      })
  })
};

// POST /api/auth/login - Fazer login
router.post('/login', validate(authSchemas.login), async (req, res) => {
  try {
    const { email, password } = req.body;

    // Buscar usuário por email
    const [users] = await db.execute(
      'SELECT id, nome, email, senha, tipo, ativo, salao_id FROM usuarios WHERE email = ?',
      [email]
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Email ou senha incorretos'
      });
    }

    const user = users[0];

    // Verificar se o usuário está ativo
    if (!user.ativo) {
      return res.status(401).json({
        success: false,
        message: 'Conta desativada. Entre em contato com o suporte.'
      });
    }

    // Verificar senha
    const isPasswordValid = await bcrypt.compare(password, user.senha);
    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        message: 'Email ou senha incorretos'
      });
    }

    // Gerar token JWT
    const token = jwt.sign(
      { 
        userId: user.id,
        email: user.email,
        tipo: user.tipo,
        salao_id: user.salao_id
      },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
    );

    // Atualizar último login
    await db.execute(
      'UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?',
      [user.id]
    );

    res.json({
      success: true,
      message: 'Login realizado com sucesso',
      data: {
        token,
        user: {
          id: user.id,
          nome: user.nome,
          email: user.email,
          tipo: user.tipo,
          salao_id: user.salao_id
        }
      }
    });

  } catch (error) {
    console.error('Erro no login:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// POST /api/auth/register - Registrar novo usuário
router.post('/register', validate(authSchemas.register), async (req, res) => {
  try {
    const { nome, email, password, telefone, tipo, nome_salao, endereco } = req.body;

    // Verificar se o email já existe
    const [existingUsers] = await db.execute(
      'SELECT id FROM usuarios WHERE email = ?',
      [email]
    );

    if (existingUsers.length > 0) {
      return res.status(400).json({
        success: false,
        message: 'Este email já está cadastrado'
      });
    }

    // Hash da senha
    const saltRounds = parseInt(process.env.BCRYPT_ROUNDS) || 10;
    const hashedPassword = await bcrypt.hash(password, saltRounds);

    // Iniciar transação
    await db.execute('START TRANSACTION');

    try {
      let salao_id = null;

      // Se for parceiro, criar o salão primeiro
      if (tipo === 'parceiro') {
        const [salaoResult] = await db.execute(
          'INSERT INTO saloes (nome, endereco, telefone, email, ativo, data_cadastro) VALUES (?, ?, ?, ?, 1, NOW())',
          [nome_salao, endereco, telefone, email]
        );
        salao_id = salaoResult.insertId;
      }

      // Criar o usuário
      const [userResult] = await db.execute(
        'INSERT INTO usuarios (nome, email, senha, telefone, tipo, salao_id, ativo, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())',
        [nome, email, hashedPassword, telefone, tipo, salao_id]
      );

      const userId = userResult.insertId;

      // Commit da transação
      await db.execute('COMMIT');

      // Gerar token JWT
      const token = jwt.sign(
        { 
          userId,
          email,
          tipo,
          salao_id
        },
        process.env.JWT_SECRET,
        { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
      );

      res.status(201).json({
        success: true,
        message: 'Usuário cadastrado com sucesso',
        data: {
          token,
          user: {
            id: userId,
            nome,
            email,
            tipo,
            salao_id
          }
        }
      });

    } catch (error) {
      // Rollback em caso de erro
      await db.execute('ROLLBACK');
      throw error;
    }

  } catch (error) {
    console.error('Erro no registro:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// GET /api/auth/me - Obter dados do usuário logado
router.get('/me', authenticateToken, async (req, res) => {
  try {
    const userId = req.user.id;

    // Buscar dados completos do usuário
    const [users] = await db.execute(
      `SELECT u.id, u.nome, u.email, u.telefone, u.tipo, u.ativo, u.data_cadastro, u.ultimo_login,
              s.id as salao_id, s.nome as salao_nome, s.endereco as salao_endereco
       FROM usuarios u
       LEFT JOIN saloes s ON u.salao_id = s.id
       WHERE u.id = ?`,
      [userId]
    );

    if (users.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado'
      });
    }

    const user = users[0];

    res.json({
      success: true,
      data: {
        id: user.id,
        nome: user.nome,
        email: user.email,
        telefone: user.telefone,
        tipo: user.tipo,
        ativo: user.ativo,
        data_cadastro: user.data_cadastro,
        ultimo_login: user.ultimo_login,
        salao: user.salao_id ? {
          id: user.salao_id,
          nome: user.salao_nome,
          endereco: user.salao_endereco
        } : null
      }
    });

  } catch (error) {
    console.error('Erro ao buscar dados do usuário:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// PUT /api/auth/change-password - Alterar senha
router.put('/change-password', authenticateToken, validate(authSchemas.changePassword), async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;
    const userId = req.user.id;

    // Buscar senha atual do usuário
    const [users] = await db.execute(
      'SELECT senha FROM usuarios WHERE id = ?',
      [userId]
    );

    if (users.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Usuário não encontrado'
      });
    }

    // Verificar senha atual
    const isCurrentPasswordValid = await bcrypt.compare(currentPassword, users[0].senha);
    if (!isCurrentPasswordValid) {
      return res.status(400).json({
        success: false,
        message: 'Senha atual incorreta'
      });
    }

    // Hash da nova senha
    const saltRounds = parseInt(process.env.BCRYPT_ROUNDS) || 10;
    const hashedNewPassword = await bcrypt.hash(newPassword, saltRounds);

    // Atualizar senha
    await db.execute(
      'UPDATE usuarios SET senha = ?, data_atualizacao = NOW() WHERE id = ?',
      [hashedNewPassword, userId]
    );

    res.json({
      success: true,
      message: 'Senha alterada com sucesso'
    });

  } catch (error) {
    console.error('Erro ao alterar senha:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// POST /api/auth/logout - Fazer logout (opcional, para invalidar token no frontend)
router.post('/logout', authenticateToken, async (req, res) => {
  try {
    // Em uma implementação mais robusta, você poderia adicionar o token a uma blacklist
    // Por enquanto, apenas retornamos sucesso
    res.json({
      success: true,
      message: 'Logout realizado com sucesso'
    });
  } catch (error) {
    console.error('Erro no logout:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// GET /api/auth/verify-token - Verificar se o token é válido
router.get('/verify-token', authenticateToken, (req, res) => {
  res.json({
    success: true,
    message: 'Token válido',
    data: {
      user: req.user
    }
  });
});

module.exports = router;