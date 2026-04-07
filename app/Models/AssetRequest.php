<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'asset_id',
        'status',
        'reason',
        'requested_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships 

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }
}
