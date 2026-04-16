<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{

    protected $fillable = ['asset_id', 'reported_by', 'description', 'status', 'reported_at', 'resolved_at', 'resolution_note'];
    protected $casts = ['reported_at'=>'datetime', 'resolved_at'=>'datetime'];
    protected $hidden = ['created_at', 'updated_at'];
    
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
