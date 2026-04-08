<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description'];
    
    protected $hidden = ['created_at', 'updated_at'];
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
