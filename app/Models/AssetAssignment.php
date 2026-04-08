<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_id',
        'user_id',
        'assigned_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'return_date'   => 'datetime',
    ];
    protected $hidden = ['created_at', 'updated_at'];


    // Relationships 

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}