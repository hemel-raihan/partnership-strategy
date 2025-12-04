<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempInvoiceDetails extends Model
{
    use HasFactory;
    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'InvoiceNo';
    public $keyType = 'String';
    protected $guarded = [];
    public $incrementing = false;
    protected $table = "TempInvoiceDetails";
}
