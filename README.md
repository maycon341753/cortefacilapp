# CorteFácil - Sistema de Agendamentos (Versão HTML/CSS/JS)

Sistema completo de agendamentos convertido para usar apenas **HTML**, **CSS** e **JavaScript**, eliminando dependências de PHP e MySQL. Versão moderna e responsiva com dados simulados.

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
- Servidor web local (XAMPP, WAMP, Live Server, etc.) ou qualquer servidor HTTP
- Navegador moderno com suporte a ES6+

### Passos de Instalação

1. **Copiar arquivos**
   ```
   Extrair/copiar todos os arquivos para: C:\xampp\htdocs\cortefacil\cortefacilapp\
   ```

2. **Iniciar servidor web**
   - Para XAMPP: Iniciar apenas o Apache (MySQL não é necessário)
   - Para Live Server: Abrir a pasta no VS Code e usar Live Server
   - Para Python: `python -m http.server 8000`

3. **Acessar o sistema**
   - URL: http://localhost/cortefacil/cortefacilapp/
   - Ou conforme configuração do seu servidor

### Não é necessário
- ❌ Banco de dados (dados simulados via JavaScript)
- ❌ PHP (convertido para JavaScript puro)
- ❌ Configurações de backend

## 👥 Como Usar o Sistema

### 🚀 Acesso Rápido
- Use os **botões de login rápido** nas páginas de login
- Ou cadastre-se normalmente (dados salvos no localStorage)

### 📱 Para Clientes
1. Acesse `index.html` → "Entrar" → "Login Rápido Cliente"
2. Ou cadastre-se como "Cliente" em `register.html`
3. Faça agendamentos em `booking.html`
4. Gerencie no dashboard `dashboard-client.html`

### 🏪 Para Parceiros (Salões)
1. Acesse `index.html` → "Entrar" → "Login Rápido Parceiro"
2. Ou cadastre-se como "Parceiro" em `register.html`
3. Gerencie agendamentos em `dashboard-partner.html`

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
├── index.html                    # 🏠 Página inicial moderna
├── register.html                 # 📝 Cadastro de usuários
├── login.html                    # 🔐 Login de usuários
├── booking.html                  # 📅 Sistema de agendamento
├── payment.html                  # 💳 Processamento de pagamentos
├── dashboard-client.html         # 👤 Dashboard do cliente
├── dashboard-partner.html        # 🏪 Dashboard do parceiro
├── assets/                       # 📦 Recursos estáticos
│   ├── css/
│   │   └── style.css            # 🎨 Estilos principais modernos
│   └── js/
│       └── main.js              # ⚡ JavaScript com funcionalidades avançadas
├── database/                     # 📋 Scripts de referência (SQL)
│   ├── README_BANCO.md
│   ├── schema.sql
│   └── usuarios_teste.sql
├── .htaccess                     # ⚙️ Configurações do servidor
├── DEMO.md                       # 🎯 Guia de demonstração
└── README.md                     # 📖 Documentação
```

### 🎯 Arquivos Principais (HTML/CSS/JS)
- **index.html**: Landing page com animações e design moderno
- **register.html**: Cadastro com validação em tempo real
- **login.html**: Login com opções rápidas para demonstração
- **booking.html**: Agendamento em 4 etapas com dados simulados
- **payment.html**: Pagamento com cartão e PIX simulados
- **dashboard-client.html**: Dashboard completo do cliente
- **dashboard-partner.html**: Dashboard avançado do parceiro
- **style.css**: Estilos responsivos e modernos
- **main.js**: JavaScript com funcionalidades completas

## 🔧 Tecnologias Utilizadas

### Frontend
- **HTML5**: Estrutura semântica e moderna
- **CSS3**: Estilos responsivos com Flexbox e Grid
- **JavaScript (ES6+)**: Funcionalidades interativas e dinâmicas
- **Google Fonts**: Tipografia Inter
- **Intersection Observer API**: Animações de scroll
- **LocalStorage**: Persistência de dados do usuário

### Funcionalidades JavaScript
- **Módulos ES6**: Organização do código
- **Async/Await**: Operações assíncronas simuladas
- **Event Delegation**: Performance otimizada
- **Form Validation**: Validação em tempo real
- **Máscaras de entrada**: Telefone e cartão
- **Sistema de notificações**: Toast customizado

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