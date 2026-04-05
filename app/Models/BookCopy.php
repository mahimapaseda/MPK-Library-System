<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    use HasFactory, LogsActivity;

    protected function getActivityDescription(): string
    {
        return "Copy '{$this->accession_number}'";
    }

    protected $fillable = [
        'book_id',
        'accession_number',
        'sequence_number',
        'status',
        'location_rack',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }
}
