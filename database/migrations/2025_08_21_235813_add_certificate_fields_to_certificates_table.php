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
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('file_path')->after('path');
            $table->string('subject')->nullable()->after('password');
            $table->string('issuer')->nullable()->after('subject');
            $table->timestamp('expires_at')->nullable()->after('issuer');
            $table->boolean('is_valid')->default(false)->after('expires_at');
            $table->boolean('is_default')->default(false)->after('is_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'file_path',
                'subject',
                'issuer',
                'expires_at',
                'is_valid',
                'is_default'
            ]);
        });
    }
};
