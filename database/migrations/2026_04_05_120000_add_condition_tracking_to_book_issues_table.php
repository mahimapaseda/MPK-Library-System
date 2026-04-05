<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost', 'damaged'])->default('issued')->change();
            $table->timestamp('resolved_at')->nullable()->after('returned_at');
            $table->text('condition_notes')->nullable()->after('status');
            $table->decimal('condition_fee', 8, 2)->nullable()->after('condition_notes');
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropColumn(['resolved_at', 'condition_notes', 'condition_fee']);
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued')->change();
        });
    }
};