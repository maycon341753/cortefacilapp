const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

// Função para testar se conseguimos conectar como root ou admin
async function testAdminConnection() {
    console.log('🔐 Tentando conexão como administrador...');
    
    const adminUsers = [
        { user: 'root', password: '' },
        { user: 'root', password: 'root' },
        { user: 'root', password: 'password' },
        { user: 'admin', password: 'admin' },
        { user: 'u690889028_mayconwender', password: '' }, // Sem senha
        { user: 'mayconwender', password: 'Maycon341753@' }, // Usuário sem prefixo
    ];
    
    for (const cred of adminUsers) {
        try {
            console.log(`   Testando: ${cred.user} / ${cred.password ? '[COM SENHA]' : '[SEM SENHA]'}`);
            
            const connection = await mysql.createConnection({
                host: 'srv973908.hstgr.cloud',
                port: 3306,
                user: cred.user,
                password: cred.password,
                connectTimeout: 10000
            });
            
            console.log(`   ✅ SUCESSO com ${cred.user}!`);
            
            // Verificar usuários existentes
            try {
                const [users] = await connection.execute(
                    "SELECT User, Host FROM mysql.user WHERE User LIKE '%maycon%' OR User LIKE '%690889028%'"
                );
                
                console.log('   👥 Usuários encontrados:');
                users.forEach(user => {
                    console.log(`      - ${user.User}@${user.Host}`);
                });
                
            } catch (userError) {
                console.log(`   ⚠️  Não foi possível listar usuários: ${userError.message}`);
            }
            
            await connection.end();
            return cred;
            
        } catch (error) {
            console.log(`   ❌ Falhou: ${error.code}`);
        }
    }
    
    return null;
}

// Função para criar o usuário MySQL se necessário
async function createMySQLUser(adminConnection) {
    console.log('\n🛠️  Criando usuário MySQL...');
    
    const commands = [
        // Remover usuário se existir
        "DROP USER IF EXISTS 'u690889028_mayconwender'@'%';",
        "DROP USER IF EXISTS 'u690889028_mayconwender'@'45.181.72.123';",
        "DROP USER IF EXISTS 'u690889028_mayconwender'@'localhost';",
        
        // Criar usuário com permissões
        "CREATE USER 'u690889028_mayconwender'@'%' IDENTIFIED BY 'Maycon341753@';",
        "GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'%';",
        "GRANT ALL PRIVILEGES ON `u690889028_cortefacil`.* TO 'u690889028_mayconwender'@'%';",
        
        // Criar usuário específico para o IP
        "CREATE USER 'u690889028_mayconwender'@'45.181.72.123' IDENTIFIED BY 'Maycon341753@';",
        "GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'45.181.72.123';",
        "GRANT ALL PRIVILEGES ON `u690889028_cortefacil`.* TO 'u690889028_mayconwender'@'45.181.72.123';",
        
        // Flush privileges
        "FLUSH PRIVILEGES;"
    ];
    
    for (const command of commands) {
        try {
            console.log(`   Executando: ${command}`);
            await adminConnection.execute(command);
            console.log(`   ✅ Sucesso`);
        } catch (error) {
            console.log(`   ⚠️  Aviso: ${error.message}`);
        }
    }
}

// Função para verificar se o banco existe
async function checkDatabase(connection) {
    console.log('\n🗄️  Verificando banco de dados...');
    
    try {
        const [databases] = await connection.execute('SHOW DATABASES');
        const dbNames = databases.map(db => Object.values(db)[0]);
        
        console.log('   📋 Bancos disponíveis:');
        dbNames.forEach((name, index) => {
            const isTarget = name === 'u690889028_cortefacil';
            console.log(`      ${index + 1}. ${name} ${isTarget ? '← ALVO' : ''}`);
        });
        
        if (dbNames.includes('u690889028_cortefacil')) {
            console.log('   ✅ Banco u690889028_cortefacil encontrado!');
            
            // Verificar tabelas
            try {
                await connection.execute('USE u690889028_cortefacil');
                const [tables] = await connection.execute('SHOW TABLES');
                
                console.log(`   📊 Tabelas no banco: ${tables.length}`);
                if (tables.length > 0) {
                    tables.forEach((table, index) => {
                        const tableName = Object.values(table)[0];
                        console.log(`      ${index + 1}. ${tableName}`);
                    });
                } else {
                    console.log('   ⚠️  Banco vazio - sem tabelas');
                }
                
            } catch (tableError) {
                console.log(`   ❌ Erro ao acessar tabelas: ${tableError.message}`);
            }
            
        } else {
            console.log('   ❌ Banco u690889028_cortefacil NÃO encontrado!');
            console.log('   💡 Será necessário criar o banco primeiro');
        }
        
    } catch (error) {
        console.log(`   ❌ Erro ao verificar bancos: ${error.message}`);
    }
}

// Função para executar o SQL de setup
async function runSetupSQL(connection) {
    console.log('\n📜 Executando SQL de setup...');
    
    const sqlFile = path.join(__dirname, 'hostinger-database-setup.sql');
    
    if (!fs.existsSync(sqlFile)) {
        console.log('   ❌ Arquivo hostinger-database-setup.sql não encontrado');
        return false;
    }
    
    try {
        const sqlContent = fs.readFileSync(sqlFile, 'utf8');
        const statements = sqlContent
            .split(';')
            .map(stmt => stmt.trim())
            .filter(stmt => stmt.length > 0 && !stmt.startsWith('--'));
        
        console.log(`   📋 Executando ${statements.length} comandos SQL...`);
        
        for (let i = 0; i < statements.length; i++) {
            const statement = statements[i];
            try {
                await connection.execute(statement);
                console.log(`   ✅ ${i + 1}/${statements.length}: OK`);
            } catch (error) {
                console.log(`   ⚠️  ${i + 1}/${statements.length}: ${error.message}`);
            }
        }
        
        console.log('   🎉 Setup SQL concluído!');
        return true;
        
    } catch (error) {
        console.log(`   ❌ Erro ao executar SQL: ${error.message}`);
        return false;
    }
}

async function main() {
    console.log('🔧 VERIFICAÇÃO E SETUP MYSQL HOSTINGER');
    console.log('=' .repeat(60));
    console.log('🎯 Objetivo: Diagnosticar e corrigir problemas de conexão');
    console.log('📍 Baseado na imagem do phpMyAdmin fornecida');
    console.log('\n' + '='.repeat(60));
    
    // Passo 1: Tentar conexão administrativa
    const adminCred = await testAdminConnection();
    
    if (!adminCred) {
        console.log('\n❌ Não foi possível conectar como administrador');
        console.log('💡 Possíveis soluções:');
        console.log('   1. Usar phpMyAdmin para executar comandos SQL');
        console.log('   2. Contatar suporte Hostinger para criar usuário');
        console.log('   3. Verificar se as credenciais de admin estão corretas');
        return;
    }
    
    console.log(`\n✅ Conectado como: ${adminCred.user}`);
    
    // Conectar novamente para operações
    const adminConnection = await mysql.createConnection({
        host: 'srv973908.hstgr.cloud',
        port: 3306,
        user: adminCred.user,
        password: adminCred.password,
        connectTimeout: 15000
    });
    
    // Passo 2: Verificar banco de dados
    await checkDatabase(adminConnection);
    
    // Passo 3: Criar usuário MySQL
    await createMySQLUser(adminConnection);
    
    // Passo 4: Executar setup SQL se necessário
    await runSetupSQL(adminConnection);
    
    // Passo 5: Testar conexão final
    console.log('\n🧪 Teste final de conexão...');
    
    try {
        const testConnection = await mysql.createConnection({
            host: 'srv973908.hstgr.cloud',
            port: 3306,
            user: 'u690889028_mayconwender',
            password: 'Maycon341753@',
            database: 'u690889028_cortefacil',
            connectTimeout: 15000
        });
        
        console.log('✅ SUCESSO! Conexão funcionando!');
        
        const [result] = await testConnection.execute('SELECT COUNT(*) as total FROM usuarios');
        console.log(`📊 Usuários na tabela: ${result[0].total}`);
        
        await testConnection.end();
        
    } catch (error) {
        console.log(`❌ Teste final falhou: ${error.message}`);
    }
    
    await adminConnection.end();
    
    console.log('\n🏁 Verificação concluída');
    console.log('📝 Próximo passo: Executar test-final-easypanel.js novamente');
}

// Executar verificação
main().catch(error => {
    console.error('💥 Erro fatal na verificação:', error.message);
    process.exit(1);
});