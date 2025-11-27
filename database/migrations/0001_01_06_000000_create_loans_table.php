<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('copy_id')->constrained()->onDelete('cascade');
            $table->dateTime('checkout_date');
            $table->dateTime('due_date');
            $table->dateTime('return_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['copy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
