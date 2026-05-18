# 💻 Exemplo Prático: Usando Supabase no Projeto

## 📤 Caso 1: Upload de Documentação em Avaliação

### Controller Completo: EvaluationDocumentController

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
    /**
     * Listar documentos de uma avaliação
     * GET /api/v1/evaluations/{evaluation}/documents
     */
    public function index(Evaluation $evaluation)
    {
        $documents = $evaluation->documents()->get()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'url' => Storage::disk('supabase')->url($doc->file_path),
                'file_size' => $doc->file_size,
                'file_type' => $doc->file_type,
                'created_at' => $doc->created_at,
            ];
        });

        return response()->json(['data' => $documents], 200);
    }

    /**
     * Upload de novo documento
     * POST /api/v1/evaluations/{evaluation}/documents
     */
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
        
        // Salvar no Supabase com acesso público
        $uploaded = Storage::disk('supabase')->putFileAs(
            "evaluations/{$evaluation->id}/documents",
            $file,
            $filename,
            'public'
        );

        if (!$uploaded) {
            return response()->json([
                'message' => 'Erro ao salvar documento',
            ], 500);
        }

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
                'url' => Storage::disk('supabase')->url($document->file_path),
                'file_size' => $document->file_size,
                'created_at' => $document->created_at,
            ],
        ], 201);
    }

    /**
     * Obter documento específico
     * GET /api/v1/evaluations/{evaluation}/documents/{document}
     */
    public function show(Evaluation $evaluation, EvaluationDocument $document)
    {
        // Verificar se documento pertence à avaliação
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json([
                'message' => 'Documento não encontrado',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'url' => Storage::disk('supabase')->url($document->file_path),
                'file_size' => $document->file_size,
                'file_type' => $document->file_type,
                'created_at' => $document->created_at,
            ],
        ], 200);
    }

    /**
     * Deletar documento
     * DELETE /api/v1/evaluations/{evaluation}/documents/{document}
     */
    public function destroy(Evaluation $evaluation, EvaluationDocument $document)
    {
        // Verificar se documento pertence à avaliação
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json([
                'message' => 'Documento não encontrado',
            ], 404);
        }

        // Deletar arquivo do Supabase
        Storage::disk('supabase')->delete($document->file_path);

        // Deletar registro do banco
        $document->delete();

        return response()->json([
            'message' => 'Documento deletado com sucesso',
        ], 200);
    }

    /**
     * Download de documento
     * GET /api/v1/evaluations/{evaluation}/documents/{document}/download
     */
    public function download(Evaluation $evaluation, EvaluationDocument $document)
    {
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json([
                'message' => 'Documento não encontrado',
            ], 404);
        }

        // Obter arquivo do Supabase
        $content = Storage::disk('supabase')->get($document->file_path);

        return response($content, 200)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', "attachment; filename=\"{$document->title}\"");
    }
}
```

---

## 📝 Model: EvaluationDocument

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EvaluationDocument extends Model
{
    protected $fillable = [
        'evaluation_id',
        'title',
        'file_path',
        'file_size',
        'file_type',
    ];

    // Relacionamento
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    // Acessor para URL pública
    public function getUrlAttribute()
    {
        return Storage::disk('supabase')->url($this->file_path);
    }

    // Evento: Deletar arquivo ao deletar modelo
    protected static function booted()
    {
        static::deleting(function ($document) {
            // Deletar arquivo do Supabase
            if (Storage::disk('supabase')->exists($document->file_path)) {
                Storage::disk('supabase')->delete($document->file_path);
            }
        });
    }
}
```

---

## 📋 Migration para EvaluationDocument

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('title');
            $table->string('file_path'); // Caminho no Supabase
            $table->integer('file_size'); // Em bytes
            $table->string('file_type')->nullable(); // MIME type
            $table->timestamps();

            $table->index('evaluation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_documents');
    }
};
```

---

## 🎯 Caso 2: Upload de Documentação em Benefit Request

### Adicionar ao BenefitRequestController

```php
/**
 * Upload de documento para solicitação de benefício
 */
public function uploadDocument(Request $request, $id)
{
    $benefitRequest = BenefitRequest::findOrFail($id);

    $request->validate([
        'document' => 'required|file|max:5120',
        'type' => 'required|in:proof,receipt,certificate',
    ]);

    $file = $request->file('document');
    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
    $path = "benefit-requests/{$id}/documents/{$filename}";

    Storage::disk('supabase')->putFileAs(
        "benefit-requests/{$id}/documents",
        $file,
        $filename,
        'public'
    );

    // Atualizar modelo com URL do documento
    $benefitRequest->update([
        'documentation' => array_merge(
            $benefitRequest->documentation ?? [],
            [
                'file_path' => $path,
                'file_url' => Storage::disk('supabase')->url($path),
                'file_type' => $request->type,
                'uploaded_at' => now(),
            ]
        ),
    ]);

    return response()->json([
        'message' => 'Documento enviado com sucesso',
        'data' => [
            'url' => Storage::disk('supabase')->url($path),
            'type' => $request->type,
        ],
    ], 201);
}
```

---

## 🔐 Caso 3: Upload com Autenticação Verificada

```php
/**
 * Garantir que apenas o proprietário pode fazer upload
 */
public function uploadPrivate(Request $request, $id)
{
    // Buscar a solicitação
    $benefitRequest = BenefitRequest::findOrFail($id);

    // Verificar se usuário é proprietário ou admin
    // (adicionar lógica conforme seu modelo de permissões)

    $request->validate([
        'document' => 'required|file|max:10240',
    ]);

    $file = $request->file('document');
    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
    $path = "private/benefit-requests/{$id}/{$filename}";

    // Salvar como privado (não public)
    Storage::disk('supabase')->putFileAs(
        "private/benefit-requests/{$id}",
        $file,
        $filename
        // Sem 'public' = arquivo privado
    );

    return response()->json([
        'message' => 'Arquivo privado salvo',
        'file_path' => $path,
    ], 201);
}
```

---

## 📊 Caso 4: Listar Todos os Arquivos de um Usuário

```php
/**
 * Listar arquivos do usuário
 */
public function myFiles(Request $request)
{
    $userId = $request->user()->id;

    // Listar todos os arquivos do usuário
    $files = Storage::disk('supabase')->allFiles("users/{$userId}");

    return response()->json([
        'data' => array_map(function ($file) {
            return [
                'path' => $file,
                'url' => Storage::disk('supabase')->url($file),
                'size' => Storage::disk('supabase')->size($file),
            ];
        }, $files),
    ]);
}
```

---

## 🧪 Testes com Postman

### 1️⃣ Upload de Documento

```
POST {{base_url}}/v1/evaluations/:evaluation/documents
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

Body:
- document: [arquivo.pdf]
- title: Documento Importante
```

### 2️⃣ Obter URL do Documento

A resposta do upload retorna:
```json
{
  "data": {
    "id": 1,
    "title": "Documento Importante",
    "url": "https://seu-projeto.supabase.co/storage/v1/object/public/...",
    "file_size": 102400
  }
}
```

### 3️⃣ Download do Documento

```
GET {{base_url}}/v1/evaluations/:evaluation/documents/:document/download
Authorization: Bearer {{token}}
```

---

## 🎨 Front-end: Exemplo React

```jsx
// Upload de arquivo
async function uploadDocument(evaluationId, file, title) {
  const formData = new FormData();
  formData.append('document', file);
  formData.append('title', title);

  const response = await fetch(
    `/api/v1/evaluations/${evaluationId}/documents`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    }
  );

  const data = await response.json();
  return data.data; // { id, title, url, file_size }
}

// Usar
const file = e.target.files[0];
const document = await uploadDocument(1, file, 'Meu Documento');
console.log(document.url); // URL pública do arquivo
```

---

## ✅ Checklist de Implementação

- [ ] Instalar pacote AWS S3: `composer require league/flysystem-aws-s3-v3`
- [ ] Configurar variáveis no `.env`
- [ ] Adicionar disco 'supabase' em `config/filesystems.php`
- [ ] Criar model `EvaluationDocument`
- [ ] Criar migration para tabela
- [ ] Criar controller com métodos de upload/download/delete
- [ ] Adicionar rotas em `routes/api.php`
- [ ] Testar com Postman
- [ ] Testar no front-end
- [ ] Deploy em produção

---

**Nota:** Todos os exemplos acima usam `Storage::disk('supabase')` - configure como padrão no `.env` se quiser usar apenas `Storage::` sem especificar o disco.
