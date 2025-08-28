const express = require('express');
const router = express.Router();
const mercadoPagoService = require('../services/mercadoPagoService');
const db = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

// Criar pagamento
router.post('/create', authenticateToken, async (req, res) => {
    try {
        const {
            agendamento_id,
            payment_method_id,
            token,
            payer,
            serviceName
        } = req.body;

        // Verificar se o agendamento existe e pertence ao usuário
        const [agendamento] = await db.query(
            'SELECT * FROM agendamentos WHERE id = ? AND cliente_id = ?',
            [agendamento_id, req.user.id]
        );

        if (!agendamento.length) {
            return res.status(404).json({
                success: false,
                message: 'Agendamento não encontrado'
            });
        }

        // Verificar se já não foi pago
        if (agendamento[0].status_pagamento === 'pago') {
            return res.status(400).json({
                success: false,
                message: 'Agendamento já foi pago'
            });
        }

        const paymentData = {
            agendamento_id,
            payment_method_id,
            token,
            payer,
            serviceName,
            user_id: req.user.id,
            salao_id: agendamento[0].salao_id
        };

        const payment = await mercadoPagoService.createPayment(paymentData);

        // Salvar informações do pagamento no banco
        await db.query(
            `INSERT INTO pagamentos 
             (agendamento_id, mercadopago_payment_id, status, valor, metodo_pagamento, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())`,
            [
                agendamento_id,
                payment.id,
                payment.status,
                payment.transaction_amount,
                payment_method_id
            ]
        );

        // Atualizar status do agendamento
        await db.query(
            'UPDATE agendamentos SET status_pagamento = ? WHERE id = ?',
            [payment.status === 'approved' ? 'pago' : 'pendente', agendamento_id]
        );

        res.json({
            success: true,
            payment: {
                id: payment.id,
                status: payment.status,
                status_detail: payment.status_detail,
                transaction_amount: payment.transaction_amount,
                qr_code: payment.point_of_interaction?.transaction_data?.qr_code,
                qr_code_base64: payment.point_of_interaction?.transaction_data?.qr_code_base64,
                ticket_url: payment.point_of_interaction?.transaction_data?.ticket_url
            }
        });

    } catch (error) {
        console.error('Erro ao criar pagamento:', error);
        res.status(500).json({
            success: false,
            message: 'Erro interno do servidor',
            error: error.message
        });
    }
});

// Consultar status do pagamento
router.get('/status/:paymentId', authenticateToken, async (req, res) => {
    try {
        const { paymentId } = req.params;

        const payment = await mercadoPagoService.getPayment(paymentId);

        // Atualizar status no banco de dados
        await db.query(
            'UPDATE pagamentos SET status = ? WHERE mercadopago_payment_id = ?',
            [payment.status, paymentId]
        );

        // Se foi aprovado, atualizar agendamento
        if (payment.status === 'approved') {
            await db.query(
                `UPDATE agendamentos SET status_pagamento = 'pago' 
                 WHERE id = (SELECT agendamento_id FROM pagamentos WHERE mercadopago_payment_id = ?)`,
                [paymentId]
            );
        }

        res.json({
            success: true,
            payment: {
                id: payment.id,
                status: payment.status,
                status_detail: payment.status_detail,
                transaction_amount: payment.transaction_amount
            }
        });

    } catch (error) {
        console.error('Erro ao consultar pagamento:', error);
        res.status(500).json({
            success: false,
            message: 'Erro interno do servidor',
            error: error.message
        });
    }
});

// Webhook do Mercado Pago
router.post('/webhook', async (req, res) => {
    try {
        const notification = mercadoPagoService.processWebhook(req.body);
        
        if (notification && notification.payment_id) {
            // Buscar informações atualizadas do pagamento
            const payment = await mercadoPagoService.getPayment(notification.payment_id);
            
            // Atualizar status no banco
            await db.query(
                'UPDATE pagamentos SET status = ? WHERE mercadopago_payment_id = ?',
                [payment.status, notification.payment_id]
            );
            
            // Se aprovado, atualizar agendamento
            if (payment.status === 'approved') {
                await db.query(
                    `UPDATE agendamentos SET status_pagamento = 'pago' 
                     WHERE id = (SELECT agendamento_id FROM pagamentos WHERE mercadopago_payment_id = ?)`,
                    [notification.payment_id]
                );
            }
        }
        
        res.status(200).json({ success: true });
        
    } catch (error) {
        console.error('Erro no webhook:', error);
        res.status(500).json({
            success: false,
            message: 'Erro interno do servidor'
        });
    }
});

// Obter métodos de pagamento disponíveis
router.get('/methods', async (req, res) => {
    try {
        const methods = await mercadoPagoService.getPaymentMethods();
        
        res.json({
            success: true,
            methods
        });
        
    } catch (error) {
        console.error('Erro ao buscar métodos de pagamento:', error);
        res.status(500).json({
            success: false,
            message: 'Erro interno do servidor',
            error: error.message
        });
    }
});

module.exports = router;