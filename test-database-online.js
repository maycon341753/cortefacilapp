const mysql = require('mysql2/promise');
const dotenv = require('dotenv');
const path = require('path');

// Carregar configurações do EasyPanel
dotenv.config({ path: path.join(__dirname, 'backend', 'server', '.env.easypanel') });

console.log('🔍 Testando Conectividade com Banco de Dados Online');
console.log('=' .repeat(60));

// Configurações do banco
const dbConfig = {
    host: process.env.DB_HOST,
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    connectTimeout: 30000,
    acquireTimeout: 30000,
    timeout: 30000
};

console.log('📋 Configurações do Banco:');
console.log(`   Host: ${dbConfig.host}`);
console.log(`   Port: ${dbConfig.port}`);
console.log(`   User: ${dbConfig.user}`);
console.log(`   Database: ${dbConfig.database}`);
console.log(`   Password: ${dbConfig.password ? '***' : 'VAZIO'}`);
console.log('');

async function testDatabaseConnection() {
    let connection = null;
    
    try {
        console.log('🔌 Tentando conectar ao banco de dados...');
        
        connection = await mysql.createConnection(dbConfig);
        
        console.log('✅ Conexão estabelecida com sucesso!');
        
        // Testar consulta básica
        console.log('\n📊 Testando consultas básicas...');
        
        // Verificar versão do MySQL
        const [versionResult] = await connection.execute('SELECT VERSION() as version');
        console.log(`   MySQL Version: ${versionResult[0].version}`);
        
        // Listar tabelas
        const [tablesResult] = await connection.execute('SHOW TABLES');
        console.log(`   Tabelas encontradas: ${tablesResult.length}`);
        
        if (tablesResult.length > 0) {
            console.log('   📋 Lista de tabelas:');
            tablesResult.forEach((table, index) => {
                const tableName = Object.values(table)[0];
                console.log(`      ${index + 1}. ${tableName}`);
            });
        }
        
        // Verificar tabela usuarios se existir
        const [userTableCheck] = await connection.execute(
            "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = 'usuarios'",
            [dbConfig.database]
        );
        
        if (userTableCheck[0].count > 0) {
            console.log('\n👥 Verificando tabela usuarios...');
            const [userCount] = await connection.execute('SELECT COUNT(*) as count FROM usuarios');
            console.log(`   Total de usuários: ${userCount[0].count}`);
            
            if (userCount[0].count > 0) {
                const [users] = await connection.execute('SELECT id, nome, email, created_at FROM usuarios LIMIT 5');
                console.log('   📋 Primeiros usuários:');
                users.forEach((user, index) => {
                    console.log(`      ${index + 1}. ${user.nome} (${user.email}) - ${user.created_at}`);
                });
            }
        } else {
            console.log('\n⚠️  Tabela usuarios não encontrada');
        }
        
        console.log('\n✅ Teste de conectividade concluído com sucesso!');
        
    } catch (error) {
        console.error('\n❌ Erro na conexão com o banco de dados:');
        console.error(`   Tipo: ${error.code || 'UNKNOWN'}`);
        console.error(`   Mensagem: ${error.message}`);
        
        if (error.code === 'ECONNREFUSED') {
            console.error('\n💡 Possíveis soluções:');
            console.error('   1. Verificar se o servidor MySQL está rodando');
            console.error('   2. Verificar se o host e porta estão corretos');
            console.error('   3. Verificar configurações de firewall');
        } else if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.error('\n💡 Possíveis soluções:');
            console.error('   1. Verificar usuário e senha');
            console.error('   2. Verificar permissões do usuário no banco');
            console.error('   3. Verificar se o usuário pode conectar do host atual');
        } else if (error.code === 'ER_BAD_DB_ERROR') {
            console.error('\n💡 Possíveis soluções:');
            console.error('   1. Verificar se o banco de dados existe');
            console.error('   2. Verificar se o nome do banco está correto');
        }
        
    } finally {
        if (connection) {
            await connection.end();
            console.log('\n🔌 Conexão fechada.');
        }
    }
}

// Executar teste
testDatabaseConnection().catch(console.error);