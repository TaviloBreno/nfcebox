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
        Schema::table('customers', function (Blueprint $table) {
            // Renomear cpf_cnpj para document
            $table->renameColumn('cpf_cnpj', 'document');
            
            // Renomear address_json para address
            $table->renameColumn('address_json', 'address');
            
            // Remover campo ie (não será usado)
            $table->dropColumn('ie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Reverter as alterações
            $table->renameColumn('document', 'cpf_cnpj');
            $table->renameColumn('address', 'address_json');
            $table->string('ie')->nullable()->after('document');
        });
    }
};
