const { spawn } = require('child_process');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

// Configurações SSH para EasyPanel
const SSH_CONFIG = {
    host: process.env.SSH_HOST || 'srv973908.hstgr.cloud',
    user: process.env.SSH_USER || 'u973908341',
    password: process.env.SSH_PASSWORD, // Deve ser configurado no EasyPanel
    localPort: 3307,
    remotePort: 3306
};

// Configurações MySQL via tunnel
const DB_CONFIG = {
    host: 'localhost',
    port: SSH_CONFIG.localPort,
    user: process.env.DB_USER || 'u690889028_mayconwender',
    password: process.env.DB_PASSWORD || 'Maycon341753',
    database: process.env.DB_NAME || 'u690889028_cortefacil'
};

class SSHTunnelManager {
    constructor() {
        this.tunnelProcess = null;
        this.isConnected = false;
        this.retryCount = 0;
        this.maxRetries = 3;
    }

    // Verificar se as variáveis SSH estão configuradas
    checkSSHConfig() {
        console.log('🔍 Verificando configuração SSH...');
        
        const required = ['SSH_HOST', 'SSH_USER', 'SSH_PASSWORD'];
        const missing = required.filter(key => !process.env[key]);
        
        if (missing.length > 0) {
            console.log('❌ Variáveis SSH não configuradas:');
            missing.forEach(key => console.log(`   - ${key}`));
            console.log('\n📋 Configure no EasyPanel:');
            console.log('   SSH_HOST=srv973908.hstgr.cloud');
            console.log('   SSH_USER=u973908341');
            console.log('   SSH_PASSWORD=[sua_senha_hostinger]');
            return false;
        }
        
        console.log('✅ Configuração SSH completa');
        return true;
    }

    // Estabelecer túnel SSH usando sshpass (para senha)
    async establishTunnel() {
        return new Promise((resolve, reject) => {
            console.log('🚇 Estabelecendo túnel SSH...');
            console.log(`   Host: ${SSH_CONFIG.host}`);
            console.log(`   User: ${SSH_CONFIG.user}`);
            console.log(`   Tunnel: localhost:${SSH_CONFIG.localPort} -> ${SSH_CONFIG.host}:${SSH_CONFIG.remotePort}`);
            
            // Comando SSH com sshpass para usar senha
            const sshCommand = [
                'sshpass',
                '-p', SSH_CONFIG.password,
                'ssh',
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-o', 'ServerAliveInterval=60',
                '-o', 'ServerAliveCountMax=3',
                '-N', // Não executar comando remoto
                '-L', `${SSH_CONFIG.localPort}:localhost:${SSH_CONFIG.remotePort}`,
                `${SSH_CONFIG.user}@${SSH_CONFIG.host}`
            ];
            
            console.log('🔧 Executando comando SSH tunnel...');
            
            this.tunnelProcess = spawn(sshCommand[0], sshCommand.slice(1), {
                stdio: ['pipe', 'pipe', 'pipe']
            });
            
            let output = '';
            let errorOutput = '';
            
            this.tunnelProcess.stdout.on('data', (data) => {
                output += data.toString();
                console.log(`SSH stdout: ${data.toString().trim()}`);
            });
            
            this.tunnelProcess.stderr.on('data', (data) => {
                errorOutput += data.toString();
                console.log(`SSH stderr: ${data.toString().trim()}`);
            });
            
            this.tunnelProcess.on('error', (error) => {
                console.log('❌ Erro ao iniciar processo SSH:');
                console.log(`   ${error.message}`);
                
                if (error.code === 'ENOENT') {
                    console.log('\n💡 Solução: Instalar sshpass no container:');
                    console.log('   RUN apk add --no-cache sshpass openssh-client');
                }
                
                reject(error);
            });
            
            this.tunnelProcess.on('close', (code) => {
                console.log(`🔚 Processo SSH encerrado com código: ${code}`);
                this.isConnected = false;
                
                if (code !== 0) {
                    reject(new Error(`SSH tunnel falhou com código ${code}`));
                } else {
                    resolve();
                }
            });
            
            // Aguardar um pouco para o tunnel se estabelecer
            setTimeout(() => {
                if (this.tunnelProcess && !this.tunnelProcess.killed) {
                    console.log('✅ Túnel SSH iniciado com sucesso');
                    this.isConnected = true;
                    resolve();
                }
            }, 3000);
        });
    }

    // Testar conexão MySQL via tunnel
    async testMySQLConnection() {
        try {
            console.log('🔍 Testando conexão MySQL via tunnel...');
            console.log(`   Host: ${DB_CONFIG.host}:${DB_CONFIG.port}`);
            console.log(`   User: ${DB_CONFIG.user}`);
            console.log(`   Database: ${DB_CONFIG.database}`);
            
            const connection = await mysql.createConnection({
                host: DB_CONFIG.host,
                port: DB_CONFIG.port,
                user: DB_CONFIG.user,
                password: DB_CONFIG.password,
                database: DB_CONFIG.database,
                connectTimeout: 10000
            });
            
            // Testar query simples
            const [rows] = await connection.execute('SELECT 1 as test, NOW() as timestamp');
            console.log('✅ Conexão MySQL via tunnel: SUCESSO');
            console.log(`   Resultado: ${JSON.stringify(rows[0])}`);
            
            // Testar acesso às tabelas
            try {
                const [tables] = await connection.execute('SHOW TABLES');
                console.log(`📋 Tabelas no banco: ${tables.length}`);
                if (tables.length > 0) {
                    console.log(`   Primeira tabela: ${Object.values(tables[0])[0]}`);
                }
            } catch (tableErr) {
                console.log(`⚠️  Aviso ao listar tabelas: ${tableErr.message}`);
            }
            
            await connection.end();
            return true;
            
        } catch (error) {
            console.log('❌ Erro na conexão MySQL via tunnel:');
            console.log(`   Código: ${error.code}`);
            console.log(`   Mensagem: ${error.message}`);
            return false;
        }
    }

    // Gerar arquivo .env para o backend
    generateEnvFile() {
        const envContent = `# Configuração MySQL via SSH Tunnel
# Gerado automaticamente em ${new Date().toISOString()}

# Database via SSH Tunnel
DB_HOST=localhost
DB_PORT=${SSH_CONFIG.localPort}
DB_USER=${DB_CONFIG.user}
DB_PASSWORD=${DB_CONFIG.password}
DB_NAME=${DB_CONFIG.database}
DATABASE_URL=mysql://${DB_CONFIG.user}:${DB_CONFIG.password}@localhost:${SSH_CONFIG.localPort}/${DB_CONFIG.database}

# SSH Tunnel Configuration
SSH_HOST=${SSH_CONFIG.host}
SSH_USER=${SSH_CONFIG.user}
SSH_LOCAL_PORT=${SSH_CONFIG.localPort}
SSH_REMOTE_PORT=${SSH_CONFIG.remotePort}

# Outras configurações
NODE_ENV=production
JWT_SECRET=${process.env.JWT_SECRET || '3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53'}
FRONTEND_URL=${process.env.FRONTEND_URL || 'https://cortefacil.app'}
BACKEND_URL=${process.env.BACKEND_URL || 'https://cortefacil.app/api'}
`;
        
        const envPath = path.join(__dirname, '.env.tunnel');
        fs.writeFileSync(envPath, envContent);
        console.log(`📄 Arquivo .env.tunnel criado: ${envPath}`);
        
        return envPath;
    }

    // Cleanup ao encerrar
    cleanup() {
        if (this.tunnelProcess && !this.tunnelProcess.killed) {
            console.log('🧹 Encerrando túnel SSH...');
            this.tunnelProcess.kill('SIGTERM');
            this.isConnected = false;
        }
    }
}

// Função principal
async function setupSSHTunnel() {
    console.log('🔧 CONFIGURAÇÃO SSH TUNNEL - CORTEFACIL');
    console.log('=' .repeat(50));
    
    const tunnelManager = new SSHTunnelManager();
    
    // Verificar configuração
    if (!tunnelManager.checkSSHConfig()) {
        console.log('\n❌ Configure as variáveis SSH no EasyPanel e tente novamente.');
        process.exit(1);
    }
    
    try {
        // Estabelecer túnel
        await tunnelManager.establishTunnel();
        
        // Aguardar um pouco mais para estabilizar
        console.log('⏳ Aguardando túnel estabilizar...');
        await new Promise(resolve => setTimeout(resolve, 5000));
        
        // Testar conexão MySQL
        const mysqlSuccess = await tunnelManager.testMySQLConnection();
        
        if (mysqlSuccess) {
            console.log('\n🎉 SSH TUNNEL CONFIGURADO COM SUCESSO!');
            console.log('=' .repeat(50));
            
            // Gerar arquivo .env
            const envPath = tunnelManager.generateEnvFile();
            
            console.log('\n📋 PRÓXIMOS PASSOS:');
            console.log('1. Copie as configurações do .env.tunnel para seu .env principal');
            console.log('2. Reinicie o backend com as novas configurações');
            console.log('3. Mantenha este processo rodando para manter o túnel ativo');
            
            // Manter o processo vivo
            console.log('\n🔄 Túnel ativo. Pressione Ctrl+C para encerrar.');
            
            // Configurar cleanup
            process.on('SIGINT', () => {
                console.log('\n🛑 Encerrando túnel SSH...');
                tunnelManager.cleanup();
                process.exit(0);
            });
            
            process.on('SIGTERM', () => {
                tunnelManager.cleanup();
                process.exit(0);
            });
            
            // Manter vivo
            setInterval(() => {
                if (tunnelManager.isConnected) {
                    console.log(`⏰ Túnel ativo - ${new Date().toLocaleTimeString()}`);
                }
            }, 60000); // Log a cada minuto
            
        } else {
            console.log('\n❌ Falha na conexão MySQL via túnel');
            tunnelManager.cleanup();
            process.exit(1);
        }
        
    } catch (error) {
        console.log('\n❌ Erro ao configurar SSH tunnel:');
        console.log(`   ${error.message}`);
        
        console.log('\n💡 Soluções alternativas:');
        console.log('1. Verificar credenciais SSH no painel Hostinger');
        console.log('2. Contatar suporte Hostinger para liberar porta 3306');
        console.log('3. Usar banco de dados do próprio EasyPanel');
        
        tunnelManager.cleanup();
        process.exit(1);
    }
}

// Executar se chamado diretamente
if (require.main === module) {
    setupSSHTunnel().catch(console.error);
}

module.exports = { SSHTunnelManager, setupSSHTunnel };