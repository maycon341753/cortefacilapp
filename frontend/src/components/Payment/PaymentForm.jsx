import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import api from '../../services/api';

const PaymentForm = ({ agendamento, onPaymentSuccess, onCancel }) => {
    const [loading, setLoading] = useState(false);
    const [paymentMethods, setPaymentMethods] = useState([]);
    const [selectedMethod, setSelectedMethod] = useState('');
    const [paymentData, setPaymentData] = useState({
        cardNumber: '',
        expiryDate: '',
        cvv: '',
        cardholderName: '',
        email: '',
        firstName: '',
        lastName: '',
        documentType: 'CPF',
        documentNumber: ''
    });
    const [paymentResult, setPaymentResult] = useState(null);

    useEffect(() => {
        loadPaymentMethods();
    }, []);

    const loadPaymentMethods = async () => {
        try {
            const response = await api.get('/payments/methods');
            if (response.data.success) {
                setPaymentMethods(Object.values(response.data.methods));
            }
        } catch (error) {
            console.error('Erro ao carregar métodos de pagamento:', error);
            toast.error('Erro ao carregar métodos de pagamento');
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setPaymentData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const formatCardNumber = (value) => {
        return value.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, '$1 ');
    };

    const formatExpiryDate = (value) => {
        return value.replace(/\D/g, '').replace(/(\d{2})(\d{2})/, '$1/$2');
    };

    const handleCardNumberChange = (e) => {
        const formatted = formatCardNumber(e.target.value);
        if (formatted.length <= 19) {
            setPaymentData(prev => ({ ...prev, cardNumber: formatted }));
        }
    };

    const handleExpiryDateChange = (e) => {
        const formatted = formatExpiryDate(e.target.value);
        if (formatted.length <= 5) {
            setPaymentData(prev => ({ ...prev, expiryDate: formatted }));
        }
    };

    const processPayment = async () => {
        setLoading(true);
        try {
            const paymentPayload = {
                agendamento_id: agendamento.id,
                payment_method_id: selectedMethod,
                serviceName: agendamento.servico_nome || 'Serviço de Beleza',
                payer: {
                    email: paymentData.email,
                    first_name: paymentData.firstName,
                    last_name: paymentData.lastName,
                    identification: {
                        type: paymentData.documentType,
                        number: paymentData.documentNumber.replace(/\D/g, '')
                    }
                }
            };

            // Se for cartão de crédito, adicionar dados do cartão
            if (selectedMethod === 'credit_card') {
                // Aqui você integraria com o SDK do Mercado Pago para tokenizar o cartão
                // Por simplicidade, vamos simular um token
                paymentPayload.token = 'simulated_card_token';
            }

            const response = await api.post('/payments/create', paymentPayload);
            
            if (response.data.success) {
                setPaymentResult(response.data.payment);
                
                if (response.data.payment.status === 'approved') {
                    toast.success('Pagamento aprovado com sucesso!');
                    onPaymentSuccess(response.data.payment);
                } else if (response.data.payment.status === 'pending') {
                    toast.info('Pagamento pendente. Aguarde a confirmação.');
                    if (selectedMethod === 'pix') {
                        // Mostrar QR Code do PIX
                        toast.info('Use o QR Code para finalizar o pagamento via PIX');
                    }
                } else {
                    toast.error('Pagamento rejeitado. Tente novamente.');
                }
            } else {
                toast.error('Erro ao processar pagamento');
            }
        } catch (error) {
            console.error('Erro no pagamento:', error);
            toast.error(error.response?.data?.message || 'Erro ao processar pagamento');
        } finally {
            setLoading(false);
        }
    };

    const checkPaymentStatus = async (paymentId) => {
        try {
            const response = await api.get(`/payments/status/${paymentId}`);
            if (response.data.success) {
                setPaymentResult(response.data.payment);
                
                if (response.data.payment.status === 'approved') {
                    toast.success('Pagamento confirmado!');
                    onPaymentSuccess(response.data.payment);
                }
            }
        } catch (error) {
            console.error('Erro ao verificar status:', error);
        }
    };

    if (paymentResult && paymentResult.status === 'pending' && selectedMethod === 'pix') {
        return (
            <div className="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto">
                <div className="text-center">
                    <div className="mb-4">
                        <i className="fas fa-qrcode text-6xl text-blue-600 mb-4"></i>
                        <h3 className="text-xl font-bold text-gray-800 mb-2">
                            Pagamento via PIX
                        </h3>
                        <p className="text-gray-600 mb-4">
                            Escaneie o QR Code ou copie o código PIX
                        </p>
                    </div>
                    
                    {paymentResult.qr_code_base64 && (
                        <div className="mb-4">
                            <img 
                                src={`data:image/png;base64,${paymentResult.qr_code_base64}`}
                                alt="QR Code PIX"
                                className="mx-auto border rounded"
                            />
                        </div>
                    )}
                    
                    <div className="mb-4">
                        <p className="text-sm text-gray-600 mb-2">Valor:</p>
                        <p className="text-2xl font-bold text-green-600">
                            R$ {paymentResult.transaction_amount.toFixed(2)}
                        </p>
                    </div>
                    
                    <div className="space-y-3">
                        <button
                            onClick={() => checkPaymentStatus(paymentResult.id)}
                            className="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <i className="fas fa-sync-alt mr-2"></i>
                            Verificar Pagamento
                        </button>
                        
                        <button
                            onClick={onCancel}
                            className="w-full bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto">
            <div className="mb-6">
                <h3 className="text-xl font-bold text-gray-800 mb-2">
                    Pagamento do Agendamento
                </h3>
                <div className="bg-gray-50 p-4 rounded-lg">
                    <p className="text-sm text-gray-600">Valor da taxa:</p>
                    <p className="text-2xl font-bold text-green-600">R$ 1,29</p>
                </div>
            </div>

            <div className="space-y-4">
                {/* Seleção do método de pagamento */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Método de Pagamento
                    </label>
                    <select
                        value={selectedMethod}
                        onChange={(e) => setSelectedMethod(e.target.value)}
                        className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                        <option value="">Selecione um método</option>
                        {paymentMethods.map(method => (
                            <option key={method.id} value={method.id}>
                                {method.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Dados do pagador */}
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Nome
                        </label>
                        <input
                            type="text"
                            name="firstName"
                            value={paymentData.firstName}
                            onChange={handleInputChange}
                            className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Sobrenome
                        </label>
                        <input
                            type="text"
                            name="lastName"
                            value={paymentData.lastName}
                            onChange={handleInputChange}
                            className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        E-mail
                    </label>
                    <input
                        type="email"
                        name="email"
                        value={paymentData.email}
                        onChange={handleInputChange}
                        className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Documento
                        </label>
                        <select
                            name="documentType"
                            value={paymentData.documentType}
                            onChange={handleInputChange}
                            className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="CPF">CPF</option>
                            <option value="CNPJ">CNPJ</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Número
                        </label>
                        <input
                            type="text"
                            name="documentNumber"
                            value={paymentData.documentNumber}
                            onChange={handleInputChange}
                            placeholder={paymentData.documentType === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'}
                            className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        />
                    </div>
                </div>

                {/* Dados do cartão (apenas se cartão selecionado) */}
                {selectedMethod === 'credit_card' && (
                    <div className="space-y-4 border-t pt-4">
                        <h4 className="font-medium text-gray-800">Dados do Cartão</h4>
                        
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Nome no Cartão
                            </label>
                            <input
                                type="text"
                                name="cardholderName"
                                value={paymentData.cardholderName}
                                onChange={handleInputChange}
                                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            />
                        </div>
                        
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Número do Cartão
                            </label>
                            <input
                                type="text"
                                name="cardNumber"
                                value={paymentData.cardNumber}
                                onChange={handleCardNumberChange}
                                placeholder="0000 0000 0000 0000"
                                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            />
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Validade
                                </label>
                                <input
                                    type="text"
                                    name="expiryDate"
                                    value={paymentData.expiryDate}
                                    onChange={handleExpiryDateChange}
                                    placeholder="MM/AA"
                                    className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    CVV
                                </label>
                                <input
                                    type="text"
                                    name="cvv"
                                    value={paymentData.cvv}
                                    onChange={handleInputChange}
                                    placeholder="000"
                                    maxLength="4"
                                    className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                )}

                {/* Botões */}
                <div className="flex space-x-4 pt-4">
                    <button
                        onClick={onCancel}
                        className="flex-1 bg-gray-500 text-white py-3 px-4 rounded-lg hover:bg-gray-600 transition-colors"
                        disabled={loading}
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={processPayment}
                        disabled={loading || !selectedMethod}
                        className="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                    >
                        {loading ? (
                            <>
                                <i className="fas fa-spinner fa-spin mr-2"></i>
                                Processando...
                            </>
                        ) : (
                            <>
                                <i className="fas fa-credit-card mr-2"></i>
                                Pagar R$ 1,29
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default PaymentForm;