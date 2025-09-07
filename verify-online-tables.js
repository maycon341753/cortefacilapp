const mysql = require('mysql2/promise');
require('dotenv').config({ path: './backend/server/.env.easypanel' });

async function verifyOnlineTables() {
    console.log('🔍 Verificando tabelas no banco de dados online EasyPanel...');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        console.log('✅ Conectado ao banco:', process.env.DB_NAME);
        
        // Listar todas as tabelas
        const [tables] = await connection.execute('SHOW TABLES');
        console.log('\n📋 Tabelas encontradas:');
        tables.forEach((table, index) => {
            const tableName = Object.values(table)[0];
            console.log(`${index + 1}. ${tableName}`);
        });

        // Tabelas esperadas para o sistema de cadastro
        const expectedTables = [
            'usuarios',
            'agendamentos', 
            'servicos',
            'horarios_disponiveis',
            'password_resets'
        ];

        console.log('\n🎯 Verificando tabelas essenciais para cadastro:');
        const missingTables = [];
        
        for (const expectedTable of expectedTables) {
            const tableExists = tables.some(table => 
                Object.values(table)[0] === expectedTable
            );
            
            if (tableExists) {
                console.log(`✅ ${expectedTable} - OK`);
                
                // Verificar estrutura da tabela usuarios (essencial para cadastro)
                if (expectedTable === 'usuarios') {
                    const [columns] = await connection.execute(`DESCRIBE ${expectedTable}`);
                    console.log(`   📊 Colunas da tabela usuarios:`);
                    columns.forEach(col => {
                        console.log(`      - ${col.Field} (${col.Type})`);
                    });
                }
            } else {
                console.log(`❌ ${expectedTable} - FALTANDO`);
                missingTables.push(expectedTable);
            }
        }

        // Verificar se existe usuário admin
        if (tables.some(table => Object.values(table)[0] === 'usuarios')) {
            const [adminUsers] = await connection.execute(
                "SELECT id, nome, email, tipo_usuario FROM usuarios WHERE tipo_usuario = 'admin' LIMIT 5"
            );
            
            console.log('\n👤 Usuários admin encontrados:');
            if (adminUsers.length > 0) {
                adminUsers.forEach(user => {
                    console.log(`   - ID: ${user.id}, Nome: ${user.nome}, Email: ${user.email}`);
                });
            } else {
                console.log('   ⚠️  Nenhum usuário admin encontrado');
            }
        }

        // Resumo final
        console.log('\n📊 RESUMO DA VERIFICAÇÃO:');
        console.log(`✅ Tabelas encontradas: ${tables.length}`);
        console.log(`✅ Tabelas essenciais OK: ${expectedTables.length - missingTables.length}/${expectedTables.length}`);
        
        if (missingTables.length > 0) {
            console.log(`❌ Tabelas faltando: ${missingTables.join(', ')}`);
            console.log('\n⚠️  AÇÃO NECESSÁRIA: Executar script de criação do banco de dados');
        } else {
            console.log('✅ Todas as tabelas essenciais estão presentes!');
            console.log('✅ Sistema de cadastro deve funcionar corretamente');
        }

    } catch (error) {
        console.error('❌ Erro ao verificar tabelas:', error.message);
    } finally {
        await connection.end();
    }
}

verifyOnlineTables().catch(console.error);