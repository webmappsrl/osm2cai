<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HikingRoutesOsm extends Model
{
    protected $table = 'hiking_routes_osm';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'relation_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    use HasFactory;
}
