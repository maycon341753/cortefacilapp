# Configuração do Banco de Dados - EasyPanel MySQL

## Script para Executar no Terminal do MySQL (cortefacil_user)

### 1. Acesse o Terminal do MySQL no EasyPanel
1. Vá para o serviço `cortefacil_user` (MySQL)
2. Clique na aba "Terminal"
3. Execute os comandos abaixo:

### 2. Script de Configuração do Banco

```sql
-- Conectar ao MySQL
mysql -u root -p

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS cortefacil;
USE cortefacil;

-- Tabela de usuários (clientes, parceiros e administradores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL,
    telefone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de salões
CREATE TABLE saloes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dono INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de profissionais
CREATE TABLE profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_salao INT NOT NULL,
    id_profissional INT NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
    valor_taxa DECIMAL(10,2) DEFAULT 1.29,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE,
    UNIQUE KEY unique_appointment (id_profissional, data, hora)
);

-- Inserir usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Índices para melhor performance
CREATE INDEX idx_agendamentos_data_hora ON agendamentos(data, hora);
CREATE INDEX idx_agendamentos_profissional ON agendamentos(id_profissional);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_tipo ON usuarios(tipo_usuario);

-- Verificar se as tabelas foram criadas
SHOW TABLES;

-- Verificar se o usuário admin foi inserido
SELECT id, nome, email, tipo_usuario FROM usuarios WHERE tipo_usuario = 'admin';

-- Sair do MySQL
EXIT;
```

### 3. Verificação
Após executar o script:
1. Verifique se todas as tabelas foram criadas
2. Confirme que o usuário administrador foi inserido
3. Anote a senha do MySQL para usar nas variáveis de ambiente

### 4. Credenciais para o Backend
Após configurar o banco, use estas informações no backend:

```
DB_HOST=cortefacil_user
DB_USER=root
DB_PASSWORD=[SENHA_DO_MYSQL]
DB_NAME=cortefacil
```

### 5. Teste de Conexão
Para testar se o backend consegue conectar:
1. Configure as variáveis de ambiente do backend
2. Reinicie o serviço backend
3. Verifique os logs - deve parar de mostrar erros de conexão
4. Acesse: `https://api.cortefacil.app/api/health`

## Próximos Passos
1. ✅ Executar este script no MySQL
2. ✅ Configurar variáveis de ambiente do backend
3. ✅ Configurar variáveis de ambiente do frontend
4. ✅ Reiniciar ambos os serviços
5. ✅ Verificar se ficam verdes
6. ✅ Configurar SSL/domínios