const { MercadoPagoConfig, Payment } = require('mercadopago');

class MercadoPagoService {
    constructor() {
        this.client = new MercadoPagoConfig({
            accessToken: process.env.MERCADOPAGO_ACCESS_TOKEN,
            options: {
                timeout: 5000,
                idempotencyKey: 'abc'
            }
        });
        this.payment = new Payment(this.client);
    }

    async createPayment(paymentData) {
        try {
            const body = {
                transaction_amount: parseFloat(process.env.PAYMENT_AMOUNT),
                description: `Agendamento - ${paymentData.serviceName}`,
                payment_method_id: paymentData.payment_method_id,
                payer: {
                    email: paymentData.payer.email,
                    first_name: paymentData.payer.first_name,
                    last_name: paymentData.payer.last_name,
                    identification: {
                        type: paymentData.payer.identification.type,
                        number: paymentData.payer.identification.number
                    }
                },
                external_reference: paymentData.agendamento_id.toString(),
                notification_url: `${process.env.FRONTEND_URL}/api/payments/webhook`,
                metadata: {
                    agendamento_id: paymentData.agendamento_id,
                    user_id: paymentData.user_id,
                    salao_id: paymentData.salao_id
                }
            };

            // Se for cartão de crédito, adicionar dados do token
            if (paymentData.token) {
                body.token = paymentData.token;
            }

            // Se for PIX, configurar método de pagamento
            if (paymentData.payment_method_id === 'pix') {
                body.payment_method_id = 'pix';
            }

            const result = await this.payment.create({ body });
            return result;
        } catch (error) {
            console.error('Erro ao criar pagamento:', error);
            throw error;
        }
    }

    async getPayment(paymentId) {
        try {
            const result = await this.payment.get({ id: paymentId });
            return result;
        } catch (error) {
            console.error('Erro ao buscar pagamento:', error);
            throw error;
        }
    }

    async getPaymentMethods() {
        try {
            // Retorna métodos de pagamento disponíveis
            return {
                credit_card: {
                    id: 'credit_card',
                    name: 'Cartão de Crédito',
                    payment_type_id: 'credit_card'
                },
                pix: {
                    id: 'pix',
                    name: 'PIX',
                    payment_type_id: 'bank_transfer'
                }
            };
        } catch (error) {
            console.error('Erro ao buscar métodos de pagamento:', error);
            throw error;
        }
    }

    processWebhook(notification) {
        try {
            // Processar notificação do webhook
            const { type, data } = notification;
            
            if (type === 'payment') {
                return {
                    payment_id: data.id,
                    status: 'received'
                };
            }
            
            return null;
        } catch (error) {
            console.error('Erro ao processar webhook:', error);
            throw error;
        }
    }
}

module.exports = new MercadoPagoService();