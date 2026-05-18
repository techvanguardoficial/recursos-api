# 📋 Painel Administrativo - Recursos API

## Objetivo

Criar uma aplicação web (frontend) para administração completa dos dados recebidos pelo formulário público do frontend, com autenticação e controle de acesso.

---

## 🔐 Autenticação

### Login
- Email e senha
- Integrar com rota: `POST /api/v1/login`
- Armazenar token em localStorage/sessionStorage
- Redirecionar para dashboard após sucesso
- Exibir erro se credenciais inválidas
- Implementar "Lembrar-me" (opcional)

### Logout
- Integrar com rota: `POST /api/v1/logout`
- Limpar token armazenado
- Redirecionar para login

### Validação de Sessão
- GET `/api/v1/me` para validar usuário autenticado
- Verificar token a cada 5 minutos
- Renovar token automaticamente (refresh)
- Redirecionar para login se token expirar

---

## 📊 Dashboard Principal

### Visão Geral
Cards com estatísticas:
- Total de formulários recebidos
- Total de solicitações de benefício
- Total de avaliações
- Distribuição por status (in_progress, submitted, completed)

### Gráficos
- **Formulários por período** (últimos 7 dias)
- **Status das solicitações** (pizza/barra)
- **Taxa de conclusão**
- **Tipos de benefício mais solicitados**

---

## 💼 Módulo 1: Gerenciamento de Solicitações de Benefício

### Listagem
Tabela com colunas:
| Campo | Descrição |
|-------|-----------|
| ID | Identificador único |
| Solicitante | Nome do solicitante |
| Email | Email do solicitante |
| Benefício | Tipo de benefício |
| Step | Etapa atual (1-4) |
| Status | in_progress / submitted / completed |
| Data | Data de criação |
| Ações | Visualizar, Editar, Mudar Step, Mudar Status, Excluir |

### Funcionalidades
- **Filtros:**
  - Por status
  - Por step
  - Por tipo de benefício
  - Por período
  - Busca por nome/CPF

- **Ordenação:** Data, Status, Step

- **Paginação:** 15 itens por página

### Visualizar Detalhes (Abas/Cards)

#### 1️⃣ Dados Pessoais
```
- Nome completo
- CPF
- Email
- Telefone
```

#### 2️⃣ Situação do Benefício
```
- Tipo de benefício
- Detalhes adicionais
```

#### 3️⃣ Detalhes do Benefício
```
- Nome
- Tempo desde notificação
```

#### 4️⃣ Razão de Indeferimento
```
- Motivo (se aplicável)
```

#### 5️⃣ Documentação
```
- Arquivos anexados
- Botão para download (se houver base64)
- Data de upload
```

#### 6️⃣ Histórico
```
- Timeline de alterações
- Quem alterou (admin logado)
- Quando foi alterado
- O que foi alterado
```

### Editar Solicitação
- Formulário com todos os campos editáveis
- Validação igual ao frontend (mantém integridade)
- Botão Salvar
- Rota: `PATCH /api/v1/benefit-requests/{id}`

### Atualizar Step
- Dropdown com passos disponíveis (1, 2, 3, 4, etc)
- Rota: `PATCH /api/v1/benefit-requests/{id}/step`
- Modal de confirmação
- Log da alteração

### Atualizar Status
- Dropdown: `in_progress` → `submitted` → `completed`
- Se status = rejected: exigir seleção de razão de indeferimento
- Rota: `PATCH /api/v1/benefit-requests/{id}/status`
- Modal de confirmação

### Excluir
- Rota: `DELETE /api/v1/benefit-requests/{id}`
- Modal de confirmação com aviso
- Log de exclusão
- Soft delete (não remove permanentemente)

---

## 👤 Módulo 1.5: Gerenciamento de Usuários (Admin)

### Alterar Própria Senha
- Menu do usuário → Alterar Senha
- Modal com campos:
  - Senha atual (obrigatória)
  - Senha nova (min 8 caracteres)
  - Confirmar senha
- Rota: `POST /api/v1/change-password`
- Validações:
  - Senha atual deve estar correta
  - Senha nova deve ter min 8 caracteres
  - Senha nova deve ser diferente da atual
  - Ambos os campos devem coincidir

### Criar Novo Usuário
- Menu Admin → Gerenciar Usuários → Novo Usuário
- Formulário com:
  - Nome completo (obrigatório)
  - Email (obrigatório, único)
  - Senha (min 8 caracteres)
  - Confirmar senha
- Rota: `POST /api/v1/users`
- Permissões: Apenas usuários autenticados podem criar
- Resposta: Dados do novo usuário criado

---

## 📋 Módulo 2: Gerenciamento de Avaliações

### Listagem
Tabela com colunas:
| Campo | Descrição |
|-------|-----------|
| ID | Identificador único |
| Título | Título da avaliação |
| Status | draft / published |
| Criado em | Data de criação |
| Atualizado em | Última atualização |
| Ações | Visualizar, Editar, Deletar |

### CRUD Completo

#### 🆕 Criar
- Formulário com:
  - Title (obrigatório)
  - Description
  - Status (draft/published)
- Rota: `POST /api/v1/evaluations`

#### ✏️ Editar
- Editar todos os campos
- Rota: `PATCH /api/v1/evaluations/{id}`

#### 👁️ Visualizar
- Detalhes completos
- Abas para respostas e documentos

#### 🗑️ Deletar
- Rota: `DELETE /api/v1/evaluations/{id}`
- Modal de confirmação

### Gerenciar Respostas
- Listar respostas de uma avaliação
- Editar respostas: `PATCH /api/v1/evaluations/{evaluation}/answers`
- Deletar respostas: `DELETE /api/v1/evaluations/{evaluation}/answers`
- Timeline de quem respondeu e quando

### Gerenciar Documentos
- Listar documentos anexados
- Upload de documentos: `POST /api/v1/evaluations/{evaluation}/documents`
- Visualizar documento
- Deletar documento: `DELETE /api/v1/evaluations/{evaluation}/documents/{document}`

---

## 📚 Módulo 3: Gerenciamento de Catálogos

### Tipos de Benefício

Tabela com:
| Campo | Ação |
|-------|------|
| ID | - |
| Nome | Editar |
| Descrição | Deletar |
| Data Criação | - |

**Rotas:**
- `GET /api/v1/benefit-types` - Listar
- `POST /api/v1/benefit-types` - Criar
- `PATCH /api/v1/benefit-types/{id}` - Editar
- `DELETE /api/v1/benefit-types/{id}` - Deletar

### Razões de Indeferimento

Tabela com:
| Campo | Ação |
|-------|------|
| ID | - |
| Razão | Editar |
| Descrição | Deletar |
| Data Criação | - |

**Rotas:**
- `GET /api/v1/indeferment-reasons` - Listar
- `POST /api/v1/indeferment-reasons` - Criar
- `PATCH /api/v1/indeferment-reasons/{id}` - Editar
- `DELETE /api/v1/indeferment-reasons/{id}` - Deletar

---

## 🔍 Funcionalidades Cross-módulo

### Busca Global
- Buscar formulários, solicitações e avaliações em um só lugar
- Filtro por tipo (formulário, solicitação, avaliação)
- Destaque dos resultados

### Exportar Dados
- Exportar tabelas em CSV/Excel
- Exportar relatório de período
- Seleção de colunas a exportar

### Notificações
- Toast para ações bem-sucedidas
- Toast para erros
- Modal de confirmação antes de ações destrutivas
- Progress bar para operações longas

### Responsividade
- Desktop first
- Funcional em tablet
- Mobile-friendly (menu hambúrguer)

### Tema
- Light mode / Dark mode
- Salvar preferência do usuário

---

## 🎨 Layout Base

```
┌─────────────────────────────────────────────────────┐
│  Logo  │         HEADER - Bem-vindo Admin           │ Perfil | Logout
├────────┼─────────────────────────────────────────────┤
│        │                                             │
│ MENU   │                                             │
│ LATERAL│         CONTEÚDO PRINCIPAL                 │
│        │    (Listagem, Detalhes, Forms, etc)        │
│ - Dash │                                             │
│ - Forms│                                             │
│ - B.R. │                                             │
│ - Aval │                                             │
│ - Cata │                                             │
│        │                                             │
└────────┴─────────────────────────────────────────────┘
```

### Componentes Base
- Header com logo e usuário logado
- Menu lateral com navegação
- Breadcrumb de navegação
- Footer com versão/info
- Sidebar colapsável

---

## 🔌 Endpoints Necessários

### Autenticação
```
Públicas:
POST   /api/v1/login          - Login (email + senha)
POST   /api/v1/register       - Registrar novo usuário (público)

Autenticadas:
POST   /api/v1/logout         - Logout
GET    /api/v1/me             - Dados do usuário logado
POST   /api/v1/change-password - Alterar senha do usuário
POST   /api/v1/users          - Criar novo usuário (admin)
```

### Solicitações de Benefício
```
Públicas (sem autenticação):
POST   /api/v1/benefit-requests              - Criar solicitação
PATCH  /api/v1/benefit-requests/{id}         - Atualizar solicitação

Autenticadas (requer token):
GET    /api/v1/benefit-requests              - Listar
GET    /api/v1/benefit-requests/{id}         - Visualizar
PATCH  /api/v1/benefit-requests/{id}/status  - Atualizar status
PATCH  /api/v1/benefit-requests/{id}/step    - Atualizar step
DELETE /api/v1/benefit-requests/{id}         - Deletar
GET    /api/v1/benefit-requests/{id}/document - Gerar/Baixar documento
```

### Avaliações
```
GET    /api/v1/evaluations              - Listar
POST   /api/v1/evaluations              - Criar
GET    /api/v1/evaluations/{id}         - Visualizar
PATCH  /api/v1/evaluations/{id}         - Editar
DELETE /api/v1/evaluations/{id}         - Deletar
```

### Respostas de Avaliações
```
GET    /api/v1/evaluations/{evaluation}/answers       - Listar
POST   /api/v1/evaluations/{evaluation}/answers       - Criar
PATCH  /api/v1/evaluations/{evaluation}/answers       - Editar
DELETE /api/v1/evaluations/{evaluation}/answers       - Deletar
```

### Documentos de Avaliações
```
GET    /api/v1/evaluations/{evaluation}/documents              - Listar
POST   /api/v1/evaluations/{evaluation}/documents              - Upload
GET    /api/v1/evaluations/{evaluation}/documents/{document}   - Visualizar
DELETE /api/v1/evaluations/{evaluation}/documents/{document}   - Deletar
```

### Catálogos - Tipos de Benefício
```
GET    /api/v1/benefit-types      - Listar
POST   /api/v1/benefit-types      - Criar
PATCH  /api/v1/benefit-types/{id} - Editar
DELETE /api/v1/benefit-types/{id} - Deletar
```

### Catálogos - Razões de Indeferimento
```
GET    /api/v1/indeferment-reasons      - Listar
POST   /api/v1/indeferment-reasons      - Criar
PATCH  /api/v1/indeferment-reasons/{id} - Editar
DELETE /api/v1/indeferment-reasons/{id} - Deletar
```

---

## 🛠️ Stack Recomendado

### Frontend Framework
- **React** (recomendado) / Vue 3 / Angular

### HTTP Client
- **Axios** ou Fetch API nativa

### State Management
- **Context API** (simples) / **TanStack Query** (data) / **Zustand** (complex)

### UI Components
- **Material-UI (MUI)** / **Tailwind CSS** / **Bootstrap**
- **shadcn/ui** para componentes customizados

### Tabelas Avançadas
- **TanStack Table (React Table)** / **ag-Grid**

### Gráficos
- **Chart.js** / **Recharts** / **ApexCharts**

### Forms
- **React Hook Form** + **Zod/Yup** para validação

### Roteamento
- **React Router v6** (React) / **Vue Router** (Vue)

### Utilitários
- **date-fns** ou **dayjs** para datas
- **lodash-es** para utilitários
- **clsx** para classes condicionais

---

## 🔐 Segurança

- ✅ HTTPS only (em produção)
- ✅ Token Bearer em localStorage com expiração
- ✅ CORS configurado no backend
- ✅ Validação de campos no frontend
- ✅ Proteção contra XSS (sanitizar dados)
- ✅ CSRF tokens se necessário
- ✅ Modal de confirmação para ações destrutivas
- ✅ Rate limiting no backend
- ✅ Logs de ação do admin
- ✅ Refresh token automaticamente antes de expirar

---

## 📈 Prioridade de Desenvolvimento

### P1 - MVP (Semana 1)
- ✅ Sistema de Login/Logout
- ✅ Dashboard com estatísticas básicas
- ✅ Listagem de Solicitações de Benefício
- ✅ Visualizar detalhes de solicitação
- ✅ Editar status/step de solicitação

### P2 - Core Features (Semana 2)
- ✅ Gerenciar Catálogos (Tipos de Benefício, Razões de Indeferimento)
- ✅ Editar dados completos de solicitação
- ✅ Deletar solicitações
- ✅ Gerar/Baixar documentos

### P3 - Admin Full (Semana 3)
- ✅ Gerenciar Avaliações (CRUD)
- ✅ Gerenciar Respostas de Avaliações
- ✅ Gerenciar Documentos de Avaliações

### P4 - Polish (Semana 4)
- ✅ Gráficos avançados no dashboard
- ✅ Exportar dados (CSV/Excel)
- ✅ Busca global
- ✅ Dark mode
- ✅ Temas customizáveis

### P5 - Nice to Have
- ✅ Relatórios PDF gerados dinamicamente
- ✅ Agendamento de ações
- ✅ Notificações em tempo real
- ✅ Auditoria completa
- ✅ Sincronização em tempo real

---

## 📱 Fluxo de Autenticação

```
1. Acessar http://localhost:3000
   ↓ (sem token)
2. Redirecionar para /login
   ↓
3. Preencher email/senha
   ↓
4. POST /api/v1/login
   ↓
5. Backend retorna token + dados do usuário
   ↓
6. Armazenar token em localStorage
   ↓
7. GET /api/v1/me (validar usuário)
   ↓
8. Armazenar dados do usuário em context/state
   ↓
9. Redirecionar para /dashboard
   ↓
10. Protected routes verificam token
    - Se válido → Renderiza componente
    - Se inválido/expirado → Redireciona para /login
```

---

## 🚀 Como Usar Este Documento

1. **Para Desenvolvedores Frontend:**
   - Use como especificação técnica
   - Crie tasks/issues baseado em cada módulo
   - Implemente na prioridade indicada

2. **Para Designers:**
   - Use como base para wireframes
   - Crie protótipo no Figma/Adobe XD
   - Revise fluxos de UX

3. **Para Product:**
   - Valide escopo com stakeholders
   - Priorize features conforme negócio
   - Atualize com feedback de usuários

4. **Para QA:**
   - Crie test cases baseado em cada funcionalidade
   - Teste integrações com API
   - Verifique segurança

---

## 📞 Contato & Suporte

Para dúvidas sobre a especificação:
- Revisar a seção correspondente deste documento
- Consultar a API documentation (Postman Collection)
- Contatar o backend lead

---

**Última atualização:** 2026-05-12  
**Versão:** 1.0  
**Status:** Ready for Development
