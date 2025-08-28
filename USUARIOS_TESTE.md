# Usuários de Teste - CorteF\u00e1cil

## Credenciais Criadas

Os seguintes usuários foram criados para testes do sistema:

### 1. ADMINISTRADOR
- **Email:** `admin@teste.com`
- **Senha:** `123456`
- **Tipo:** Admin
- **ID:** 40
- **Telefone:** (11) 99999-0001

### 2. CLIENTE
- **Email:** `cliente@teste.com`
- **Senha:** `123456`
- **Tipo:** Cliente
- **ID:** 41
- **Telefone:** (11) 99999-0002

### 3. PARCEIRO (Sal\u00e3o)
- **Email:** `parceiro@teste.com`
- **Senha:** `123456`
- **Tipo:** Parceiro
- **ID:** 42
- **Telefone:** (11) 99999-0003

## Sal\u00e3o Criado

### Sal\u00e3o Teste
- **ID:** 12
- **Nome:** Sal\u00e3o Teste
- **Dono:** Parceiro Teste (ID: 42)
- **Endere\u00e7o:** Rua das Flores, 123 - Centro - S\u00e3o Paulo/SP - CEP: 01234-567
- **Telefone:** (11) 3333-4444
- **Descri\u00e7\u00e3o:** Sal\u00e3o de beleza para testes do sistema CorteF\u00e1cil

## Profissionais Criados

1. **Jo\u00e3o Silva** (ID: 22)
   - Especialidade: Corte Masculino
   - Sal\u00e3o: Sal\u00e3o Teste

2. **Maria Santos** (ID: 23)
   - Especialidade: Corte Feminino
   - Sal\u00e3o: Sal\u00e3o Teste

3. **Pedro Costa** (ID: 24)
   - Especialidade: Barba e Bigode
   - Sal\u00e3o: Sal\u00e3o Teste

## Como Testar

### 1. Teste de Login
1. Acesse: `http://localhost:3000/auth/login`
2. Use qualquer uma das credenciais acima
3. Verifique se o redirecionamento funciona corretamente para cada tipo de usu\u00e1rio

### 2. Funcionalidades por Tipo de Usu\u00e1rio

#### ADMIN
- Acesso completo ao sistema
- Gerenciamento de usu\u00e1rios
- Relat\u00f3rios e estat\u00edsticas
- Configura\u00e7\u00f5es do sistema

#### CLIENTE
- Buscar sal\u00f5es
- Agendar servi\u00e7os
- Visualizar hist\u00f3rico de agendamentos
- Gerenciar perfil

#### PARCEIRO
- Gerenciar sal\u00e3o
- Gerenciar profissionais
- Visualizar agendamentos
- Gerenciar hor\u00e1rios de funcionamento

## Observa\u00e7\u00f5es

- Todos os usu\u00e1rios usam a senha padr\u00e3o: `123456`
- Os dados s\u00e3o apenas para teste e desenvolvimento
- O sal\u00e3o j\u00e1 possui 3 profissionais configurados
- Os usu\u00e1rios foram criados no banco de dados em: `$(Get-Date)`

## Script de Cria\u00e7\u00e3o

Para recriar os usu\u00e1rios, execute:
```bash
php scripts/create_test_users.php
```

---
*Documento gerado automaticamente pelo sistema de testes*