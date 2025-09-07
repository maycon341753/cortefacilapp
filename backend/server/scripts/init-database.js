const Database = require('../config/database');
const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

// Carregar variáveis de ambiente
// Usar .env.easypanel para conexão com banco remoto
dotenv.config({ path: '.env.easypanel' });

/**
 * Script para inicializar o banco de dados automaticamente
 * Cria as tabelas necessárias se elas não existirem
 */
class DatabaseInitializer {
    constructor() {
        this.db = Database.getInstance();
    }

    /**
     * Executa a inicialização completa do banco
     */
    async initialize() {
        console.log('🚀 Iniciando configuração do banco de dados...');
        
        try {
            // Testar conexão
            console.log('🔍 Testando conexão com o banco...');
            const isConnected = await this.db.testConnection();
            
            if (!isConnected) {
                throw new Error('Não foi possível conectar ao banco de dados');
            }
            
            console.log('✅ Conexão estabelecida com sucesso!');
            
            // Verificar se o banco existe
            await this.ensureDatabase();
            
            // Verificar e criar tabelas
            await this.createTables();
            
            // Inserir dados iniciais
            await this.insertInitialData();
            
            // Criar índices
            await this.createIndexes();
            
            console.log('🎉 Banco de dados configurado com sucesso!');
            
        } catch (error) {
            console.error('❌ Erro na inicialização do banco:', error.message);
            throw error;
        }
    }

    /**
     * Garante que o banco de dados existe
     */
    async ensureDatabase() {
        try {
            console.log('📋 Verificando banco de dados...');
            
            // Obter conexão direta para comandos DDL
            const connection = await this.db.getConnection();
            
            try {
                // Criar banco se não existir (usando query simples)
                const dbName = process.env.DB_NAME || 'cortefacil';
                await connection.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\``);
                await connection.query(`USE \`${dbName}\``);
                
                console.log(`✅ Banco de dados "${dbName}" pronto`);
            } finally {
                connection.release();
            }
            
        } catch (error) {
            console.error('❌ Erro ao criar banco:', error);
            throw error;
        }
    }

    /**
     * Cria todas as tabelas necessárias
     */
    async createTables() {
        console.log('🏗️ Criando tabelas...');
        
        const tables = [
            {
                name: 'usuarios',
                sql: `
                    CREATE TABLE IF NOT EXISTS usuarios (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nome VARCHAR(100) NOT NULL,
                        email VARCHAR(100) UNIQUE NOT NULL,
                        senha VARCHAR(255) NOT NULL,
                        tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL,
                        telefone VARCHAR(20),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                `
            },
            {
                name: 'saloes',
                sql: `
                    CREATE TABLE IF NOT EXISTS saloes (
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
                    )
                `
            },
            {
                name: 'profissionais',
                sql: `
                    CREATE TABLE IF NOT EXISTS profissionais (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        id_salao INT NOT NULL,
                        nome VARCHAR(100) NOT NULL,
                        especialidade VARCHAR(100) NOT NULL,
                        ativo BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
                    )
                `
            },
            {
                name: 'agendamentos',
                sql: `
                    CREATE TABLE IF NOT EXISTS agendamentos (
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
                    )
                `
            }
        ];

        // Obter conexão direta para comandos DDL
        const connection = await this.db.getConnection();
        
        try {
            for (const table of tables) {
                try {
                    await connection.query(table.sql);
                    console.log(`✅ Tabela "${table.name}" criada/verificada`);
                } catch (error) {
                    console.error(`❌ Erro ao criar tabela "${table.name}":`, error.message);
                    throw error;
                }
            }
        } finally {
            connection.release();
        }
    }

    /**
     * Insere dados iniciais necessários
     */
    async insertInitialData() {
        console.log('📝 Inserindo dados iniciais...');
        
        try {
            // Verificar se já existe usuário admin
            const adminExists = await this.db.queryOne(
                "SELECT id FROM usuarios WHERE email = 'admin@cortefacil.com'"
            );
            
            if (!adminExists) {
                await this.db.query(`
                    INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
                    ('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
                `);
                console.log('✅ Usuário administrador criado');
            } else {
                console.log('ℹ️ Usuário administrador já existe');
            }
            
        } catch (error) {
            console.error('❌ Erro ao inserir dados iniciais:', error);
            throw error;
        }
    }

    /**
     * Cria índices para melhor performance
     */
    async createIndexes() {
        console.log('🔍 Criando índices...');
        
        const indexes = [
            'CREATE INDEX IF NOT EXISTS idx_agendamentos_data_hora ON agendamentos(data, hora)',
            'CREATE INDEX IF NOT EXISTS idx_agendamentos_profissional ON agendamentos(id_profissional)',
            'CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email)',
            'CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario)'
        ];

        // Obter conexão direta para comandos DDL
        const connection = await this.db.getConnection();
        
        try {
            for (const indexSql of indexes) {
                try {
                    await connection.query(indexSql);
                } catch (error) {
                    // Ignorar erros de índices que já existem
                    if (!error.message.includes('Duplicate key name')) {
                        console.warn('⚠️ Aviso ao criar índice:', error.message);
                    }
                }
            }
        } finally {
            connection.release();
        }
        
        console.log('✅ Índices criados/verificados');
    }

    /**
     * Verifica se as tabelas existem
     */
    async checkTables() {
        try {
            const tables = await this.db.query('SHOW TABLES');
            console.log('📋 Tabelas existentes:', tables.map(t => Object.values(t)[0]));
            return tables;
        } catch (error) {
            console.error('❌ Erro ao verificar tabelas:', error);
            return [];
        }
    }
}

/**
 * Função principal para executar a inicialização
 */
async function initializeDatabase() {
    const initializer = new DatabaseInitializer();
    
    try {
        await initializer.initialize();
        
        // Verificar resultado
        console.log('\n📊 Verificação final:');
        await initializer.checkTables();
        
        // Verificar usuário admin
        const admin = await initializer.db.queryOne(
            "SELECT id, nome, email, tipo_usuario FROM usuarios WHERE tipo_usuario = 'admin'"
        );
        
        if (admin) {
            console.log('👤 Usuário admin:', admin);
        }
        
        console.log('\n🎉 Inicialização concluída com sucesso!');
        
    } catch (error) {
        console.error('\n💥 Falha na inicialização:', error.message);
        process.exit(1);
    } finally {
        await initializer.db.closePool();
    }
}

// Executar se chamado diretamente
if (require.main === module) {
    initializeDatabase();
}

module.exports = { DatabaseInitializer, initializeDatabase };