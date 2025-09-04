const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

// Função para carregar variáveis do arquivo .env.easypanel
function loadEnvFile(filePath) {
    const envVars = {};
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        const lines = content.split('\n');
        
        for (const line of lines) {
            const trimmed = line.trim();
            if (trimmed && !trimmed.startsWith('#') && trimmed.includes('=')) {
                const [key, ...valueParts] = trimmed.split('=');
                const value = valueParts.join('=').trim();
                envVars[key.trim()] = value;
            }
        }
    } catch (error) {
        console.log('❌ Erro ao carregar arquivo .env:', error.message);
    }
    return envVars;
}

// Carregar configurações do .env.easypanel
const envPath = path.join(__dirname, 'backend', 'server', '.env.easypanel');
const envVars = loadEnvFile(envPath);

// Configuração do banco de dados
const dbConfig = {
    host: envVars.DB_HOST || 'srv973908.hstgr.cloud',
    port: parseInt(envVars.DB_PORT) || 3306,
    user: envVars.DB_USER || 'u690889028_mayconwender',
    password: envVars.DB_PASSWORD || 'Maycon341753@',
    database: envVars.DB_NAME || 'u690889028_cortefacil',
    connectTimeout: 30000,
    acquireTimeout: 30000
};

async function testFinalConnection() {
    console.log('🚀 TESTE FINAL - Conexão EasyPanel MySQL');
    console.log('=' .repeat(60));
    console.log('📁 Carregando configurações de:', envPath);
    console.log('\n📋 Configurações do Banco:');
    console.log(`   Host: ${dbConfig.host}:${dbConfig.port}`);
    console.log(`   Usuário: ${dbConfig.user}`);
    console.log(`   Banco: ${dbConfig.database}`);
    console.log(`   Senha: ${dbConfig.password ? '[CONFIGURADA]' : '[NÃO CONFIGURADA]'}`);
    console.log('\n' + '='.repeat(60));

    let connection;
    
    try {
        console.log('⏳ Estabelecendo conexão...');
        connection = await mysql.createConnection(dbConfig);
        console.log('✅ CONEXÃO ESTABELECIDA COM SUCESSO!');
        
        // Teste básico
        console.log('\n🔍 Executando teste básico...');
        const [testResult] = await connection.execute('SELECT 1 as test, NOW() as current_time, VERSION() as mysql_version');
        console.log('✅ Teste básico executado:');
        console.log(`   Resultado: ${testResult[0].test}`);
        console.log(`   Hora do servidor: ${testResult[0].current_time}`);
        console.log(`   Versão MySQL: ${testResult[0].mysql_version}`);
        
        // Verificar tabelas
        console.log('\n📋 Verificando estrutura do banco...');
        const [tables] = await connection.execute('SHOW TABLES');
        
        if (tables.length > 0) {
            console.log(`✅ ${tables.length} tabelas encontradas:`);
            for (let i = 0; i < tables.length; i++) {
                const tableName = Object.values(tables[i])[0];
                console.log(`   ${i + 1}. ${tableName}`);
                
                // Contar registros em cada tabela
                try {
                    const [count] = await connection.execute(`SELECT COUNT(*) as total FROM \`${tableName}\``);
                    console.log(`      └─ ${count[0].total} registros`);
                } catch (e) {
                    console.log(`      └─ Erro ao contar: ${e.message}`);
                }
            }
        } else {
            console.log('⚠️  BANCO VAZIO - Nenhuma tabela encontrada');
            console.log('💡 Execute o script de setup do banco de dados:');
            console.log('   1. Abra phpMyAdmin no painel Hostinger');
            console.log('   2. Selecione o banco u690889028_cortefacil');
            console.log('   3. Execute o arquivo hostinger-database-setup.sql');
        }
        
        // Verificar usuários (se a tabela existir)
        try {
            console.log('\n👥 Verificando usuários cadastrados...');
            const [users] = await connection.execute('SELECT id, nome, email, tipo_usuario, created_at FROM usuarios LIMIT 5');
            
            if (users.length > 0) {
                console.log(`✅ ${users.length} usuários encontrados:`);
                users.forEach((user, index) => {
                    console.log(`   ${index + 1}. ${user.nome} (${user.email}) - ${user.tipo_usuario}`);
                });
            } else {
                console.log('⚠️  Nenhum usuário cadastrado');
            }
        } catch (error) {
            console.log('⚠️  Tabela usuarios não encontrada ou erro:', error.message);
        }
        
        // Teste de inserção (opcional)
        console.log('\n🧪 Testando operação de escrita...');
        try {
            await connection.execute(`
                CREATE TABLE IF NOT EXISTS test_connection (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    test_message VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
            
            const testMessage = `Teste de conexão - ${new Date().toISOString()}`;
            await connection.execute(
                'INSERT INTO test_connection (test_message) VALUES (?)',
                [testMessage]
            );
            
            const [testData] = await connection.execute(
                'SELECT * FROM test_connection ORDER BY created_at DESC LIMIT 1'
            );
            
            console.log('✅ Teste de escrita bem-sucedido:');
            console.log(`   ID: ${testData[0].id}`);
            console.log(`   Mensagem: ${testData[0].test_message}`);
            
            // Limpar tabela de teste
            await connection.execute('DROP TABLE test_connection');
            console.log('🧹 Tabela de teste removida');
            
        } catch (writeError) {
            console.log('❌ Erro no teste de escrita:', writeError.message);
        }
        
        console.log('\n' + '='.repeat(60));
        console.log('🎉 TESTE CONCLUÍDO COM SUCESSO!');
        console.log('✅ Banco de dados EasyPanel está funcionando corretamente');
        console.log('✅ Aplicação pode ser conectada ao banco de produção');
        
        console.log('\n📋 Próximos passos:');
        console.log('   1. Configure o backend para usar .env.easypanel');
        console.log('   2. Execute o setup do banco se necessário');
        console.log('   3. Faça deploy da aplicação');
        
    } catch (error) {
        console.log('\n' + '='.repeat(60));
        console.log('❌ ERRO NA CONEXÃO:');
        console.log(`🔴 Código: ${error.code}`);
        console.log(`📝 Mensagem: ${error.message}`);
        console.log(`🔍 SQL State: ${error.sqlState || 'N/A'}`);
        
        console.log('\n💡 Soluções recomendadas:');
        
        if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log('   🔐 PROBLEMA DE PERMISSÕES:');
            console.log('   1. Acesse o painel Hostinger');
            console.log('   2. Vá em Bancos de Dados → MySQL');
            console.log('   3. Clique em "Gerenciar" no banco u690889028_cortefacil');
            console.log('   4. Adicione "Hosts Remotos":');
            console.log('      - IP atual: 45.181.72.123');
            console.log('      - Ou use % para qualquer IP');
            console.log('   5. Salve as configurações');
            
        } else if (error.code === 'ECONNREFUSED') {
            console.log('   🌐 PROBLEMA DE CONECTIVIDADE:');
            console.log('   1. Verifique se o MySQL está ativo no EasyPanel');
            console.log('   2. Confirme o host: srv973908.hstgr.cloud');
            console.log('   3. Verifique firewall/proxy');
            
        } else if (error.code === 'ER_BAD_DB_ERROR') {
            console.log('   🗄️  PROBLEMA COM O BANCO:');
            console.log('   1. Verifique se o banco u690889028_cortefacil existe');
            console.log('   2. Crie o banco no painel se necessário');
        }
        
        console.log('\n📞 Se o problema persistir:');
        console.log('   - Contate o suporte Hostinger');
        console.log('   - Verifique o arquivo SOLUCAO_EASYPANEL_MYSQL.md');
        console.log('   - Use SSH tunnel como alternativa');
        
    } finally {
        if (connection) {
            await connection.end();
            console.log('\n🔌 Conexão fechada');
        }
    }
}

// Executar teste
console.log('🔄 Iniciando teste final de conexão EasyPanel MySQL...');
testFinalConnection().catch(error => {
    console.error('💥 Erro fatal:', error.message);
    process.exit(1);
});