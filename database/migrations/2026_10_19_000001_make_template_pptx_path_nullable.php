<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('pptx_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable on rollback: existing PDF-only templates legitimately have no PPTX.
    }
};
