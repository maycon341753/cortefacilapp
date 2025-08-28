const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const db = Database.getInstance();
const { authenticateToken, requireCliente, requireParceiro, requireSalaoAccess, optionalAuth } = require('../middleware/auth');
const Joi = require('joi');
const moment = require('moment-timezone');

// Validation schemas
const agendamentoCreateSchema = Joi.object({
  id_cliente: Joi.number().integer().positive().required(),
  id_salao: Joi.number().integer().positive().required(),
  id_profissional: Joi.number().integer().positive().required(),
  data: Joi.date().iso().required(),
  hora: Joi.string().pattern(/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/).required(),
  observacoes: Joi.string().max(500).allow('', null)
});

// Rota para confirmar pagamento manualmente (para testes ou integração bancária)
router.post('/:id/confirmar-pagamento', authenticateToken, async (req, res) => {
  try {
    const agendamentoId = req.params.id;
    
    console.log(`🏦 Confirmando pagamento para agendamento ${agendamentoId}`);
    
    // Verificar se o agendamento existe
    const agendamento = await db.queryOne(
      'SELECT * FROM agendamentos WHERE id = ?',
      [agendamentoId]
    );
    
    if (!agendamento) {
      return res.status(404).json({
        success: false,
        error: 'Agendamento não encontrado'
      });
    }
    
    // Verificar se já não foi pago
    if (agendamento.status_pagamento === 'pago') {
      return res.json({
        success: true,
        message: 'Pagamento já confirmado anteriormente',
        data: {
          agendamento_id: agendamentoId,
          status: 'confirmado'
        }
      });
    }
    
    // Atualizar status do pagamento
    await db.query(
      'UPDATE agendamentos SET status_pagamento = ?, updated_at = NOW() WHERE id = ?',
      ['pago', agendamentoId]
    );
    
    await db.query(
      'UPDATE pagamentos SET status = ?, updated_at = NOW() WHERE agendamento_id = ?',
      ['confirmado', agendamentoId]
    );
    
    console.log(`✅ Pagamento confirmado com sucesso para agendamento ${agendamentoId}`);
    
    res.json({
      success: true,
      message: 'Pagamento confirmado com sucesso',
      data: {
        agendamento_id: agendamentoId,
        status: 'confirmado'
      }
    });
    
  } catch (error) {
    console.error('Erro ao confirmar pagamento:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

const agendamentoQuerySchema = Joi.object({
  data_inicio: Joi.date().iso().optional(),
  data_fim: Joi.date().iso().optional(),
  status: Joi.string().valid('pendente', 'confirmado', 'cancelado', 'concluido').optional(),
  cliente_busca: Joi.string().max(100).optional(),
  page: Joi.number().integer().min(1).default(1),
  limit: Joi.number().integer().min(1).max(100).default(20)
});

const validateAgendamentoCreate = (req, res, next) => {
  const { error } = agendamentoCreateSchema.validate(req.body);
  if (error) {
    return res.status(400).json({
      success: false,
      error: 'Dados inválidos',
      details: error.details[0].message
    });
  }
  next();
};

const validateAgendamentoQuery = (req, res, next) => {
  const { error } = agendamentoQuerySchema.validate(req.query);
  if (error) {
    return res.status(400).json({
      success: false,
      error: 'Parâmetros inválidos',
      details: error.details[0].message
    });
  }
  next();
};

/**
 * Verifica disponibilidade de horário
 */
const verificarDisponibilidade = async (id_profissional, data, hora) => {
  try {
    const rows = await db.query(
      `SELECT COUNT(*) as count FROM agendamentos 
       WHERE id_profissional = ? AND data = ? AND hora = ? 
       AND status IN ('pendente', 'confirmado')`,
      [id_profissional, data, hora]
    );
    
    return rows[0].count === 0;
  } catch (error) {
    console.error('Erro ao verificar disponibilidade:', error);
    return false;
  }
};

/**
 * Gera horários disponíveis para um profissional em uma data
 */
const gerarHorariosDisponiveis = async (id_profissional, data, id_salao) => {
  try {
    // Verificar se o profissional existe e está ativo
    const profRows = await db.query(
      'SELECT id FROM profissionais WHERE id = ? AND id_salao = ? AND ativo = 1',
      [id_profissional, id_salao]
    );
    
    if (profRows.length === 0) {
      return [];
    }
    
    // Determinar o dia da semana (0=domingo, 1=segunda, etc.)
    const diaSemana = moment(data).day();
    const diaSemanaDb = diaSemana === 0 ? 7 : diaSemana; // Converter para formato do banco
    
    // Buscar horários de funcionamento do salão (com fallback para horários padrão)
    console.log(`🔍 Buscando horários para salão ${id_salao}, dia da semana ${diaSemanaDb}`);
    let funcionamento;
    
    try {
      const funcRows = await db.query(
        `SELECT hora_abertura, hora_fechamento 
         FROM horarios_funcionamento 
         WHERE id_salao = ? AND dia_semana = ? AND ativo = 1`,
        [id_salao, diaSemanaDb]
      );
      
      console.log(`📅 Horários de funcionamento encontrados:`, funcRows);
      
      if (funcRows.length === 0) {
        console.log(`⚠️ Nenhum horário de funcionamento encontrado, usando horários padrão`);
        funcionamento = { hora_abertura: '08:00:00', hora_fechamento: '18:00:00' };
      } else {
        funcionamento = funcRows[0];
      }
    } catch (error) {
      console.log(`⚠️ Tabela horarios_funcionamento não existe, usando horários padrão`);
      funcionamento = { hora_abertura: '08:00:00', hora_fechamento: '18:00:00' };
    }
    
    // Buscar horários cadastrados para o profissional (com fallback para horários padrão)
    let horariosCadastrados = [];
    
    try {
      const horarioRows = await db.query(
        `SELECT hora_inicio, hora_fim 
         FROM horarios 
         WHERE profissional_id = ? AND salao_id = ? AND ativo = 1 
         ORDER BY hora_inicio`,
        [id_profissional, id_salao]
      );
      horariosCadastrados = horarioRows;
    } catch (error) {
      console.log(`⚠️ Tabela horarios não existe, usando horários padrão`);
    }
    
    // Se não há horários cadastrados, usar horários padrão
    if (horariosCadastrados.length === 0) {
      console.log(`📋 Usando horários padrão para profissional ${id_profissional}`);
      horariosCadastrados = [
        { hora_inicio: '08:00:00', hora_fim: '08:30:00' },
        { hora_inicio: '08:30:00', hora_fim: '09:00:00' },
        { hora_inicio: '09:00:00', hora_fim: '09:30:00' },
        { hora_inicio: '09:30:00', hora_fim: '10:00:00' },
        { hora_inicio: '10:00:00', hora_fim: '10:30:00' },
        { hora_inicio: '10:30:00', hora_fim: '11:00:00' },
        { hora_inicio: '11:00:00', hora_fim: '11:30:00' },
        { hora_inicio: '11:30:00', hora_fim: '12:00:00' },
        { hora_inicio: '13:00:00', hora_fim: '13:30:00' },
        { hora_inicio: '13:30:00', hora_fim: '14:00:00' },
        { hora_inicio: '14:00:00', hora_fim: '14:30:00' },
        { hora_inicio: '14:30:00', hora_fim: '15:00:00' },
        { hora_inicio: '15:00:00', hora_fim: '15:30:00' },
        { hora_inicio: '15:30:00', hora_fim: '16:00:00' },
        { hora_inicio: '16:00:00', hora_fim: '16:30:00' },
        { hora_inicio: '16:30:00', hora_fim: '17:00:00' },
        { hora_inicio: '17:00:00', hora_fim: '17:30:00' },
        { hora_inicio: '17:30:00', hora_fim: '18:00:00' }
      ];
    }
    
    // Buscar horários ocupados
    const ocupadosRows = await db.query(
      `SELECT hora FROM agendamentos 
       WHERE id_profissional = ? AND id_salao = ? AND data = ? 
       AND status IN ('confirmado', 'pendente')`,
      [id_profissional, id_salao, data]
    );
    
    const horariosOcupados = ocupadosRows.map(row => row.hora);
    
    // Buscar bloqueios temporários (últimos 10 minutos)
    let bloqueiosRows = [];
    try {
      bloqueiosRows = await db.query(
        `SELECT hora_inicio, hora_fim FROM bloqueios_temporarios 
         WHERE id_profissional = ? AND id_salao = ? AND data_bloqueio = ? 
         AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)`,
        [id_profissional, id_salao, data]
      );
    } catch (error) {
      console.log('Tabela bloqueios_temporarios não existe, usando array vazio');
      bloqueiosRows = [];
    }
    
    // Processar todos os horários com status de disponibilidade
    const todosHorarios = [];
    
    for (const horario of horariosCadastrados) {
      let disponivel = true;
      let motivo = '';
      
      // Verificar se está dentro do funcionamento do salão
      if (horario.hora_inicio < funcionamento.hora_abertura || 
          horario.hora_fim > funcionamento.hora_fechamento) {
        disponivel = false;
        motivo = 'Fora do horário de funcionamento';
      }
      
      // Verificar se está ocupado
      if (disponivel && horariosOcupados.includes(horario.hora_inicio)) {
        disponivel = false;
        motivo = 'Horário já agendado';
      }
      
      // Verificar bloqueios temporários
      if (disponivel) {
        for (const bloqueio of bloqueiosRows) {
          if (horario.hora_inicio === bloqueio.hora_inicio && 
              horario.hora_fim === bloqueio.hora_fim) {
            disponivel = false;
            motivo = 'Temporariamente bloqueado';
            break;
          }
        }
      }
      
      // Remover horários passados se for hoje
      if (disponivel && data === moment().format('YYYY-MM-DD')) {
        const horaAtual = moment().format('HH:mm');
        if (horario.hora_inicio.substring(0, 5) <= horaAtual) {
          disponivel = false;
          motivo = 'Horário já passou';
        }
      }
      
      todosHorarios.push({
        hora_inicio: horario.hora_inicio.substring(0, 5),
        hora_fim: horario.hora_fim.substring(0, 5),
        display: `${horario.hora_inicio.substring(0, 5)} - ${horario.hora_fim.substring(0, 5)}`,
        disponivel: disponivel,
        motivo: motivo
      });
    }
    
    return todosHorarios;
    
  } catch (error) {
    console.error('Erro ao gerar horários disponíveis:', error);
    return [];
  }
};

// GET /api/agendamentos/horarios-disponiveis
router.get('/horarios-disponiveis', optionalAuth, async (req, res) => {
  try {
    const { salao_id, profissional_id, data } = req.query;
    
    if (!salao_id || !profissional_id || !data) {
      return res.status(400).json({
        success: false,
        error: 'Parâmetros obrigatórios: salao_id, profissional_id, data'
      });
    }
    
    // Validar formato da data
    if (!moment(data, 'YYYY-MM-DD', true).isValid()) {
      return res.status(400).json({
        success: false,
        error: 'Formato de data inválido. Use YYYY-MM-DD'
      });
    }
    
    let todosHorarios;
    try {
      todosHorarios = await gerarHorariosDisponiveis(
        parseInt(profissional_id), 
        data,
        parseInt(salao_id)
      );
    } catch (funcError) {
      console.error('Erro na função gerarHorariosDisponiveis:', funcError);
      return res.status(500).json({
        success: false,
        error: 'Erro ao gerar horários disponíveis'
      });
    }
    
    // Separar horários disponíveis e indisponíveis
    const horariosDisponiveis = todosHorarios.filter(h => h.disponivel);
    
    res.json({
      success: true,
      data: horariosDisponiveis.map(h => h.display), // Manter compatibilidade
      todos_horarios: todosHorarios, // Nova estrutura completa
      total: todosHorarios.length,
      total_disponiveis: horariosDisponiveis.length,
      date: data,
      profissional_id: parseInt(profissional_id),
      salao_id: parseInt(salao_id)
    });
    
  } catch (error) {
    console.error('Erro ao buscar horários disponíveis:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// POST /api/agendamentos
router.post('/', authenticateToken, requireCliente, validateAgendamentoCreate, async (req, res) => {
  try {
    const { id_cliente, id_salao, id_profissional, data, hora, observacoes } = req.body;
    
    // Verificar se o usuário pode agendar para este cliente
    if (req.user.tipo !== 'admin' && req.user.id !== id_cliente) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado a agendar para este cliente'
      });
    }
    
    // Verificar disponibilidade
    const disponivel = await verificarDisponibilidade(id_profissional, data, hora);
    if (!disponivel) {
      return res.status(400).json({
        success: false,
        error: 'Horário não disponível'
      });
    }
    
    // Criar agendamento
    const result = await db.query(
      `INSERT INTO agendamentos (id_cliente, id_salao, id_profissional, data, hora, observacoes, status, created_at) 
       VALUES (?, ?, ?, ?, ?, ?, 'pendente', NOW())`,
      [id_cliente, id_salao, id_profissional, data, hora, observacoes || null]
    );
    
    res.status(201).json({
      success: true,
      data: {
        id: result.insertId,
        id_cliente,
        id_salao,
        id_profissional,
        data,
        hora,
        observacoes,
        status: 'pendente'
      }
    });
    
  } catch (error) {
    console.error('Erro ao criar agendamento:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// GET /api/agendamentos/cliente/:id
router.get('/cliente/:id', authenticateToken, async (req, res) => {
  try {
    const clienteId = parseInt(req.params.id);
    
    // Verificar se o usuário pode ver agendamentos deste cliente
    if (req.user.tipo !== 'admin' && req.user.id !== clienteId) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado'
      });
    }
    
    const rows = await db.query(
      `SELECT a.*, s.nome as nome_salao, p.nome as nome_profissional, p.especialidade 
       FROM agendamentos a 
       INNER JOIN saloes s ON a.id_salao = s.id 
       INNER JOIN profissionais p ON a.id_profissional = p.id 
       WHERE a.id_cliente = ? 
       ORDER BY a.data DESC, a.hora DESC`,
      [clienteId]
    );
    
    res.json({
      success: true,
      data: rows
    });
    
  } catch (error) {
    console.error('Erro ao buscar agendamentos do cliente:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// GET /api/agendamentos/salao/:id
router.get('/salao/:id', authenticateToken, requireParceiro, requireSalaoAccess, validateAgendamentoQuery, async (req, res) => {
  try {
    const salaoId = parseInt(req.params.id);
    const { data_inicio, data_fim, status, cliente_busca, page, limit } = req.query;
    const offset = (page - 1) * limit;
    
    let whereClause = 'WHERE a.id_salao = ?';
    let params = [salaoId];
    
    if (data_inicio && data_fim) {
      whereClause += ' AND a.data BETWEEN ? AND ?';
      params.push(data_inicio, data_fim);
    }
    
    if (status) {
      whereClause += ' AND a.status = ?';
      params.push(status);
    }
    
    if (cliente_busca) {
      whereClause += ' AND u.nome LIKE ?';
      params.push(`%${cliente_busca}%`);
    }
    
    // Buscar agendamentos
    const rows = await db.query(
      `SELECT a.*, u.nome as nome_cliente, u.telefone as telefone_cliente, 
              p.nome as nome_profissional, p.especialidade 
       FROM agendamentos a 
       INNER JOIN usuarios u ON a.id_cliente = u.id 
       INNER JOIN profissionais p ON a.id_profissional = p.id 
       ${whereClause} 
       ORDER BY a.data ASC, a.hora ASC 
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    
    // Contar total
    const countRows = await db.query(
      `SELECT COUNT(*) as total FROM agendamentos a 
       INNER JOIN usuarios u ON a.id_cliente = u.id 
       ${whereClause}`,
      params
    );
    
    res.json({
      success: true,
      data: rows,
      pagination: {
        page,
        limit,
        total: countRows[0].total,
        pages: Math.ceil(countRows[0].total / limit)
      }
    });
    
  } catch (error) {
    console.error('Erro ao buscar agendamentos do salão:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// GET /api/agendamentos/:id
router.get('/:id', authenticateToken, async (req, res) => {
  try {
    const agendamentoId = parseInt(req.params.id);
    
    const rows = await db.query(
      `SELECT a.*, u.nome as nome_cliente, u.telefone as telefone_cliente, 
              s.nome as nome_salao, p.nome as nome_profissional, p.especialidade 
       FROM agendamentos a 
       INNER JOIN usuarios u ON a.id_cliente = u.id 
       INNER JOIN saloes s ON a.id_salao = s.id 
       INNER JOIN profissionais p ON a.id_profissional = p.id 
       WHERE a.id = ?`,
      [agendamentoId]
    );
    
    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        error: 'Agendamento não encontrado'
      });
    }
    
    const agendamento = rows[0];
    
    // Verificar permissões
    if (req.user.tipo !== 'admin' && 
        req.user.id !== agendamento.id_cliente && 
        (req.user.tipo !== 'parceiro' || req.user.salao_id !== agendamento.id_salao)) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado'
      });
    }
    
    res.json({
      success: true,
      data: agendamento
    });
    
  } catch (error) {
    console.error('Erro ao buscar agendamento:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// PUT /api/agendamentos/:id/status
router.put('/:id/status', authenticateToken, async (req, res) => {
  try {
    const agendamentoId = parseInt(req.params.id);
    const { status } = req.body;
    
    if (!['pendente', 'confirmado', 'cancelado', 'concluido'].includes(status)) {
      return res.status(400).json({
        success: false,
        error: 'Status inválido'
      });
    }
    
    // Buscar agendamento para verificar permissões
    const rows = await db.query(
      'SELECT id_cliente, id_salao FROM agendamentos WHERE id = ?',
      [agendamentoId]
    );
    
    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        error: 'Agendamento não encontrado'
      });
    }
    
    const agendamento = rows[0];
    
    // Verificar permissões
    if (req.user.tipo !== 'admin' && 
        req.user.id !== agendamento.id_cliente && 
        (req.user.tipo !== 'parceiro' || req.user.salao_id !== agendamento.id_salao)) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado'
      });
    }
    
    // Atualizar status
    await db.query(
      'UPDATE agendamentos SET status = ?, updated_at = NOW() WHERE id = ?',
      [status, agendamentoId]
    );
    
    res.json({
      success: true,
      message: 'Status atualizado com sucesso'
    });
    
  } catch (error) {
    console.error('Erro ao atualizar status:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// DELETE /api/agendamentos/:id (cancelar)
router.delete('/:id', authenticateToken, async (req, res) => {
  try {
    const agendamentoId = parseInt(req.params.id);
    
    // Buscar agendamento para verificar permissões
    const rows = await db.query(
      'SELECT id_cliente, id_salao, status FROM agendamentos WHERE id = ?',
      [agendamentoId]
    );
    
    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        error: 'Agendamento não encontrado'
      });
    }
    
    const agendamento = rows[0];
    
    // Verificar permissões
    if (req.user.tipo !== 'admin' && 
        req.user.id !== agendamento.id_cliente && 
        (req.user.tipo !== 'parceiro' || req.user.salao_id !== agendamento.id_salao)) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado'
      });
    }
    
    // Verificar se pode ser cancelado
    if (agendamento.status === 'cancelado') {
      return res.status(400).json({
        success: false,
        error: 'Agendamento já está cancelado'
      });
    }
    
    // Cancelar agendamento
    await db.query(
      'UPDATE agendamentos SET status = "cancelado", updated_at = NOW() WHERE id = ?',
      [agendamentoId]
    );
    
    res.json({
      success: true,
      message: 'Agendamento cancelado com sucesso'
    });
    
  } catch (error) {
    console.error('Erro ao cancelar agendamento:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

// GET /api/agendamentos/:id/status-pagamento
router.get('/:id/status-pagamento', authenticateToken, async (req, res) => {
  try {
    const agendamentoId = parseInt(req.params.id);
    
    // Buscar agendamento para verificar permissões
    const agendamentoRows = await db.query(
      'SELECT id_cliente, id_salao, status_pagamento FROM agendamentos WHERE id = ?',
      [agendamentoId]
    );
    
    if (agendamentoRows.length === 0) {
      return res.status(404).json({
        success: false,
        error: 'Agendamento não encontrado'
      });
    }
    
    const agendamento = agendamentoRows[0];
    
    // Verificar permissões
    if (req.user.tipo !== 'admin' && 
        req.user.id !== agendamento.id_cliente && 
        (req.user.tipo !== 'parceiro' || req.user.salao_id !== agendamento.id_salao)) {
      return res.status(403).json({
        success: false,
        error: 'Não autorizado'
      });
    }
    
    // Verificar se existe um pagamento para este agendamento
    const pagamentoRows = await db.query(
      'SELECT * FROM pagamentos WHERE agendamento_id = ? ORDER BY created_at DESC LIMIT 1',
      [agendamentoId]
    );
    
    let statusPagamento = agendamento.status_pagamento || 'pendente';
    
    if (pagamentoRows.length > 0) {
      const pagamento = pagamentoRows[0];
      statusPagamento = pagamento.status;
    } else {
      // Se não existe pagamento, criar um registro pendente
      const valorTaxa = 1.29; // Taxa fixa de agendamento
      
      await db.query(
        `INSERT INTO pagamentos (agendamento_id, status, valor, metodo_pagamento, dados_pagamento) 
         VALUES (?, 'pendente', ?, 'pix', JSON_OBJECT('tipo', 'taxa_agendamento'))`,
        [agendamentoId, valorTaxa]
      );
      
      statusPagamento = 'pendente';
    }
    
    // Verificação real do status do pagamento
    // O status só será alterado quando houver confirmação externa (webhook, API bancária, etc.)
    // Não há mais simulação automática - o pagamento permanece pendente até confirmação real
    
    console.log(`📊 Status atual do pagamento para agendamento ${agendamentoId}: ${statusPagamento}`);
    
    res.json({
      success: true,
      data: {
        agendamento_id: agendamentoId,
        status: statusPagamento
      }
    });
    
  } catch (error) {
    console.error('Erro ao verificar status do pagamento:', error);
    res.status(500).json({
      success: false,
      error: 'Erro interno do servidor'
    });
  }
});

module.exports = router;