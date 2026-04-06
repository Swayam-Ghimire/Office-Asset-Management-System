<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRequest extends Model
{
    protected $fillable = ['user_id', 'asset_id', 'status', 'requested_at', 'approved_by', 'approved_at', 'reason'];

    public function asset() {
        return $this->belongsTo(Asset::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
