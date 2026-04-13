<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni')->nullable()->unique()->after('email');
            $table->string('phone')->nullable()->after('dni');
            $table->string('position')->nullable()->after('phone'); // cargo
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete()->after('position');
          });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['dni', 'phone', 'position', 'unit_id']);
        });
    }
};