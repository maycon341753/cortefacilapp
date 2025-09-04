# 🔍 Como Verificar o Banco de Dados Online no EasyPanel

## ❌ Resultado do Teste Local

O teste executado localmente falhou (como esperado) porque:
- Os hosts internos do EasyPanel (`cortefacil_cortefacil_user`, `cortefacil_user`) só funcionam dentro da rede do EasyPanel
- Não é possível conectar externamente sem configuração especial

## ✅ Como Verificar se o Banco Está Online

### 1. 🖥️ Verificação pelo Painel EasyPanel

1. **Acesse seu painel EasyPanel**
2. **Vá para o serviço MySQL** (`cortefacil_user` ou similar)
3. **Verifique o status:**
   - ✅ **Running** = Banco online
   - ❌ **Stopped** = Banco offline
4. **Clique em "Terminal"** para acessar o MySQL diretamente

### 2. 🔧 Comandos para Testar no Terminal do EasyPanel

```bash
# Conectar ao MySQL
mysql -u mayconwender -p
# Digite a senha: Maycon341753@

# Verificar bancos disponíveis
SHOW DATABASES;

# Usar o banco do projeto
USE u690889028_cortefacil;

# Verificar tabelas
SHOW TABLES;

# Verificar usuários
SELECT COUNT(*) as total_usuarios FROM usuarios;

# Verificar usuário admin
SELECT id, nome, email, tipo_usuario FROM usuarios WHERE tipo_usuario = 'admin';

# Verificar salões
SELECT COUNT(*) as total_saloes FROM saloes;

# Teste de conectividade
SELECT 'Banco funcionando!' as status, NOW() as data_hora;
```

### 3. 🚀 Verificação via Deploy

A melhor forma de verificar se o banco está funcionando é:

1. **Fazer deploy do backend no EasyPanel**
2. **Verificar os logs do container**
3. **Testar os endpoints da API**

### 4. 📊 Status Esperado do Banco

Se o banco estiver funcionando corretamente, você deve ver:

```
✅ Tabelas principais:
- usuarios (com usuário admin)
- saloes
- profissionais  
- agendamentos
- especialidades
- pagamentos
- password_resets

✅ Dados iniciais:
- Usuário admin: admin@cortefacil.com
- Senha admin: admin123 (hash bcrypt)
```

## 🔄 Configurações de Ambiente

### Para Desenvolvimento Local:
```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=cortefacil
```

### Para Produção EasyPanel:
```env
DB_HOST=cortefacil_cortefacil_user
DB_USER=mayconwender
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_cortefacil
```

## 🛠️ Troubleshooting

### Se o banco não estiver funcionando:

1. **Reiniciar o serviço MySQL no EasyPanel**
2. **Verificar logs do container MySQL**
3. **Recriar o banco usando o script SQL**
4. **Verificar variáveis de ambiente**
5. **Testar conexão via terminal**

### Comandos úteis no EasyPanel:

```bash
# Ver status dos serviços
docker ps

# Ver logs do MySQL
docker logs cortefacil_user

# Reiniciar serviço
docker restart cortefacil_user
```

## 📝 Próximos Passos

1. ✅ **Verificar status no painel EasyPanel**
2. ✅ **Testar conexão via terminal**
3. ✅ **Executar comandos SQL de verificação**
4. ✅ **Fazer deploy do backend para teste completo**
5. ✅ **Verificar logs da aplicação**

---

**💡 Lembre-se:** O banco online só pode ser testado completamente de dentro da rede do EasyPanel ou via deploy da aplicação.