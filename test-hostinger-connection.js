require('dotenv').config();
const mysql = require('mysql2/promise');

// Diferentes configurações de host para testar
const hostConfigs = [
  {
    name: 'Host Interno (cortefacil_cortefacil_user)',
    host: 'cortefacil_cortefacil_user',
    port: 3306
  },
  {
    name: 'Localhost',
    host: 'localhost',
    port: 3306
  },
  {
    name: 'Host Externo (srv973908.hstgr.cloud)',
    host: 'srv973908.hstgr.cloud',
    port: 3306
  },
  {
    name: 'IP Direto (31.97.171.104)',
    host: '31.97.171.104',
    port: 3306
  }
];

const testConnection = async (config) => {
  console.log(`\n🔍 Testando: ${config.name}`);
  console.log(`📋 Host: ${config.host}:${config.port}`);
  
  try {
    const connection = await mysql.createConnection({
      host: config.host,
      port: config.port,
      user: process.env.DB_USER || 'mayconwender',
      password: process.env.DB_PASSWORD || 'Maycon341753@',
      database: process.env.DB_NAME || 'u690889028_cortefacil',
      connectTimeout: 10000,
      acquireTimeout: 10000,
      timeout: 10000
    });
    
    console.log('✅ Conexão estabelecida com sucesso!');
    
    // Testar uma query simples
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log('✅ Query de teste executada:', rows[0]);
    
    // Listar tabelas
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`✅ Tabelas encontradas: ${tables.length}`);
    tables.forEach(table => {
      console.log(`   - ${Object.values(table)[0]}`);
    });
    
    await connection.end();
    return true;
    
  } catch (error) {
    console.log('❌ Erro na conexão:');
    console.log(`   Código: ${error.code}`);
    console.log(`   Mensagem: ${error.message}`);
    if (error.sqlState) {
      console.log(`   SQL State: ${error.sqlState}`);
    }
    return false;
  }
};

const testAllConfigurations = async () => {
  console.log('🚀 Testando diferentes configurações de host do Hostinger...');
  console.log('📋 Credenciais:');
  console.log(`   User: ${process.env.DB_USER || 'mayconwender'}`);
  console.log(`   Database: ${process.env.DB_NAME || 'u690889028_cortefacil'}`);
  console.log(`   Password: [${process.env.DB_PASSWORD ? 'DEFINIDA' : 'NÃO DEFINIDA'}]`);
  
  let successCount = 0;
  
  for (const config of hostConfigs) {
    const success = await testConnection(config);
    if (success) {
      successCount++;
      console.log(`\n🎉 CONFIGURAÇÃO FUNCIONANDO: ${config.name}`);
      console.log(`   Use: DB_HOST=${config.host}`);
      console.log(`   Use: DB_PORT=${config.port}`);
    }
    
    // Aguardar um pouco entre os testes
    await new Promise(resolve => setTimeout(resolve, 1000));
  }
  
  console.log(`\n📊 Resumo: ${successCount}/${hostConfigs.length} configurações funcionaram`);
  
  if (successCount === 0) {
    console.log('\n💡 Sugestões:');
    console.log('   1. Verifique se o banco de dados foi criado no painel Hostinger');
    console.log('   2. Configure "Hosts Remotos" no painel MySQL do Hostinger');
    console.log('   3. Adicione seu IP atual aos hosts permitidos');
    console.log('   4. Teste a conexão via phpMyAdmin primeiro');
    console.log('   5. Verifique se as credenciais estão corretas');
  }
};

testAllConfigurations().catch(console.error);