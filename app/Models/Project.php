<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use App\Models\Ticket;

class Project extends Eloquent
{
    // use HasFactory;
    protected $collection = 'projects';
    protected $connection = 'mongodb';
    
    protected $fillable = [
        'name',
        'description',
        'status',
        'owner_id',
        'created_by',
        'administrators',
        'teams',
        'created_at',
        'updated_at'
    ];

    // Relation avec les tickets
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'project_id');
    }

    // Hook pour cascade delete
    protected static function booted()
    {
        static::deleting(function ($project) {
            $project->tickets()->delete(); // Supprime tous les tickets liés
        });
    }

    public $timestamps = true;
}
