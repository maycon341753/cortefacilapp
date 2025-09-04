#!/bin/bash

# Script para corrigir permissões MySQL no Hostinger
# Executa via SSH no servidor srv973908.hstgr.cloud

echo "🔧 Corrigindo permissões MySQL no Hostinger..."
echo "📋 Servidor: srv973908.hstgr.cloud"
echo "👤 Usuário: u690889028_mayconwender"
echo "🗄️ Banco: u690889028_cortefacil"
echo ""

# Verificar se o servidor está acessível
echo "🔍 Verificando conectividade com o servidor..."
ping -c 3 srv973908.hstgr.cloud

if [ $? -ne 0 ]; then
    echo "❌ Servidor não está acessível!"
    exit 1
fi

echo "✅ Servidor acessível!"
echo ""

# Comandos SQL para executar
SQL_COMMANDS="
SELECT 'Verificando usuários existentes:' as info;
SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender';

SELECT 'Criando usuário com permissões remotas:' as info;
CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'%' IDENTIFIED BY 'Maycon341753';
GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'%';
FLUSH PRIVILEGES;

SELECT 'Verificando usuário criado:' as info;
SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender';

SELECT 'Verificando permissões:' as info;
SHOW GRANTS FOR 'u690889028_mayconwender'@'%';
"

echo "🔐 Conectando ao MySQL no servidor..."
echo "⚠️  Você precisará inserir:"
echo "   1. Senha SSH do root (se solicitada)"
echo "   2. Senha do MySQL root"
echo ""

# Executar comandos via SSH
ssh root@srv973908.hstgr.cloud << EOF
echo "📊 Verificando status do MySQL..."
sudo systemctl status mysql --no-pager -l

echo ""
echo "🔍 Verificando se MySQL está escutando em todas as interfaces..."
sudo ss -tlnp | grep :3306

echo ""
echo "🗄️ Executando comandos SQL..."
mysql -u root -p << SQL_EOF
$SQL_COMMANDS
SQL_EOF

echo ""
echo "🔍 Verificando configuração bind-address..."
grep -n "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf || echo "bind-address não encontrado"

echo ""
echo "🔥 Verificando firewall UFW..."
sudo ufw status | grep 3306 || echo "Porta 3306 não está explicitamente liberada no UFW"

echo ""
echo "✅ Comandos executados! Testando conexão local..."
mysql -u u690889028_mayconwender -p'Maycon341753' -h localhost u690889028_cortefacil -e "SELECT 'Conexão local OK!' as status;"

EOF

echo ""
echo "🎉 Script concluído!"
echo "📋 Próximos passos:"
echo "   1. Teste a conexão remota: node test-database-connection.js"
echo "   2. Se ainda não funcionar, verifique o firewall do Hostinger"
echo "   3. Considere usar SSH tunnel como alternativa"