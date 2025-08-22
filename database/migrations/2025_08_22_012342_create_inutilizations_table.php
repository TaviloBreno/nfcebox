<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inutilizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('series', 3); // Série da NFC-e
            $table->integer('numero_inicial'); // Número inicial da faixa
            $table->integer('numero_final'); // Número final da faixa
            $table->text('justificativa'); // Justificativa da inutilização
            $table->string('protocol', 50)->nullable(); // Protocolo de autorização da SEFAZ
            $table->string('xml_path')->nullable(); // Caminho do XML do evento
            $table->enum('status', ['pending', 'authorized', 'rejected', 'error'])->default('pending');
            $table->text('sefaz_response')->nullable(); // Resposta completa da SEFAZ
            $table->string('sefaz_error_code', 10)->nullable(); // Código de erro da SEFAZ
            $table->text('sefaz_error_message')->nullable(); // Mensagem de erro da SEFAZ
            $table->timestamp('authorized_at')->nullable(); // Data/hora da autorização
            $table->integer('retry_count')->default(0); // Contador de tentativas
            $table->timestamp('next_retry_at')->nullable(); // Próxima tentativa
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'next_retry_at']);
            $table->index(['series', 'numero_inicial', 'numero_final']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inutilizations');
    }
};
