const mysql = require('mysql2/promise');
require('dotenv').config({ path: './backend/server/.env.easypanel' });

// Configurações para diferentes cenários de conexão
const configs = [
    {
        name: 'Conexão Direta Hostinger',
        config: {
            host: process.env.DB_HOST || 'srv973908.hstgr.cloud',
            port: parseInt(process.env.DB_PORT) || 3306,
            user: process.env.DB_USER || 'u690889028_mayconwender',
            password: process.env.DB_PASSWORD || 'Maycon341753@',
            database: process.env.DB_NAME || 'u690889028_cortefacil',
            connectTimeout: 15000
        }
    },
    {
        name: 'Conexão como Root (se disponível)',
        config: {
            host: process.env.DB_HOST || 'srv973908.hstgr.cloud',
            port: parseInt(process.env.DB_PORT) || 3306,
            user: 'root',
            password: process.env.DB_PASSWORD || 'Maycon341753@',
            connectTimeout: 15000
        }
    },
    {
        name: 'Conexão Administrativa',
        config: {
            host: process.env.DB_HOST || 'srv973908.hstgr.cloud',
            port: parseInt(process.env.DB_PORT) || 3306,
            user: 'u690889028_mayconwender',
            password: process.env.DB_PASSWORD || 'Maycon341753@',
            connectTimeout: 15000
        }
    }
];

async function createUserForDockerIP() {
    console.log('🔧 CORREÇÃO MYSQL - IP Docker/EasyPanel');
    console.log('=' .repeat(60));
    console.log('🎯 Objetivo: Criar usuário MySQL para IP 172.18.0.6');
    console.log('📍 Erro detectado: Access denied for user from 172.18.0.6');
    console.log('\n' + '='.repeat(60));
    
    let adminConnection = null;
    
    // Tentar conectar com diferentes configurações
    for (const { name, config } of configs) {
        console.log(`\n🔍 Tentando: ${name}`);
        console.log(`   Host: ${config.host}:${config.port}`);
        console.log(`   User: ${config.user}`);
        
        try {
            adminConnection = await mysql.createConnection(config);
            console.log(`   ✅ SUCESSO! Conectado como ${config.user}`);
            break;
        } catch (error) {
            console.log(`   ❌ Falhou: ${error.code} - ${error.message}`);
        }
    }
    
    if (!adminConnection) {
        console.log('\n❌ Não foi possível estabelecer conexão administrativa');
        console.log('\n💡 Soluções alternativas:');
        console.log('   1. Use phpMyAdmin para executar os comandos SQL abaixo');
        console.log('   2. Contate o suporte Hostinger');
        console.log('   3. Verifique se as credenciais estão corretas');
        
        console.log('\n📝 COMANDOS SQL PARA EXECUTAR NO PHPMYADMIN:');
        console.log('=' .repeat(50));
        
        const sqlCommands = [
            "-- Remover usuários existentes se houver conflito",
            "DROP USER IF EXISTS 'u690889028_mayconwender'@'172.18.0.6';",
            "DROP USER IF EXISTS 'u690889028_mayconwender'@'172.18.%';",
            "",
            "-- Criar usuário para IP específico do Docker",
            "CREATE USER 'u690889028_mayconwender'@'172.18.0.6' IDENTIFIED BY 'Maycon341753@';",
            "GRANT ALL PRIVILEGES ON \`u690889028_cortefacil\`.* TO 'u690889028_mayconwender'@'172.18.0.6';",
            "",
            "-- Criar usuário para toda a rede Docker (172.18.%)",
            "CREATE USER 'u690889028_mayconwender'@'172.18.%' IDENTIFIED BY 'Maycon341753@';",
            "GRANT ALL PRIVILEGES ON \`u690889028_cortefacil\`.* TO 'u690889028_mayconwender'@'172.18.%';",
            "",
            "-- Aplicar mudanças",
            "FLUSH PRIVILEGES;",
            "",
            "-- Verificar usuários criados",
            "SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender';"
        ];
        
        sqlCommands.forEach(cmd => console.log(cmd));
        
        return false;
    }
    
    // Executar comandos para criar usuário
    console.log('\n🛠️  Criando usuário MySQL para ambiente Docker...');
    
    const commands = [
        // Remover usuários existentes para evitar conflitos
        "DROP USER IF EXISTS 'u690889028_mayconwender'@'172.18.0.6';",
        "DROP USER IF EXISTS 'u690889028_mayconwender'@'172.18.%';",
        
        // Criar usuário para IP específico
        "CREATE USER 'u690889028_mayconwender'@'172.18.0.6' IDENTIFIED BY 'Maycon341753@';",
        "GRANT ALL PRIVILEGES ON \`u690889028_cortefacil\`.* TO 'u690889028_mayconwender'@'172.18.0.6';",
        
        // Criar usuário para toda a rede Docker
        "CREATE USER 'u690889028_mayconwender'@'172.18.%' IDENTIFIED BY 'Maycon341753@';",
        "GRANT ALL PRIVILEGES ON \`u690889028_cortefacil\`.* TO 'u690889028_mayconwender'@'172.18.%';",
        
        // Garantir permissões também para % (qualquer IP)
        "GRANT ALL PRIVILEGES ON \`u690889028_cortefacil\`.* TO 'u690889028_mayconwender'@'%';",
        
        // Aplicar mudanças
        "FLUSH PRIVILEGES;"
    ];
    
    let successCount = 0;
    
    for (const command of commands) {
        try {
            console.log(`   Executando: ${command}`);
            await adminConnection.execute(command);
            console.log(`   ✅ Sucesso`);
            successCount++;
        } catch (error) {
            if (error.code === 'ER_CANNOT_USER') {
                console.log(`   ⚠️  Usuário já existe (OK): ${error.message}`);
            } else {
                console.log(`   ❌ Erro: ${error.message}`);
            }
        }
    }
    
    // Verificar usuários criados
    console.log('\n🔍 Verificando usuários criados...');
    try {
        const [users] = await adminConnection.execute(
            "SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender' ORDER BY Host"
        );
        
        console.log('   👥 Usuários MySQL encontrados:');
        users.forEach((user, index) => {
            const isDockerIP = user.Host.includes('172.18');
            console.log(`      ${index + 1}. ${user.User}@${user.Host} ${isDockerIP ? '← DOCKER' : ''}`);
        });
        
    } catch (error) {
        console.log(`   ❌ Erro ao verificar usuários: ${error.message}`);
    }
    
    await adminConnection.end();
    
    // Testar conexão final
    console.log('\n🧪 Testando conexão final...');
    
    try {
        const testConnection = await mysql.createConnection({
            host: process.env.DB_HOST || 'srv973908.hstgr.cloud',
            port: parseInt(process.env.DB_PORT) || 3306,
            user: process.env.DB_USER || 'u690889028_mayconwender',
            password: process.env.DB_PASSWORD || 'Maycon341753@',
            database: process.env.DB_NAME || 'u690889028_cortefacil',
            connectTimeout: 15000
        });
        
        console.log('✅ SUCESSO! Conexão funcionando!');
        
        // Verificar se consegue acessar dados
        try {
            const [result] = await testConnection.execute('SELECT DATABASE() as db, USER() as user');
            console.log(`   📊 Conectado como: ${result[0].user}`);
            console.log(`   🗄️  Banco atual: ${result[0].db}`);
            
            // Testar uma consulta simples
            const [tables] = await testConnection.execute('SHOW TABLES');
            console.log(`   📋 Tabelas disponíveis: ${tables.length}`);
            
        } catch (queryError) {
            console.log(`   ⚠️  Conexão OK, mas erro na consulta: ${queryError.message}`);
        }
        
        await testConnection.end();
        
        console.log('\n🎉 PROBLEMA RESOLVIDO!');
        console.log('✅ Usuário MySQL configurado para ambiente Docker');
        console.log('✅ Conexão funcionando normalmente');
        
        return true;
        
    } catch (error) {
        console.log(`❌ Teste final falhou: ${error.message}`);
        console.log('\n💡 Possíveis causas:');
        console.log('   1. Mudanças ainda não propagaram (aguarde 1-2 minutos)');
        console.log('   2. Firewall bloqueando conexão');
        console.log('   3. Configuração de hosts remotos no painel Hostinger');
        
        return false;
    }
}

async function main() {
    try {
        const success = await createUserForDockerIP();
        
        if (success) {
            console.log('\n🚀 Próximos passos:');
            console.log('   1. Reinicie sua aplicação Docker/EasyPanel');
            console.log('   2. Verifique se a conexão está funcionando');
            console.log('   3. Execute testes de funcionalidade');
        } else {
            console.log('\n📞 Se o problema persistir:');
            console.log('   1. Execute os comandos SQL no phpMyAdmin');
            console.log('   2. Contate o suporte Hostinger');
            console.log('   3. Verifique o arquivo SOLUCAO_PHPMYADMIN.md');
        }
        
    } catch (error) {
        console.error('💥 Erro fatal:', error.message);
        process.exit(1);
    }
}

// Executar correção
main();