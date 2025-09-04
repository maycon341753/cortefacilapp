const mysql = require('mysql2/promise');

// Configurações do EasyPanel conforme mostrado na imagem
const configs = [
  {
    name: 'Host Interno (EasyPanel)',
    host: 'cortefacil_cortefacil_user',
    port: 3306,
    user: 'mayconwender',
    password: 'Maycon341753@',
    database: 'u690889028_cortefacil'
  },
  {
    name: 'Localhost (se estiver rodando localmente)',
    host: 'localhost',
    port: 3306,
    user: 'mayconwender',
    password: 'Maycon341753@',
    database: 'u690889028_cortefacil'
  },
  {
    name: 'Host Externo (se disponível)',
    host: '127.0.0.1',
    port: 3306,
    user: 'mayconwender',
    password: 'Maycon341753@',
    database: 'u690889028_cortefacil'
  }
];

async function testConnection(config) {
  console.log(`\n🔍 Testando: ${config.name}`);
  console.log(`   Host: ${config.host}:${config.port}`);
  console.log(`   User: ${config.user}`);
  console.log(`   Database: ${config.database}`);
  
  try {
    const connection = await mysql.createConnection({
      host: config.host,
      port: config.port,
      user: config.user,
      password: config.password,
      database: config.database,
      connectTimeout: 10000
    });
    
    // Teste simples
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log(`   ✅ Conexão bem-sucedida! Resultado: ${JSON.stringify(rows[0])}`);
    
    // Listar tabelas
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`   📋 Tabelas encontradas: ${tables.length}`);
    
    await connection.end();
    return true;
  } catch (error) {
    console.log(`   ❌ Erro: ${error.message}`);
    if (error.code) {
      console.log(`   🔧 Código do erro: ${error.code}`);
    }
    return false;
  }
}

async function main() {
  console.log('🚀 Testando conexões com o banco de dados EasyPanel...');
  console.log('=' .repeat(60));
  
  let successCount = 0;
  
  for (const config of configs) {
    const success = await testConnection(config);
    if (success) successCount++;
  }
  
  console.log('\n' + '='.repeat(60));
  console.log(`📊 Resumo: ${successCount}/${configs.length} configurações funcionaram`);
  
  if (successCount === 0) {
    console.log('\n🔧 Sugestões:');
    console.log('1. Verifique se o serviço MySQL está rodando no EasyPanel');
    console.log('2. Confirme se o banco de dados "u690889028_cortefacil" existe');
    console.log('3. Verifique se o usuário "mayconwender" tem permissões');
    console.log('4. Se estiver rodando localmente, pode precisar de um túnel SSH ou proxy');
    console.log('5. Verifique se há firewall bloqueando a conexão');
  }
}

main().catch(console.error);