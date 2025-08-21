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
        Schema::create('company_configs', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 14)->unique();
            $table->string('ie', 20);
            $table->string('im', 20)->nullable();
            $table->string('corporate_name');
            $table->string('trade_name');
            $table->json('address_json');
            $table->enum('environment', ['homologacao', 'producao'])->default('homologacao');
            $table->integer('nfce_series');
            $table->integer('nfce_number');
            $table->string('csc_id');
            $table->string('csc_token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_configs');
    }
};
