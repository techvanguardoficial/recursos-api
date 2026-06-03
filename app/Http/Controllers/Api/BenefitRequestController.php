<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BenefitRequest;
use App\Services\BenefitDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BenefitRequestController extends Controller
{
    public function __construct(private BenefitDocumentService $documentService) {}

    /**
     * Listar todas as solicitações de benefício
     */
    public function index()
    {
        return response()->json(
            BenefitRequest::paginate(15),
            200
        );
    }

    /**
     * Criar ou atualizar uma solicitação de benefício
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|uuid',
            'version' => 'nullable|string|max:10',
            'currentStep' => 'required|integer|min:1',
            'status' => 'required|string|in:in_progress,submitted,completed',

            'personalData' => 'required|array',
            'personalData.fullName' => 'nullable|string|max:255',
            'personalData.cpf' => 'nullable|string|max:20',
            'personalData.email' => 'nullable|email|max:255',
            'personalData.phone' => 'nullable|string|max:20',

            'benefitSituation' => 'required|array',
            'benefitSituation.type' => 'nullable|string',

            'benefitDetails' => 'required|array',
            'benefitDetails.name' => 'nullable|string|max:255',
            'benefitDetails.timeSinceNotification' => 'nullable|string',

            'defermentReason' => 'required|array',
            'defermentReason.reason' => 'nullable|string|max:500',

            'documentation' => 'required|array',
            'documentation.fileName' => 'nullable|string|max:255',
            'documentation.fileSize' => 'nullable|integer|min:0',
            'documentation.base64Data' => 'nullable|string',

            'submission' => 'nullable|array',
            'submission.submitted' => 'nullable|boolean',
            'submission.webhookStatus' => 'nullable|string',

            'analysis' => 'nullable|array',
            'analysis.analysis' => 'nullable|string',
            'analysis.advice' => 'nullable|string',
            'analysis.recommendation' => 'nullable|string',
            'analysis.documents' => 'nullable|array',
            'analysis.content' => 'nullable|string',

            'price' => 'nullable|decimal:2',

            'payment_status' => 'nullable|string|in:pending,paid,failed',
        ]);

        // Processar documentação (converter base64 para URL do Supabase)
        if (isset($validated['documentation'])) {
            $validated['documentation'] = $this->documentService->processDocumentation($validated['documentation']);
        }

        // Se vier um ID, atualizar; senão, criar novo
        if ($request->has('id') && $request->input('id')) {
            $benefitRequest = BenefitRequest::findOrFail($request->input('id'));

            // Version é imutável, ignorar valor enviado
            $validated['version'] = $benefitRequest->version;

            $benefitRequest->update($this->formatData($validated));

            return response()->json([
                'message' => 'Solicitação de benefício atualizada com sucesso',
                'data' => $benefitRequest->fresh(),
            ], 200);
        }

        // Criar novo
        $benefitRequest = BenefitRequest::create([
            'version' => $validated['version'] ?? '1.0',
            'current_step' => $validated['currentStep'] ?? 1,
            'status' => $validated['status'] ?? 'in_progress',
            'personal_data' => $validated['personalData'] ?? null,
            'benefit_situation' => $validated['benefitSituation'] ?? null,
            'benefit_details' => $validated['benefitDetails'] ?? null,
            'deferment_reason' => $validated['defermentReason'] ?? null,
            'documentation' => $validated['documentation'] ?? null,
            'submission' => $validated['submission'] ?? null,
            'analysis' => $validated['analysis'] ?? null,
            'price' => $validated['price'] ?? null,
            'payment_status' => $validated['payment_status'] ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Solicitação de benefício criada com sucesso',
            'data' => $benefitRequest,
        ], 201);
    }

    /**
     * Exibir uma solicitação específica
     */
    public function show($id)
    {
        $benefitRequest = BenefitRequest::findOrFail($id);

        return response()->json([
            'data' => $benefitRequest,
        ], 200);
    }

    /**
     * Atualizar apenas o status
     */
    public function updateStatus(Request $request, $id)
    {
        $benefitRequest = BenefitRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:in_progress,submitted,completed',
        ]);

        $benefitRequest->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status atualizado com sucesso',
            'data' => $benefitRequest->fresh(),
        ], 200);
    }

    /**
     * Atualizar apenas o step atual
     */
    public function updateStep(Request $request, $id)
    {
        $benefitRequest = BenefitRequest::findOrFail($id);

        $validated = $request->validate([
            'currentStep' => 'required|integer|min:1',
        ]);

        $benefitRequest->update([
            'current_step' => $validated['currentStep'],
        ]);

        return response()->json([
            'message' => 'Step atualizado com sucesso',
            'data' => $benefitRequest->fresh(),
        ], 200);
    }

    /**
     * Atualizar solicitação publicamente (sem autenticação)
     */
    public function updatePublic(Request $request, $id)
    {
        $benefitRequest = BenefitRequest::findOrFail($id);

        $validated = $request->validate([
            'currentStep' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:in_progress,submitted,completed',
            'personalData' => 'nullable|array',
            'personalData.fullName' => 'nullable|string|max:255',
            'personalData.cpf' => 'nullable|string|max:20',
            'personalData.email' => 'nullable|email|max:255',
            'personalData.phone' => 'nullable|string|max:20',
            'benefitSituation' => 'nullable|array',
            'benefitSituation.type' => 'nullable|string',
            'benefitDetails' => 'nullable|array',
            'benefitDetails.name' => 'nullable|string|max:255',
            'benefitDetails.timeSinceNotification' => 'nullable|string',
            'defermentReason' => 'nullable|array',
            'defermentReason.reason' => 'nullable|string|max:500',
            'documentation' => 'nullable|array',
            'documentation.fileName' => 'nullable|string|max:255',
            'documentation.fileSize' => 'nullable|integer|min:0',
            'documentation.base64Data' => 'nullable|string',

            'analysis' => 'nullable|array',
            'analysis.analysis' => 'nullable|string',
            'analysis.advice' => 'nullable|string',
            'analysis.recommendation' => 'nullable|string',
            'analysis.documents' => 'nullable|array',
            'analysis.content' => 'nullable|string',

            'price' => 'nullable|decimal:2',

            'payment_status' => 'nullable|string|in:pending,paid,failed',
        ]);

        $dataToUpdate = [];

        if ($request->has('currentStep')) {
            $dataToUpdate['current_step'] = $validated['currentStep'];
        }
        if ($request->has('status')) {
            $dataToUpdate['status'] = $validated['status'];
        }
        if ($request->has('personalData')) {
            $dataToUpdate['personal_data'] = $validated['personalData'];
        }
        if ($request->has('benefitSituation')) {
            $dataToUpdate['benefit_situation'] = $validated['benefitSituation'];
        }
        if ($request->has('benefitDetails')) {
            $dataToUpdate['benefit_details'] = $validated['benefitDetails'];
        }
        if ($request->has('defermentReason')) {
            $dataToUpdate['deferment_reason'] = $validated['defermentReason'];
        }
        if ($request->has('documentation')) {
            $dataToUpdate['documentation'] = $this->documentService->processDocumentation($validated['documentation']);
        }
        if ($request->has('analysis')) {
            $dataToUpdate['analysis'] = $validated['analysis'];
        }
        if ($request->has('price')) {
            $dataToUpdate['price'] = $validated['price'];
        }
        if ($request->has('payment_status')) {
            $dataToUpdate['payment_status'] = $validated['payment_status'];
        }

        $benefitRequest->update($dataToUpdate);

        return response()->json([
            'message' => 'Solicitação de benefício atualizada com sucesso',
            'data' => $benefitRequest->fresh(),
        ], 200);
    }

    /**
     * Deletar uma solicitação (soft delete)
     */
    public function destroy($id)
    {
        $benefitRequest = BenefitRequest::findOrFail($id);
        $benefitRequest->delete();

        return response()->json([
            'message' => 'Solicitação de benefício deletada com sucesso',
        ], 200);
    }

    /**
     * Formata os dados do request para o padrão snake_case do banco
     */
    private function formatData($data)
    {
        return [
            'version' => $data['version'] ?? '1.0',
            'current_step' => $data['currentStep'] ?? 1,
            'status' => $data['status'],
            'personal_data' => $data['personalData'] ?? null,
            'benefit_situation' => $data['benefitSituation'] ?? null,
            'benefit_details' => $data['benefitDetails'] ?? null,
            'deferment_reason' => $data['defermentReason'] ?? null,
            'documentation' => $data['documentation'] ?? null,
            'submission' => $data['submission'] ?? null,
            'analysis' => $data['analysis'] ?? null,
            'price' => $data['price'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pending',
        ];
    }

    public function generateDocument(
        string $id,
        string $type,
        Request $request
    ): JsonResponse {
        // Validar tipo de documento
        $request->validate([
            'type' => 'required|in:draft,official,presentation,comparison,final',
        ]);

        $documentGenerator = new DocumentGenerator();
        $documentText = $documentGenerator->generateDocument($id, $type);

        return response()->json([
            'message' => "Documento {$type} gerado com sucesso",
            'data' => [
                'documentType' => $type,
                'documentText' => $documentText,
                'contentLength' => strlen($documentText),
                'generatedAt' => now()->toIso8601String(),
            ],
        ]);
    }

}
