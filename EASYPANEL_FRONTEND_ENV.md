# Configuração de Variáveis de Ambiente - Frontend EasyPanel

## Problema Identificado
O frontend está configurado para usar a API local (localhost:3001) em vez da API de produção.

## Variáveis de Ambiente Necessárias no EasyPanel

### Frontend (cortefacil-frontend)
```
VITE_API_URL=https://api.cortefacil.app/api
VITE_APP_NAME=CortefácilApp
VITE_APP_VERSION=1.0.0
VITE_FRONTEND_URL=https://cortefacil.app
```

## Como Configurar no EasyPanel

### Para o Frontend:
1. **Acesse o serviço cortefacil-frontend**
2. **Vá para a aba "Environment"**
3. **Adicione as variáveis acima**
4. **Salve as configurações**
5. **Reinicie o serviço**

## Verificação da Configuração de Domínios

### Frontend (cortefacil-frontend)
- **Host**: `cortefacil.app`
- **Porta**: `3000` (ou a porta que o Vite usa)
- **HTTPS**: Ativado
- **SSL**: Let's Encrypt

### Backend (cortefacil-backend)
- **Host**: `api.cortefacil.app`
- **Porta**: `3001`
- **HTTPS**: Ativado
- **SSL**: Let's Encrypt

## Configuração DNS Necessária

### No seu provedor de domínio:
1. **Registro A**: `cortefacil.app` → IP do servidor EasyPanel
2. **Registro CNAME**: `api.cortefacil.app` → `cortefacil.app`

## Ordem de Configuração Recomendada

1. ✅ **Configurar Backend primeiro**
   - Adicionar variáveis de ambiente
   - Reiniciar serviço
   - Verificar se fica verde

2. ✅ **Configurar Frontend**
   - Adicionar variáveis de ambiente
   - Reiniciar serviço
   - Verificar se fica verde

3. ✅ **Configurar SSL**
   - Ativar Let's Encrypt para ambos
   - Aguardar geração dos certificados

4. ✅ **Executar Migrações**
   - Usar terminal do backend para executar schema.sql

## Status Esperado
Após todas as configurações:
- 🟢 cortefacil-backend (verde)
- 🟢 cortefacil-frontend (verde)
- 🟢 cortefacil_user (MySQL - já está verde)