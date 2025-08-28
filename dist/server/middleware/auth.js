const jwt = require('jsonwebtoken');
const Database = require('../config/database');

/**
 * Middleware de autenticação JWT
 * Verifica se o token é válido e adiciona os dados do usuário à requisição
 */
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({
        success: false,
        message: 'Token de acesso requerido'
      });
    }

    // Verificar o token JWT
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Buscar dados atualizados do usuário no banco
    const db = Database.getInstance();
    const [users] = await db.execute(
      'SELECT id, nome, email, tipo, ativo, salao_id FROM usuarios WHERE id = ? AND ativo = 1',
      [decoded.userId]
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Usuário não encontrado ou inativo'
      });
    }

    // Adicionar dados do usuário à requisição
    req.user = {
      id: users[0].id,
      nome: users[0].nome,
      email: users[0].email,
      tipo: users[0].tipo,
      salao_id: users[0].salao_id
    };

    next();
  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        success: false,
        message: 'Token inválido'
      });
    }
    
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Token expirado'
      });
    }

    console.error('Erro na autenticação:', error);
    return res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
};

/**
 * Middleware para verificar se o usuário é admin
 */
const requireAdmin = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: 'Autenticação requerida'
    });
  }

  if (req.user.tipo !== 'admin') {
    return res.status(403).json({
      success: false,
      message: 'Acesso negado. Apenas administradores podem acessar este recurso.'
    });
  }

  next();
};

/**
 * Middleware para verificar se o usuário é parceiro
 */
const requireParceiro = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: 'Autenticação requerida'
    });
  }

  if (req.user.tipo !== 'parceiro') {
    return res.status(403).json({
      success: false,
      message: 'Acesso negado. Apenas parceiros podem acessar este recurso.'
    });
  }

  next();
};

/**
 * Middleware para verificar se o usuário é cliente
 */
const requireCliente = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: 'Autenticação requerida'
    });
  }

  if (req.user.tipo !== 'cliente') {
    return res.status(403).json({
      success: false,
      message: 'Acesso negado. Apenas clientes podem acessar este recurso.'
    });
  }

  next();
};

/**
 * Middleware para verificar se o usuário tem acesso ao salão
 * Usado para garantir que parceiros só acessem dados do próprio salão
 */
const requireSalaoAccess = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: 'Autenticação requerida'
    });
  }

  // Admin tem acesso a todos os salões
  if (req.user.tipo === 'admin') {
    return next();
  }

  // Parceiros só podem acessar dados do próprio salão
  if (req.user.tipo === 'parceiro') {
    const salaoId = req.params.salao_id || req.body.salao_id || req.query.salao_id;
    
    if (!salaoId) {
      return res.status(400).json({
        success: false,
        message: 'ID do salão é obrigatório'
      });
    }

    if (parseInt(salaoId) !== req.user.salao_id) {
      return res.status(403).json({
        success: false,
        message: 'Acesso negado. Você só pode acessar dados do seu salão.'
      });
    }
  }

  next();
};

/**
 * Middleware opcional de autenticação
 * Adiciona dados do usuário se o token for válido, mas não bloqueia se não houver token
 */
const optionalAuth = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
      return next();
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    const db = Database.getInstance();
    const [users] = await db.execute(
      'SELECT id, nome, email, tipo, ativo, salao_id FROM usuarios WHERE id = ? AND ativo = 1',
      [decoded.userId]
    );

    if (users.length > 0) {
      req.user = {
        id: users[0].id,
        nome: users[0].nome,
        email: users[0].email,
        tipo: users[0].tipo,
        salao_id: users[0].salao_id
      };
    }

    next();
  } catch (error) {
    // Em caso de erro, continua sem autenticação
    next();
  }
};

module.exports = {
  authenticateToken,
  requireAdmin,
  requireParceiro,
  requireCliente,
  requireSalaoAccess,
  optionalAuth
};