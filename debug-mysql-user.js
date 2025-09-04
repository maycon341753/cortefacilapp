const mysql = require('mysql2/promise');

// Diferentes variações de usuário para testar
const userVariations = [
    'u690889028_mayconwender',
    'mayconwender',
    'u690889028_cortefacil',
    'cortefacil'
];

// Configurações base
const baseConfig = {
    host: 'srv973908.hstgr.cloud',
    port: 3306,
    password: 'Maycon341753@',
    database: 'u690889028_cortefacil',
    connectTimeout: 15000
};

async function testUserVariation(user) {
    console.log(`\n🔍 Testando usuário: ${user}`);
    console.log(`   Host: ${baseConfig.host}:${baseConfig.port}`);
    console.log(`   Database: ${baseConfig.database}`);
    
    try {
        const connection = await mysql.createConnection({
            ...baseConfig,
            user: user
        });
        
        console.log(`✅ SUCESSO! Usuário ${user} conectou com sucesso!`);
        
        // Testar consulta básica
        const [result] = await connection.execute('SELECT USER() as current_user, DATABASE() as current_db');
        console.log(`   Usuário atual: ${result[0].current_user}`);
        console.log(`   Banco atual: ${result[0].current_db}`);
        
        await connection.end();
        return true;
        
    } catch (error) {
        console.log(`❌ Falhou para ${user}:`);
        console.log(`   Código: ${error.code}`);
        console.log(`   Mensagem: ${error.message}`);
        return false;
    }
}

async function testWithoutDatabase() {
    console.log(`\n🔍 Testando conexão SEM especificar banco de dados`);
    console.log(`   Host: ${baseConfig.host}:${baseConfig.port}`);
    console.log(`   Usuário: u690889028_mayconwender`);
    
    try {
        const connection = await mysql.createConnection({
            host: baseConfig.host,
            port: baseConfig.port,
            user: 'u690889028_mayconwender',
            password: baseConfig.password,
            connectTimeout: 15000
            // Sem especificar database
        });
        
        console.log(`✅ SUCESSO! Conexão sem banco específico funcionou!`);
        
        // Verificar bancos disponíveis
        const [databases] = await connection.execute('SHOW DATABASES');
        console.log(`   Bancos disponíveis:`);
        databases.forEach((db, index) => {
            const dbName = Object.values(db)[0];
            console.log(`     ${index + 1}. ${dbName}`);
        });
        
        // Tentar usar o banco específico
        try {
            await connection.execute('USE u690889028_cortefacil');
            console.log(`   ✅ Conseguiu usar o banco u690889028_cortefacil`);
            
            const [tables] = await connection.execute('SHOW TABLES');
            console.log(`   📋 Tabelas no banco: ${tables.length}`);
            
        } catch (useError) {
            console.log(`   ❌ Erro ao usar banco: ${useError.message}`);
        }
        
        await connection.end();
        return true;
        
    } catch (error) {
        console.log(`❌ Falhou conexão sem banco:`);
        console.log(`   Código: ${error.code}`);
        console.log(`   Mensagem: ${error.message}`);
        return false;
    }
}

async function testDifferentHosts() {
    const hosts = [
        'srv973908.hstgr.cloud',
        '31.97.171.104', // IP direto se disponível
        'localhost' // Teste local
    ];
    
    console.log(`\n🌐 Testando diferentes hosts...`);
    
    for (const host of hosts) {
        console.log(`\n🔍 Testando host: ${host}`);
        
        try {
            const connection = await mysql.createConnection({
                host: host,
                port: 3306,
                user: 'u690889028_mayconwender',
                password: 'Maycon341753@',
                database: 'u690889028_cortefacil',
                connectTimeout: 10000
            });
            
            console.log(`✅ SUCESSO com host ${host}!`);
            await connection.end();
            return host;
            
        } catch (error) {
            console.log(`❌ Falhou com ${host}: ${error.code} - ${error.message}`);
        }
    }
    
    return null;
}

async function main() {
    console.log('🔧 DEBUG - Testando Variações de Usuário MySQL');
    console.log('=' .repeat(60));
    console.log('🎯 Objetivo: Encontrar a combinação correta de credenciais');
    console.log('📍 IP atual detectado pelo erro: 45.181.72.123');
    console.log('\n' + '='.repeat(60));
    
    let successCount = 0;
    
    // Testar diferentes usuários
    console.log('\n📋 TESTE 1: Variações de usuário');
    for (const user of userVariations) {
        const success = await testUserVariation(user);
        if (success) {
            successCount++;
            console.log(`\n🎉 ENCONTRADO! Usuário funcional: ${user}`);
            break;
        }
    }
    
    // Testar sem especificar banco
    console.log('\n📋 TESTE 2: Conexão sem banco específico');
    const withoutDbSuccess = await testWithoutDatabase();
    if (withoutDbSuccess) successCount++;
    
    // Testar diferentes hosts
    console.log('\n📋 TESTE 3: Diferentes hosts');
    const workingHost = await testDifferentHosts();
    if (workingHost) {
        successCount++;
        console.log(`\n🎉 ENCONTRADO! Host funcional: ${workingHost}`);
    }
    
    // Resumo final
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO DOS TESTES');
    console.log('=' .repeat(60));
    
    if (successCount > 0) {
        console.log(`✅ ${successCount} teste(s) bem-sucedido(s)!`);
        console.log('🎯 Problema identificado e solucionado!');
    } else {
        console.log('❌ Todos os testes falharam');
        console.log('\n💡 Possíveis causas:');
        console.log('   1. Configuração de hosts remotos ainda não propagou');
        console.log('   2. Usuário MySQL não existe ou está desabilitado');
        console.log('   3. Senha incorreta');
        console.log('   4. Banco de dados não existe');
        console.log('   5. Firewall ou proxy bloqueando conexão');
        
        console.log('\n🔧 Próximos passos recomendados:');
        console.log('   1. Aguardar 5-10 minutos para propagação');
        console.log('   2. Verificar no painel Hostinger se o usuário existe');
        console.log('   3. Confirmar se o banco u690889028_cortefacil foi criado');
        console.log('   4. Testar conexão via phpMyAdmin primeiro');
        console.log('   5. Contatar suporte Hostinger novamente');
    }
    
    console.log('\n🏁 Debug concluído');
}

// Executar debug
main().catch(error => {
    console.error('💥 Erro fatal no debug:', error.message);
    process.exit(1);
});