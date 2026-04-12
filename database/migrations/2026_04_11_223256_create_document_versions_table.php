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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('version_number');
            $table->string('file_path');
            $table->string('file_original_name');
            $table->string('file_hash')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->string('file_type')->nullable();
            $table->text('change_description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
