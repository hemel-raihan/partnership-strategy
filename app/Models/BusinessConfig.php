<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessConfig extends Model
{
    use HasFactory;

    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'id';
    protected $guarded = [];
    protected $table = "BusinessConfig";
}
