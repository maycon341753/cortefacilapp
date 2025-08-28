const express = require('express');
const Database = require('../config/database');
const moment = require('moment-timezone');
const { authenticateToken, optionalAuth } = require('../middleware/auth');
const { validateHorariosQuery } = require('../middleware/validation');

const router = express.Router();

const db = Database.getInstance();

// GET /api/horarios - Buscar horários disponíveis
router.get('/', validateHorariosQuery, optionalAuth, async (req, res) => {
    try {
        const { salao_id, profissional_id, data } = req.query;
        
        // Validar parâmetros obrigatórios
        if (!salao_id || !profissional_id || !data) {
            return res.status(400).json({
                error: 'Parâmetros obrigatórios: salao_id, profissional_id, data'
            });
        }
        
        // Validar formato da data
        if (!/^\d{4}-\d{2}-\d{2}$/.test(data)) {
            return res.status(400).json({
                error: 'Formato de data inválido. Use YYYY-MM-DD'
            });
        }
        
        // Calcular dia da semana
        const dataObj = new Date(data + 'T00:00:00');
        let diaSemana = dataObj.getDay(); // 0=domingo, 1=segunda, etc.
        const diaSemanaDb = diaSemana === 0 ? 7 : diaSemana; // Converter para formato do banco
        
        // Buscar horários de funcionamento do salão
        const funcionamento = await db.queryOne(`
            SELECT hora_abertura, hora_fechamento 
            FROM horarios_funcionamento 
            WHERE id_salao = ? 
            AND dia_semana = ? 
            AND ativo = 1
        `, [salao_id, diaSemanaDb]);
        
        if (!funcionamento) {
            return res.status(404).json({
                error: 'Salão fechado neste dia da semana'
            });
        }
        
        // Buscar horários cadastrados para o profissional
        let horariosCadastrados = await db.query(`
            SELECT hora_inicio, hora_fim 
            FROM horarios 
            WHERE profissional_id = ? 
            AND salao_id = ? 
            AND ativo = 1
            ORDER BY hora_inicio
        `, [profissional_id, salao_id]);
        
        // Se não há horários cadastrados, usar horários padrão
        if (horariosCadastrados.length === 0) {
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
        
        // Buscar horários já agendados
        const horariosOcupados = await db.query(`
            SELECT hora 
            FROM agendamentos 
            WHERE id_profissional = ? 
            AND id_salao = ? 
            AND data = ? 
            AND status IN ('confirmado', 'pendente')
        `, [profissional_id, salao_id, data]);
        
        // Buscar bloqueios temporários (últimos 10 minutos)
        const bloqueiosTemporarios = await db.query(`
            SELECT hora_inicio, hora_fim 
            FROM bloqueios_temporarios 
            WHERE id_profissional = ? 
            AND id_salao = ? 
            AND data_bloqueio = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        `, [profissional_id, salao_id, data]);
        
        // Processar todos os horários com status de disponibilidade
        const todosHorarios = [];
        const horariosOcupadosSet = new Set(horariosOcupados.map(h => h.hora));
        
        for (const horario of horariosCadastrados) {
            let disponivel = true;
            let motivo = null;
            
            // Verificar se está dentro do funcionamento
            if (horario.hora_inicio < funcionamento.hora_abertura || 
                horario.hora_fim > funcionamento.hora_fechamento) {
                disponivel = false;
                motivo = 'Fora do horário de funcionamento';
            }
            
            // Verificar se está ocupado
            if (disponivel && horariosOcupadosSet.has(horario.hora_inicio)) {
                disponivel = false;
                motivo = 'Horário já agendado';
            }
            
            // Verificar bloqueios temporários
            if (disponivel) {
                for (const bloqueio of bloqueiosTemporarios) {
                    if (horario.hora_inicio >= bloqueio.hora_inicio && 
                        horario.hora_inicio < bloqueio.hora_fim) {
                        disponivel = false;
                        motivo = 'Temporariamente bloqueado';
                        break;
                    }
                }
            }
            
            // Verificar se é horário passado (apenas para hoje)
            const hoje = moment().format('YYYY-MM-DD');
            if (disponivel && data === hoje) {
                const horaAtual = moment().format('HH:mm:ss');
                if (horario.hora_inicio <= horaAtual) {
                    disponivel = false;
                    motivo = 'Horário já passou';
                }
            }
            
            // Adicionar todos os horários à lista
            todosHorarios.push({
                hora_inicio: horario.hora_inicio,
                hora_fim: horario.hora_fim,
                disponivel: disponivel,
                motivo: motivo
            });
        }
        
        // Separar horários disponíveis para compatibilidade
        const horariosDisponiveis = todosHorarios.filter(h => h.disponivel);
        
        res.json({
            success: true,
            data: {
                salao_id: parseInt(salao_id),
                profissional_id: parseInt(profissional_id),
                data: data,
                funcionamento: funcionamento,
                todos_horarios: todosHorarios,
                horarios_disponiveis: horariosDisponiveis,
                total_horarios: todosHorarios.length,
                total_disponiveis: horariosDisponiveis.length
            }
        });
        
    } catch (error) {
        console.error('Erro ao buscar horários:', error);
        res.status(500).json({
            error: 'Erro interno do servidor',
            message: error.message
        });
    }
});

module.exports = router;