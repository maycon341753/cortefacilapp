const mysql = require('mysql2/promise');

// Configurações para desenvolvimento local
const localConfigs = [
  {
    name: 'XAMPP MySQL (sem senha)',
    host: 'localhost',
    port: 3306,
    user: 'root',
    password: '',
    database: 'cortefacil'
  },
  {
    name: 'XAMPP MySQL (com senha)',
    host: 'localhost',
    port: 3306,
    user: 'root',
    password: 'root',
    database: 'cortefacil'
  },
  {
    name: 'MySQL Workbench Local',
    host: '127.0.0.1',
    port: 3306,
    user: 'root',
    password: '',
    database: 'cortefacil'
  }
];

async function testLocalConnection(config) {
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
      connectTimeout: 5000
    });
    
    console.log(`   ✅ Conexão com MySQL estabelecida!`);
    
    // Tentar criar o banco de dados
    try {
      await connection.execute(`CREATE DATABASE IF NOT EXISTS ${config.database}`);
      console.log(`   ✅ Banco '${config.database}' criado/verificado!`);
      
      // Conectar ao banco específico
      await connection.execute(`USE ${config.database}`);
      
      // Verificar tabelas
      const [tables] = await connection.execute('SHOW TABLES');
      console.log(`   📋 Tabelas encontradas: ${tables.length}`);
      
      if (tables.length === 0) {
        console.log(`   ⚠️  Banco vazio - será necessário executar o script de criação das tabelas`);
      }
      
    } catch (dbError) {
      console.log(`   ⚠️  Erro ao criar/usar banco: ${dbError.message}`);
    }
    
    await connection.end();
    return true;
  } catch (error) {
    console.log(`   ❌ Erro: ${error.message}`);
    if (error.code === 'ECONNREFUSED') {
      console.log(`   🔧 MySQL não está rodando. Inicie o XAMPP e ative o MySQL.`);
    }
    return false;
  }
}

async function main() {
  console.log('🚀 Testando conexões locais com MySQL...');
  console.log('=' .repeat(60));
  
  let successCount = 0;
  let workingConfig = null;
  
  for (const config of localConfigs) {
    const success = await testLocalConnection(config);
    if (success) {
      successCount++;
      if (!workingConfig) workingConfig = config;
    }
  }
  
  console.log('\n' + '='.repeat(60));
  console.log(`📊 Resumo: ${successCount}/${localConfigs.length} configurações funcionaram`);
  
  if (successCount === 0) {
    console.log('\n🔧 Para resolver:');
    console.log('1. Abra o XAMPP Control Panel');
    console.log('2. Clique em "Start" ao lado do MySQL');
    console.log('3. Aguarde até aparecer "Running" em verde');
    console.log('4. Execute este script novamente');
    console.log('\n📝 Alternativamente, você pode:');
    console.log('- Usar o banco de dados do EasyPanel (deploy completo)');
    console.log('- Configurar outro servidor MySQL local');
  } else {
    console.log('\n✅ Configuração recomendada para .env.local:');
    console.log(`DB_HOST=${workingConfig.host}`);
    console.log(`DB_PORT=${workingConfig.port}`);
    console.log(`DB_USER=${workingConfig.user}`);
    console.log(`DB_PASSWORD=${workingConfig.password}`);
    console.log(`DB_NAME=${workingConfig.database}`);
  }
}

main().catch(console.error);