<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\BookIssue;

use App\Traits\LogsActivity;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory, LogsActivity;

    protected function getActivityDescription(): string
    {
        return "Book '{$this->title}'";
    }

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'language',
        'total_quantity',
        'available_quantity',
        'location_rack'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }
}
