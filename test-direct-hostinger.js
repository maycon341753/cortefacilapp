const mysql = require('mysql2/promise');

// Configurações originais do Hostinger
const HOSTINGER_CONFIG = {
    host: 'srv973908.hstgr.cloud',
    port: 3306,
    user: 'u690889028_mayconwender',
    password: 'Maycon341753',
    database: 'u690889028_cortefacil'
};

// Configurações alternativas para teste
const ALT_CONFIGS = [
    {
        name: 'Hostinger Direto',
        config: HOSTINGER_CONFIG
    },
    {
        name: 'Hostinger sem Database',
        config: {
            ...HOSTINGER_CONFIG,
            database: undefined
        }
    },
    {
        name: 'Hostinger com timeout maior',
        config: {
            ...HOSTINGER_CONFIG,
            connectTimeout: 30000,
            acquireTimeout: 30000
        }
    }
];

async function testConnection(name, config) {
    console.log(`\n🔍 Testando: ${name}`);
    console.log(`   Host: ${config.host}:${config.port}`);
    console.log(`   User: ${config.user}`);
    console.log(`   Database: ${config.database || 'N/A'}`);
    
    try {
        const connection = await mysql.createConnection(config);
        console.log('✅ Conexão estabelecida!');
        
        // Teste básico
        const [result] = await connection.execute('SELECT 1 as test, NOW() as server_time, USER() as current_user');
        console.log('✅ Query executada:', result[0]);
        
        // Verificar privilégios
        try {
            const [privileges] = await connection.execute('SHOW GRANTS');
            console.log('📋 Privilégios do usuário:');
            privileges.forEach((grant, index) => {
                console.log(`   ${index + 1}. ${Object.values(grant)[0]}`);
            });
        } catch (privError) {
            console.log('⚠️  Não foi possível verificar privilégios:', privError.message);
        }
        
        // Se conectou a um database, verificar tabelas
        if (config.database) {
            try {
                const [tables] = await connection.execute('SHOW TABLES');
                console.log(`📊 Tabelas encontradas: ${tables.length}`);
                if (tables.length > 0) {
                    console.log('   Primeiras 5 tabelas:');
                    tables.slice(0, 5).forEach((table, index) => {
                        console.log(`   ${index + 1}. ${Object.values(table)[0]}`);
                    });
                }
            } catch (tableError) {
                console.log('⚠️  Erro ao listar tabelas:', tableError.message);
            }
        }
        
        await connection.end();
        return true;
        
    } catch (error) {
        console.log('❌ Erro na conexão:', error.message);
        console.log('   Código:', error.code);
        console.log('   Estado SQL:', error.sqlState);
        
        // Análise do erro
        if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log('\n🔍 Análise do erro de acesso:');
            console.log('   - O usuário existe mas não tem permissão para conectar deste IP');
            console.log('   - Possíveis soluções:');
            console.log('     1. Configurar usuário para aceitar conexões de qualquer IP (%)'); 
            console.log('     2. Adicionar o IP específico nas permissões do usuário');
            console.log('     3. Usar SSH tunnel (recomendado)');
            console.log('     4. Contatar suporte Hostinger para liberar IP');
        } else if (error.code === 'ECONNREFUSED') {
            console.log('\n🔍 Análise do erro de conexão:');
            console.log('   - Servidor MySQL não está aceitando conexões na porta 3306');
            console.log('   - Firewall pode estar bloqueando');
            console.log('   - Servidor pode estar offline');
        } else if (error.code === 'ENOTFOUND') {
            console.log('\n🔍 Análise do erro DNS:');
            console.log('   - Hostname não foi encontrado');
            console.log('   - Problema de DNS ou hostname incorreto');
        }
        
        return false;
    }
}

async function main() {
    console.log('🚀 Testando conexões diretas com Hostinger MySQL');
    console.log('=' .repeat(60));
    
    let successCount = 0;
    
    for (const { name, config } of ALT_CONFIGS) {
        const success = await testConnection(name, config);
        if (success) successCount++;
        
        // Aguardar um pouco entre testes
        await new Promise(resolve => setTimeout(resolve, 1000));
    }
    
    console.log('\n' + '='.repeat(60));
    console.log(`📊 Resultado: ${successCount}/${ALT_CONFIGS.length} testes bem-sucedidos`);
    
    if (successCount === 0) {
        console.log('\n❌ Nenhuma conexão funcionou');
        console.log('\n💡 Próximos passos recomendados:');
        console.log('   1. Verificar se as credenciais estão corretas');
        console.log('   2. Contatar suporte Hostinger para:');
        console.log('      - Verificar se MySQL está ativo');
        console.log('      - Liberar seu IP para conexões remotas');
        console.log('      - Confirmar configurações do usuário');
        console.log('   3. Usar phpMyAdmin para verificar usuário e permissões');
        console.log('   4. Considerar usar SSH tunnel como solução temporária');
    } else {
        console.log('\n✅ Pelo menos uma conexão funcionou!');
        console.log('   Você pode usar essas configurações no seu aplicativo.');
    }
}

if (require.main === module) {
    main().catch(error => {
        console.error('❌ Erro fatal:', error.message);
        process.exit(1);
    });
}