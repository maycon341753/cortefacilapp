const { spawn } = require('child_process');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

// Configurações do SSH Tunnel
const SSH_CONFIG = {
    host: 'srv973908.hstgr.cloud',
    user: 'root',
    localPort: 3307, // Porta local para o tunnel
    remotePort: 3306 // Porta MySQL no servidor
};

// Configurações do banco via tunnel
const TUNNEL_DB_CONFIG = {
    host: 'localhost',
    port: SSH_CONFIG.localPort,
    user: 'u690889028_mayconwender',
    password: 'Maycon341753',
    database: 'u690889028_cortefacil'
};

class SSHTunnelManager {
    constructor() {
        this.tunnelProcess = null;
        this.isConnected = false;
    }

    async startTunnel() {
        return new Promise((resolve, reject) => {
            console.log('🔧 Iniciando SSH Tunnel...');
            console.log(`📋 ${SSH_CONFIG.user}@${SSH_CONFIG.host}:${SSH_CONFIG.remotePort} -> localhost:${SSH_CONFIG.localPort}`);

            // Comando SSH para criar o tunnel
            const sshCommand = [
                '-L', `${SSH_CONFIG.localPort}:localhost:${SSH_CONFIG.remotePort}`,
                '-N', // Não executar comando remoto
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                `${SSH_CONFIG.user}@${SSH_CONFIG.host}`
            ];

            this.tunnelProcess = spawn('ssh', sshCommand);

            this.tunnelProcess.stdout.on('data', (data) => {
                console.log(`SSH: ${data}`);
            });

            this.tunnelProcess.stderr.on('data', (data) => {
                const message = data.toString();
                console.log(`SSH: ${message}`);
                
                // SSH tunnel estabelecido (não há output específico, então aguardamos um tempo)
                if (!this.isConnected) {
                    setTimeout(() => {
                        this.isConnected = true;
                        console.log('✅ SSH Tunnel estabelecido!');
                        resolve();
                    }, 3000);
                }
            });

            this.tunnelProcess.on('error', (error) => {
                console.error('❌ Erro no SSH Tunnel:', error.message);
                reject(error);
            });

            this.tunnelProcess.on('close', (code) => {
                console.log(`🔌 SSH Tunnel fechado com código: ${code}`);
                this.isConnected = false;
            });

            // Timeout de segurança
            setTimeout(() => {
                if (!this.isConnected) {
                    this.isConnected = true;
                    console.log('⏰ Timeout - assumindo que tunnel está ativo');
                    resolve();
                }
            }, 5000);
        });
    }

    async testConnection() {
        console.log('🔍 Testando conexão via SSH Tunnel...');
        
        try {
            const connection = await mysql.createConnection(TUNNEL_DB_CONFIG);
            console.log('✅ Conexão via tunnel estabelecida!');
            
            // Teste básico
            const [rows] = await connection.execute('SELECT 1 as test');
            console.log('✅ Query de teste executada:', rows[0]);
            
            // Verificar tabelas
            const [tables] = await connection.execute('SHOW TABLES');
            console.log(`📊 Tabelas encontradas: ${tables.length}`);
            
            await connection.end();
            return true;
            
        } catch (error) {
            console.error('❌ Erro na conexão via tunnel:', error.message);
            return false;
        }
    }

    async createTunnelEnvFile() {
        const envContent = `# Configurações para SSH Tunnel
# Use estas configurações quando o tunnel estiver ativo

DB_HOST=localhost
DB_PORT=${SSH_CONFIG.localPort}
DB_USER=u690889028_mayconwender
DB_PASSWORD=Maycon341753
DB_NAME=u690889028_cortefacil
DATABASE_URL=mysql://u690889028_mayconwender:Maycon341753@localhost:${SSH_CONFIG.localPort}/u690889028_cortefacil

# Outras configurações (manter as mesmas)
JWT_SECRET=cortefacil_jwt_secret_2024_super_secure_key_maycon
JWT_EXPIRES_IN=7d
CORS_ORIGINS=http://localhost:3000,https://cortefacil.easypanel.host
RATE_LIMIT=100
LOG_LEVEL=info
CACHE_TTL=300
BCRYPT_ROUNDS=12
FRONTEND_URL=https://cortefacil.easypanel.host
BACKEND_URL=https://cortefacil-backend.easypanel.host

# Mercado Pago
MERCADO_PAGO_ACCESS_TOKEN=APP_USR-1234567890123456-123456-abcdef1234567890abcdef1234567890-123456789
MERCADO_PAGO_PUBLIC_KEY=APP_USR-abcdef12-3456-7890-abcd-ef1234567890
`;

        const envPath = path.join(__dirname, 'backend', 'server', '.env.tunnel');
        fs.writeFileSync(envPath, envContent);
        console.log('📄 Arquivo .env.tunnel criado!');
        console.log('💡 Para usar: copie o conteúdo para .env.easypanel quando o tunnel estiver ativo');
    }

    stopTunnel() {
        if (this.tunnelProcess) {
            console.log('🛑 Parando SSH Tunnel...');
            this.tunnelProcess.kill();
            this.tunnelProcess = null;
            this.isConnected = false;
        }
    }
}

// Função principal
async function main() {
    const tunnel = new SSHTunnelManager();
    
    try {
        // Criar arquivo de configuração do tunnel
        await tunnel.createTunnelEnvFile();
        
        console.log('🚀 Iniciando solução SSH Tunnel...');
        console.log('⚠️  Você precisará inserir a senha SSH do root quando solicitado');
        console.log('');
        
        // Iniciar tunnel
        await tunnel.startTunnel();
        
        // Testar conexão
        const success = await tunnel.testConnection();
        
        if (success) {
            console.log('');
            console.log('🎉 SSH Tunnel funcionando perfeitamente!');
            console.log('📋 Próximos passos:');
            console.log('   1. Mantenha este terminal aberto (tunnel ativo)');
            console.log('   2. Em outro terminal, copie as configurações de .env.tunnel para .env.easypanel');
            console.log('   3. Reinicie o backend: npm start');
            console.log('   4. O backend conectará via tunnel na porta 3307');
            console.log('');
            console.log('⌨️  Pressione Ctrl+C para parar o tunnel');
            
            // Manter o processo ativo
            process.on('SIGINT', () => {
                console.log('\n🛑 Parando SSH Tunnel...');
                tunnel.stopTunnel();
                process.exit(0);
            });
            
            // Manter ativo indefinidamente
            setInterval(() => {}, 1000);
            
        } else {
            console.log('❌ Falha na conexão via tunnel');
            tunnel.stopTunnel();
            process.exit(1);
        }
        
    } catch (error) {
        console.error('❌ Erro:', error.message);
        tunnel.stopTunnel();
        process.exit(1);
    }
}

// Executar se chamado diretamente
if (require.main === module) {
    main();
}

module.exports = SSHTunnelManager;