<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_trip',
        'id_category',
        'title',
        'description',
        'destination',
        'start_datetime',
        'end_datetime',
        'cost',
        'share_cost'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id_category');
    }

    public function trip(): HasOne
    {
        return $this->hasOne(Trip::class, 'id_trip');
    }
}
