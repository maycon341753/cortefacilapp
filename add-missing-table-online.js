#!/usr/bin/env node

/**
 * Script para adicionar a tabela password_resets no banco de dados online do EasyPanel
 */

const mysql = require('mysql2/promise');
const fs = require('fs').promises;
const path = require('path');

// Configurações do EasyPanel (produção)
const easypanelConfig = {
  host: 'cortefacil_cortefacil_user',
  port: 3306,
  user: 'mayconwender',
  password: 'Maycon341753@',
  database: 'u690889028_cortefacil',
  connectTimeout: 15000
};

// SQL para criar a tabela password_resets
const createPasswordResetsTable = `
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);
`;

async function addMissingTableOnline() {
  console.log('🌐 Adicionando Tabela Faltante no Banco Online');
  console.log('=' .repeat(55));
  
  console.log('📋 Configurações EasyPanel:');
  console.log(`   Host: ${easypanelConfig.host}:${easypanelConfig.port}`);
  console.log(`   User: ${easypanelConfig.user}`);
  console.log(`   Database: ${easypanelConfig.database}`);
  console.log('');
  
  let connection;
  
  try {
    // 1. Conectar ao banco
    console.log('🔌 Conectando ao MySQL do EasyPanel...');
    connection = await mysql.createConnection(easypanelConfig);
    console.log('   ✅ Conexão estabelecida!');
    
    // 2. Verificar tabelas existentes
    console.log('\n📊 Verificando tabelas existentes...');
    const [tables] = await connection.execute('SHOW TABLES');
    const existingTables = tables.map(table => Object.values(table)[0]);
    
    console.log(`   📋 Tabelas encontradas: ${existingTables.length}`);
    existingTables.forEach((table, index) => {
      console.log(`      ${index + 1}. ${table}`);
    });
    
    // 3. Verificar se password_resets existe
    const passwordResetsExists = existingTables.includes('password_resets');
    
    if (passwordResetsExists) {
      console.log('\n✅ Tabela password_resets já existe!');
      
      // Verificar estrutura
      const [columns] = await connection.execute('DESCRIBE password_resets');
      console.log(`   📝 Colunas: ${columns.length}`);
      columns.forEach(col => {
        console.log(`      - ${col.Field} (${col.Type})`);
      });
      
      console.log('\n🎉 Nenhuma ação necessária - tabela já está presente!');
      return true;
    }
    
    // 4. Criar tabela password_resets
    console.log('\n🔧 Criando tabela password_resets...');
    await connection.execute(createPasswordResetsTable);
    console.log('   ✅ Tabela password_resets criada com sucesso!');
    
    // 5. Verificar se foi criada corretamente
    console.log('\n🔍 Verificando estrutura da nova tabela...');
    const [newColumns] = await connection.execute('DESCRIBE password_resets');
    console.log(`   📝 Colunas criadas: ${newColumns.length}`);
    
    newColumns.forEach(col => {
      const nullable = col.Null === 'YES' ? 'NULL' : 'NOT NULL';
      const defaultVal = col.Default ? ` DEFAULT ${col.Default}` : '';
      const extra = col.Extra ? ` ${col.Extra}` : '';
      console.log(`      ✅ ${col.Field}: ${col.Type} ${nullable}${defaultVal}${extra}`);
    });
    
    // 6. Verificar índices
    console.log('\n🔍 Verificando índices...');
    const [indexes] = await connection.execute('SHOW INDEX FROM password_resets');
    const uniqueIndexes = [...new Set(indexes.map(idx => idx.Key_name))];
    
    console.log(`   📊 Índices criados: ${uniqueIndexes.length}`);
    uniqueIndexes.forEach(indexName => {
      const indexCols = indexes
        .filter(idx => idx.Key_name === indexName)
        .map(idx => idx.Column_name)
        .join(', ');
      console.log(`      ✅ ${indexName}: (${indexCols})`);
    });
    
    // 7. Teste básico na nova tabela
    console.log('\n🧪 Testando nova tabela...');
    
    // Inserir registro de teste
    const testToken = 'test_token_' + Date.now();
    const testEmail = 'test@example.com';
    const expiresAt = new Date(Date.now() + 3600000); // 1 hora
    
    await connection.execute(
      'INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)',
      [testEmail, testToken, expiresAt]
    );
    console.log('   ✅ Inserção de teste: OK');
    
    // Verificar registro
    const [testResult] = await connection.execute(
      'SELECT * FROM password_resets WHERE token = ?',
      [testToken]
    );
    
    if (testResult.length > 0) {
      console.log('   ✅ Consulta de teste: OK');
      console.log(`      ID: ${testResult[0].id}`);
      console.log(`      Email: ${testResult[0].email}`);
      console.log(`      Token: ${testResult[0].token.substring(0, 20)}...`);
    }
    
    // Limpar registro de teste
    await connection.execute('DELETE FROM password_resets WHERE token = ?', [testToken]);
    console.log('   ✅ Limpeza de teste: OK');
    
    // 8. Verificar total de tabelas após criação
    console.log('\n📊 Verificação final...');
    const [finalTables] = await connection.execute('SHOW TABLES');
    console.log(`   📋 Total de tabelas agora: ${finalTables.length}`);
    
    const expectedTables = [
      'usuarios', 'saloes', 'profissionais', 'especialidades',
      'agendamentos', 'pagamentos', 'password_resets'
    ];
    
    const finalTableNames = finalTables.map(table => Object.values(table)[0]);
    
    console.log('\n   📝 Status das tabelas necessárias:');
    expectedTables.forEach(tableName => {
      const exists = finalTableNames.includes(tableName);
      console.log(`      ${exists ? '✅' : '❌'} ${tableName}`);
    });
    
    console.log('\n' + '='.repeat(55));
    console.log('🎉 TABELA ADICIONADA COM SUCESSO!');
    console.log('✅ password_resets criada no banco online');
    console.log('🔧 Sistema de redefinição de senha pronto');
    console.log('🌐 Banco de dados online completo');
    
    return true;
    
  } catch (error) {
    console.log('\n❌ ERRO AO ADICIONAR TABELA:');
    console.log(`   Mensagem: ${error.message}`);
    console.log(`   Código: ${error.code || 'N/A'}`);
    
    // Diagnósticos específicos
    switch (error.code) {
      case 'ENOTFOUND':
        console.log('\n💡 Solução:');
        console.log('   ❌ Execute este script DENTRO do EasyPanel');
        console.log('   🔧 Ou use SSH tunnel para conectar externamente');
        break;
        
      case 'ECONNREFUSED':
        console.log('\n💡 Solução:');
        console.log('   ❌ Inicie o serviço MySQL no EasyPanel');
        console.log('   🔧 Verifique se o container está rodando');
        break;
        
      case 'ER_ACCESS_DENIED_ERROR':
        console.log('\n💡 Solução:');
        console.log('   ❌ Verifique credenciais do banco');
        console.log('   🔧 Confirme usuário e senha no painel');
        break;
        
      case 'ER_TABLE_EXISTS_ERROR':
        console.log('\n💡 Informação:');
        console.log('   ✅ Tabela já existe - isso é normal');
        console.log('   🎉 Nenhuma ação necessária');
        break;
        
      default:
        console.log('\n💡 Ações recomendadas:');
        console.log('   1. Verifique status do MySQL no EasyPanel');
        console.log('   2. Confirme configurações de rede');
        console.log('   3. Execute dentro do ambiente EasyPanel');
    }
    
    return false;
    
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

// Função alternativa para usar configurações locais (teste)
async function addMissingTableLocal() {
  console.log('🏠 Adicionando Tabela no Banco Local (Teste)');
  console.log('=' .repeat(50));
  
  const localConfig = {
    host: 'localhost',
    port: 3306,
    user: 'root',
    password: '',
    database: 'cortefacil'
  };
  
  let connection;
  
  try {
    connection = await mysql.createConnection(localConfig);
    console.log('✅ Conectado ao banco local');
    
    // Verificar se tabela existe
    const [tables] = await connection.execute('SHOW TABLES');
    const existingTables = tables.map(table => Object.values(table)[0]);
    
    if (existingTables.includes('password_resets')) {
      console.log('✅ Tabela password_resets já existe localmente');
      return true;
    }
    
    // Criar tabela
    await connection.execute(createPasswordResetsTable);
    console.log('✅ Tabela password_resets criada localmente');
    
    return true;
    
  } catch (error) {
    console.log(`❌ Erro local: ${error.message}`);
    return false;
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

// Executar baseado no argumento
if (require.main === module) {
  const args = process.argv.slice(2);
  const isLocal = args.includes('--local') || args.includes('-l');
  
  if (isLocal) {
    console.log('🔧 Modo: Teste Local\n');
    addMissingTableLocal()
      .then(success => {
        console.log(`\n🏁 Resultado Local: ${success ? 'SUCESSO' : 'FALHA'}`);
        process.exit(success ? 0 : 1);
      })
      .catch(error => {
        console.error('💥 Erro fatal local:', error.message);
        process.exit(1);
      });
  } else {
    console.log('🌐 Modo: Produção EasyPanel\n');
    addMissingTableOnline()
      .then(success => {
        console.log(`\n🏁 Resultado Online: ${success ? 'SUCESSO' : 'FALHA'}`);
        process.exit(success ? 0 : 1);
      })
      .catch(error => {
        console.error('💥 Erro fatal online:', error.message);
        process.exit(1);
      });
  }
}

module.exports = {
  addMissingTableOnline,
  addMissingTableLocal,
  easypanelConfig,
  createPasswordResetsTable
};