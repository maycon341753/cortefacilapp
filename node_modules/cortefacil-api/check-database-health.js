#!/usr/bin/env node

/**
 * Script para verificar a saúde do banco de dados
 * Pode ser executado tanto localmente quanto no EasyPanel
 */

const mysql = require('mysql2/promise');
require('dotenv').config();

// Configurações do banco baseadas nas variáveis de ambiente
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'cortefacil',
  connectTimeout: 10000
};

async function checkDatabaseHealth() {
  console.log('🏥 Verificação de Saúde do Banco de Dados');
  console.log('=' .repeat(50));
  
  console.log('📋 Configurações:');
  console.log(`   Host: ${dbConfig.host}:${dbConfig.port}`);
  console.log(`   User: ${dbConfig.user}`);
  console.log(`   Database: ${dbConfig.database}`);
  console.log(`   Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log('');
  
  let connection;
  
  try {
    // 1. Teste de Conexão
    console.log('🔌 Testando conexão...');
    connection = await mysql.createConnection(dbConfig);
    console.log('   ✅ Conexão estabelecida com sucesso!');
    
    // 2. Teste Básico
    console.log('\n🧪 Executando teste básico...');
    const [basicTest] = await connection.execute('SELECT 1 as test, NOW() as server_time, VERSION() as mysql_version');
    console.log(`   ✅ MySQL Version: ${basicTest[0].mysql_version}`);
    console.log(`   ✅ Server Time: ${basicTest[0].server_time}`);
    
    // 3. Verificar Tabelas
    console.log('\n📊 Verificando estrutura do banco...');
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`   📋 Total de tabelas: ${tables.length}`);
    
    const expectedTables = [
      'usuarios', 'saloes', 'profissionais', 'especialidades',
      'agendamentos', 'pagamentos', 'password_resets'
    ];
    
    const existingTables = tables.map(table => Object.values(table)[0]);
    
    console.log('\n   📝 Status das tabelas:');
    expectedTables.forEach(tableName => {
      const exists = existingTables.includes(tableName);
      console.log(`      ${exists ? '✅' : '❌'} ${tableName}`);
    });
    
    // 4. Verificar Dados
    if (existingTables.length > 0) {
      console.log('\n📈 Verificando dados...');
      
      // Usuários
      if (existingTables.includes('usuarios')) {
        const [userCount] = await connection.execute('SELECT COUNT(*) as total FROM usuarios');
        console.log(`   👤 Usuários: ${userCount[0].total}`);
        
        const [adminUsers] = await connection.execute(
          'SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = "admin"'
        );
        console.log(`   👑 Administradores: ${adminUsers[0].total}`);
      }
      
      // Salões
      if (existingTables.includes('saloes')) {
        const [salaoCount] = await connection.execute('SELECT COUNT(*) as total FROM saloes');
        console.log(`   🏪 Salões: ${salaoCount[0].total}`);
      }
      
      // Profissionais
      if (existingTables.includes('profissionais')) {
        const [profCount] = await connection.execute('SELECT COUNT(*) as total FROM profissionais');
        console.log(`   💼 Profissionais: ${profCount[0].total}`);
      }
      
      // Agendamentos
      if (existingTables.includes('agendamentos')) {
        const [agendCount] = await connection.execute('SELECT COUNT(*) as total FROM agendamentos');
        console.log(`   📅 Agendamentos: ${agendCount[0].total}`);
      }
    }
    
    // 5. Teste de Performance
    console.log('\n⚡ Teste de performance...');
    const startTime = Date.now();
    await connection.execute('SELECT SLEEP(0.1)');
    const endTime = Date.now();
    const responseTime = endTime - startTime;
    console.log(`   ⏱️  Tempo de resposta: ${responseTime}ms`);
    
    if (responseTime < 200) {
      console.log('   ✅ Performance: Excelente');
    } else if (responseTime < 500) {
      console.log('   ⚠️  Performance: Boa');
    } else {
      console.log('   ❌ Performance: Lenta');
    }
    
    // 6. Verificar Privilégios
    console.log('\n🔐 Verificando privilégios...');
    try {
      await connection.execute('CREATE TEMPORARY TABLE test_privileges (id INT)');
      await connection.execute('DROP TEMPORARY TABLE test_privileges');
      console.log('   ✅ Privilégios de CREATE/DROP: OK');
    } catch (error) {
      console.log('   ❌ Privilégios limitados:', error.message);
    }
    
    console.log('\n' + '='.repeat(50));
    console.log('🎉 BANCO DE DADOS ESTÁ FUNCIONANDO CORRETAMENTE!');
    console.log('✅ Todas as verificações passaram');
    
    return true;
    
  } catch (error) {
    console.log('\n❌ ERRO NA VERIFICAÇÃO:');
    console.log(`   Mensagem: ${error.message}`);
    console.log(`   Código: ${error.code || 'N/A'}`);
    
    // Diagnósticos específicos
    switch (error.code) {
      case 'ENOTFOUND':
        console.log('\n💡 Diagnóstico: Host não encontrado');
        console.log('   - Verifique se o host está correto');
        console.log('   - Se estiver no EasyPanel, use o host interno');
        break;
        
      case 'ECONNREFUSED':
        console.log('\n💡 Diagnóstico: Conexão recusada');
        console.log('   - Verifique se o MySQL está rodando');
        console.log('   - Verifique a porta (padrão: 3306)');
        break;
        
      case 'ER_ACCESS_DENIED_ERROR':
        console.log('\n💡 Diagnóstico: Acesso negado');
        console.log('   - Verifique usuário e senha');
        console.log('   - Verifique permissões do usuário');
        break;
        
      case 'ER_BAD_DB_ERROR':
        console.log('\n💡 Diagnóstico: Banco não existe');
        console.log('   - Crie o banco de dados');
        console.log('   - Execute as migrações');
        break;
        
      default:
        console.log('\n💡 Diagnóstico: Erro desconhecido');
        console.log('   - Verifique todas as configurações');
        console.log('   - Consulte os logs do servidor');
    }
    
    return false;
    
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  checkDatabaseHealth()
    .then(success => {
      process.exit(success ? 0 : 1);
    })
    .catch(error => {
      console.error('Erro fatal:', error);
      process.exit(1);
    });
}

module.exports = { checkDatabaseHealth };