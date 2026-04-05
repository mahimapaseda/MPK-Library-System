<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->enum('status', ['unpaid', 'paid', 'waived'])->default('unpaid')->change();
            $table->foreignId('resolved_by_user_id')->nullable()->after('member_id')->constrained('users')->nullOnDelete();
            $table->timestamp('waived_at')->nullable()->after('paid_at');
            $table->text('resolution_notes')->nullable()->after('waived_at');
        });
    }

    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by_user_id');
            $table->dropColumn(['waived_at', 'resolution_notes']);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid')->change();
        });
    }
};
