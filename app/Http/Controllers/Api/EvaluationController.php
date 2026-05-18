<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index()
    {
        $evaluations = Evaluation::paginate(15);

        return response()->json($evaluations, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        $evaluation = Evaluation::create($validated);

        return response()->json([
            'message' => 'Avaliação criada com sucesso',
            'data' => $evaluation,
        ], 201);
    }

    public function show(Evaluation $evaluation)
    {
        return response()->json([
            'data' => $evaluation,
        ], 200);
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        $evaluation->update($validated);

        return response()->json([
            'message' => 'Avaliação atualizada com sucesso',
            'data' => $evaluation->fresh(),
        ], 200);
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();

        return response()->json([
            'message' => 'Avaliação deletada com sucesso',
        ], 200);
    }
}
