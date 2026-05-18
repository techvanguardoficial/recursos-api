# 🆕 Novas Funcionalidades - Autenticação

## 📋 Resumo

Foram adicionadas duas novas funcionalidades ao sistema de autenticação:

1. **Alterar Senha** - Permite que usuários autenticados alterem sua senha
2. **Criar Novo Usuário** - Permite que usuários autenticados criem novos usuários no sistema

---

## 🔐 1. Alterar Senha do Usuário

### Endpoint
```
POST /api/v1/change-password
```

### Autenticação
✅ **Requer token Bearer (usuário autenticado)**

### Request
```json
{
  "current_password": "senhaatual123",
  "new_password": "novaSenha456",
  "new_password_confirmation": "novaSenha456"
}
```

### Validações
- ✅ `current_password`: Obrigatório e deve estar correto
- ✅ `new_password`: Mínimo 8 caracteres, obrigatório
- ✅ `new_password_confirmation`: Deve coincidir com `new_password`
- ✅ Nova senha deve ser diferente da senha atual

### Response (Sucesso)
```json
{
  "message": "Senha alterada com sucesso."
}
```

**Status Code:** `200 OK`

### Response (Erro - Senha Atual Incorreta)
```json
{
  "errors": {
    "current_password": [
      "A senha atual está incorreta."
    ]
  }
}
```

**Status Code:** `422 Unprocessable Entity`

### Exemplo de Uso no Postman

1. Faça login e copie o token
2. Na aba "Authorization", selecione "Bearer Token"
3. Cole o token
4. Envie o request com as senhas

---

## 👥 2. Criar Novo Usuário

### Endpoint
```
POST /api/v1/users
```

### Autenticação
✅ **Requer token Bearer (usuário autenticado)**

### Request
```json
{
  "name": "Novo Administrador",
  "email": "novouser@example.com",
  "password": "senhaSegura123",
  "password_confirmation": "senhaSegura123"
}
```

### Validações
- ✅ `name`: Obrigatório, máximo 255 caracteres
- ✅ `email`: Obrigatório, deve ser email válido, deve ser único (não pode repetir)
- ✅ `password`: Mínimo 8 caracteres, obrigatório
- ✅ `password_confirmation`: Deve coincidir com `password`

### Response (Sucesso)
```json
{
  "message": "Usuário criado com sucesso",
  "data": {
    "id": 2,
    "name": "Novo Administrador",
    "email": "novouser@example.com",
    "created_at": "2026-05-12T14:00:00Z",
    "updated_at": "2026-05-12T14:00:00Z"
  }
}
```

**Status Code:** `201 Created`

### Response (Erro - Email Duplicado)
```json
{
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

**Status Code:** `422 Unprocessable Entity`

### Response (Erro - Senha Muito Curta)
```json
{
  "errors": {
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

**Status Code:** `422 Unprocessable Entity`

---

## 🧪 Testes com cURL

### Teste 1: Alterar Senha
```bash
curl -X POST http://localhost:8000/api/v1/change-password \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "password123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
  }'
```

### Teste 2: Criar Novo Usuário
```bash
curl -X POST http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Novo Usuário",
    "email": "novo@example.com",
    "password": "senhaSegura123",
    "password_confirmation": "senhaSegura123"
  }'
```

---

## 📱 Fluxo no Painel Admin

### Alterar Minha Senha
1. Login no painel
2. Menu do usuário (canto superior direito)
3. Clicar em "Alterar Senha"
4. Preencher formulário:
   - Senha Atual
   - Senha Nova
   - Confirmar Nova Senha
5. Clicar em "Salvar"
6. Mensagem de sucesso: "Senha alterada com sucesso"

### Gerenciar Usuários (Admin)
1. Login como admin
2. Menu lateral → "Gerenciar Usuários"
3. Clicar em "+ Novo Usuário"
4. Preencher formulário:
   - Nome Completo
   - Email
   - Senha
   - Confirmar Senha
5. Clicar em "Criar"
6. Novo usuário aparece na lista
7. Admin pode enviar credenciais ao novo usuário

---

## 🔒 Considerações de Segurança

### ✅ Implementado
- Senhas são hasheadas com `bcrypt`
- Validação de senha atual obrigatória
- Email único obrigatório
- Autenticação por token Bearer (Sanctum)
- Validação mínima de 8 caracteres

### 💡 Recomendações Futuras
- Adicionar confirmação de email para novos usuários
- Implementar roles/permissões (admin, user, etc)
- Auditoria de alteração de senha
- Limpar tokens antigos ao alterar senha
- Notificar por email quando novo usuário é criado
- Implementar 2FA (Two-Factor Authentication)

---

## 📊 Status da Implementação

| Funcionalidade | Status | Endpoint | Auth |
|---|---|---|---|
| Alterar Senha | ✅ Implementado | POST /change-password | ✅ Requer |
| Criar Novo Usuário | ✅ Implementado | POST /users | ✅ Requer |

---

## 📚 Referências

- **Postman Collection:** Recursos-API.postman_collection.json
- **Documentação:** ADMIN_PANEL_SPECIFICATION.md
- **Controller:** app/Http/Controllers/Api/AuthController.php
- **Rotas:** routes/api.php

---

**Versão:** 1.0  
**Data:** 2026-05-12  
**Status:** Pronto para uso
