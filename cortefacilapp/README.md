# CorteFácil - Sistema SaaS de Agendamentos para Salões de Beleza

Sistema completo de agendamentos desenvolvido em PHP + JavaScript com MySQL, funcionando localmente via XAMPP.

## 📋 Características do Sistema

### Regras de Negócio
- **Parceiros (donos de salão)**: Não pagam mensalidade
- **Clientes**: Pagam R$ 1,29 por agendamento para a plataforma
- **Pagamento de serviços**: Realizado diretamente no salão (fora da plataforma)

### Módulos Implementados

#### 1. 🏠 Página Inicial
- Apresentação do sistema
- Opções de cadastro para clientes e salões
- Informações sobre funcionalidades

#### 2. 👤 Painel do Cliente
- ✅ Cadastro e login de clientes
- ✅ Escolha de salão, profissional e horário
- ✅ Sistema de agendamento com validação de conflitos
- ✅ Simulação de pagamento (R$ 1,29)
- ✅ Histórico de agendamentos com filtros
- ✅ Dashboard com estatísticas pessoais

#### 3. 🏪 Painel do Parceiro (Dono do Salão)
- ✅ Cadastro e login do salão
- ✅ Cadastro de profissionais
- ✅ Dashboard com estatísticas do salão
- ✅ Visualização de próximos agendamentos
- ✅ Controle de status dos agendamentos

#### 4. 🔧 Painel do Administrador
- ⏳ Em desenvolvimento

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais
- **usuarios**: Clientes, parceiros e administradores
- **saloes**: Informações dos salões parceiros
- **profissionais**: Profissionais que trabalham nos salões
- **agendamentos**: Agendamentos com controle de conflitos

## 🚀 Instalação e Configuração

### Pré-requisitos
- XAMPP instalado (Apache + MySQL + PHP)
- Navegador web moderno

### Passos de Instalação

1. **Copiar arquivos**
   ```
   Extrair/copiar todos os arquivos para: C:\xampp\htdocs\cortefacilapp\
   ```

2. **Iniciar XAMPP**
   - Abrir o painel de controle do XAMPP
   - Iniciar Apache e MySQL

3. **Criar banco de dados**
   - Acessar: http://localhost/phpmyadmin
   - Executar o script: `database/schema.sql`
   - Ou criar manualmente o banco `cortefacil_db`

4. **Configurar conexão** (se necessário)
   - Editar: `config/database.php`
   - Ajustar credenciais do MySQL

5. **Acessar o sistema**
   - URL: http://localhost/cortefacilapp

## 👥 Usuários de Teste

### Administrador
- **Email**: admin@cortefacil.com
- **Senha**: admin123

### Parceiro (Dono de Salão)
- **Email**: salao@teste.com
- **Senha**: senha123

### Cliente
- **Email**: cliente@teste.com
- **Senha**: senha123

## 🎯 Funcionalidades Implementadas

### ✅ Sistema de Autenticação
- Login/logout seguro
- Controle de sessões
- Redirecionamento baseado no tipo de usuário

### ✅ Gestão de Agendamentos
- Seleção de salão e profissional
- Calendário com horários disponíveis
- Validação de conflitos de horário
- Status: pendente → confirmado → concluído/cancelado

### ✅ Sistema de Pagamento
- Simulação de pagamento de R$ 1,29
- Confirmação automática do agendamento
- Histórico de transações

### ✅ Interface Responsiva
- Design moderno e intuitivo
- Compatível com dispositivos móveis
- Feedback visual para ações do usuário

### ✅ APIs RESTful
- `get_profissionais.php`: Buscar profissionais por salão
- `get_horarios.php`: Verificar disponibilidade de horários
- `criar_agendamento.php`: Criar novo agendamento
- `processar_pagamento.php`: Simular pagamento
- `atualizar_status_agendamento.php`: Alterar status

## 📁 Estrutura de Arquivos

```
cortefacilapp/
├── api/                          # APIs do sistema
│   ├── criar_agendamento.php
│   ├── get_horarios.php
│   ├── get_profissionais.php
│   ├── processar_pagamento.php
│   └── atualizar_status_agendamento.php
├── assets/                       # Recursos estáticos
│   ├── css/
│   │   └── style.css            # Estilos principais
│   └── js/
│       └── main.js              # JavaScript principal
├── cliente/                      # Módulo do cliente
│   ├── agendar.php
│   ├── dashboard.php
│   ├── historico.php
│   └── pagamento.php
├── config/                       # Configurações
│   └── database.php             # Conexão com banco
├── database/                     # Scripts do banco
│   └── schema.sql               # Estrutura e dados iniciais
├── includes/                     # Arquivos incluídos
│   └── auth.php                 # Sistema de autenticação
├── parceiro/                     # Módulo do parceiro
│   ├── cadastrar_salao.php
│   └── dashboard.php
├── index.php                     # Página inicial
├── login.php                     # Página de login
├── register.php                  # Página de cadastro
├── logout.php                    # Script de logout
└── README.md                     # Este arquivo
```

## 🔧 Tecnologias Utilizadas

### Backend
- **PHP 7.4+**: Linguagem principal
- **MySQL**: Banco de dados
- **PDO**: Conexão segura com banco

### Frontend
- **HTML5**: Estrutura das páginas
- **CSS3**: Estilos e responsividade
- **JavaScript**: Interatividade e validações
- **Bootstrap-like**: Sistema de grid responsivo

### Segurança
- **Prepared Statements**: Prevenção de SQL Injection
- **Password Hashing**: Senhas criptografadas
- **Session Management**: Controle de sessões
- **Input Validation**: Validação de dados

## 🎨 Características da Interface

### Design Responsivo
- Layout adaptável para desktop, tablet e mobile
- Sistema de grid flexível
- Componentes reutilizáveis

### Experiência do Usuário
- Navegação intuitiva
- Feedback visual imediato
- Formulários com validação em tempo real
- Alertas e confirmações

### Acessibilidade
- Estrutura semântica
- Contraste adequado
- Navegação por teclado

## 🔄 Fluxo de Agendamento

1. **Cliente acessa o sistema**
2. **Seleciona um salão** (visualiza informações)
3. **Escolhe um profissional** (carregado via API)
4. **Seleciona data e horário** (verifica disponibilidade)
5. **Preenche detalhes do serviço**
6. **Confirma agendamento** (status: pendente)
7. **Realiza pagamento** (R$ 1,29)
8. **Agendamento confirmado** (status: confirmado)

## 📊 Próximas Funcionalidades

### Em Desenvolvimento
- [ ] Painel completo do administrador
- [ ] Sistema de relatórios avançados
- [ ] Notificações por email/SMS
- [ ] Integração com gateway de pagamento real
- [ ] Sistema de avaliações
- [ ] Agenda visual (calendário)

### Melhorias Planejadas
- [ ] Upload de fotos do salão
- [ ] Sistema de promoções
- [ ] Agendamento recorrente
- [ ] Integração com redes sociais
- [ ] App mobile

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
1. Verificar se MySQL está rodando
2. Conferir credenciais em `config/database.php`
3. Verificar se o banco `cortefacil_db` existe

### Página em Branco
1. Verificar se Apache está rodando
2. Conferir logs de erro do PHP
3. Verificar permissões dos arquivos

### Problemas de Login
1. Verificar se os usuários de teste existem
2. Conferir se as senhas estão corretas
3. Limpar cookies/sessão do navegador

## 📞 Suporte

Para dúvidas ou problemas:
1. Verificar este README
2. Conferir comentários no código
3. Verificar logs de erro do sistema

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e demonstração.

---

**Desenvolvido com ❤️ para facilitar o agendamento em salões de beleza**