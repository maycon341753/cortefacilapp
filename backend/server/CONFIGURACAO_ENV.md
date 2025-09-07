# 📋 Configuração de Variáveis de Ambiente

## 📁 Arquivos de Configuração Disponíveis

### 1. `.env` - Desenvolvimento Local (XAMPP)
```bash
# Para desenvolvimento local com XAMPP
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=cortefacil
```

### 2. `.env.local` - Desenvolvimento com EasyPanel
```bash
# Para testes locais conectando ao EasyPanel
DB_HOST=31.97.171.104
DB_USER=mysql
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_mayconwender
```

### 3. `.env.easypanel` - Produção EasyPanel
```bash
# Para deploy no EasyPanel (configuração interna)
DB_HOST=cortefacil_u690889028_mayconwender
DB_USER=mysql
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_mayconwender
```

## 🔧 Como Usar

### Desenvolvimento Local (XAMPP)
1. Use o arquivo `.env`
2. Certifique-se que o XAMPP está rodando
3. Crie o banco `cortefacil` no phpMyAdmin local

### Desenvolvimento com EasyPanel
1. Use o arquivo `.env.local`
2. Conecta diretamente ao banco de produção
3. **Cuidado:** Alterações afetam dados reais

### Deploy no EasyPanel
1. Use o arquivo `.env.easypanel`
2. Configurações otimizadas para produção
3. Host interno para melhor performance

## 📊 Informações do Banco EasyPanel

**Credenciais Confirmadas:**
- **Usuário:** `mysql`
- **Senha:** `Maycon341753@`
- **Banco:** `u690889028_mayconwender`
- **Host Interno:** `cortefacil_u690889028_mayconwender:3306`
- **Host Externo:** `31.97.171.104:3306`

## 🚀 Comandos Úteis

```bash
# Copiar configuração para desenvolvimento local
cp .env.example .env

# Copiar configuração para EasyPanel
cp .env.easypanel .env

# Testar conexão
node ../test-db-connection.js
```

## ⚠️ Observações Importantes

1. **Nunca commite arquivos `.env` com credenciais reais**
2. **Use `.env.example` como template**
3. **Mantenha senhas seguras e atualizadas**
4. **Teste sempre as conexões após mudanças**

## 🔍 Troubleshooting

Se houver problemas de conexão:
1. Verifique se as credenciais estão corretas
2. Teste com o host externo se o interno falhar
3. Consulte os arquivos de solução na raiz do projeto:
   - `SOLUCAO_EASYPANEL_MYSQL.md`
   - `SOLUCAO_DOCKER_IP.md`
   - `GUIA_SSH_TUNNEL_EASYPANEL.md`