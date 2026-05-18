# ⚡ Supabase Storage - Quick Start

## 🚀 Em 5 Minutos

### Passo 1: Criar Bucket no Supabase (1 min)

```
1. Acesse: https://app.supabase.com
2. Settings → API (copie dados)
3. Storage → Create Bucket → "recursos-api-uploads" → Create
```

### Passo 2: Configurar Laravel (2 min)

```bash
# Instalar pacote
composer require league/flysystem-aws-s3-v3

# Editar .env (copiar do SUPABASE_ENV_EXAMPLE.md)
# Adicionar variáveis Supabase
```

### Passo 3: Configurar Filesystem (1 min)

Edite `config/filesystems.php`:

```php
'disks' => [
    'supabase' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => true,
    ],
],

'default' => env('FILESYSTEM_DISK', 'supabase'),
```

### Passo 4: Testar (1 min)

```bash
php artisan tinker

# No tinker:
>>> Storage::disk('supabase')->put('test.txt', 'Hello', 'public')
# true ✓

>>> Storage::disk('supabase')->url('test.txt')
# "https://seu-projeto.supabase.co/storage/v1/object/public/..."
```

---

## 📤 Usar em Code

### Upload
```php
Storage::disk('supabase')->putFileAs(
    'folder',
    $request->file('document'),
    'filename.pdf',
    'public'
);
```

### Obter URL
```php
$url = Storage::disk('supabase')->url('folder/filename.pdf');
```

### Deletar
```php
Storage::disk('supabase')->delete('folder/filename.pdf');
```

---

## 🎯 Próximas Etapas

1. ✅ Seguir **SUPABASE_STORAGE_SETUP.md** para completo
2. ✅ Ver **SUPABASE_PRATICO.md** para exemplos
3. ✅ Copiar código de **EvaluationDocumentController**
4. ✅ Adicionar em suas rotas e models

---

## ❓ FAQ Rápido

**P: Preciso adicionar credenciais no Git?**  
R: Não! Coloque no `.env` (não commitir) e `.env.example` (com placeholders)

**P: Como deixar arquivo privado?**  
R: Remova o parâmetro `'public'` do `putFileAs()`

**P: Qual é o limite de tamanho?**  
R: Supabase Free: 100GB / Pro: Conforme uso

**P: Como fazer download de arquivo?**  
R: `Storage::disk('supabase')->get('path/file')`

---

## 🔗 Arquivos Importantes

- **SUPABASE_STORAGE_SETUP.md** - Documentação completa
- **SUPABASE_ENV_EXAMPLE.md** - Exemplo de configuração
- **SUPABASE_PRATICO.md** - Exemplos de código

---

**Dúvidas?** Veja a seção de Troubleshooting em SUPABASE_STORAGE_SETUP.md
