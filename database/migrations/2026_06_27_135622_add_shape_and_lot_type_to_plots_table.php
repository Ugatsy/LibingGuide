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
        Schema::table('plots', function (Blueprint $table) {
            $table->json('shape')->nullable()->after('lng');
            $table->string('lot_type', 20)->nullable()->after('shape');
            $table->string('dimension', 100)->nullable()->after('lot_type');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['shape', 'lot_type', 'dimension']);
        });
    }
};
