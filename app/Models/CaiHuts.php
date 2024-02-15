<?php

namespace App\Models;

use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaiHuts extends Model
{
    use HasFactory;

    protected $fillable = ['unico_id', 'created_at', 'updated_at', 'name', 'second_name', 'description', 'elevation', 'owner', 'geometry', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
