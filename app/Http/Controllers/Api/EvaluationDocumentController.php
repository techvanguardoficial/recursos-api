<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationDocument;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;

class EvaluationDocumentController extends Controller
{
    public function __construct(private SupabaseStorageService $storage) {}

    public function index(Evaluation $evaluation)
    {
        $documents = $evaluation->documents()->get()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'url' => $this->storage->getPublicUrl($doc->file_path),
                'file_size' => $doc->file_size,
                'file_type' => $doc->file_type,
                'created_at' => $doc->created_at,
            ];
        });

        return response()->json(['data' => $documents], 200);
    }

    public function store(Request $request, Evaluation $evaluation)
    {
        $request->validate([
            'document' => 'required|file|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        $path = "evaluations/{$evaluation->id}/documents";

        $uploaded = $this->storage->upload($file, $path);

        if (!$uploaded) {
            return response()->json([
                'message' => 'Erro ao salvar documento',
            ], 500);
        }

        $document = EvaluationDocument::create([
            'evaluation_id' => $evaluation->id,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'file_path' => $uploaded,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
        ]);

        return response()->json([
            'message' => 'Documento salvo com sucesso',
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'url' => $this->storage->getPublicUrl($document->file_path),
                'file_size' => $document->file_size,
                'created_at' => $document->created_at,
            ],
        ], 201);
    }

    public function show(Evaluation $evaluation, EvaluationDocument $document)
    {
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json([
                'message' => 'Documento não encontrado',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'url' => $this->storage->getPublicUrl($document->file_path),
                'file_size' => $document->file_size,
                'file_type' => $document->file_type,
                'created_at' => $document->created_at,
            ],
        ], 200);
    }

    public function destroy(Evaluation $evaluation, EvaluationDocument $document)
    {
        if ($document->evaluation_id !== $evaluation->id) {
            return response()->json([
                'message' => 'Documento não encontrado',
            ], 404);
        }

        $this->storage->delete($document->file_path);
        $document->delete();

        return response()->json([
            'message' => 'Documento deletado com sucesso',
        ], 200);
    }
}
