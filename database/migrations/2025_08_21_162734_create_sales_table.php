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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('number'); // sequencial interno
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null'); // nullable para "consumidor não identificado"
            $table->decimal('total', 15, 2);
            $table->string('payment_method');
            $table->enum('status', ['draft', 'authorized', 'canceled'])->default('draft');
            $table->string('nfce_key')->nullable();
            $table->string('protocol')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
