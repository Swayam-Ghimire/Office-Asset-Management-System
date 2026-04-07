<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'model_name',
        'category_id',
        'brand',
        'purchase_date',
        'condition',
        'status',
        'img_path',
    ];

    // Relationships 

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requests()
    {
        return $this->hasMany(AssetRequest::class)->withTrashed();
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->withTrashed();
    }

    public function maintenance()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class)->latest();
    }

    // Scopes 

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'under_maintenance');
    }
}
