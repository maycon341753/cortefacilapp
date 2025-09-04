const net = require('net');
const { spawn } = require('child_process');

// Configurações
const SSH_CONFIG = {
    host: 'srv973908.hstgr.cloud',
    user: 'root',
    localPort: 3307,
    remotePort: 3306
};

function testPort(port, host = 'localhost') {
    return new Promise((resolve) => {
        const socket = new net.Socket();
        
        socket.setTimeout(3000);
        
        socket.on('connect', () => {
            console.log(`✅ Porta ${port} está aberta em ${host}`);
            socket.destroy();
            resolve(true);
        });
        
        socket.on('timeout', () => {
            console.log(`⏰ Timeout na porta ${port} em ${host}`);
            socket.destroy();
            resolve(false);
        });
        
        socket.on('error', (err) => {
            console.log(`❌ Erro na porta ${port} em ${host}: ${err.code}`);
            resolve(false);
        });
        
        socket.connect(port, host);
    });
}

async function testSSHTunnel() {
    console.log('🔧 Testando SSH Tunnel e conectividade...');
    
    // Primeiro, testar se conseguimos conectar diretamente ao servidor SSH
    console.log('\n1. Testando conexão SSH...');
    const sshConnected = await testPort(22, SSH_CONFIG.host);
    
    if (!sshConnected) {
        console.log('❌ Não foi possível conectar ao servidor SSH');
        return false;
    }
    
    // Testar se a porta local já está em uso
    console.log('\n2. Verificando se porta local está livre...');
    const portInUse = await testPort(SSH_CONFIG.localPort);
    
    if (portInUse) {
        console.log(`⚠️  Porta ${SSH_CONFIG.localPort} já está em uso`);
    } else {
        console.log(`✅ Porta ${SSH_CONFIG.localPort} está livre`);
    }
    
    // Iniciar SSH tunnel
    console.log('\n3. Iniciando SSH Tunnel...');
    console.log(`📋 ${SSH_CONFIG.user}@${SSH_CONFIG.host}:${SSH_CONFIG.remotePort} -> localhost:${SSH_CONFIG.localPort}`);
    
    const args = [
        '-L', `${SSH_CONFIG.localPort}:localhost:${SSH_CONFIG.remotePort}`,
        '-N',
        '-o', 'StrictHostKeyChecking=no',
        '-v', // Verbose para debug
        `${SSH_CONFIG.user}@${SSH_CONFIG.host}`
    ];

    const sshProcess = spawn('ssh', args);
    
    sshProcess.stdout.on('data', (data) => {
        console.log(`SSH OUT: ${data}`);
    });
    
    sshProcess.stderr.on('data', (data) => {
        console.log(`SSH ERR: ${data}`);
    });
    
    // Aguardar tunnel se estabelecer
    console.log('⏳ Aguardando tunnel se estabelecer...');
    await new Promise(resolve => setTimeout(resolve, 8000));
    
    // Testar se a porta do tunnel está funcionando
    console.log('\n4. Testando porta do tunnel...');
    const tunnelWorking = await testPort(SSH_CONFIG.localPort);
    
    if (tunnelWorking) {
        console.log('🎉 SSH Tunnel está funcionando!');
        
        // Testar conexão MySQL básica
        console.log('\n5. Testando conexão MySQL básica...');
        try {
            const mysql = require('mysql2/promise');
            const connection = await mysql.createConnection({
                host: 'localhost',
                port: SSH_CONFIG.localPort,
                user: 'u690889028_mayconwender',
                password: 'Maycon341753',
                database: 'u690889028_cortefacil',
                connectTimeout: 5000
            });
            
            console.log('✅ Conexão MySQL estabelecida!');
            const [result] = await connection.execute('SELECT 1 as test');
            console.log('✅ Query executada:', result[0]);
            
            await connection.end();
            
        } catch (mysqlError) {
            console.log('❌ Erro MySQL:', mysqlError.message);
            console.log('   Código:', mysqlError.code);
        }
        
    } else {
        console.log('❌ SSH Tunnel não está funcionando');
    }
    
    // Parar processo SSH
    console.log('\n🛑 Parando SSH Tunnel...');
    sshProcess.kill();
    
    return tunnelWorking;
}

if (require.main === module) {
    testSSHTunnel().then(() => {
        process.exit(0);
    }).catch((error) => {
        console.error('❌ Erro:', error.message);
        process.exit(1);
    });
}