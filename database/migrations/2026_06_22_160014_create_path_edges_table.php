<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('path_edges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_node_id');
            $table->unsignedBigInteger('to_node_id');
            $table->decimal('weight', 10, 4)->nullable();
            $table->string('path_type')->default('walkway');
            $table->boolean('is_bidirectional')->default(true);
            $table->timestamps();

            $table->index('from_node_id');
            $table->index('to_node_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('path_edges');
    }
};
