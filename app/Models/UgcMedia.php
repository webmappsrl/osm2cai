<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UgcMedia extends Model
{
    use HasFactory;

    protected $fillable = ['geohub_id', 'name', 'description', 'geometry', 'user_id', 'updated_at', 'raw_data', 'taxonomy_wheres', 'relative_url'];
}
