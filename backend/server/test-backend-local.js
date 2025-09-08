const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const cors = require('cors');

// Configurações do banco de dados (funcionando)
const dbConfig = {
  host: '31.97.171.104',
  port: 3306,
  user: 'u690889028_mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_mayconwender'
};

const JWT_SECRET = '3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53';

// Criar aplicação Express
const app = express();
const PORT = 3002; // Porta diferente para não conflitar

// Middlewares
app.use(cors({
  origin: ['http://localhost:5173', 'https://www.cortefacil.app', 'https://cortefacil.vercel.app'],
  credentials: true
}));
app.use(express.json());

// Pool de conexões
let pool;

async function initDatabase() {
  try {
    pool = mysql.createPool({
      ...dbConfig,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });
    
    // Testar conexão
    const connection = await pool.getConnection();
    console.log('✅ Conexão com banco de dados estabelecida!');
    connection.release();
    
    return true;
  } catch (error) {
    console.error('❌ Erro ao conectar com banco:', error.message);
    return false;
  }
}

// Rotas da API
app.get('/', (req, res) => {
  res.json({
    message: 'CortefácilApp API - Teste Local',
    status: 'online',
    timestamp: new Date().toISOString(),
    database: pool ? 'conectado' : 'desconectado'
  });
});

app.get('/health', async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [rows] = await connection.execute('SELECT 1 as test');
    connection.release();
    
    res.json({
      status: 'healthy',
      database: 'connected',
      timestamp: new Date().toISOString(),
      test_query: rows[0]
    });
  } catch (error) {
    res.status(500).json({
      status: 'unhealthy',
      database: 'disconnected',
      error: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

app.get('/tables', async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [tables] = await connection.execute('SHOW TABLES');
    connection.release();
    
    res.json({
      tables: tables.map(t => Object.values(t)[0]),
      count: tables.length,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    res.status(500).json({
      error: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

app.post('/register', async (req, res) => {
  try {
    const { nome, email, senha, telefone } = req.body;
    
    if (!nome || !email || !senha) {
      return res.status(400).json({
        error: 'Nome, email e senha são obrigatórios'
      });
    }
    
    const connection = await pool.getConnection();
    
    // Verificar se usuário já existe
    const [existing] = await connection.execute(
      'SELECT id FROM usuarios WHERE email = ?',
      [email]
    );
    
    if (existing.length > 0) {
      connection.release();
      return res.status(400).json({
        error: 'Email já cadastrado'
      });
    }
    
    // Hash da senha
    const hashedPassword = await bcrypt.hash(senha, 10);
    
    // Inserir usuário
    const [result] = await connection.execute(
      'INSERT INTO usuarios (nome, email, senha, telefone, created_at) VALUES (?, ?, ?, ?, NOW())',
      [nome, email, hashedPassword, telefone || null]
    );
    
    connection.release();
    
    // Gerar token
    const token = jwt.sign(
      { id: result.insertId, email },
      JWT_SECRET,
      { expiresIn: '24h' }
    );
    
    res.status(201).json({
      message: 'Usuário cadastrado com sucesso',
      user: {
        id: result.insertId,
        nome,
        email,
        telefone
      },
      token
    });
    
  } catch (error) {
    console.error('Erro no registro:', error);
    res.status(500).json({
      error: 'Erro interno do servidor',
      details: error.message
    });
  }
});

app.post('/login', async (req, res) => {
  try {
    const { email, senha } = req.body;
    
    if (!email || !senha) {
      return res.status(400).json({
        error: 'Email e senha são obrigatórios'
      });
    }
    
    const connection = await pool.getConnection();
    
    // Buscar usuário
    const [users] = await connection.execute(
      'SELECT id, nome, email, senha FROM usuarios WHERE email = ?',
      [email]
    );
    
    connection.release();
    
    if (users.length === 0) {
      return res.status(401).json({
        error: 'Credenciais inválidas'
      });
    }
    
    const user = users[0];
    
    // Verificar senha
    const validPassword = await bcrypt.compare(senha, user.senha);
    
    if (!validPassword) {
      return res.status(401).json({
        error: 'Credenciais inválidas'
      });
    }
    
    // Gerar token
    const token = jwt.sign(
      { id: user.id, email: user.email },
      JWT_SECRET,
      { expiresIn: '24h' }
    );
    
    res.json({
      message: 'Login realizado com sucesso',
      user: {
        id: user.id,
        nome: user.nome,
        email: user.email
      },
      token
    });
    
  } catch (error) {
    console.error('Erro no login:', error);
    res.status(500).json({
      error: 'Erro interno do servidor',
      details: error.message
    });
  }
});

// Inicializar servidor
async function startServer() {
  console.log('🚀 Iniciando servidor de teste do backend...');
  
  const dbConnected = await initDatabase();
  
  if (!dbConnected) {
    console.error('❌ Não foi possível conectar ao banco de dados');
    process.exit(1);
  }
  
  app.listen(PORT, () => {
    console.log(`✅ Servidor rodando na porta ${PORT}`);
    console.log(`🌐 Acesse: http://localhost:${PORT}`);
    console.log('\n📋 Endpoints disponíveis:');
    console.log(`   GET  http://localhost:${PORT}/`);
    console.log(`   GET  http://localhost:${PORT}/health`);
    console.log(`   GET  http://localhost:${PORT}/tables`);
    console.log(`   POST http://localhost:${PORT}/register`);
    console.log(`   POST http://localhost:${PORT}/login`);
    console.log('\n🔧 Para testar, use outro terminal ou Postman');
  });
}

startServer().catch(console.error);