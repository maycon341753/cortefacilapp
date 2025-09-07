const mysql = require('mysql2/promise');

// Configurações do banco de dados Hostinger
const dbConfig = {
  host: 'srv973908.hstgr.cloud',
  user: 'u690889028_mayconwender',
  password: 'Maycon@2024',
  database: 'u690889028_cortefacil',
  port: 3306,
  connectTimeout: 10000,
  acquireTimeout: 10000,
  timeout: 10000
};

async function testConnection() {
  console.log('🔍 Testando conexão MySQL para IP 172.18.0.5...');
  console.log('📋 Configurações:', {
    host: dbConfig.host,
    user: dbConfig.user,
    database: dbConfig.database,
    port: dbConfig.port
  });

  try {
    const connection = await mysql.createConnection(dbConfig);
    console.log('✅ Conexão estabelecida com sucesso!');
    
    // Teste básico de query
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log('✅ Query de teste executada:', rows);
    
    await connection.end();
    console.log('✅ Conexão fechada corretamente');
    
    return true;
  } catch (error) {
    console.error('❌ Erro na conexão:', error.message);
    console.error('📊 Código do erro:', error.code);
    return false;
  }
}

// Executar teste
testConnection()
  .then(success => {
    if (success) {
      console.log('\n🎉 Teste de conexão MySQL concluído com SUCESSO!');
      console.log('✅ O problema do IP 172.18.0.5 foi resolvido!');
    } else {
      console.log('\n❌ Teste de conexão MySQL FALHOU!');
      console.log('🔧 Verifique as configurações e tente novamente.');
    }
    process.exit(success ? 0 : 1);
  })
  .catch(error => {
    console.error('💥 Erro inesperado:', error);
    process.exit(1);
  });