<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'objective',
        'date',
        'start_time',
        'end_time',
        'status',
        'scheduled_by',
        'room_id'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
    ];

    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function agenda()
    {
        return $this->hasOne(Agenda::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class)->withPivot('status')->withTimestamps();
    }

    public function momEntries()
    {
        return $this->hasMany(MomEntry::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
