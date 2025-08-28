-- Adicionar colunas de pagamento na tabela agendamentos
ALTER TABLE agendamentos 
ADD COLUMN status_pagamento ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente' AFTER valor_taxa;

-- Criar tabela de pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    mercadopago_payment_id VARCHAR(100) UNIQUE,
    status VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo_pagamento VARCHAR(50) NOT NULL,
    dados_pagamento JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE
);

-- Índices para melhor performance
CREATE INDEX idx_pagamentos_agendamento ON pagamentos(agendamento_id);
CREATE INDEX idx_pagamentos_mercadopago ON pagamentos(mercadopago_payment_id);
CREATE INDEX idx_pagamentos_status ON pagamentos(status);
CREATE INDEX idx_agendamentos_status_pagamento ON agendamentos(status_pagamento);