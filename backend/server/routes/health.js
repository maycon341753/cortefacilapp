const express = require('express');
const router = express.Router();
const Database = require('../config/database');
const os = require('os');

const db = Database.getInstance();

// GET /api/health - Verificação de saúde do sistema
router.get('/', async (req, res) => {
    const startTime = Date.now();
    
    try {
        const healthStatus = {
            healthy: true,
            timestamp: new Date().toISOString(),
            server_time: new Date().toLocaleString('pt-BR'),
            uptime: process.uptime(),
            environment: process.env.NODE_ENV || 'development',
            version: process.env.npm_package_version || '1.0.0',
            checks: {}
        };
        
        // Verificar conexão com banco de dados
        try {
            const dbStart = Date.now();
            await db.testConnection();
            const dbTime = Date.now() - dbStart;
            
            healthStatus.checks.database = {
                status: 'healthy',
                response_time: `${dbTime}ms`,
                message: 'Conexão com banco de dados OK'
            };
        } catch (dbError) {
            healthStatus.healthy = false;
            healthStatus.checks.database = {
                status: 'unhealthy',
                error: dbError.message,
                message: 'Erro na conexão com banco de dados'
            };
        }
        
        // Verificar uso de memória
        const memoryUsage = process.memoryUsage();
        const totalMemory = os.totalmem();
        const freeMemory = os.freemem();
        const usedMemory = totalMemory - freeMemory;
        const memoryPercentage = ((usedMemory / totalMemory) * 100).toFixed(2);
        
        healthStatus.checks.memory = {
            status: memoryPercentage < 90 ? 'healthy' : 'warning',
            usage: {
                rss: `${Math.round(memoryUsage.rss / 1024 / 1024)}MB`,
                heapTotal: `${Math.round(memoryUsage.heapTotal / 1024 / 1024)}MB`,
                heapUsed: `${Math.round(memoryUsage.heapUsed / 1024 / 1024)}MB`,
                external: `${Math.round(memoryUsage.external / 1024 / 1024)}MB`
            },
            system: {
                total: `${Math.round(totalMemory / 1024 / 1024 / 1024)}GB`,
                free: `${Math.round(freeMemory / 1024 / 1024 / 1024)}GB`,
                used_percentage: `${memoryPercentage}%`
            }
        };
        
        // Verificar CPU
        const cpus = os.cpus();
        const loadAverage = os.loadavg();
        
        healthStatus.checks.cpu = {
            status: loadAverage[0] < cpus.length * 0.8 ? 'healthy' : 'warning',
            cores: cpus.length,
            model: cpus[0]?.model || 'Unknown',
            load_average: {
                '1min': loadAverage[0].toFixed(2),
                '5min': loadAverage[1].toFixed(2),
                '15min': loadAverage[2].toFixed(2)
            }
        };
        
        // Verificar espaço em disco (simplificado)
        healthStatus.checks.disk = {
            status: 'healthy',
            message: 'Verificação de disco não implementada'
        };
        
        // Informações do sistema
        healthStatus.system = {
            platform: os.platform(),
            arch: os.arch(),
            hostname: os.hostname(),
            node_version: process.version,
            pid: process.pid
        };
        
        // Tempo total de resposta
        const totalTime = Date.now() - startTime;
        healthStatus.response_time = `${totalTime}ms`;
        
        // Determinar status HTTP
        const httpStatus = healthStatus.healthy ? 200 : 503;
        
        res.status(httpStatus).json(healthStatus);
        
    } catch (error) {
        console.error('Erro no health check:', error);
        
        const errorResponse = {
            healthy: false,
            timestamp: new Date().toISOString(),
            error: 'Erro interno do sistema',
            message: 'Sistema indisponível',
            details: process.env.NODE_ENV === 'development' ? error.message : undefined
        };
        
        res.status(500).json(errorResponse);
    }
});

// GET /api/health/simple - Verificação simples (apenas status)
router.get('/simple', async (req, res) => {
    try {
        // Teste rápido de conexão com banco
        await db.testConnection();
        
        res.json({
            status: 'healthy',
            timestamp: new Date().toISOString(),
            message: 'Sistema funcionando normalmente'
        });
        
    } catch (error) {
        res.status(503).json({
            status: 'unhealthy',
            timestamp: new Date().toISOString(),
            message: 'Sistema com problemas',
            error: error.message
        });
    }
});

// GET /api/health/database - Verificação específica do banco
router.get('/database', async (req, res) => {
    try {
        const startTime = Date.now();
        
        // Testar conexão
        await db.testConnection();
        
        // Testar query simples
        const result = await db.query('SELECT 1 as test, NOW() as current_time');
        
        const responseTime = Date.now() - startTime;
        
        res.json({
            status: 'healthy',
            timestamp: new Date().toISOString(),
            database: {
                connection: 'OK',
                query_test: 'OK',
                response_time: `${responseTime}ms`,
                server_time: result[0]?.current_time
            }
        });
        
    } catch (error) {
        console.error('Erro no health check do banco:', error);
        
        res.status(503).json({
            status: 'unhealthy',
            timestamp: new Date().toISOString(),
            database: {
                connection: 'ERROR',
                error: error.message
            }
        });
    }
});

// GET /api/health/metrics - Métricas detalhadas
router.get('/metrics', (req, res) => {
    try {
        const memoryUsage = process.memoryUsage();
        const cpuUsage = process.cpuUsage();
        
        const metrics = {
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            memory: {
                rss: memoryUsage.rss,
                heapTotal: memoryUsage.heapTotal,
                heapUsed: memoryUsage.heapUsed,
                external: memoryUsage.external,
                arrayBuffers: memoryUsage.arrayBuffers
            },
            cpu: {
                user: cpuUsage.user,
                system: cpuUsage.system
            },
            system: {
                platform: os.platform(),
                arch: os.arch(),
                cpus: os.cpus().length,
                totalmem: os.totalmem(),
                freemem: os.freemem(),
                loadavg: os.loadavg()
            },
            process: {
                pid: process.pid,
                version: process.version,
                versions: process.versions
            }
        };
        
        res.json(metrics);
        
    } catch (error) {
        console.error('Erro ao obter métricas:', error);
        res.status(500).json({
            error: 'Erro ao obter métricas do sistema',
            message: error.message
        });
    }
});

module.exports = router;