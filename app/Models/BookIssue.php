<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Member;
use App\Models\Fine;

use App\Traits\LogsActivity;

class BookIssue extends Model
{
    /** @use HasFactory<\Database\Factories\BookIssueFactory> */
    use HasFactory, LogsActivity;

    protected function getActivityDescription(): string
    {
        return "Issue Record #{$this->id}";
    }

    protected $fillable = [
        'book_id',
        'book_copy_id',
        'member_id',
        'issued_at',
        'due_date',
        'returned_at',
        'resolved_at',
        'status',
        'condition_notes',
        'condition_fee',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'condition_fee' => 'decimal:2',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function copy()
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }
}
