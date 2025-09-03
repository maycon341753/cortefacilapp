const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const dotenv = require('dotenv');
const path = require('path');
const Database = require('./config/database');
const { initializeDatabase } = require('./scripts/init-database');

// Carregar variáveis de ambiente
dotenv.config();

const app = express();
const PORT = process.env.PORT || 3001;

// Inicializar Database singleton
const db = Database.getInstance();
db.createPool();

// Configuração CORS para aceitar requisições do Vercel e outros domínios
const corsOptions = {
    origin: [
        'http://localhost:5173', // Desenvolvimento local
        'http://localhost:3000', // Desenvolvimento alternativo
        'https://cortefacil.app', // Domínio principal
        'https://www.cortefacil.app', // Domínio com www
        'https://cortefacil.vercel.app', // Vercel deploy
        ...(process.env.CORS_ORIGINS ? process.env.CORS_ORIGINS.split(',').map(origin => origin.trim()) : [])
    ],
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
};

// Middlewares
app.use(cors(corsOptions));

// Middleware para tratar erros de JSON malformado
app.use(express.json({ 
    limit: '10mb',
    verify: (req, res, buf, encoding) => {
        try {
            JSON.parse(buf);
        } catch (e) {
            res.status(400).json({
                success: false,
                message: 'JSON inválido',
                error: 'Formato de dados inválido'
            });
            return;
        }
    }
}));
app.use(express.urlencoded({ extended: true }));

// Configuração do banco de dados
const dbConfig = {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'cortefacil',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

// Pool de conexões
const pool = mysql.createPool(dbConfig);

// Middleware para disponibilizar o pool nas rotas
app.use((req, res, next) => {
    req.db = pool;
    next();
});

// Importar rotas
const authRoutes = require('./routes/auth');
const horariosRoutes = require('./routes/horarios');
const profissionaisRoutes = require('./routes/profissionais');
const servicosRoutes = require('./routes/servicos');
const bloqueiosRoutes = require('./routes/bloqueios');
const agendamentosRoutes = require('./routes/agendamentos');
const saloesRoutes = require('./routes/saloes');
const paymentsRoutes = require('./routes/payments');
const healthRoutes = require('./routes/health');

// Configurar rotas
app.use('/api/auth', authRoutes);
app.use('/api/horarios', horariosRoutes);
app.use('/api/profissionais', profissionaisRoutes);
app.use('/api/servicos', servicosRoutes);
app.use('/api/bloqueios', bloqueiosRoutes);
app.use('/api/agendamentos', agendamentosRoutes);
app.use('/api/saloes', saloesRoutes);
app.use('/api/payments', paymentsRoutes);
app.use('/api/health', healthRoutes);

// Rota de teste
app.get('/api/test', (req, res) => {
    res.json({ 
        message: 'API CortefácilApp funcionando!', 
        timestamp: new Date().toISOString() 
    });
});

// Middleware de tratamento de erros
app.use((err, req, res, next) => {
    console.error('Erro na API:', err);
    
    // Erro de JSON malformado
    if (err instanceof SyntaxError && err.status === 400 && 'body' in err) {
        return res.status(400).json({
            success: false,
            message: 'JSON inválido',
            error: 'Formato de dados inválido'
        });
    }
    
    // Outros erros
    res.status(500).json({ 
        success: false,
        message: 'Erro interno do servidor',
        error: process.env.NODE_ENV === 'development' ? err.message : 'Algo deu errado'
    });
});

// Middleware para rotas não encontradas
app.use('*', (req, res) => {
    res.status(404).json({ error: 'Endpoint não encontrado' });
});

// Função para iniciar o servidor
async function startServer() {
    try {
        // Inicializar banco de dados automaticamente
        console.log('🔧 Inicializando banco de dados...');
        await initializeDatabase();
        console.log('✅ Banco de dados inicializado com sucesso!');
        
        // Iniciar servidor
        app.listen(PORT, () => {
            console.log(`🚀 Servidor API rodando na porta ${PORT}`);
            console.log(`📍 Ambiente: ${process.env.NODE_ENV || 'development'}`);
            console.log(`🔗 URL: http://localhost:${PORT}`);
            console.log('🎉 Sistema pronto para uso!');
        });
        
    } catch (error) {
        console.error('❌ Erro ao inicializar sistema:', error.message);
        console.error('💡 Dica: Verifique se o banco MySQL está rodando e as credenciais estão corretas');
        process.exit(1);
    }
}

// Iniciar o sistema
startServer();

// Tratamento de erros não capturados
process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
    process.exit(1);
});

module.exports = app;