const mysql = require('mysql2/promise');
require('dotenv').config({ path: './backend/server/.env.easypanel' });

async function createMissingTables() {
    console.log('🔧 Criando tabelas faltantes no banco de dados online...');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        console.log('✅ Conectado ao banco:', process.env.DB_NAME);
        
        // Tabela de serviços
        console.log('\n📋 Criando tabela: servicos');
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS servicos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_salao INT NOT NULL,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                duracao INT NOT NULL COMMENT 'Duração em minutos',
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
            )
        `);
        console.log('✅ Tabela servicos criada com sucesso');

        // Tabela de horários disponíveis
        console.log('\n📋 Criando tabela: horarios_disponiveis');
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS horarios_disponiveis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_profissional INT NOT NULL,
                dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL,
                hora_inicio TIME NOT NULL,
                hora_fim TIME NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE,
                UNIQUE KEY unique_schedule (id_profissional, dia_semana, hora_inicio)
            )
        `);
        console.log('✅ Tabela horarios_disponiveis criada com sucesso');

        // Tabela de reset de senhas
        console.log('\n📋 Criando tabela: password_resets');
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_token (token),
                INDEX idx_expires (expires_at)
            )
        `);
        console.log('✅ Tabela password_resets criada com sucesso');

        // Criar índices adicionais para performance
        console.log('\n🔍 Criando índices para melhor performance...');
        
        try {
            await connection.execute('CREATE INDEX idx_servicos_salao ON servicos(id_salao)');
            console.log('✅ Índice idx_servicos_salao criado');
        } catch (e) {
            console.log('ℹ️  Índice idx_servicos_salao já existe');
        }

        try {
            await connection.execute('CREATE INDEX idx_horarios_profissional ON horarios_disponiveis(id_profissional)');
            console.log('✅ Índice idx_horarios_profissional criado');
        } catch (e) {
            console.log('ℹ️  Índice idx_horarios_profissional já existe');
        }

        // Verificar se as tabelas foram criadas
        console.log('\n🔍 Verificando tabelas criadas...');
        const [tables] = await connection.execute('SHOW TABLES');
        const tableNames = tables.map(table => Object.values(table)[0]);
        
        const requiredTables = ['usuarios', 'agendamentos', 'servicos', 'horarios_disponiveis', 'password_resets'];
        let allTablesExist = true;
        
        requiredTables.forEach(tableName => {
            if (tableNames.includes(tableName)) {
                console.log(`✅ ${tableName} - OK`);
            } else {
                console.log(`❌ ${tableName} - FALTANDO`);
                allTablesExist = false;
            }
        });

        console.log('\n📊 RESULTADO FINAL:');
        if (allTablesExist) {
            console.log('✅ Todas as tabelas necessárias estão presentes!');
            console.log('✅ Sistema de cadastro está pronto para funcionar!');
            console.log('✅ Usuários podem se cadastrar e fazer agendamentos!');
        } else {
            console.log('❌ Ainda existem tabelas faltando');
        }

    } catch (error) {
        console.error('❌ Erro ao criar tabelas:', error.message);
        throw error;
    } finally {
        await connection.end();
    }
}

createMissingTables().catch(console.error);