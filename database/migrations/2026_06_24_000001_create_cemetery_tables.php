<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cemetery_polygons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('geojson');
            $table->decimal('area_sqm', 12, 2)->default(0);
            $table->decimal('area_hectares', 10, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('graves', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->string('section', 100)->nullable();
            $table->string('plot_number', 50)->nullable();
            $table->decimal('latitude', 10, 8)->default(0);
            $table->decimal('longitude', 11, 8)->default(0);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graves');
        Schema::dropIfExists('cemetery_polygons');
    }
};
