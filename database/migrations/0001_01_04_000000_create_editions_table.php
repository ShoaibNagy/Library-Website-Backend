<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->string('isbn')->nullable()->index();
            $table->date('publication_date')->nullable();
            $table->string('publisher')->nullable();
            $table->string('language')->default('en');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
