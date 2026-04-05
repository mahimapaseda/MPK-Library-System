<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('accession_number')->unique();
            $table->unsignedInteger('sequence_number');
            $table->enum('status', ['available', 'issued', 'lost', 'damaged'])->default('available');
            $table->string('location_rack')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'sequence_number']);
            $table->index(['book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
