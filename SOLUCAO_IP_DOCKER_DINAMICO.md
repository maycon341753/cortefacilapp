# Solução para IPs Docker Dinâmicos

## 🎯 Problema

O Docker atribui IPs dinâmicos aos containers, causando erros como:
```
Access denied for user 'u690889028_mayconwender'@'172.18.0.11'
```

## 🔧 Solução Rápida

### Via SSH no Servidor

1. **Conectar ao servidor:**
   ```bash
   ssh root@srv973908.hstgr.cloud
   ```

2. **Criar usuário para novo IP:**
   ```sql
   mysql -e "CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'172.18.0.X' IDENTIFIED BY 'Maycon@2024';"
   mysql -e "GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'172.18.0.X';"
   mysql -e "FLUSH PRIVILEGES;"
   ```

   **Substitua `172.18.0.X` pelo IP do erro atual.**

## 🚀 Solução Automatizada

### Script de Correção Rápida

Crie um arquivo `fix-ip.sh` no servidor:

```bash
#!/bin/bash
# Arquivo: fix-ip.sh

if [ -z "$1" ]; then
    echo "Uso: ./fix-ip.sh <IP>"
    echo "Exemplo: ./fix-ip.sh 172.18.0.11"
    exit 1
fi

IP=$1
USER="u690889028_mayconwender"
PASS="Maycon@2024"
DB="u690889028_cortefacil"

echo "🔧 Criando usuário para IP: $IP"

mysql -e "CREATE USER IF NOT EXISTS '$USER'@'$IP' IDENTIFIED BY '$PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB.* TO '$USER'@'$IP';"
mysql -e "FLUSH PRIVILEGES;"

echo "✅ Usuário criado com sucesso!"
echo "📋 Verificando usuários existentes:"
mysql -e "SELECT user, host FROM mysql.user WHERE user = '$USER' ORDER BY host;"
```

### Como usar:

1. **No servidor, criar o script:**
   ```bash
   nano fix-ip.sh
   chmod +x fix-ip.sh
   ```

2. **Executar quando necessário:**
   ```bash
   ./fix-ip.sh 172.18.0.11
   ```

## 📋 IPs Já Configurados

- ✅ `u690889028_mayconwender@%` (qualquer IP)
- ✅ `u690889028_mayconwender@172.18.0.11` (IP atual)

## 🔍 Como Identificar o Novo IP

Quando ocorrer o erro, procure por:
```
Access denied for user 'u690889028_mayconwender'@'172.18.0.X'
```

O IP `172.18.0.X` é o que precisa ser adicionado.

## ⚡ Solução de Emergência

### Comando Único (via SSH)

```bash
# Substitua 172.18.0.X pelo IP do erro
mysql -e "CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'172.18.0.X' IDENTIFIED BY 'Maycon@2024'; GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'172.18.0.X'; FLUSH PRIVILEGES;"
```

## 🛡️ Prevenção

### Opção 1: IP Fixo no Docker

No `docker-compose.yml`:
```yaml
services:
  app:
    networks:
      - app-network

networks:
  app-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
          ip_range: 172.20.240.0/20
```

### Opção 2: Usuário com Wildcard

Já configurado: `u690889028_mayconwender@%` permite acesso de qualquer IP.

## 📞 Troubleshooting

### Se o erro persistir:

1. **Verificar usuários existentes:**
   ```sql
   SELECT user, host FROM mysql.user WHERE user = 'u690889028_mayconwender';
   ```

2. **Verificar permissões:**
   ```sql
   SHOW GRANTS FOR 'u690889028_mayconwender'@'172.18.0.X';
   ```

3. **Reiniciar aplicação Docker:**
   ```bash
   docker-compose restart
   ```

## 📝 Histórico de IPs

- `172.18.0.8` - IP inicial (resolvido)
- `172.18.0.11` - IP atual (resolvido)
- `172.18.0.X` - Próximos IPs (usar script)

---

**Status:** ✅ **RESOLVIDO**  
**Última atualização:** $(date)  
**Próximo IP:** Use o script `fix-ip.sh` ou comando único