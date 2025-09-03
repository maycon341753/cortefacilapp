#!/usr/bin/env node

/**
 * Script para configurar o banco de dados do CorteFácil
 * 
 * Uso:
 * npm run setup-db
 * ou
 * node setup-database.js
 */

const { initializeDatabase } = require('./backend/server/scripts/init-database');
const dotenv = require('dotenv');

// Carregar variáveis de ambiente
dotenv.config();

console.log('🎯 CorteFácil - Configuração do Banco de Dados');
console.log('=' .repeat(50));
console.log('');

// Mostrar configurações
console.log('📋 Configurações do banco:');
console.log(`   Host: ${process.env.DB_HOST || 'localhost'}`);
console.log(`   Usuário: ${process.env.DB_USER || 'root'}`);
console.log(`   Banco: ${process.env.DB_NAME || 'cortefacil'}`);
console.log(`   Senha: ${process.env.DB_PASSWORD ? '[CONFIGURADA]' : '[VAZIA]'}`);
console.log('');

// Executar inicialização
initializeDatabase()
    .then(() => {
        console.log('');
        console.log('🎉 Configuração concluída com sucesso!');
        console.log('');
        console.log('📝 Próximos passos:');
        console.log('   1. Inicie o servidor: npm run dev');
        console.log('   2. Acesse: http://localhost:3001');
        console.log('   3. Login admin: admin@cortefacil.com');
        console.log('');
        process.exit(0);
    })
    .catch((error) => {
        console.log('');
        console.error('💥 Erro na configuração:', error.message);
        console.log('');
        console.log('🔧 Possíveis soluções:');
        console.log('   1. Verifique se o MySQL está rodando');
        console.log('   2. Confirme as credenciais no arquivo .env');
        console.log('   3. Verifique se o usuário tem permissões para criar bancos');
        console.log('');
        process.exit(1);
    });