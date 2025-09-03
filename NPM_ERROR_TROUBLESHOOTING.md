# 🔧 NPM Error Troubleshooting - EasyPanel Deploy

## 📋 Erro Reportado
```
npm error A complete log of this run can be found in: /home/nodejs/.npm/_logs/2025-09-01T20_39_04_796Z-debug-0.log
```

## 🔍 Possíveis Causas e Soluções

### 1. **Problema de Dependências**

**Causa**: Conflitos ou dependências incompatíveis no `package.json`

**Solução**:
```bash
# Limpar cache do npm
npm cache clean --force

# Remover node_modules e package-lock.json
rm -rf node_modules package-lock.json

# Reinstalar dependências
npm install
```

### 2. **Problema de Memória**

**Causa**: Insuficiência de memória durante a instalação

**Solução**:
```bash
# Instalar com limite de memória reduzido
npm install --max_old_space_size=4096

# Ou usar yarn como alternativa
yarn install
```

### 3. **Problema de Permissões**

**Causa**: Permissões inadequadas no diretório do projeto

**Solução**:
```bash
# Corrigir permissões do diretório
sudo chown -R nodejs:nodejs /app
sudo chmod -R 755 /app
```

### 4. **Problema de Registry**

**Causa**: Problemas de conectividade com o registry do npm

**Solução**:
```bash
# Configurar registry alternativo
npm config set registry https://registry.npmjs.org/

# Ou usar registry do Yarn
npm config set registry https://registry.yarnpkg.com/
```

### 5. **Problema de Node.js Version**

**Causa**: Incompatibilidade de versão do Node.js

**Solução**:
- Verificar se a versão do Node.js no EasyPanel é compatível
- Atualizar `engines` no `package.json`:
```json
{
  "engines": {
    "node": ">=18.0.0",
    "npm": ">=8.0.0"
  }
}
```

## 🚀 Passos de Resolução Recomendados

### Passo 1: Verificar package.json
```bash
# Verificar se há dependências conflitantes
npm ls

# Verificar vulnerabilidades
npm audit
npm audit fix
```

### Passo 2: Limpar e Reinstalar
```bash
# No EasyPanel, via terminal do container
docker exec -it <container_name> /bin/bash

# Dentro do container
cd /app
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Passo 3: Verificar Logs Detalhados
```bash
# Acessar o log específico mencionado
cat /home/nodejs/.npm/_logs/2025-09-01T20_39_04_796Z-debug-0.log

# Ou verificar logs mais recentes
ls -la /home/nodejs/.npm/_logs/
cat /home/nodejs/.npm/_logs/$(ls -t /home/nodejs/.npm/_logs/ | head -1)
```

### Passo 4: Alternativas de Deploy

**Opção A: Usar Yarn**
```dockerfile
# No Dockerfile, substituir npm por yarn
RUN yarn install --frozen-lockfile
RUN yarn build
```

**Opção B: Usar npm ci**
```dockerfile
# Para builds mais confiáveis
RUN npm ci --only=production
```

**Opção C: Multi-stage build**
```dockerfile
# Build stage
FROM node:18-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

# Production stage
FROM node:18-alpine
WORKDIR /app
COPY --from=builder /app/node_modules ./node_modules
COPY . .
EXPOSE 3000
CMD ["npm", "start"]
```

## 🔧 Comandos de Debug no EasyPanel

### Acessar Container
```bash
# Via EasyPanel terminal
docker ps
docker exec -it <container_id> /bin/bash
```

### Verificar Recursos
```bash
# Verificar memória disponível
free -h

# Verificar espaço em disco
df -h

# Verificar versão do Node.js
node --version
npm --version
```

### Verificar Variáveis de Ambiente
```bash
# Verificar se todas as variáveis estão definidas
env | grep -E '(NODE_ENV|DB_|PORT)'
```

## 📝 Checklist de Verificação

- [ ] Verificar se o `package.json` está correto
- [ ] Confirmar que todas as dependências são compatíveis
- [ ] Verificar se há espaço suficiente em disco
- [ ] Confirmar que a versão do Node.js é compatível
- [ ] Verificar se as variáveis de ambiente estão definidas
- [ ] Confirmar que o MySQL está acessível (já resolvido)
- [ ] Verificar se não há conflitos de porta
- [ ] Confirmar que o Dockerfile está otimizado

## 🆘 Se Nada Funcionar

1. **Fazer rebuild completo do container**
2. **Verificar logs do EasyPanel**
3. **Tentar deploy em ambiente local primeiro**
4. **Considerar usar imagem Docker diferente**
5. **Contatar suporte do EasyPanel se necessário**

---

**Próximo Passo**: Acessar o EasyPanel e verificar os logs específicos do container para identificar a causa exata do erro.