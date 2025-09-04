const mysql = require('mysql2/promise');

// Configurações do banco Hostinger
const config = {
  host: 'srv973908.hstgr.cloud',
  port: 3306,
  user: 'mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_cortefacil',
  connectTimeout: 10000,
  acquireTimeout: 10000
};

async function testConnection() {
  console.log('🔍 Testando conexão com Hostinger MySQL...');
  console.log(`📍 Host: ${config.host}`);
  console.log(`👤 Usuário: ${config.user}`);
  console.log(`🗄️ Banco: ${config.database}`);
  console.log(`🌐 Seu IP atual será mostrado no erro se houver problema de acesso`);
  
  try {
    const connection = await mysql.createConnection(config);
    console.log('✅ Conexão estabelecida com sucesso!');
    
    // Teste simples
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log('✅ Query de teste executada:', rows);
    
    await connection.end();
    console.log('✅ Conexão fechada corretamente');
    
  } catch (error) {
    console.log('❌ Erro na conexão:', error.message);
    
    if (error.code === 'ER_ACCESS_DENIED_ERROR') {
      console.log('\n🔧 SOLUÇÃO NECESSÁRIA:');
      console.log('1. Acesse o painel do Hostinger (hPanel)');
      console.log('2. Vá em "Websites" → "Manage" → "Databases" → "Remote MySQL"');
      console.log('3. Adicione seu IP atual ou marque "Any Host" (%)'); 
      console.log('4. Selecione o banco "u690889028_cortefacil"');
      console.log('5. Clique em "Create"');
      console.log('\n💡 Seu IP atual aparece na mensagem de erro acima');
    }
    
    if (error.code === 'ENOTFOUND') {
      console.log('\n🔧 SOLUÇÃO: Verifique se o host está correto');
      console.log('Host atual:', config.host);
    }
  }
}

testConnection();