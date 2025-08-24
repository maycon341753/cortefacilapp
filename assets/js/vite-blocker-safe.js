/**
 * Bloqueador de requisições Vite (Versão Segura)
 * Previne o erro net::ERR_ABORTED causado por extensões do navegador
 * SEM Service Worker para evitar interferência com navegação
 */

(function() {
    'use strict';
    
    console.log('🛡️ Vite Blocker Seguro ativado');
    
    // Interceptar fetch
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const url = args[0];
        if (typeof url === 'string' && url.includes('@vite')) {
            console.warn('🚫 Requisição Vite bloqueada:', url);
            return Promise.reject(new Error('Vite request blocked'));
        }
        return originalFetch.apply(this, args);
    };
    
    // Interceptar XMLHttpRequest
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, ...args) {
        if (typeof url === 'string' && url.includes('@vite')) {
            console.warn('🚫 XHR Vite bloqueada:', url);
            throw new Error('Vite XHR blocked');
        }
        return originalXHROpen.call(this, method, url, ...args);
    };
    
    // Interceptar criação de elementos
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Verificar scripts
                    if (node.tagName === 'SCRIPT' && node.src && node.src.includes('@vite')) {
                        console.warn('🚫 Script Vite removido:', node.src);
                        node.remove();
                    }
                    // Verificar links
                    if (node.tagName === 'LINK' && node.href && node.href.includes('@vite')) {
                        console.warn('🚫 Link Vite removido:', node.href);
                        node.remove();
                    }
                    // Verificar elementos filhos
                    const viteScripts = node.querySelectorAll && node.querySelectorAll('script[src*="@vite"], link[href*="@vite"]');
                    if (viteScripts && viteScripts.length > 0) {
                        viteScripts.forEach(function(element) {
                            console.warn('🚫 Elemento Vite filho removido:', element);
                            element.remove();
                        });
                    }
                }
            });
        });
    });
    
    // Observar mudanças no DOM
    if (document.documentElement) {
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            observer.observe(document.documentElement, {
                childList: true,
                subtree: true
            });
        });
    }
    
    // Interceptar addEventListener APENAS para eventos de erro relacionados ao Vite
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        if (type === 'error' && typeof listener === 'function') {
            const wrappedListener = function(event) {
                // Suprimir APENAS erros relacionados ao Vite
                if (event.target && event.target.src && event.target.src.includes('@vite')) {
                    console.warn('🚫 Erro Vite suprimido:', event.target.src);
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
                return listener.call(this, event);
            };
            return originalAddEventListener.call(this, type, wrappedListener, options);
        }
        return originalAddEventListener.call(this, type, listener, options);
    };
    
    // Interceptar window.onerror APENAS para erros do Vite
    const originalOnError = window.onerror;
    window.onerror = function(message, source, lineno, colno, error) {
        if (source && source.includes('@vite')) {
            console.warn('🚫 Erro global Vite suprimido:', source);
            return true; // Previne o erro padrão
        }
        if (originalOnError) {
            return originalOnError.call(this, message, source, lineno, colno, error);
        }
        return false;
    };
    
    // Interceptar unhandledrejection APENAS para Promises do Vite
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.message && event.reason.message.includes('Vite')) {
            console.warn('🚫 Promise rejection Vite suprimida:', event.reason);
            event.preventDefault();
        }
    });
    
    // NÃO registrar Service Worker para evitar interferência com navegação
    console.log('✅ Vite Blocker Seguro configurado (sem Service Worker)');
})();