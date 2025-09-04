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
  }),

  forgotPassword: Joi.object({
    email: Joi.string().email().required()
      .messages({
        'string.email': 'Email deve ter um formato válido',
        'any.required': 'Email é obrigatório'
      })
  }),

  resetPassword: Joi.object({
    token: Joi.string().required()
      .messages({
        'any.required': 'Token é obrigatório'
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
    const users = await db.query(
      'SELECT id, nome, email, senha, tipo_usuario FROM usuarios WHERE email = ?',
      [email]
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Email ou senha incorretos'
      });
    }

    const user = users[0];

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
        tipo: user.tipo_usuario
      },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
    );

    // Login realizado com sucesso - não é necessário atualizar ultimo_login

    res.json({
      success: true,
      message: 'Login realizado com sucesso',
      data: {
        token,
        user: {
          id: user.id,
          nome: user.nome,
          email: user.email,
          tipo: user.tipo_usuario
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
    const existingUsers = await db.query(
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
    await db.query('START TRANSACTION');

    try {
      let salao_id = null;

      // Se for parceiro, criar o salão primeiro
      if (tipo === 'parceiro') {
        const salaoResult = await db.query(
          'INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao) VALUES (?, ?, ?, ?, ?)',
          [0, nome_salao, endereco, telefone, 'Salão cadastrado automaticamente']
        );
        salao_id = salaoResult.insertId;
      }

      // Criar o usuário
      const userResult = await db.query(
        'INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?)',
        [nome, email, hashedPassword, telefone, tipo]
      );

      const userId = userResult.insertId;

      // Commit da transação
      await db.query('COMMIT');

      // Gerar token JWT
      const token = jwt.sign(
        { 
          userId,
          email,
          tipo
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
            tipo
          }
        }
      });

    } catch (error) {
      // Rollback em caso de erro
      await db.query('ROLLBACK');
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
    const users = await db.query(
      'SELECT id, nome, email, telefone, tipo_usuario, created_at, updated_at FROM usuarios WHERE id = ?',
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
        tipo: user.tipo_usuario,
        created_at: user.created_at,
        updated_at: user.updated_at
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
    const users = await db.query(
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
    await db.query(
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

// POST /api/auth/forgot-password - Solicitar redefinição de senha
router.post('/forgot-password', validate(authSchemas.forgotPassword), async (req, res) => {
  try {
    const { email } = req.body;

    // Verificar se o usuário existe
    const users = await db.query(
      'SELECT id, nome, email FROM usuarios WHERE email = ?',
      [email]
    );

    if (users.length === 0) {
      // Por segurança, sempre retornar sucesso mesmo se o email não existir
      return res.json({
        success: true,
        message: 'Se o email estiver cadastrado, você receberá as instruções para redefinir sua senha.'
      });
    }

    const user = users[0];

    // Gerar token único
    const crypto = require('crypto');
    const resetToken = crypto.randomBytes(32).toString('hex');
    const expiresAt = new Date(Date.now() + 3600000); // 1 hora

    // Salvar token no banco de dados
    await db.query(
      'INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)',
      [email, resetToken, expiresAt]
    );

    // Em um ambiente real, aqui você enviaria um email
    // Por enquanto, vamos apenas retornar o token para teste
    console.log(`Token de redefinição para ${email}: ${resetToken}`);
    console.log(`Link de redefinição: ${process.env.FRONTEND_URL || 'http://localhost:5173'}/auth/redefinir-senha?token=${resetToken}`);

    res.json({
      success: true,
      message: 'Se o email estiver cadastrado, você receberá as instruções para redefinir sua senha.',
      // Em produção, remover esta linha:
      resetToken: resetToken // Apenas para desenvolvimento
    });

  } catch (error) {
    console.error('Erro ao solicitar redefinição de senha:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// POST /api/auth/reset-password - Redefinir senha com token
router.post('/reset-password', validate(authSchemas.resetPassword), async (req, res) => {
  try {
    const { token, newPassword } = req.body;

    // Buscar token válido
    const resetRequests = await db.query(
      'SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = FALSE',
      [token]
    );

    if (resetRequests.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Token inválido ou expirado'
      });
    }

    const resetRequest = resetRequests[0];

    // Hash da nova senha
    const saltRounds = parseInt(process.env.BCRYPT_ROUNDS) || 10;
    const hashedPassword = await bcrypt.hash(newPassword, saltRounds);

    // Atualizar senha do usuário
    await db.query(
      'UPDATE usuarios SET senha = ?, updated_at = NOW() WHERE email = ?',
      [hashedPassword, resetRequest.email]
    );

    // Marcar token como usado
    await db.query(
      'UPDATE password_resets SET used = TRUE, updated_at = NOW() WHERE id = ?',
      [resetRequest.id]
    );

    res.json({
      success: true,
      message: 'Senha redefinida com sucesso'
    });

  } catch (error) {
    console.error('Erro ao redefinir senha:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

module.exports = router;