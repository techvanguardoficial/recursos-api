# ⚙️ Correções de Configuração - Supabase Storage

## ✅ O que foi corrigido no `.env`:

1. **FILESYSTEM_DISK** - Mudado de `local` para `supabase`
2. **AWS_SECRET_ACCESS_KEY** - Corrigido (era secret key, agora é chave pública)
3. **AWS_BUCKET** - Mudado para `recursos-api-uploads`
4. **AWS_URL** - Adicionado endpoint público
5. **AWS_ENDPOINT** - Corrigido para endpoint S3 correto
6. **SUPABASE_URL** - Adicionado
7. **SUPABASE_ANON_KEY** - Adicionado
8. **SUPABASE_BUCKET** - Adicionado

---

## 🚀 Execute Agora:

### Passo 1: Instalar Pacote (OBRIGATÓRIO)
```bash
./vendor/bin/sail composer require league/flysystem-aws-s3-v3:^3.0
```

### Passo 2: Executar Migrations
```bash
./vendor/bin/sail artisan migrate
```

### Passo 3: Testar Conexão
```bash
./vendor/bin/sail artisan tinker

# Digite:
> Storage::disk('supabase')->put('test.txt', 'Hello', 'public')
# Deve retornar: true ✓

> Storage::disk('supabase')->url('test.txt')
# Deve retornar: URL pública do arquivo

> exit
```

---

## 📊 Sua Configuração Supabase

```
URL: https://xsjwuxfplsumldvfqzpy.supabase.co
Anon Key: sb_publishable_5OFci0m1uYJNA3Osm1dXNw_jTkBkGzR
Bucket: recursos-api-uploads
Região: us-east-1
```

⚠️ **Importante:** Certifique-se de que o bucket `recursos-api-uploads` foi criado no Supabase Dashboard!

---

## 🔍 Se Ainda Não Funcionar:

### 1. Verificar Bucket no Supabase
- Acesse: https://app.supabase.com
- Selecione seu projeto
- Clique em **Storage**
- Verifique se existe bucket chamado `recursos-api-uploads`
- Se não existir, crie um novo com esse nome

### 2. Verificar se o Pacote Foi Instalado
```bash
./vendor/bin/sail composer show | grep flysystem-aws-s3-v3
```

### 3. Limpar Cache do Laravel
```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### 4. Testar de Novo
```bash
./vendor/bin/sail artisan tinker
> Storage::disk('supabase')->put('test2.txt', 'Test', 'public')
```

---

## 📁 Estrutura de Diretórios Criada

Os seguintes arquivos foram criados:

```
app/
  ├── Http/Controllers/Api/
  │   ├── EvaluationController.php (NOVO)
  │   └── EvaluationDocumentController.php (NOVO)
  └── Models/
      ├── Evaluation.php (NOVO)
      └── EvaluationDocument.php (NOVO)

database/
  └── migrations/
      ├── 2026_05_12_000001_create_evaluations_table.php (NOVO)
      └── 2026_05_12_000000_create_evaluation_documents_table.php (NOVO)

config/
  └── filesystems.php (MODIFICADO - disco 'supabase' adicionado)

routes/
  └── api.php (MODIFICADO - rotas de avaliações adicionadas)

.env (MODIFICADO - variáveis Supabase configuradas)
```

---

## ✅ Checklist de Implementação

- [ ] Executar: `./vendor/bin/sail composer require league/flysystem-aws-s3-v3:^3.0`
- [ ] Executar: `./vendor/bin/sail artisan migrate`
- [ ] Testar com tinker: `Storage::disk('supabase')->put('test.txt', 'Hello', 'public')`
- [ ] Verificar arquivo no Supabase Dashboard
- [ ] Testar API com Postman
- [ ] Verificar URL pública do arquivo

---

## 🎯 Próximas Etapas

1. ✅ Instalar pacote
2. ✅ Executar migrations
3. ✅ Testar com tinker
4. ✅ Testar endpoints via Postman
5. ✅ Usar no front-end React

---

**Status:** Aguardando instalação do pacote  
**Próximo:** `./vendor/bin/sail composer require league/flysystem-aws-s3-v3:^3.0`
