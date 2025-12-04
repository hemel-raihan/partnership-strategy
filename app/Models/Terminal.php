<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terminal extends Model
{
    use HasFactory;

    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'TerminalID';
    public $keyType = 'string';
    protected $guarded = [];
    protected $table = "Terminal";
}
