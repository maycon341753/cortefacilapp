-- Script para corrigir permissões do usuário MySQL no Hostinger
-- Problema: Access denied for user 'u690889028_mayconwender'@'45.181.72.123'

-- 1. Verificar usuários existentes
SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender';

-- 2. Remover usuários existentes se necessário (cuidado!)
-- DROP USER IF EXISTS 'u690889028_mayconwender'@'localhost';
-- DROP USER IF EXISTS 'u690889028_mayconwender'@'%';

-- 3. Criar usuário que aceita conexões de qualquer IP
CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'%' IDENTIFIED BY 'Maycon341753';

-- 4. Conceder todas as permissões no banco específico
GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'%';

-- 5. Aplicar as mudanças
FLUSH PRIVILEGES;

-- 6. Verificar se o usuário foi criado corretamente
SELECT User, Host FROM mysql.user WHERE User = 'u690889028_mayconwender';

-- 7. Verificar permissões do usuário
SHOW GRANTS FOR 'u690889028_mayconwender'@'%';

-- ALTERNATIVA: Criar usuário para IP específico (mais seguro)
-- CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'45.181.72.123' IDENTIFIED BY 'Maycon341753';
-- GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'45.181.72.123';
-- FLUSH PRIVILEGES;

-- COMANDOS PARA EXECUTAR NO VPS VIA SSH:
-- 1. ssh root@srv973908.hstgr.cloud
-- 2. mysql -u root -p
-- 3. Executar os comandos SQL acima
-- 4. EXIT;

-- TESTE DE CONEXÃO LOCAL NO VPS:
-- mysql -u u690889028_mayconwender -p -h localhost u690889028_cortefacil

-- VERIFICAR SE MYSQL ESTÁ ESCUTANDO EM TODAS AS INTERFACES:
-- sudo ss -tlnp | grep :3306
-- Deve mostrar: 0.0.0.0:3306 (não apenas 127.0.0.1:3306)

-- SE NECESSÁRIO, CONFIGURAR bind-address NO MySQL:
-- sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
-- Alterar: bind-address = 0.0.0.0
-- sudo systemctl restart mysql