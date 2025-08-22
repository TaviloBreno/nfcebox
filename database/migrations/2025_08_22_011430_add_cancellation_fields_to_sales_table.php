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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('xml_path');
            $table->timestamp('canceled_at')->nullable()->after('cancellation_reason');
            $table->string('cancellation_xml_path')->nullable()->after('canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'canceled_at', 'cancellation_xml_path']);
        });
    }
};
