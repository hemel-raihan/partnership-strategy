<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempInvoiceTender extends Model
{
    use HasFactory;
    protected $connection = "sqlsrv";
    public $timestamps = false;
    public $primaryKey = 'InvoiceNo';
    protected $guarded = [];
    protected $table = "TempInvoiceTender";
}
