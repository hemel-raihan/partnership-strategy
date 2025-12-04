<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function searchCustomer(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $customerCode = $request->customerCode;

            $sql = "EXEC SP_CustomerInfoForBill_Web '$customerCode'";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            // return response()->json(['customer' => $res[0], 'discountInfo' => $res[1], 'msg' => 'Success fully generated data'], 200);
            return response()->json(['customer' => $res[0], 'msg' => 'Success fully generated data'], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function searchProduct(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $searchInput = $request->searchInput;

            $sql = "EXEC SP_ScanProductList_Web '$searchInput'";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            return response()->json(['data' => $res[0], 'msg' => 'Success fully generated data'], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }
}
