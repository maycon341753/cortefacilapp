const Joi = require('joi');

/**
 * Middleware genérico de validação usando Joi
 * @param {Object} schema - Schema de validação Joi
 * @param {string} property - Propriedade da requisição a ser validada ('body', 'query', 'params')
 */
const validate = (schema, property = 'body') => {
  return (req, res, next) => {
    const { error, value } = schema.validate(req[property], {
      abortEarly: false, // Retorna todos os erros, não apenas o primeiro
      stripUnknown: true // Remove campos não definidos no schema
    });

    if (error) {
      const errors = error.details.map(detail => ({
        field: detail.path.join('.'),
        message: detail.message
      }));

      return res.status(400).json({
        success: false,
        message: 'Dados inválidos',
        errors
      });
    }

    // Substitui os dados originais pelos dados validados e limpos
    req[property] = value;
    next();
  };
};

// Schemas de validação comuns
const schemas = {
  // Validação para horários
  horarios: {
    query: Joi.object({
      salao_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do salão deve ser um número',
          'number.integer': 'ID do salão deve ser um número inteiro',
          'number.positive': 'ID do salão deve ser positivo',
          'any.required': 'ID do salão é obrigatório'
        }),
      profissional_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do profissional deve ser um número',
          'number.integer': 'ID do profissional deve ser um número inteiro',
          'number.positive': 'ID do profissional deve ser positivo',
          'any.required': 'ID do profissional é obrigatório'
        }),
      data: Joi.date().iso().required()
        .messages({
          'date.base': 'Data deve ser uma data válida',
          'date.format': 'Data deve estar no formato ISO (YYYY-MM-DD)',
          'any.required': 'Data é obrigatória'
        })
    })
  },

  // Validação para profissionais
  profissionais: {
    create: Joi.object({
      nome: Joi.string().min(2).max(100).required()
        .messages({
          'string.base': 'Nome deve ser um texto',
          'string.min': 'Nome deve ter pelo menos 2 caracteres',
          'string.max': 'Nome deve ter no máximo 100 caracteres',
          'any.required': 'Nome é obrigatório'
        }),
      email: Joi.string().email().max(150).required()
        .messages({
          'string.base': 'Email deve ser um texto',
          'string.email': 'Email deve ter um formato válido',
          'string.max': 'Email deve ter no máximo 150 caracteres',
          'any.required': 'Email é obrigatório'
        }),
      telefone: Joi.string().pattern(/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/).required()
        .messages({
          'string.base': 'Telefone deve ser um texto',
          'string.pattern.base': 'Telefone deve ter um formato válido (ex: (11) 99999-9999)',
          'any.required': 'Telefone é obrigatório'
        }),
      especialidades: Joi.string().max(500).optional()
        .messages({
          'string.base': 'Especialidades deve ser um texto',
          'string.max': 'Especialidades deve ter no máximo 500 caracteres'
        }),
      salao_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do salão deve ser um número',
          'number.integer': 'ID do salão deve ser um número inteiro',
          'number.positive': 'ID do salão deve ser positivo',
          'any.required': 'ID do salão é obrigatório'
        })
    }),
    update: Joi.object({
      nome: Joi.string().min(2).max(100).optional(),
      email: Joi.string().email().max(150).optional(),
      telefone: Joi.string().pattern(/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/).optional(),
      especialidades: Joi.string().max(500).optional(),
      ativo: Joi.boolean().optional()
    })
  },

  // Validação para serviços
  servicos: {
    create: Joi.object({
      nome: Joi.string().min(2).max(100).required()
        .messages({
          'string.base': 'Nome deve ser um texto',
          'string.min': 'Nome deve ter pelo menos 2 caracteres',
          'string.max': 'Nome deve ter no máximo 100 caracteres',
          'any.required': 'Nome é obrigatório'
        }),
      descricao: Joi.string().max(500).optional()
        .messages({
          'string.base': 'Descrição deve ser um texto',
          'string.max': 'Descrição deve ter no máximo 500 caracteres'
        }),
      preco: Joi.number().precision(2).positive().required()
        .messages({
          'number.base': 'Preço deve ser um número',
          'number.positive': 'Preço deve ser positivo',
          'any.required': 'Preço é obrigatório'
        }),
      duracao: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'Duração deve ser um número',
          'number.integer': 'Duração deve ser um número inteiro',
          'number.positive': 'Duração deve ser positiva',
          'any.required': 'Duração é obrigatória'
        }),
      categoria: Joi.string().max(50).optional()
        .messages({
          'string.base': 'Categoria deve ser um texto',
          'string.max': 'Categoria deve ter no máximo 50 caracteres'
        }),
      salao_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do salão deve ser um número',
          'number.integer': 'ID do salão deve ser um número inteiro',
          'number.positive': 'ID do salão deve ser positivo',
          'any.required': 'ID do salão é obrigatório'
        })
    }),
    update: Joi.object({
      nome: Joi.string().min(2).max(100).optional(),
      descricao: Joi.string().max(500).optional(),
      preco: Joi.number().precision(2).positive().optional(),
      duracao: Joi.number().integer().positive().optional(),
      categoria: Joi.string().max(50).optional(),
      ativo: Joi.boolean().optional()
    }),
    query: Joi.object({
      salao_id: Joi.number().integer().positive().optional(),
      categoria: Joi.string().max(50).optional(),
      ativo: Joi.boolean().optional()
    })
  },

  // Validação para bloqueios
  bloqueios: {
    create: Joi.object({
      profissional_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do profissional deve ser um número',
          'number.integer': 'ID do profissional deve ser um número inteiro',
          'number.positive': 'ID do profissional deve ser positivo',
          'any.required': 'ID do profissional é obrigatório'
        }),
      data: Joi.date().iso().required()
        .messages({
          'date.base': 'Data deve ser uma data válida',
          'date.format': 'Data deve estar no formato ISO (YYYY-MM-DD)',
          'any.required': 'Data é obrigatória'
        }),
      hora: Joi.string().pattern(/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/).required()
        .messages({
          'string.base': 'Hora deve ser um texto',
          'string.pattern.base': 'Hora deve ter um formato válido (HH:MM)',
          'any.required': 'Hora é obrigatória'
        }),
      salao_id: Joi.number().integer().positive().required()
        .messages({
          'number.base': 'ID do salão deve ser um número',
          'number.integer': 'ID do salão deve ser um número inteiro',
          'number.positive': 'ID do salão deve ser positivo',
          'any.required': 'ID do salão é obrigatório'
        }),
      motivo: Joi.string().max(200).optional()
        .messages({
          'string.base': 'Motivo deve ser um texto',
          'string.max': 'Motivo deve ter no máximo 200 caracteres'
        })
    }),
    query: Joi.object({
      profissional_id: Joi.number().integer().positive().optional(),
      data: Joi.date().iso().optional(),
      salao_id: Joi.number().integer().positive().required()
    })
  },

  // Validação para parâmetros de ID
  id: Joi.object({
    id: Joi.number().integer().positive().required()
      .messages({
        'number.base': 'ID deve ser um número',
        'number.integer': 'ID deve ser um número inteiro',
        'number.positive': 'ID deve ser positivo',
        'any.required': 'ID é obrigatório'
      })
  })
};

// Middlewares específicos para cada rota
const validateHorariosQuery = validate(schemas.horarios.query, 'query');
const validateProfissionalCreate = validate(schemas.profissionais.create);
const validateProfissionalUpdate = validate(schemas.profissionais.update);
const validateServicoCreate = validate(schemas.servicos.create);
const validateServicoUpdate = validate(schemas.servicos.update);
const validateServicoQuery = validate(schemas.servicos.query, 'query');
const validateBloqueioCreate = validate(schemas.bloqueios.create);
const validateBloqueioQuery = validate(schemas.bloqueios.query, 'query');
const validateId = validate(schemas.id, 'params');

module.exports = {
  validate,
  schemas,
  validateHorariosQuery,
  validateProfissionalCreate,
  validateProfissionalUpdate,
  validateServicoCreate,
  validateServicoUpdate,
  validateServicoQuery,
  validateBloqueioCreate,
  validateBloqueioQuery,
  validateId
};