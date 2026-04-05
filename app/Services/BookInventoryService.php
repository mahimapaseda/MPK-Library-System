<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Support\Collection;

class BookInventoryService
{
    public function provisionCopies(Book $book, int $activeCount, int $lostCount = 0, int $damagedCount = 0): Collection
    {
        $copies = collect();

        for ($index = 1; $index <= $activeCount; $index++) {
            $copies->push($this->createCopy($book, 'available', $index));
        }

        for ($index = 1; $index <= $lostCount; $index++) {
            $copies->push($this->createCopy($book, 'lost', $activeCount + $index));
        }

        for ($index = 1; $index <= $damagedCount; $index++) {
            $copies->push($this->createCopy($book, 'damaged', $activeCount + $lostCount + $index));
        }

        $this->syncBookCounts($book);

        return $copies;
    }

    public function reserveAvailableCopy(Book $book): ?BookCopy
    {
        $copy = BookCopy::query()
            ->where('book_id', $book->id)
            ->where('status', 'available')
            ->orderBy('sequence_number')
            ->lockForUpdate()
            ->first();

        if (! $copy) {
            return null;
        }

        $copy->forceFill(['status' => 'issued'])->save();
        $this->syncBookCounts($book);

        return $copy;
    }

    public function markCopyAvailable(BookCopy $copy): void
    {
        $copy->forceFill(['status' => 'available'])->save();
        $this->syncBookCounts($copy->book);
    }

    public function markCopyIncident(BookCopy $copy, string $status): void
    {
        $copy->forceFill(['status' => $status])->save();
        $this->syncBookCounts($copy->book);
    }

    public function syncBookCounts(Book $book): void
    {
        $book->forceFill([
            'total_quantity' => BookCopy::query()->where('book_id', $book->id)->whereNotIn('status', ['lost', 'damaged'])->count(),
            'available_quantity' => BookCopy::query()->where('book_id', $book->id)->where('status', 'available')->count(),
        ])->save();
    }

    public function alignActiveCopyCount(Book $book, int $targetActiveCount): void
    {
        $currentActiveCount = BookCopy::query()
            ->where('book_id', $book->id)
            ->whereNotIn('status', ['lost', 'damaged'])
            ->count();

        if ($targetActiveCount > $currentActiveCount) {
            $start = ((int) BookCopy::query()->where('book_id', $book->id)->max('sequence_number')) + 1;
            for ($sequence = $start; $sequence < $start + ($targetActiveCount - $currentActiveCount); $sequence++) {
                $this->createCopy($book, 'available', $sequence);
            }
        }

        if ($targetActiveCount < $currentActiveCount) {
            $removable = BookCopy::query()
                ->where('book_id', $book->id)
                ->where('status', 'available')
                ->orderByDesc('sequence_number')
                ->limit($currentActiveCount - $targetActiveCount)
                ->get();

            if ($removable->count() !== ($currentActiveCount - $targetActiveCount)) {
                abort(422, 'Not enough available copies to reduce active stock to the requested level.');
            }

            BookCopy::query()
                ->whereIn('id', $removable->pluck('id')->all())
                ->delete();
        }

        $this->syncBookCounts($book);
    }

    public function nextAccessionNumber(Book $book, int $sequenceNumber): string
    {
        return sprintf('BK-%04d-%04d', $book->id, $sequenceNumber);
    }

    private function createCopy(Book $book, string $status, int $sequenceNumber): BookCopy
    {
        return BookCopy::query()->create([
            'book_id' => $book->id,
            'accession_number' => $this->nextAccessionNumber($book, $sequenceNumber),
            'sequence_number' => $sequenceNumber,
            'status' => $status,
            'location_rack' => $book->location_rack,
        ]);
    }
}
