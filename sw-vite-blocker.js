/**
 * Service Worker para bloquear requisições @vite
 * Esta é a solução mais eficaz para prevenir o erro net::ERR_ABORTED
 */

const CACHE_NAME = 'cortefacil-vite-blocker-v1';

// Instalar o service worker
self.addEventListener('install', function(event) {
    console.log('🛡️ Vite Blocker Service Worker instalado');
    self.skipWaiting();
});

// Ativar o service worker
self.addEventListener('activate', function(event) {
    console.log('✅ Vite Blocker Service Worker ativado');
    event.waitUntil(self.clients.claim());
});

// Interceptar todas as requisições
self.addEventListener('fetch', function(event) {
    const url = event.request.url;
    
    // Bloquear requisições que contenham @vite
    if (url.includes('@vite')) {
        console.warn('🚫 Service Worker bloqueou requisição Vite:', url);
        
        // Retornar uma resposta vazia para evitar o erro
        event.respondWith(
            new Response('', {
                status: 204,
                statusText: 'Vite Request Blocked',
                headers: {
                    'Content-Type': 'text/plain',
                    'X-Blocked-By': 'CorteFacil-Vite-Blocker'
                }
            })
        );
        return;
    }
    
    // Para outras requisições, deixar passar normalmente
    event.respondWith(fetch(event.request));
});

// Lidar com mensagens do cliente
self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({
            version: CACHE_NAME,
            status: 'active'
        });
    }
});

console.log('🛡️ Vite Blocker Service Worker carregado');