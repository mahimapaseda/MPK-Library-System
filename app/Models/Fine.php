<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BookIssue;
use App\Models\Member;

class Fine extends Model
{
    /** @use HasFactory<\Database\Factories\FineFactory> */
    use HasFactory;

    protected $fillable = [
        'book_issue_id',
        'member_id',
        'amount',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function issue()
    {
        return $this->belongsTo(BookIssue::class, 'book_issue_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
