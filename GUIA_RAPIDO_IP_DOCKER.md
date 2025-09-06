# 🚀 Guia Rápido - IP Docker Dinâmico

## ⚡ Solução em 30 Segundos

### 1. Identificar o IP do Erro
```
Error: Access denied for user 'u690889028_mayconwender'@'172.18.0.X'
```
**IP a corrigir:** `172.18.0.X`

### 2. Conectar ao Servidor
```bash
ssh root@srv973908.hstgr.cloud
```

### 3. Executar Correção
```bash
./fix-ip.sh 172.18.0.X
```

**Substitua `172.18.0.X` pelo IP do erro!**

---

## 🔧 Comando Manual (se não tiver o script)

```bash
mysql -e "CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'172.18.0.X' IDENTIFIED BY 'Maycon@2024'; GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'172.18.0.X'; FLUSH PRIVILEGES;"
```

---

## 📋 Histórico de IPs Resolvidos

- ✅ `172.18.0.8` - Primeiro IP
- ✅ `172.18.0.11` - IP atual
- 🔄 `172.18.0.X` - Próximos IPs (usar script)

---

## 🆘 Se o Script Não Existir

### Recriar o script fix-ip.sh:

```bash
cat > fix-ip.sh << 'EOF'
#!/bin/bash
if [ -z "$1" ]; then
    echo "Uso: ./fix-ip.sh <IP>"
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
EOF

chmod +x fix-ip.sh
```

---

**⏱️ Tempo total de correção: ~30 segundos**