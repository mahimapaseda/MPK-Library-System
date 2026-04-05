<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->foreignId('book_copy_id')->nullable()->after('book_id')->constrained('book_copies')->nullOnDelete();
        });

        $books = DB::table('books')->orderBy('id')->get();

        foreach ($books as $book) {
            $lostCount = DB::table('book_issues')->where('book_id', $book->id)->where('status', 'lost')->count();
            $damagedCount = DB::table('book_issues')->where('book_id', $book->id)->where('status', 'damaged')->count();
            $activeCount = (int) $book->total_quantity;
            $activeCopyIds = [];
            $lostCopyIds = [];
            $damagedCopyIds = [];

            for ($sequence = 1; $sequence <= $activeCount; $sequence++) {
                $copyId = DB::table('book_copies')->insertGetId([
                    'book_id' => $book->id,
                    'accession_number' => sprintf('BK-%04d-%04d', $book->id, $sequence),
                    'sequence_number' => $sequence,
                    'status' => 'available',
                    'location_rack' => $book->location_rack,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $activeCopyIds[] = $copyId;
            }

            for ($offset = 1; $offset <= $lostCount; $offset++) {
                $sequence = $activeCount + $offset;
                $copyId = DB::table('book_copies')->insertGetId([
                    'book_id' => $book->id,
                    'accession_number' => sprintf('BK-%04d-%04d', $book->id, $sequence),
                    'sequence_number' => $sequence,
                    'status' => 'lost',
                    'location_rack' => $book->location_rack,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $lostCopyIds[] = $copyId;
            }

            for ($offset = 1; $offset <= $damagedCount; $offset++) {
                $sequence = $activeCount + $lostCount + $offset;
                $copyId = DB::table('book_copies')->insertGetId([
                    'book_id' => $book->id,
                    'accession_number' => sprintf('BK-%04d-%04d', $book->id, $sequence),
                    'sequence_number' => $sequence,
                    'status' => 'damaged',
                    'location_rack' => $book->location_rack,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $damagedCopyIds[] = $copyId;
            }

            $issuedIds = DB::table('book_issues')
                ->where('book_id', $book->id)
                ->whereIn('status', ['issued', 'overdue'])
                ->orderBy('issued_at')
                ->pluck('id')
                ->all();

            foreach ($issuedIds as $index => $issueId) {
                if (! isset($activeCopyIds[$index])) {
                    break;
                }

                DB::table('book_issues')->where('id', $issueId)->update(['book_copy_id' => $activeCopyIds[$index]]);
                DB::table('book_copies')->where('id', $activeCopyIds[$index])->update(['status' => 'issued', 'updated_at' => now()]);
            }

            $returnedIds = DB::table('book_issues')
                ->where('book_id', $book->id)
                ->where('status', 'returned')
                ->orderBy('issued_at')
                ->pluck('id')
                ->all();

            if (count($activeCopyIds) > 0) {
                foreach ($returnedIds as $index => $issueId) {
                    $copyId = $activeCopyIds[$index % count($activeCopyIds)];
                    DB::table('book_issues')->where('id', $issueId)->update(['book_copy_id' => $copyId]);
                }
            }

            foreach (DB::table('book_issues')->where('book_id', $book->id)->where('status', 'lost')->orderBy('issued_at')->pluck('id')->all() as $index => $issueId) {
                if (isset($lostCopyIds[$index])) {
                    DB::table('book_issues')->where('id', $issueId)->update(['book_copy_id' => $lostCopyIds[$index]]);
                }
            }

            foreach (DB::table('book_issues')->where('book_id', $book->id)->where('status', 'damaged')->orderBy('issued_at')->pluck('id')->all() as $index => $issueId) {
                if (isset($damagedCopyIds[$index])) {
                    DB::table('book_issues')->where('id', $issueId)->update(['book_copy_id' => $damagedCopyIds[$index]]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_copy_id');
        });
    }
};
