#!/usr/bin/env node
/**
 * Script para Resolver IPs Docker Dinâmicos
 * Automatiza a criação de usuários MySQL para novos IPs Docker
 */

const { exec } = require('child_process');
const mysql = require('mysql2/promise');

// Configurações do servidor MySQL
const MYSQL_CONFIG = {
    host: 'srv973908.hstgr.cloud',
    user: 'root',
    password: '', // Senha do root (se houver)
    port: 3306
};

// Configurações do usuário da aplicação
const APP_USER = {
    username: 'u690889028_mayconwender',
    password: 'Maycon@2024',
    database: 'u690889028_cortefacil'
};

/**
 * Extrai o IP do erro de conexão
 */
function extractIPFromError(errorMessage) {
    const ipMatch = errorMessage.match(/'([^']+)'@'(\d+\.\d+\.\d+\.\d+)'/)
    return ipMatch ? ipMatch[2] : null;
}

/**
 * Cria usuário MySQL para um IP específico
 */
async function createUserForIP(ip) {
    console.log(`🔧 Criando usuário para IP: ${ip}`);
    
    try {
        const connection = await mysql.createConnection(MYSQL_CONFIG);
        
        // Criar usuário para o IP específico
        await connection.execute(
            `CREATE USER IF NOT EXISTS '${APP_USER.username}'@'${ip}' IDENTIFIED BY '${APP_USER.password}'`
        );
        
        // Conceder permissões
        await connection.execute(
            `GRANT ALL PRIVILEGES ON ${APP_USER.database}.* TO '${APP_USER.username}'@'${ip}'`
        );
        
        // Aplicar mudanças
        await connection.execute('FLUSH PRIVILEGES');
        
        await connection.end();
        
        console.log(`✅ Usuário criado com sucesso para IP: ${ip}`);
        return true;
        
    } catch (error) {
        console.error(`❌ Erro ao criar usuário para IP ${ip}:`, error.message);
        return false;
    }
}

/**
 * Lista todos os usuários existentes
 */
async function listExistingUsers() {
    try {
        const connection = await mysql.createConnection(MYSQL_CONFIG);
        
        const [rows] = await connection.execute(
            `SELECT user, host FROM mysql.user WHERE user = '${APP_USER.username}' ORDER BY host`
        );
        
        await connection.end();
        
        console.log('📋 Usuários existentes:');
        rows.forEach(row => {
            console.log(`   - ${row.user}@${row.host}`);
        });
        
        return rows;
        
    } catch (error) {
        console.error('❌ Erro ao listar usuários:', error.message);
        return [];
    }
}

/**
 * Função principal
 */
async function main() {
    console.log('🚀 Script de Correção de IP Docker Dinâmico');
    console.log('==========================================\n');
    
    // Verificar argumentos da linha de comando
    const args = process.argv.slice(2);
    
    if (args.length === 0) {
        console.log('📋 Uso:');
        console.log('   node fix-docker-ip-dynamic.js <IP>');
        console.log('   node fix-docker-ip-dynamic.js --list');
        console.log('   node fix-docker-ip-dynamic.js --auto "<error_message>"');
        console.log('');
        console.log('📝 Exemplos:');
        console.log('   node fix-docker-ip-dynamic.js 172.18.0.11');
        console.log('   node fix-docker-ip-dynamic.js --list');
        console.log('   node fix-docker-ip-dynamic.js --auto "Access denied for user \'u690889028_mayconwender\'@\'172.18.0.11\'"');
        return;
    }
    
    if (args[0] === '--list') {
        await listExistingUsers();
        return;
    }
    
    if (args[0] === '--auto' && args[1]) {
        const ip = extractIPFromError(args[1]);
        if (ip) {
            console.log(`🔍 IP extraído do erro: ${ip}`);
            await createUserForIP(ip);
        } else {
            console.error('❌ Não foi possível extrair o IP da mensagem de erro');
        }
        return;
    }
    
    // IP fornecido diretamente
    const ip = args[0];
    if (!/^\d+\.\d+\.\d+\.\d+$/.test(ip)) {
        console.error('❌ IP inválido. Use o formato: 192.168.1.1');
        return;
    }
    
    await createUserForIP(ip);
    
    console.log('\n📝 Próximos passos:');
    console.log('   1. Reinicie a aplicação Docker');
    console.log('   2. Teste a conexão novamente');
    console.log('   3. Se o IP mudar novamente, execute este script com o novo IP');
}

// Executar se chamado diretamente
if (require.main === module) {
    main().catch(console.error);
}

module.exports = {
    createUserForIP,
    extractIPFromError,
    listExistingUsers
};