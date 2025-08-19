# 🚀 Deploy Online - CorteFácil

## ✅ Problema Resolvido

O sistema agora detecta automaticamente o ambiente (local vs online) e usa as configurações corretas:

- **Local (XAMPP):** `localhost`
- **Online (Hostinger):** `srv488.hstgr.io`

## 📋 Checklist para Deploy Online

### 1. Upload dos Arquivos
- [ ] Faça upload de todos os arquivos para `public_html/` no Hostinger
- [ ] Mantenha a estrutura de pastas intacta
- [ ] Verifique se o arquivo `.htaccess` foi enviado

### 2. Configuração do Banco de Dados
- [ ] Acesse o painel Hostinger → Databases
- [ ] Confirme que o banco `u690889028_cortefacil` existe
- [ ] Verifique se o usuário `u690889028_mayconwender` tem acesso
- [ ] Importe o arquivo `database/schema.sql` via phpMyAdmin

### 3. Permissões de Arquivos
Defina as seguintes permissões:
- **Arquivos PHP:** `644`
- **Diretórios:** `755`
- **Arquivo .htaccess:** `644`

### 4. Teste de Funcionamento
1. Acesse: `https://cortefacil.app/login.php`
2. Use as credenciais de teste:
   - **Email:** cliente@teste.com
   - **Senha:** 123456

## 🔧 Resolução de Problemas

### Erro 500 (Internal Server Error)

**Possíveis causas:**
1. **Arquivo .htaccess problemático**
   - Renomeie `.htaccess` para `.htaccess_backup` temporariamente
   - Se funcionar, o problema está no .htaccess

2. **Permissões incorretas**
   - Verifique se arquivos têm permissão 644
   - Verifique se diretórios têm permissão 755

3. **Versão PHP incompatível**
   - No painel Hostinger, vá em "PHP" e use versão 8.0+

4. **Limites de memória**
   - O .htaccess já inclui `php_value memory_limit 256M`

### Erro de Conexão com Banco

**Verificações:**
1. Confirme as credenciais no painel Hostinger
2. Teste a conexão no phpMyAdmin
3. Verifique se o banco tem as tabelas (importe schema.sql)

### Erro "Email ou senha incorretos"

**Soluções:**
1. Certifique-se de que importou o schema.sql
2. Crie usuários de teste manualmente no phpMyAdmin:
   ```sql
   INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) 
   VALUES ('Cliente Teste', 'cliente@teste.com', '$2y$10$hash_da_senha', 'cliente', '(11) 99999-9999');
   ```

## 📁 Estrutura de Arquivos no Servidor

```
public_html/
├── index.php
├── login.php
├── cadastro.php
├── logout.php
├── .htaccess
├── config/
│   └── database.php (configuração automática)
├── includes/
│   ├── auth.php
│   └── functions.php
├── models/
│   ├── usuario.php
│   ├── salao.php
│   ├── profissional.php
│   └── agendamento.php
├── cliente/
│   ├── dashboard.php
│   ├── agendamentos.php
│   └── ...
├── parceiro/
│   ├── dashboard.php
│   ├── agendamentos.php
│   └── ...
├── admin/
│   ├── dashboard.php
│   ├── usuarios.php
│   └── ...
├── assets/
│   ├── css/
│   └── js/
└── database/
    └── schema.sql
```

## 🔒 Segurança

### Arquivos Protegidos pelo .htaccess
- `config/database.php` - Configurações do banco
- `database/schema.sql` - Estrutura do banco
- Arquivos `.inc`, `.conf`, `.config`

### Após Deploy
- [ ] Remova arquivos de teste (`test_*.php`, `debug_*.php`)
- [ ] Desative exibição de erros no .htaccess (comentar linhas de debug)
- [ ] Verifique logs de erro regularmente

## 📞 Suporte

Se os problemas persistirem:
1. Verifique os logs de erro no painel Hostinger
2. Entre em contato com o suporte Hostinger
3. Mencione que já verificou permissões, .htaccess e configurações PHP

## ✅ Status Atual

- ✅ Configuração automática de ambiente implementada
- ✅ Banco de dados configurado corretamente
- ✅ Sistema funcionando localmente
- ✅ Pronto para deploy online

---

**Última atualização:** 19/08/2025
**Versão:** 1.0
**Status:** Pronto para produção