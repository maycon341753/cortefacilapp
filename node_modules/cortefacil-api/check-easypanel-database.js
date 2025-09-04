#!/usr/bin/env node

/**
 * Script para verificar o banco de dados do EasyPanel
 * Execute este script no terminal do EasyPanel para verificar o banco online
 */

const mysql = require('mysql2/promise');

// Configurações do EasyPanel (produção)
const easypanelConfig = {
  host: 'cortefacil_cortefacil_user', // Host interno do EasyPanel
  port: 3306,
  user: 'mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_cortefacil',
  connectTimeout: 15000
};

async function checkEasypanelDatabase() {
  console.log('🌐 Verificação do Banco de Dados EasyPanel (Online)');
  console.log('=' .repeat(60));
  
  console.log('📋 Configurações de Produção:');
  console.log(`   Host: ${easypanelConfig.host}:${easypanelConfig.port}`);
  console.log(`   User: ${easypanelConfig.user}`);
  console.log(`   Database: ${easypanelConfig.database}`);
  console.log(`   Environment: production`);
  console.log('');
  
  let connection;
  
  try {
    // 1. Teste de Conexão
    console.log('🔌 Conectando ao MySQL do EasyPanel...');
    connection = await mysql.createConnection(easypanelConfig);
    console.log('   ✅ Conexão com EasyPanel estabelecida!');
    
    // 2. Informações do Servidor
    console.log('\n🖥️  Informações do servidor...');
    const [serverInfo] = await connection.execute(`
      SELECT 
        VERSION() as mysql_version,
        NOW() as server_time,
        @@hostname as hostname,
        @@port as port
    `);
    
    console.log(`   ✅ MySQL Version: ${serverInfo[0].mysql_version}`);
    console.log(`   ✅ Server Time: ${serverInfo[0].server_time}`);
    console.log(`   ✅ Hostname: ${serverInfo[0].hostname}`);
    console.log(`   ✅ Port: ${serverInfo[0].port}`);
    
    // 3. Verificar Banco de Dados
    console.log('\n🗄️  Verificando banco de dados...');
    const [databases] = await connection.execute('SHOW DATABASES');
    const dbExists = databases.some(db => Object.values(db)[0] === easypanelConfig.database);
    
    if (dbExists) {
      console.log(`   ✅ Banco '${easypanelConfig.database}' existe`);
    } else {
      console.log(`   ❌ Banco '${easypanelConfig.database}' NÃO existe`);
      console.log('   💡 Crie o banco no painel do EasyPanel');
      return false;
    }
    
    // 4. Verificar Tabelas
    console.log('\n📊 Verificando estrutura...');
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`   📋 Total de tabelas: ${tables.length}`);
    
    const expectedTables = [
      'usuarios', 'saloes', 'profissionais', 'especialidades',
      'agendamentos', 'pagamentos', 'password_resets'
    ];
    
    const existingTables = tables.map(table => Object.values(table)[0]);
    
    console.log('\n   📝 Tabelas necessárias:');
    let missingTables = [];
    expectedTables.forEach(tableName => {
      const exists = existingTables.includes(tableName);
      console.log(`      ${exists ? '✅' : '❌'} ${tableName}`);
      if (!exists) missingTables.push(tableName);
    });
    
    if (missingTables.length > 0) {
      console.log(`\n   ⚠️  Tabelas faltando: ${missingTables.join(', ')}`);
      console.log('   💡 Execute as migrações do banco');
    }
    
    // 5. Verificar Dados Essenciais
    if (existingTables.length > 0) {
      console.log('\n📈 Verificando dados essenciais...');
      
      // Usuários
      if (existingTables.includes('usuarios')) {
        const [userStats] = await connection.execute(`
          SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN tipo_usuario = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN tipo_usuario = 'cliente' THEN 1 ELSE 0 END) as clientes,
            SUM(CASE WHEN tipo_usuario = 'profissional' THEN 1 ELSE 0 END) as profissionais
          FROM usuarios
        `);
        
        console.log(`   👤 Usuários: ${userStats[0].total}`);
        console.log(`      👑 Admins: ${userStats[0].admins}`);
        console.log(`      🧑 Clientes: ${userStats[0].clientes}`);
        console.log(`      💼 Profissionais: ${userStats[0].profissionais}`);
        
        // Verificar usuário admin padrão
        const [adminUser] = await connection.execute(
          'SELECT email FROM usuarios WHERE tipo_usuario = "admin" AND email = "admin@cortefacil.com" LIMIT 1'
        );
        
        if (adminUser.length > 0) {
          console.log('   ✅ Usuário admin padrão existe');
        } else {
          console.log('   ⚠️  Usuário admin padrão não encontrado');
        }
      }
      
      // Salões
      if (existingTables.includes('saloes')) {
        const [salaoCount] = await connection.execute('SELECT COUNT(*) as total FROM saloes');
        console.log(`   🏪 Salões cadastrados: ${salaoCount[0].total}`);
      }
      
      // Especialidades
      if (existingTables.includes('especialidades')) {
        const [espCount] = await connection.execute('SELECT COUNT(*) as total FROM especialidades');
        console.log(`   ✂️  Especialidades: ${espCount[0].total}`);
      }
    }
    
    // 6. Teste de Performance
    console.log('\n⚡ Teste de performance online...');
    const startTime = Date.now();
    await connection.execute('SELECT 1');
    const endTime = Date.now();
    const responseTime = endTime - startTime;
    
    console.log(`   ⏱️  Latência: ${responseTime}ms`);
    
    if (responseTime < 100) {
      console.log('   ✅ Performance: Excelente');
    } else if (responseTime < 300) {
      console.log('   ✅ Performance: Boa');
    } else if (responseTime < 1000) {
      console.log('   ⚠️  Performance: Aceitável');
    } else {
      console.log('   ❌ Performance: Lenta');
    }
    
    // 7. Verificar Configurações
    console.log('\n⚙️  Configurações do MySQL...');
    const [configs] = await connection.execute(`
      SELECT 
        @@max_connections as max_connections,
        @@innodb_buffer_pool_size as buffer_pool_size,
        @@query_cache_size as query_cache_size
    `);
    
    console.log(`   🔗 Max Connections: ${configs[0].max_connections}`);
    console.log(`   💾 Buffer Pool: ${Math.round(configs[0].buffer_pool_size / 1024 / 1024)}MB`);
    console.log(`   🗃️  Query Cache: ${Math.round(configs[0].query_cache_size / 1024 / 1024)}MB`);
    
    console.log('\n' + '='.repeat(60));
    console.log('🎉 BANCO DE DADOS EASYPANEL ESTÁ ONLINE!');
    console.log('✅ Sistema pronto para produção');
    console.log('🌐 Aplicação pode ser deployada com segurança');
    
    return true;
    
  } catch (error) {
    console.log('\n❌ ERRO NO BANCO EASYPANEL:');
    console.log(`   Mensagem: ${error.message}`);
    console.log(`   Código: ${error.code || 'N/A'}`);
    
    // Diagnósticos específicos para EasyPanel
    switch (error.code) {
      case 'ENOTFOUND':
        console.log('\n💡 Diagnóstico EasyPanel:');
        console.log('   ❌ Host interno não encontrado');
        console.log('   🔧 Soluções:');
        console.log('      1. Execute este script DENTRO do container EasyPanel');
        console.log('      2. Verifique se o serviço MySQL está rodando');
        console.log('      3. Confirme o nome do host interno');
        break;
        
      case 'ECONNREFUSED':
        console.log('\n💡 Diagnóstico EasyPanel:');
        console.log('   ❌ MySQL não está rodando');
        console.log('   🔧 Soluções:');
        console.log('      1. Inicie o serviço MySQL no painel');
        console.log('      2. Verifique logs do container MySQL');
        console.log('      3. Reinicie o serviço se necessário');
        break;
        
      case 'ER_ACCESS_DENIED_ERROR':
        console.log('\n💡 Diagnóstico EasyPanel:');
        console.log('   ❌ Credenciais incorretas');
        console.log('   🔧 Soluções:');
        console.log('      1. Verifique usuário: mayconwender');
        console.log('      2. Verifique senha: Maycon341753@');
        console.log('      3. Recrie o usuário se necessário');
        break;
        
      case 'ER_BAD_DB_ERROR':
        console.log('\n💡 Diagnóstico EasyPanel:');
        console.log('   ❌ Banco de dados não existe');
        console.log('   🔧 Soluções:');
        console.log('      1. Crie o banco: u690889028_cortefacil');
        console.log('      2. Execute as migrações');
        console.log('      3. Importe dados se necessário');
        break;
        
      default:
        console.log('\n💡 Diagnóstico Geral:');
        console.log('   🔧 Verifique:');
        console.log('      1. Status dos serviços no EasyPanel');
        console.log('      2. Logs dos containers');
        console.log('      3. Configurações de rede');
        console.log('      4. Variáveis de ambiente');
    }
    
    console.log('\n📞 Suporte:');
    console.log('   - Acesse o painel EasyPanel');
    console.log('   - Verifique logs dos serviços');
    console.log('   - Contate suporte se necessário');
    
    return false;
    
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  checkEasypanelDatabase()
    .then(success => {
      console.log(`\n🏁 Resultado: ${success ? 'SUCESSO' : 'FALHA'}`);
      process.exit(success ? 0 : 1);
    })
    .catch(error => {
      console.error('\n💥 Erro fatal:', error.message);
      process.exit(1);
    });
}

module.exports = { checkEasypanelDatabase, easypanelConfig };