<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BookIssue;
use App\Models\Member;
use App\Models\User;

class Fine extends Model
{
    /** @use HasFactory<\Database\Factories\FineFactory> */
    use HasFactory;

    protected $fillable = [
        'book_issue_id',
        'member_id',
        'resolved_by_user_id',
        'amount',
        'status',
        'paid_at',
        'waived_at',
        'resolution_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'waived_at' => 'datetime',
    ];

    public function issue()
    {
        return $this->belongsTo(BookIssue::class, 'book_issue_id');
    }

    public function bookIssue()
    {
        return $this->belongsTo(BookIssue::class, 'book_issue_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
