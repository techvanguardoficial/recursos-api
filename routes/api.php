<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\BenefitRequestController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\EvaluationAnswerController;
use App\Http\Controllers\Api\EvaluationDocumentController;
use App\Http\Controllers\Api\BenefitTypeController;
use App\Http\Controllers\Api\IndefermentReasonController;
use App\Http\Controllers\Api\SessionTrackController;
use App\Http\Controllers\Api\StatsController;

Route::prefix('v1')->group(function () {

    Route::get(
        'status',
        function () {
            return response()->json(['status' => 'API V1 Recursos is alive!'], 200);
        }
    );
    // Públicas - para receber solicitações de benefício
    Route::post('benefit-requests', [BenefitRequestController::class, 'store']);
    Route::patch('benefit-requests/{id}', [BenefitRequestController::class, 'updatePublic']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::post('/session-count', [SessionTrackController::class, 'store']);
    Route::get('/session-count', [SessionTrackController::class, 'count']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('stats', [StatsController::class, 'index']);
        // Autenticação
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('users', [AuthController::class, 'createUser']);

        // Admin - gerenciar solicitações de benefício
        Route::get('benefit-requests', [BenefitRequestController::class, 'index']);
        Route::get('benefit-requests/{id}', [BenefitRequestController::class, 'show']);
        Route::patch('benefit-requests/{id}/status', [BenefitRequestController::class, 'updateStatus']);
        Route::patch('benefit-requests/{id}/step', [BenefitRequestController::class, 'updateStep']);
        Route::delete('benefit-requests/{id}', [BenefitRequestController::class, 'destroy']);

        Route::get(
            'benefit-requests/{id}/document',
            [BenefitRequestController::class, 'generateDocument']
        )->where('id', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}');

        // Avaliações
        Route::get('evaluations', [EvaluationController::class, 'index']);
        Route::post('evaluations', [EvaluationController::class, 'store']);
        Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show']);
        Route::patch('evaluations/{evaluation}', [EvaluationController::class, 'update']);
        Route::delete('evaluations/{evaluation}', [EvaluationController::class, 'destroy']);

        // Documentos de Avaliações
        Route::get('evaluations/{evaluation}/documents', [EvaluationDocumentController::class, 'index']);
        Route::post('evaluations/{evaluation}/documents', [EvaluationDocumentController::class, 'store']);
        Route::get('evaluations/{evaluation}/documents/{document}', [EvaluationDocumentController::class, 'show']);
        Route::delete('evaluations/{evaluation}/documents/{document}', [EvaluationDocumentController::class, 'destroy']);
    });
});
