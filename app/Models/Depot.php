<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'DepotCode';
    public $keyType = 'string';
    protected $guarded = [];
    protected $table = "Depot";
}
