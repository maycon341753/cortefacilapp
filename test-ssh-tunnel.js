const { spawn } = require('child_process');
const mysql = require('mysql2/promise');

// Configurações
const SSH_CONFIG = {
    host: 'srv973908.hstgr.cloud',
    user: 'root',
    localPort: 3307,
    remotePort: 3306
};

const DB_CONFIG = {
    host: 'localhost',
    port: 3307,
    user: 'u690889028_mayconwender',
    password: 'Maycon341753',
    database: 'u690889028_cortefacil'
};

class SimpleTunnel {
    constructor() {
        this.process = null;
    }

    async start() {
        console.log('🔧 Iniciando SSH Tunnel...');
        console.log(`📋 Tunnel: ${SSH_CONFIG.user}@${SSH_CONFIG.host}:${SSH_CONFIG.remotePort} -> localhost:${SSH_CONFIG.localPort}`);
        
        const args = [
            '-L', `${SSH_CONFIG.localPort}:localhost:${SSH_CONFIG.remotePort}`,
            '-N',
            '-o', 'StrictHostKeyChecking=no',
            `${SSH_CONFIG.user}@${SSH_CONFIG.host}`
        ];

        this.process = spawn('ssh', args, { stdio: 'inherit' });
        
        // Aguardar um tempo para o tunnel se estabelecer
        await new Promise(resolve => setTimeout(resolve, 5000));
        
        console.log('✅ Tunnel iniciado! Aguardando 5 segundos...');
    }

    async testConnection() {
        console.log('🔍 Testando conexão MySQL via tunnel...');
        
        try {
            const connection = await mysql.createConnection({
                ...DB_CONFIG,
                connectTimeout: 10000,
                acquireTimeout: 10000
            });
            
            console.log('✅ Conexão estabelecida!');
            
            // Teste simples
            const [result] = await connection.execute('SELECT 1 as test, NOW() as current_time');
            console.log('✅ Query executada:', result[0]);
            
            // Verificar tabelas
            const [tables] = await connection.execute('SHOW TABLES');
            console.log(`📊 Tabelas no banco: ${tables.length}`);
            tables.forEach((table, index) => {
                console.log(`   ${index + 1}. ${Object.values(table)[0]}`);
            });
            
            await connection.end();
            return true;
            
        } catch (error) {
            console.error('❌ Erro na conexão:', error.message);
            console.error('   Código:', error.code);
            return false;
        }
    }

    stop() {
        if (this.process) {
            console.log('🛑 Parando tunnel...');
            this.process.kill();
        }
    }
}

async function main() {
    const tunnel = new SimpleTunnel();
    
    try {
        await tunnel.start();
        
        const success = await tunnel.testConnection();
        
        if (success) {
            console.log('\n🎉 SSH Tunnel funcionando!');
            console.log('💡 Agora você pode usar as configurações do .env.tunnel');
        } else {
            console.log('\n❌ Falha no teste de conexão');
        }
        
    } catch (error) {
        console.error('❌ Erro:', error.message);
    } finally {
        tunnel.stop();
        process.exit(0);
    }
}

if (require.main === module) {
    main();
}