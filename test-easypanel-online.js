const mysql = require('mysql2/promise');

// Configurações do banco de dados EasyPanel (produção)
const easypanelConfig = {
  name: 'EasyPanel MySQL (Produção)',
  host: 'cortefacil_cortefacil_user',
  port: 3306,
  user: 'mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_cortefacil'
};

// Configurações alternativas para teste
const alternativeConfigs = [
  {
    name: 'EasyPanel - Host Interno',
    host: 'cortefacil_cortefacil_user',
    port: 3306,
    user: 'mayconwender',
    password: 'Maycon341753@',
    database: 'u690889028_cortefacil'
  },
  {
    name: 'EasyPanel - Host Simplificado',
    host: 'cortefacil_user',
    port: 3306,
    user: 'root',
    password: 'Maycon341753@',
    database: 'cortefacil'
  }
];

async function testEasypanelConnection(config) {
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
      connectTimeout: 15000,
      acquireTimeout: 15000,
      timeout: 15000
    });
    
    console.log(`   ✅ Conexão estabelecida com sucesso!`);
    
    // Teste básico
    const [testResult] = await connection.execute('SELECT 1 as test, NOW() as current_time');
    console.log(`   ✅ Teste básico OK: ${JSON.stringify(testResult[0])}`);
    
    // Verificar tabelas
    const [tables] = await connection.execute('SHOW TABLES');
    console.log(`   📋 Tabelas encontradas: ${tables.length}`);
    
    if (tables.length > 0) {
      console.log(`   📝 Lista de tabelas:`);
      tables.forEach((table, index) => {
        const tableName = Object.values(table)[0];
        console.log(`      ${index + 1}. ${tableName}`);
      });
      
      // Verificar usuários (se a tabela existir)
      const userTableExists = tables.some(table => Object.values(table)[0] === 'usuarios');
      if (userTableExists) {
        const [users] = await connection.execute('SELECT COUNT(*) as total FROM usuarios');
        console.log(`   👤 Total de usuários: ${users[0].total}`);
        
        // Verificar se existe usuário admin
        const [adminUsers] = await connection.execute('SELECT id, nome, email, tipo_usuario FROM usuarios WHERE tipo_usuario = "admin" LIMIT 1');
        if (adminUsers.length > 0) {
          console.log(`   👑 Usuário admin encontrado: ${adminUsers[0].email}`);
        }
      }
      
      // Verificar salões (se a tabela existir)
      const salaoTableExists = tables.some(table => Object.values(table)[0] === 'saloes');
      if (salaoTableExists) {
        const [saloes] = await connection.execute('SELECT COUNT(*) as total FROM saloes');
        console.log(`   🏪 Total de salões: ${saloes[0].total}`);
      }
    } else {
      console.log(`   ⚠️  Banco vazio - tabelas precisam ser criadas`);
    }
    
    await connection.end();
    return true;
  } catch (error) {
    console.log(`   ❌ Erro: ${error.message}`);
    
    if (error.code) {
      console.log(`   🔧 Código do erro: ${error.code}`);
      
      switch (error.code) {
        case 'ENOTFOUND':
          console.log(`   💡 Sugestão: Host não encontrado. Verifique se está rodando dentro da rede do EasyPanel.`);
          break;
        case 'ECONNREFUSED':
          console.log(`   💡 Sugestão: Conexão recusada. Verifique se o MySQL está rodando no EasyPanel.`);
          break;
        case 'ER_ACCESS_DENIED_ERROR':
          console.log(`   💡 Sugestão: Acesso negado. Verifique usuário e senha.`);
          break;
        case 'ER_BAD_DB_ERROR':
          console.log(`   💡 Sugestão: Banco de dados não existe. Crie o banco no EasyPanel.`);
          break;
        default:
          console.log(`   💡 Sugestão: Erro desconhecido. Verifique configurações do EasyPanel.`);
      }
    }
    
    return false;
  }
}

async function main() {
  console.log('🚀 Verificando banco de dados online do EasyPanel...');
  console.log('=' .repeat(70));
  
  let successCount = 0;
  const allConfigs = [easypanelConfig, ...alternativeConfigs];
  
  for (const config of allConfigs) {
    const success = await testEasypanelConnection(config);
    if (success) successCount++;
  }
  
  console.log('\n' + '='.repeat(70));
  console.log(`📊 Resumo: ${successCount}/${allConfigs.length} configurações funcionaram`);
  
  if (successCount === 0) {
    console.log('\n🔧 Possíveis soluções:');
    console.log('1. ✅ Verifique se o serviço MySQL está rodando no EasyPanel');
    console.log('2. ✅ Confirme se o banco de dados existe no EasyPanel');
    console.log('3. ✅ Verifique as credenciais (usuário e senha)');
    console.log('4. ✅ Este teste deve ser executado de dentro da rede do EasyPanel');
    console.log('5. ✅ Para teste externo, configure acesso remoto ou use SSH tunnel');
    console.log('\n📝 Nota: Se estiver rodando localmente, é normal que falhe.');
    console.log('   O host interno do EasyPanel só funciona dentro da própria rede.');
  } else {
    console.log('\n✅ Banco de dados online está funcionando!');
    console.log('🎉 Sistema pronto para produção no EasyPanel.');
  }
}

main().catch(console.error);

// Exportar configurações para uso em outros scripts
module.exports = {
  easypanelConfig,
  alternativeConfigs
};