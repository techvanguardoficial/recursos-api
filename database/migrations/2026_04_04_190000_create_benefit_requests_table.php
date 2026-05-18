<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('benefit_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version')->default('1.0'); // Imutável
            $table->integer('current_step')->default(1);
            $table->string('status')->default('in_progress'); // in_progress, submitted, completed

            // Dados Pessoais
            $table->json('personal_data')->nullable(); // fullName, cpf, email, phone

            // Situação do Benefício
            $table->json('benefit_situation')->nullable(); // type

            // Detalhes do Benefício
            $table->json('benefit_details')->nullable(); // name, timeSinceNotification

            // Motivo da Indeferência
            $table->json('deferment_reason')->nullable(); // reason

            // Documentação
            $table->json('documentation')->nullable(); // fileName, fileSize, base64Data

            // Submissão
            $table->json('submission')->nullable(); // submitted, webhookStatus

            // Análise
            $table->json('analysis')->nullable(); // analysis, advice, recommendation, documents

            // Preço
            $table->decimal('price', 10, 2)->nullable();

            // Status do Pagamento
            $table->string('payment_status')->default('pending'); // pending, paid, failed



            $table->timestamps();
            $table->softDeletes();

            // Índices para queries comuns
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefit_requests');
    }
};
