<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;
    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = false;
    public $incrementing = false;
    protected $guarded = [];
    protected $table = "UserLog";
}
