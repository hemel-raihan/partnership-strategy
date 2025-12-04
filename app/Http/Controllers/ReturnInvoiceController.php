<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnInvoiceController extends Controller
{
    public function getInvoiceProduct(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $invoiceNo = $request->invoiceNo;

            $sql = "EXEC SP_GetReturnInvoiceDetails '$invoiceNo'";

            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            return response()->json(['data1' => $res[0], 'data2' => $res[1], 'msg' => 'Success fully generated data'], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }
}
