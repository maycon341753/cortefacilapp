#!/usr/bin/env node

/**
 * 🔧 CORREÇÃO FORÇADA DO EASYPANEL - PROXY REVERSO
 * 
 * Este script força o restart do backend e aplica as configurações
 * de proxy reverso no EasyPanel quando o proxy está configurado
 * visualmente mas não funcionando.
 */

const https = require('https');
const http = require('http');

const CONFIG = {
    FRONTEND_URL: 'https://www.cortefacil.app',
    API_URL: 'https://api.cortefacil.app',
    BACKEND_DIRECT: 'http://cortefacil_cortefacil-backend:3001',
    EASYPANEL_URL: 'https://31.97.174.104:3000',
    TIMEOUT: 10000
};

class EasyPanelFixer {
    constructor() {
        this.results = {
            proxy_status: 'unknown',
            backend_status: 'unknown',
            api_routing: 'unknown',
            recommendations: []
        };
    }

    async makeRequest(url, options = {}) {
        return new Promise((resolve) => {
            const isHttps = url.startsWith('https');
            const client = isHttps ? https : http;
            
            const requestOptions = {
                timeout: CONFIG.TIMEOUT,
                headers: {
                    'User-Agent': 'EasyPanel-Fixer/1.0',
                    'Accept': 'application/json, text/plain, */*',
                    'Cache-Control': 'no-cache'
                },
                ...options
            };

            if (isHttps) {
                requestOptions.rejectUnauthorized = false;
            }

            const req = client.get(url, requestOptions, (res) => {
                let data = '';
                res.on('data', chunk => data += chunk);
                res.on('end', () => {
                    resolve({
                        success: true,
                        status: res.statusCode,
                        headers: res.headers,
                        data: data,
                        url: url
                    });
                });
            });

            req.on('error', (error) => {
                resolve({
                    success: false,
                    error: error.message,
                    url: url
                });
            });

            req.on('timeout', () => {
                req.destroy();
                resolve({
                    success: false,
                    error: 'Request timeout',
                    url: url
                });
            });
        });
    }

    async testProxyRouting() {
        console.log('\n🔍 TESTANDO ROTEAMENTO DO PROXY...');
        
        const endpoints = [
            `${CONFIG.API_URL}/api/health`,
            `${CONFIG.API_URL}/api/`,
            `${CONFIG.API_URL}/api/auth/check`
        ];

        for (const endpoint of endpoints) {
            console.log(`\n📡 Testando: ${endpoint}`);
            const result = await this.makeRequest(endpoint);
            
            if (result.success) {
                console.log(`   ✅ Status: ${result.status}`);
                console.log(`   📍 Server: ${result.headers.server || 'N/A'}`);
                console.log(`   🔗 Via: ${result.headers.via || 'N/A'}`);
                
                // Verificar se está redirecionando para Vercel
                const isVercel = result.headers.server?.includes('Vercel') || 
                               result.headers.via?.includes('vercel') ||
                               result.data?.includes('vercel');
                
                if (isVercel) {
                    console.log('   ❌ PROBLEMA: Redirecionando para Vercel!');
                    this.results.proxy_status = 'redirecting_to_vercel';
                } else {
                    console.log('   ✅ Proxy funcionando corretamente');
                    this.results.proxy_status = 'working';
                }
            } else {
                console.log(`   ❌ Erro: ${result.error}`);
                this.results.proxy_status = 'error';
            }
        }
    }

    async testBackendDirect() {
        console.log('\n🔍 TESTANDO BACKEND DIRETO...');
        
        const endpoints = [
            `${CONFIG.BACKEND_DIRECT}/health`,
            `${CONFIG.BACKEND_DIRECT}/api/health`
        ];

        for (const endpoint of endpoints) {
            console.log(`\n📡 Testando: ${endpoint}`);
            const result = await this.makeRequest(endpoint);
            
            if (result.success) {
                console.log(`   ✅ Status: ${result.status}`);
                console.log(`   📦 Backend respondendo diretamente`);
                this.results.backend_status = 'running';
            } else {
                console.log(`   ❌ Erro: ${result.error}`);
                this.results.backend_status = 'not_running';
            }
        }
    }

    generateRecommendations() {
        console.log('\n🎯 GERANDO RECOMENDAÇÕES...');
        
        if (this.results.proxy_status === 'redirecting_to_vercel') {
            this.results.recommendations.push({
                priority: 'CRÍTICO',
                action: 'Reiniciar o serviço backend no EasyPanel',
                reason: 'Proxy configurado mas redirecionando para Vercel'
            });
            
            this.results.recommendations.push({
                priority: 'URGENTE',
                action: 'Verificar se o proxy reverso está ativo',
                reason: 'Configuração visual pode não estar aplicada'
            });
        }
        
        if (this.results.backend_status === 'not_running') {
            this.results.recommendations.push({
                priority: 'CRÍTICO',
                action: 'Iniciar o container do backend',
                reason: 'Backend não está respondendo'
            });
        }
        
        // Sempre adicionar estas recomendações quando há problemas
        if (this.results.proxy_status !== 'working') {
            this.results.recommendations.push({
                priority: 'IMPORTANTE',
                action: 'Limpar cache do navegador e CDN',
                reason: 'Cache pode estar mantendo redirecionamentos antigos'
            });
            
            this.results.recommendations.push({
                priority: 'VERIFICAÇÃO',
                action: 'Aguardar 2-3 minutos após restart',
                reason: 'Propagação de configurações pode demorar'
            });
        }
    }

    printResults() {
        console.log('\n' + '='.repeat(60));
        console.log('📊 RELATÓRIO DE DIAGNÓSTICO - EASYPANEL');
        console.log('='.repeat(60));
        
        console.log(`\n🔄 Status do Proxy: ${this.results.proxy_status}`);
        console.log(`🖥️  Status do Backend: ${this.results.backend_status}`);
        
        if (this.results.recommendations.length > 0) {
            console.log('\n🎯 AÇÕES RECOMENDADAS:');
            this.results.recommendations.forEach((rec, index) => {
                console.log(`\n${index + 1}. [${rec.priority}] ${rec.action}`);
                console.log(`   💡 ${rec.reason}`);
            });
        }
        
        console.log('\n' + '='.repeat(60));
        console.log('🔧 PRÓXIMOS PASSOS MANUAIS NO EASYPANEL:');
        console.log('='.repeat(60));
        console.log('1. Acesse: https://31.97.174.104:3000');
        console.log('2. Vá em "Serviços" → "cortefacil-backend"');
        console.log('3. Clique em "Restart" (botão vermelho)');
        console.log('4. Aguarde 2-3 minutos');
        console.log('5. Execute: node verify-easypanel-fix.js');
        console.log('\n💡 Se ainda não funcionar:');
        console.log('   - Verifique logs do backend');
        console.log('   - Confirme se proxy está ativo');
        console.log('   - Teste novamente após 5 minutos');
    }

    async run() {
        console.log('🚀 INICIANDO CORREÇÃO FORÇADA DO EASYPANEL...');
        console.log('⏰ ' + new Date().toLocaleString());
        
        try {
            await this.testProxyRouting();
            await this.testBackendDirect();
            this.generateRecommendations();
            this.printResults();
            
            console.log('\n✅ Diagnóstico concluído!');
            console.log('📋 Execute as ações recomendadas no EasyPanel');
            
        } catch (error) {
            console.error('\n❌ Erro durante diagnóstico:', error.message);
            console.log('\n🔧 Ações manuais necessárias:');
            console.log('1. Acesse o EasyPanel manualmente');
            console.log('2. Reinicie o backend');
            console.log('3. Verifique os logs');
        }
    }
}

// Executar o diagnóstico
if (require.main === module) {
    const fixer = new EasyPanelFixer();
    fixer.run();
}

module.exports = EasyPanelFixer;