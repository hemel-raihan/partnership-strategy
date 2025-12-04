<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DenominationMaster extends Model
{
    use HasFactory;
    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'DenominationID';
    protected $guarded = [];
    protected $table = "DenominationMaster";
}
