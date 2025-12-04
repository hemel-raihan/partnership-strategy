<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderType extends Model
{
    use HasFactory;
    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'TenderID';
    public $keyType = 'String';
    protected $guarded = [];
    public $incrementing = false;
    protected $table = "TenderType";
}
