const mysql = require('mysql2/promise');
const net = require('net');

// Configurações para diagnóstico
const CONFIGS = {
    hostinger_original: {
        name: 'Hostinger Original',
        host: 'srv973908.hstgr.cloud',
        port: 3306,
        user: 'u690889028_mayconwender',
        password: 'Maycon341753',
        database: 'u690889028_cortefacil'
    },
    hostinger_sem_db: {
        name: 'Hostinger sem Database',
        host: 'srv973908.hstgr.cloud',
        port: 3306,
        user: 'u690889028_mayconwender',
        password: 'Maycon341753'
    },
    hostinger_ip: {
        name: 'Hostinger por IP',
        host: '31.97.171.104',
        port: 3306,
        user: 'u690889028_mayconwender',
        password: 'Maycon341753',
        database: 'u690889028_cortefacil'
    }
};

// Função para testar conectividade de porta
function testPortConnectivity(host, port) {
    return new Promise((resolve) => {
        const socket = new net.Socket();
        const timeout = 5000;
        
        socket.setTimeout(timeout);
        
        socket.on('connect', () => {
            socket.destroy();
            resolve({ success: true, message: 'Porta acessível' });
        });
        
        socket.on('timeout', () => {
            socket.destroy();
            resolve({ success: false, message: 'Timeout na conexão' });
        });
        
        socket.on('error', (err) => {
            socket.destroy();
            resolve({ success: false, message: `Erro: ${err.message}` });
        });
        
        socket.connect(port, host);
    });
}

// Função para testar conexão MySQL
async function testMySQLConnection(config) {
    try {
        console.log(`\n🔍 Testando: ${config.name}`);
        console.log(`   Host: ${config.host}:${config.port}`);
        console.log(`   User: ${config.user}`);
        console.log(`   Database: ${config.database || 'N/A'}`);
        
        // Primeiro, testar conectividade da porta
        const portTest = await testPortConnectivity(config.host, config.port);
        console.log(`   🌐 Porta ${config.port}: ${portTest.success ? '✅' : '❌'} ${portTest.message}`);
        
        if (!portTest.success) {
            return { success: false, error: 'Porta inacessível', details: portTest.message };
        }
        
        // Testar conexão MySQL
        const connection = await mysql.createConnection({
            host: config.host,
            port: config.port,
            user: config.user,
            password: config.password,
            database: config.database,
            connectTimeout: 10000,
            acquireTimeout: 10000
        });
        
        // Testar query simples
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log(`   ✅ Conexão MySQL: Sucesso`);
        console.log(`   ✅ Query teste: ${JSON.stringify(rows[0])}`);
        
        // Se tem database, testar acesso às tabelas
        if (config.database) {
            try {
                const [tables] = await connection.execute('SHOW TABLES');
                console.log(`   📋 Tabelas encontradas: ${tables.length}`);
                if (tables.length > 0) {
                    console.log(`   📋 Primeira tabela: ${Object.values(tables[0])[0]}`);
                }
            } catch (tableErr) {
                console.log(`   ⚠️  Erro ao listar tabelas: ${tableErr.message}`);
            }
        }
        
        await connection.end();
        return { success: true };
        
    } catch (error) {
        console.log(`   ❌ Erro MySQL: ${error.code || 'UNKNOWN'}`);
        console.log(`   📝 Mensagem: ${error.message}`);
        
        // Análise específica do erro
        if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log(`   🔍 Diagnóstico: Usuário/senha incorretos ou sem permissão para este IP`);
        } else if (error.code === 'ECONNREFUSED') {
            console.log(`   🔍 Diagnóstico: Porta MySQL bloqueada ou serviço inativo`);
        } else if (error.code === 'ETIMEDOUT') {
            console.log(`   🔍 Diagnóstico: Timeout - firewall ou rede lenta`);
        } else if (error.code === 'ENOTFOUND') {
            console.log(`   🔍 Diagnóstico: Host não encontrado - DNS ou hostname incorreto`);
        }
        
        return { success: false, error: error.code, details: error.message };
    }
}

// Função para obter informações do ambiente
function getEnvironmentInfo() {
    console.log('🖥️  Informações do Ambiente:');
    console.log(`   Node.js: ${process.version}`);
    console.log(`   Plataforma: ${process.platform}`);
    console.log(`   Arquitetura: ${process.arch}`);
    
    // Tentar obter IP externo (se possível)
    const os = require('os');
    const interfaces = os.networkInterfaces();
    console.log('\n🌐 Interfaces de Rede:');
    
    Object.keys(interfaces).forEach(name => {
        interfaces[name].forEach(iface => {
            if (iface.family === 'IPv4' && !iface.internal) {
                console.log(`   ${name}: ${iface.address}`);
            }
        });
    });
}

// Função principal
async function diagnosticarMySQL() {
    console.log('🔧 DIAGNÓSTICO MYSQL REMOTO - CORTEFACIL');
    console.log('=' .repeat(50));
    
    getEnvironmentInfo();
    
    console.log('\n📊 Testando Configurações MySQL:');
    console.log('=' .repeat(50));
    
    const resultados = {};
    
    // Testar cada configuração
    for (const [key, config] of Object.entries(CONFIGS)) {
        resultados[key] = await testMySQLConnection(config);
        await new Promise(resolve => setTimeout(resolve, 1000)); // Pausa entre testes
    }
    
    // Resumo dos resultados
    console.log('\n📋 RESUMO DOS TESTES:');
    console.log('=' .repeat(50));
    
    let sucessos = 0;
    Object.entries(resultados).forEach(([key, result]) => {
        const status = result.success ? '✅ SUCESSO' : '❌ FALHOU';
        console.log(`${CONFIGS[key].name}: ${status}`);
        if (result.success) sucessos++;
        if (!result.success && result.error) {
            console.log(`   Erro: ${result.error} - ${result.details}`);
        }
    });
    
    console.log('\n🎯 RECOMENDAÇÕES:');
    console.log('=' .repeat(50));
    
    if (sucessos === 0) {
        console.log('❌ Nenhuma conexão funcionou. Possíveis causas:');
        console.log('   1. Firewall bloqueando porta 3306');
        console.log('   2. Usuário MySQL sem permissões remotas');
        console.log('   3. Servidor MySQL inativo');
        console.log('   4. Credenciais incorretas');
        console.log('\n🚀 Soluções sugeridas:');
        console.log('   1. Usar SSH Tunnel (recomendado)');
        console.log('   2. Contatar suporte Hostinger');
        console.log('   3. Configurar hosts remotos no painel MySQL');
    } else {
        console.log(`✅ ${sucessos} configuração(ões) funcionaram!`);
        console.log('   Use a configuração que funcionou no seu backend.');
    }
    
    console.log('\n📞 Próximos Passos:');
    console.log('   1. Se nenhuma conexão funcionou, implemente SSH tunnel');
    console.log('   2. Verifique o arquivo SOLUCAO_DOCKER_IP.md');
    console.log('   3. Configure hosts remotos no phpMyAdmin');
    console.log('   4. Teste novamente após as configurações');
}

// Executar diagnóstico
if (require.main === module) {
    diagnosticarMySQL().catch(console.error);
}

module.exports = { diagnosticarMySQL, testMySQLConnection, CONFIGS };