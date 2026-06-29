<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cemeteries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->foreignId('entrance_node_id')->nullable()->constrained('path_nodes')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('cemetery_polygons', function (Blueprint $table) {
            $table->foreignId('cemetery_id')->nullable()->constrained('cemeteries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cemetery_polygons', function (Blueprint $table) {
            $table->dropForeign(['cemetery_id']);
            $table->dropColumn('cemetery_id');
        });

        Schema::dropIfExists('cemeteries');
    }
};
