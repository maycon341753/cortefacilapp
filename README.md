# CorteFácil - Sistema de Agendamentos

Sistema completo de agendamentos para salões de beleza, desenvolvido com React (frontend) e Node.js (backend).

## 📁 Estrutura do Projeto

```
cortefacilapp/
├── frontend/                 # Aplicação React
│   ├── src/                 # Código fonte do frontend
│   ├── package.json         # Dependências do frontend
│   ├── vite.config.js       # Configuração do Vite
│   └── index.html           # Template HTML
├── backend/                 # Aplicação Node.js
│   └── server/              # Servidor Express
│       ├── routes/          # Rotas da API
│       ├── middleware/      # Middlewares
│       ├── config/          # Configurações
│       ├── package.json     # Dependências do backend
│       └── server.js        # Servidor principal
├── scripts/                 # Scripts de build e deploy
├── deploy/                  # Arquivos de produção
├── database/                # Scripts de banco de dados
├── config/                  # Configurações gerais
└── package.json             # Scripts do monorepo
```

## 🚀 Como Executar

### Instalação

```bash
# Instalar dependências de todos os projetos
npm run install:all
```

### Desenvolvimento

```bash
# Executar frontend e backend simultaneamente
npm run dev

# Ou executar separadamente:
npm run dev:frontend  # Frontend na porta 3000
npm run dev:backend   # Backend na porta 3001
```

### Build para Produção

```bash
# Build completo (frontend + backend)
npm run build:prod

# Preparar arquivos para deploy
npm run deploy:build
```

## 🛠️ Tecnologias

### Frontend
- **React 18** - Biblioteca para interfaces
- **Vite** - Build tool e dev server
- **React Router** - Roteamento
- **Bootstrap 5** - Framework CSS
- **Axios** - Cliente HTTP
- **React Toastify** - Notificações

### Backend
- **Node.js** - Runtime JavaScript
- **Express** - Framework web
- **MySQL2** - Driver do banco de dados
- **JWT** - Autenticação
- **Bcrypt** - Hash de senhas
- **Joi** - Validação de dados

## 📱 Funcionalidades

- ✅ **Autenticação** - Login/logout para clientes, parceiros e admin
- ✅ **Dashboards** - Painéis específicos para cada tipo de usuário
- ✅ **Agendamentos** - Sistema completo de reservas
- ✅ **Gestão de Profissionais** - Cadastro e gerenciamento
- ✅ **Gestão de Serviços** - Catálogo de serviços
- ✅ **Gestão de Horários** - Controle de disponibilidade
- ✅ **Relatórios** - Análises e estatísticas
- ✅ **Design Responsivo** - Funciona em desktop, tablet e mobile

## 🌐 Deploy

O projeto está configurado para deploy em:
- **CyberPanel/Hostinger** (recomendado)
- **Qualquer servidor com Node.js**
- **Hospedagem estática** (apenas frontend)

Veja o arquivo `DEPLOY.md` para instruções detalhadas.

## 📄 Licença

Este projeto é proprietário e confidencial.