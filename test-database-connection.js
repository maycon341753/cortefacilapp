const mysql = require('mysql2/promise');

// Configurações do banco de dados baseadas no .env.easypanel
const dbConfig = {
  host: '31.97.171.104',  // Host externo do EasyPanel
  port: 3306,
  user: 'u690889028_mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_mayconwender'
};

// Configurações alternativas para teste
const alternativeConfigs = [
  {
    name: 'Configuração EasyPanel (Host Interno - para deploy)',
    config: {
      host: 'cortefacil_cortefacil',
      port: 3306,
      user: 'u690889028_mayconwender',
      password: 'Maycon341753@',
      database: 'u690889028_mayconwender'
    }
  },
  {
    name: 'Configuração com credenciais da imagem',
    config: {
      host: '31.97.171.104',
      port: 3306,
      user: 'mysql',
      password: 'Maycon34175@',
      database: 'u690889028_mayconwender'
    }
  }
];

async function testDatabaseConnection(config, configName = 'Principal') {
  console.log(`\n🔍 Testando conexão: ${configName}`);
  console.log('Configuração:', {
    host: config.host,
    port: config.port,
    user: config.user,
    database: config.database
  });
  
  try {
    const connection = await mysql.createConnection(config);
    console.log('✅ Conexão estabelecida com sucesso!');
    
    // Testar uma query simples
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log('✅ Query de teste executada:', rows[0]);
    
    // Verificar tabelas existentes
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`📊 Tabelas encontradas (${tables.length}):`, 
      tables.map(t => Object.values(t)[0]).join(', '));
    
    await connection.end();
    return true;
    
  } catch (error) {
    console.log('❌ Erro na conexão:', error.message);
    if (error.code) {
      console.log('   Código do erro:', error.code);
    }
    return false;
  }
}

async function main() {
  console.log('🚀 Iniciando teste de conexão com banco de dados MySQL');
  console.log('📋 Baseado nas credenciais do EasyPanel mostradas na imagem\n');
  
  let successfulConnection = false;
  
  // Testar configuração principal
  successfulConnection = await testDatabaseConnection(dbConfig, 'Principal');
  
  // Se a principal falhar, testar alternativas
  if (!successfulConnection) {
    console.log('\n🔄 Testando configurações alternativas...');
    
    for (const { name, config } of alternativeConfigs) {
      const success = await testDatabaseConnection(config, name);
      if (success) {
        successfulConnection = true;
        console.log(`\n✅ Configuração funcionando: ${name}`);
        break;
      }
    }
  }
  
  console.log('\n📊 Resumo do Teste:');
  if (successfulConnection) {
    console.log('✅ Pelo menos uma configuração de banco funcionou!');
    console.log('🔧 Use a configuração que funcionou no seu backend.');
  } else {
    console.log('❌ Nenhuma configuração de banco funcionou.');
    console.log('🔧 Possíveis soluções:');
    console.log('   1. Verificar se o serviço MySQL está rodando no EasyPanel');
    console.log('   2. Confirmar as credenciais no painel do EasyPanel');
    console.log('   3. Verificar configurações de rede/firewall');
    console.log('   4. Testar a partir do próprio container do backend');
  }
}

// Executar teste
main().catch(console.error);