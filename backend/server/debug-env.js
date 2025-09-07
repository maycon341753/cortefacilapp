const dotenv = require('dotenv');
const path = require('path');

console.log('🔍 Debugando carregamento de variáveis de ambiente...');
console.log('');

// Carregar .env.easypanel
console.log('📋 Carregando .env.easypanel...');
const result = dotenv.config({ path: '.env.easypanel' });

if (result.error) {
    console.error('❌ Erro ao carregar .env.easypanel:', result.error);
} else {
    console.log('✅ Arquivo .env.easypanel carregado com sucesso');
}

console.log('');
console.log('🔧 Variáveis de ambiente relacionadas ao banco:');
console.log(`   DB_HOST: ${process.env.DB_HOST}`);
console.log(`   DB_PORT: ${process.env.DB_PORT}`);
console.log(`   DB_USER: ${process.env.DB_USER}`);
console.log(`   DB_PASSWORD: ${process.env.DB_PASSWORD ? '[DEFINIDA]' : '[VAZIA]'}`);
console.log(`   DB_NAME: ${process.env.DB_NAME}`);
console.log('');
console.log(`   DB_HOST_ONLINE: ${process.env.DB_HOST_ONLINE || '[NÃO DEFINIDA]'}`);
console.log(`   DB_USER_ONLINE: ${process.env.DB_USER_ONLINE || '[NÃO DEFINIDA]'}`);
console.log(`   DB_PASSWORD_ONLINE: ${process.env.DB_PASSWORD_ONLINE ? '[DEFINIDA]' : '[NÃO DEFINIDA]'}`);
console.log(`   DB_NAME_ONLINE: ${process.env.DB_NAME_ONLINE || '[NÃO DEFINIDA]'}`);
console.log('');
console.log(`   DATABASE_HOST: ${process.env.DATABASE_HOST}`);
console.log(`   DATABASE_PORT: ${process.env.DATABASE_PORT}`);
console.log(`   DATABASE_USER: ${process.env.DATABASE_USER}`);
console.log(`   DATABASE_PASSWORD: ${process.env.DATABASE_PASSWORD ? '[DEFINIDA]' : '[VAZIA]'}`);
console.log(`   DATABASE_NAME: ${process.env.DATABASE_NAME}`);
console.log('');
console.log(`   NODE_ENV: ${process.env.NODE_ENV}`);
console.log('');

// Testar configuração do Database
console.log('🔧 Testando configuração da classe Database...');
const Database = require('./config/database');
const db = Database.getInstance();

console.log('📋 Configuração atual do Database:');
console.log(`   Host: ${db.config.host}`);
console.log(`   User: ${db.config.user}`);
console.log(`   Password: ${db.config.password ? '[DEFINIDA]' : '[VAZIA]'}`);
console.log(`   Database: ${db.config.database}`);
console.log('');

// Verificar se forceOnlineConfig está sendo chamado
if (process.env.DB_HOST_ONLINE) {
    console.log('🌐 Aplicando configuração online...');
    db.forceOnlineConfig();
    console.log('📋 Nova configuração após forceOnlineConfig:');
    console.log(`   Host: ${db.config.host}`);
    console.log(`   User: ${db.config.user}`);
    console.log(`   Password: ${db.config.password ? '[DEFINIDA]' : '[VAZIA]'}`);
    console.log(`   Database: ${db.config.database}`);
}

console.log('🏁 Debug finalizado');