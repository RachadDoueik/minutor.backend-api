<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'owner_id',
        'title',
        'description',
        'order',
        'estimated_duration'
    ];

    protected $casts = [
        'estimated_duration' => 'integer',
        'order' => 'integer'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
