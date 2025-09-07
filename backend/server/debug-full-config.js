#!/usr/bin/env node

/**
 * Script para debug completo das configurações
 * Verifica todas as possíveis fontes de configuração
 */

const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

console.log('🔍 DEBUG COMPLETO DAS CONFIGURAÇÕES');
console.log('=' .repeat(60));

// 1. Verificar arquivos .env existentes
console.log('📁 Arquivos .env encontrados:');
const envFiles = ['.env', '.env.local', '.env.easypanel', '.env.tunnel'];
envFiles.forEach(file => {
    if (fs.existsSync(file)) {
        console.log(`   ✅ ${file} existe`);
    } else {
        console.log(`   ❌ ${file} não existe`);
    }
});
console.log('');

// 2. Carregar .env.easypanel
console.log('🔧 Carregando .env.easypanel...');
dotenv.config({ path: '.env.easypanel' });
console.log('✅ Arquivo carregado');
console.log('');

// 3. Verificar todas as variáveis de ambiente relacionadas a DB
console.log('🌍 Variáveis de ambiente DB:');
Object.keys(process.env)
    .filter(key => key.includes('DB') || key.includes('DATABASE') || key.includes('MYSQL'))
    .sort()
    .forEach(key => {
        const value = process.env[key];
        if (value && value.includes('password') || key.toLowerCase().includes('pass')) {
            console.log(`   ${key}: [SENHA OCULTA]`);
        } else {
            console.log(`   ${key}: ${value}`);
        }
    });
console.log('');

// 4. Verificar configuração da classe Database
console.log('🔧 Configuração da classe Database:');
const Database = require('./config/database');
const db = Database.getInstance();
console.log(`   Host: ${db.config.host}`);
console.log(`   User: ${db.config.user}`);
console.log(`   Password: ${db.config.password ? '[DEFINIDA]' : '[VAZIA]'}`);
console.log(`   Database: ${db.config.database}`);
console.log('');

// 5. Verificar se há cache de módulos
console.log('📦 Cache de módulos:');
const cacheKeys = Object.keys(require.cache).filter(key => 
    key.includes('database') || key.includes('config') || key.includes('.env')
);
console.log(`   Módulos em cache: ${cacheKeys.length}`);
cacheKeys.forEach(key => {
    console.log(`   - ${key}`);
});
console.log('');

// 6. Verificar configuração do server.js
console.log('🖥️  Configuração do server.js:');
const serverDbConfig = {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'cortefacil'
};
console.log(`   Host: ${serverDbConfig.host}`);
console.log(`   User: ${serverDbConfig.user}`);
console.log(`   Password: ${serverDbConfig.password ? '[DEFINIDA]' : '[VAZIA]'}`);
console.log(`   Database: ${serverDbConfig.database}`);
console.log('');

// 7. Testar conexão direta
console.log('🔌 Testando conexão direta:');
const mysql = require('mysql2/promise');

async function testDirectConnection() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            connectTimeout: 5000
        });
        
        console.log('   ✅ Conexão direta bem-sucedida!');
        await connection.end();
    } catch (error) {
        console.log('   ❌ Erro na conexão direta:', error.message);
        if (error.message.includes('ENOTFOUND')) {
            console.log('   🔍 Hostname não encontrado:', error.hostname);
        }
    }
}

testDirectConnection().then(() => {
    console.log('\n🏁 Debug completo finalizado');
}).catch(console.error);