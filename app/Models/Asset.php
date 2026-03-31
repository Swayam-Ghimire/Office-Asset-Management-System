<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['model_name', 'category_id', 'brand', 'purchase_date', 'condition', 'status', 'img_path'];

    public function requests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function maintenance()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
