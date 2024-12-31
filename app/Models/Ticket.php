<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Ticket extends Eloquent
{
    // use HasFactory;
    protected $collection = 'tickets';
    protected $connection = 'mongodb';

    protected $fillable = [
        'title',
        'description',
        'status',
        'estimate_date',
        'project_id',
        'owner_id',
        'created_at',
        'updated_at'
    ];

    public function project(){
        return $thid->belongsTo(Project::class, 'project_id');
    }

    public function users(){
        return $this->belongsToMany(User::class, null, 'ticket_ids', 'user_ids');
    }

    public $timestamps = true;
}
