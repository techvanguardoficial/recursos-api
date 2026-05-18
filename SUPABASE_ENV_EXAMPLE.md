# 📝 Configuração Supabase - Exemplo de .env

## Copie e configure seu `.env`

```env
# ============================================
# SUPABASE STORAGE CONFIGURATION
# ============================================

# URL do seu projeto Supabase
SUPABASE_URL=https://seu-projeto-id.supabase.co

# Chave Pública (Anon Key) - Usar para client-side
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Chave Service Role - Usar apenas no servidor (MANTER SEGURA!)
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Nome do bucket criado no Supabase
SUPABASE_BUCKET=recursos-api-uploads

# ============================================
# AWS S3 CONFIGURATION (Supabase S3 Compatible)
# ============================================

# Usar a Anon Key como Access Key ID
AWS_ACCESS_KEY_ID=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Usar a Anon Key como Secret Access Key (mesmo valor)
AWS_SECRET_ACCESS_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Região (não afeta Supabase, deixar como padrão)
AWS_DEFAULT_REGION=us-east-1

# Nome do bucket
AWS_BUCKET=recursos-api-uploads

# URL base do endpoint S3 do Supabase
AWS_ENDPOINT=https://seu-projeto-id.supabase.co/storage/v1/s3

# Usar path style endpoint
AWS_USE_PATH_STYLE_ENDPOINT=true

# URL público (para gerar URLs de acesso público)
AWS_URL=https://seu-projeto-id.supabase.co/storage/v1/object/public

# ============================================
# FILESYSTEM CONFIGURATION
# ============================================

# Disco padrão (local, public, s3, supabase)
FILESYSTEM_DISK=supabase

# ============================================
```

---

## 🔍 Como Encontrar suas Credenciais

### 1️⃣ Encontrar SUPABASE_URL e SUPABASE_ANON_KEY

1. Acesse https://app.supabase.com
2. Selecione seu projeto
3. Clique em **Settings** → **API**
4. Você verá:
   - **Project URL** → Copie para `SUPABASE_URL`
   - **Anon public key** → Copie para `SUPABASE_ANON_KEY` e `AWS_ACCESS_KEY_ID`

**Exemplo:**
```
Project URL: https://abcdef123456.supabase.co
Anon Key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFiY2RlZjEyMzQ1NiIsInJvbGUiOiJhbm9uIiwiaWF0IjoxNjI1MTc0NzAwLCJleHAiOjE5NDA3NTA3MDB9.ABC123DEF456...
```

### 2️⃣ Encontrar SUPABASE_SERVICE_ROLE_KEY

⚠️ **IMPORTANTE:** Manter esta chave segura!

1. Em **Settings** → **API**, role para baixo
2. Você verá **Service role key** (geralmente escondida)
3. Copie para `SUPABASE_SERVICE_ROLE_KEY`

### 3️⃣ Encontrar ou Criar Bucket

1. No menu lateral, clique em **Storage**
2. Se não tiver bucket:
   - Clique em **Create a new bucket**
   - Nome: `recursos-api-uploads`
   - Marque **Public bucket**
   - Clique em **Create bucket**
3. Copie o nome para `SUPABASE_BUCKET` e `AWS_BUCKET`

---

## ✅ Validar Configuração

Após configurar o `.env`, execute:

```bash
# Testar conexão com Supabase
php artisan tinker

# No tinker:
>>> Storage::disk('supabase')->files('/')
>>> // Deve retornar array de arquivos ou array vazio

>>> Storage::disk('supabase')->put('test.txt', 'Hello World', 'public')
>>> // Deve retornar true

>>> Storage::disk('supabase')->url('test.txt')
>>> // Deve retornar URL pública
```

---

## 🚀 Próximos Passos

1. ✅ Configurar `.env`
2. ✅ Instalar pacote: `composer require league/flysystem-aws-s3-v3`
3. ✅ Configurar `config/filesystems.php`
4. ✅ Testar com `tinker`
5. ✅ Usar em controllers e models
6. ✅ Deploy em produção

---

**Nota:** Nunca commitir arquivo `.env` no Git. Use `.env.example` para documentar variáveis necessárias.
