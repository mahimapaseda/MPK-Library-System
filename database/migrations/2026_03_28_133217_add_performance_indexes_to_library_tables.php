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
        Schema::table('books', function (Blueprint $table) {
            $table->index('isbn');
            $table->index('title');
            $table->index('author');
            $table->index('available_quantity');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->index('member_id');
            $table->index('name');
        });

        Schema::table('book_issues', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_date');
            $table->index(['member_id', 'status']);
            $table->index(['book_id', 'status']);
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->index('status');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['isbn']);
            $table->dropIndex(['title']);
            $table->dropIndex(['author']);
            $table->dropIndex(['available_quantity']);
        });
        
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
            $table->dropIndex(['name']);
        });

        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['member_id', 'status']);
            $table->dropIndex(['book_id', 'status']);
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['member_id']);
        });
    }
};
