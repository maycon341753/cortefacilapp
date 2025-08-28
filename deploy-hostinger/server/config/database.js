const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

dotenv.config();

class Database {
    constructor() {
        this.pool = null;
        this.config = {
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'cortefacil',
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0,
            acquireTimeout: 60000,
            timeout: 60000,
            reconnect: true
        };
    }

    // Singleton pattern
    static getInstance() {
        if (!Database.instance) {
            Database.instance = new Database();
        }
        return Database.instance;
    }

    // Criar pool de conexões
    createPool() {
        if (!this.pool) {
            this.pool = mysql.createPool(this.config);
            console.log('✅ Pool de conexões MySQL criado');
        }
        return this.pool;
    }

    // Obter conexão do pool
    async getConnection() {
        try {
            if (!this.pool) {
                this.createPool();
            }
            return await this.pool.getConnection();
        } catch (error) {
            console.error('❌ Erro ao obter conexão:', error);
            throw error;
        }
    }

    // Executar query
    async query(sql, params = []) {
        let connection;
        try {
            connection = await this.getConnection();
            const [rows] = await connection.execute(sql, params);
            return rows;
        } catch (error) {
            console.error('❌ Erro na query:', error);
            throw error;
        } finally {
            if (connection) {
                connection.release();
            }
        }
    }

    // Executar query com resultado único
    async queryOne(sql, params = []) {
        const rows = await this.query(sql, params);
        return rows.length > 0 ? rows[0] : null;
    }

    // Testar conexão
    async testConnection() {
        try {
            const connection = await this.getConnection();
            await connection.ping();
            connection.release();
            console.log('✅ Conexão com banco de dados OK');
            return true;
        } catch (error) {
            console.error('❌ Erro na conexão com banco:', error);
            return false;
        }
    }

    // Fechar pool
    async closePool() {
        if (this.pool) {
            await this.pool.end();
            this.pool = null;
            console.log('✅ Pool de conexões fechado');
        }
    }

    // Configurar para ambiente online
    forceOnlineConfig() {
        this.config = {
            ...this.config,
            host: process.env.DB_HOST_ONLINE || this.config.host,
            user: process.env.DB_USER_ONLINE || this.config.user,
            password: process.env.DB_PASSWORD_ONLINE || this.config.password,
            database: process.env.DB_NAME_ONLINE || this.config.database
        };
        
        // Recriar pool com nova configuração
        if (this.pool) {
            this.pool.end();
            this.pool = null;
        }
        this.createPool();
        console.log('🌐 Configuração online aplicada');
    }
}

module.exports = Database;