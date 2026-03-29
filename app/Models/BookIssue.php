<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
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
        'member_id',
        'issued_at',
        'due_date',
        'returned_at',
        'status'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
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
