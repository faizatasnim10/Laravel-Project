<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STC_mapping extends Model
{
    use HasFactory;

    protected $table = "stc_mappings";
    protected $primaryKey = "map_id";
    public $incrementing = false;
    public $timestamps = false;
}
