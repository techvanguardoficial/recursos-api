# 🚀 Integração Supabase Storage com Laravel

## 📋 Visão Geral

Este guia explica como configurar o Supabase Storage como solução de armazenamento de arquivos no Laravel para salvar uploads do sistema.

---

## 🔧 Pré-requisitos

- ✅ Conta no Supabase (supabase.com)
- ✅ Projeto Supabase criado
- ✅ Laravel 10+
- ✅ Composer instalado

---

## 📝 Passo 1: Criar Bucket no Supabase

### 1.1 Acessar Supabase Dashboard
- Acesse https://app.supabase.com
- Selecione seu projeto

### 1.2 Criar Bucket
1. No menu lateral, clique em **Storage**
2. Clique em **Create a new bucket**
3. Nomeie como: `recursos-api-uploads`
4. Marque **Public bucket** (ou Private conforme sua necessidade)
5. Clique em **Create bucket**

### 1.3 Obter Credenciais
1. Clique em **Settings** → **API**
2. Copie:
   - **Project URL** (ex: `https://xxx.supabase.co`)
   - **Project API Key** (anon/public key)
   - **Service Role Key** (para servidor - guarde com segurança!)

---

## 🔌 Passo 2: Configurar Laravel

### 2.1 Instalar League Flysystem Adapter para S3

O Supabase oferece uma API S3-compatible, então usaremos o driver S3:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 2.2 Configurar `.env`

Adicione as variáveis de ambiente:

```env
# Supabase Storage
SUPABASE_URL=https://seu-projeto.supabase.co
SUPABASE_ANON_KEY=sua-chave-anonima-aqui
SUPABASE_SERVICE_ROLE_KEY=sua-chave-service-role-aqui
SUPABASE_BUCKET=recursos-api-uploads

# Configuração S3 para Supabase
AWS_ACCESS_KEY_ID=sua-supabase-anon-key
AWS_SECRET_ACCESS_KEY=sua-supabase-anon-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=recursos-api-uploads
AWS_ENDPOINT=https://seu-projeto.supabase.co/storage/v1/s3
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### 2.3 Configurar `config/filesystems.php`

Adicione um novo disco no array `'disks'`:

```php
'disks' => [
    // ... outros discos

    'supabase' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

### 2.4 Definir Disco Padrão

Opcionalmente, defina Supabase como disco padrão:

```php
'default' => env('FILESYSTEM_DISK', 'supabase'),
```

---

## 📤 Passo 3: Usar Supabase Storage

### 3.1 Upload de Arquivo

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:5120', // 5MB max
        ]);

        $file = $request->file('document');
        
        // Gerar nome único
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Salvar no Supabase
        $path = Storage::disk('supabase')->putFileAs(
            'documents',
            $file,
            $filename,
            'public' // Deixar público
        );

        return response()->json([
            'message' => 'Arquivo salvo com sucesso',
            'path' => $path,
            'url' => Storage::disk('supabase')->url($path),
        ]);
    }
}
```

### 3.2 Obter URL Pública

```php
// Obter URL pública do arquivo
$url = Storage::disk('supabase')->url('documents/arquivo.pdf');

// Usar em um modelo
$model->document_url = Storage::disk('supabase')->url($path);
$model->save();
```

### 3.3 Obter Arquivo

```php
// Obter conteúdo do arquivo
$contents = Storage::disk('supabase')->get('documents/arquivo.pdf');

// Verificar se existe
if (Storage::disk('supabase')->exists('documents/arquivo.pdf')) {
    // ...
}
```

### 3.4 Deletar Arquivo

```php
// Deletar um arquivo
Storage::disk('supabase')->delete('documents/arquivo.pdf');

// Deletar múltiplos arquivos
Storage::disk('supabase')->delete([
    'documents/arquivo1.pdf',
    'documents/arquivo2.pdf',
]);
```

### 3.5 Listar Arquivos

```php
// Listar arquivos de uma pasta
$files = Storage::disk('supabase')->files('documents');

// Com subpastas
$files = Storage::disk('supabase')->allFiles('documents');
```

---

## 🎯 Caso de Uso: Documentos de Avaliação

### Model: EvaluationDocument.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EvaluationDocument extends Model
{
    protected $fillable = ['evaluation_id', 'title', 'file_path', 'file_size', 'file_type'];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    // Obter URL pública
    public function getUrlAttribute()
    {
        return Storage::disk('supabase')->url($this->file_path);
    }

    // Deletar arquivo ao deletar modelo
    protected static function booted()
    {
        static::deleting(function ($document) {
            Storage::disk('supabase')->delete($document->file_path);
        });
    }
}
```

### Controller: EvaluationDocumentController.php

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvaluationDocumentController extends Controller
{
    public function store(Request $request, Evaluation $evaluation)
    {
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        
        // Gerar caminho único
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "evaluations/{$evaluation->id}/documents/{$filename}";
        
        // Salvar no Supabase
        Storage::disk('supabase')->putFileAs(
            "evaluations/{$evaluation->id}/documents",
            $file,
            $filename,
            'public'
        );

        // Registrar no banco de dados
        $document = EvaluationDocument::create([
            'evaluation_id' => $evaluation->id,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
        ]);

        return response()->json([
            'message' => 'Documento salvo com sucesso',
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'url' => $document->url,
                'file_size' => $document->file_size,
                'created_at' => $document->created_at,
            ],
        ], 201);
    }

    public function show(Evaluation $evaluation, EvaluationDocument $document)
    {
        // Verificar permissão
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'url' => $document->url,
                'file_size' => $document->file_size,
                'created_at' => $document->created_at,
            ],
        ]);
    }

    public function destroy(Evaluation $evaluation, EvaluationDocument $document)
    {
        // Verificar permissão
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Deletar arquivo (automático no modelo)
        $document->delete();

        return response()->json([
            'message' => 'Documento deletado com sucesso',
        ]);
    }

    public function download(Evaluation $evaluation, EvaluationDocument $document)
    {
        // Verificar permissão
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Obter arquivo do Supabase
        $content = Storage::disk('supabase')->get($document->file_path);

        return response($content, 200)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', "attachment; filename='{$document->title}'");
    }
}
```

---

## 🔒 Segurança

### ✅ Boas Práticas

1. **Chaves no .env** - Nunca commitir credenciais no Git
2. **Service Role Key** - Manter segura, usar apenas no servidor
3. **Anon Key** - Usar apenas para acesso público
4. **Permissões RLS** - Configurar Row Level Security no Supabase
5. **Validação** - Sempre validar tipo e tamanho de arquivo
6. **Estrutura de Pastas** - Organizar por entidade (evaluations, benefits, etc)

### Configurar RLS no Supabase

1. Vá em **Storage** → **Policies** (seu bucket)
2. Crie políticas:
   - **SELECT**: Qualquer um pode ler arquivos públicos
   - **INSERT**: Apenas autenticados
   - **DELETE**: Apenas proprietário ou admin

---

## 🧪 Testes

### Testar Upload

```php
// Arquivo de teste
$response = $this->post('/api/v1/evaluations/1/documents', [
    'document' => new \Illuminate\Http\UploadedFile(
        storage_path('test.pdf'),
        'test.pdf',
        'application/pdf'
    ),
    'title' => 'Test Document'
]);

// Verificar resposta
$response->assertStatus(201);
$response->assertJsonStructure(['data' => ['id', 'title', 'url']]);
```

---

## 📊 Monitoramento

### Verificar Uso no Supabase Dashboard

1. **Storage** → Seu bucket
2. Visualizar:
   - Arquivos armazenados
   - Espaço total usado
   - Histórico de uploads

---

## 🚀 Deploy em Produção

### Variáveis de Ambiente em Produção

1. No provedor (Heroku, AWS, etc), adicionar:
   ```
   SUPABASE_URL=...
   SUPABASE_ANON_KEY=...
   AWS_ENDPOINT=...
   ```

2. Testar antes de mergear

### Migração de Arquivos Antigos

Se tiver arquivos antigos no `local` ou `public`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\EvaluationDocument;

class MigrateToSupabase extends Command
{
    protected $signature = 'migrate:supabase';
    protected $description = 'Migrar arquivos para Supabase Storage';

    public function handle()
    {
        $documents = EvaluationDocument::all();

        foreach ($documents as $document) {
            // Ler arquivo antigo
            $content = Storage::disk('local')->get($document->file_path);

            // Salvar no Supabase
            $newPath = "evaluations/{$document->evaluation_id}/documents/" . basename($document->file_path);
            
            Storage::disk('supabase')->put($newPath, $content, 'public');

            // Atualizar referência
            $document->update(['file_path' => $newPath]);

            $this->info("Migrado: {$document->title}");
        }

        $this->info('Migração concluída!');
    }
}
```

Execute:
```bash
php artisan migrate:supabase
```

---

## 📚 Referências Úteis

- **Documentação Supabase Storage:** https://supabase.com/docs/guides/storage
- **Documentação Laravel Storage:** https://laravel.com/docs/filesystem
- **AWS S3 API:** https://docs.aws.amazon.com/s3/

---

## ❓ Troubleshooting

### Erro: "Access Denied"
- Verificar permissões RLS no Supabase
- Verificar se chaves estão corretas no `.env`

### Erro: "File not found"
- Verificar caminho do arquivo
- Verificar se bucket existe

### URL não carrega
- Verificar se bucket é público
- Verificar CORS settings no Supabase

---

**Versão:** 1.0  
**Data:** 2026-05-12  
**Status:** Pronto para implementação
