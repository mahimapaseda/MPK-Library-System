<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookIssue;
use App\Models\Fine;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class Member extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, Notifiable, LogsActivity;

    protected function getActivityDescription(): string
    {
        return "Member '{$this->name}' (#{$this->member_id})";
    }

    protected $fillable = [
        'member_id',
        'name',
        'email',
        'password',
        'type',
        'grade',
        'contact_number',
        'user_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }
}
