<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('condition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand_name')->nullable();
            $table->text('description');
            $table->unsignedInteger('price');
            $table->boolean('is_sold')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
