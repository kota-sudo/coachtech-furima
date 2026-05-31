<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('postal_code');
            $table->string('address');
            $table->string('building')->nullable();
            $table->integer('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
