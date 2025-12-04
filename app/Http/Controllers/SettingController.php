<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Menu;
use App\Models\OutletIP;
use App\Models\UserMenu;
use App\Models\UserOutlet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class SettingController extends Controller
{
    public function getOutlet(Request $request)
    {
        try {
            $outlet = Depot::where('ActiveDepot','Y')->first();
            return response()->json($outlet, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

//    public function getDepots()
//    {
//        $user = JWTAuth::parseToken()->authenticate();
//        $check = UserOutlet::where(['DepotCode' => '%', 'UserID' => $user->UserID])->exists();
//        if ($check) {
//            $depots = Depot::all();
//        } else {
//            $depots = Depot::join('UserDepot', 'vw_Depot.DepotCode', 'UserDepot.DepotCode')
//                ->where('UserDepot.userID', $user->UserID)
//                ->select('vw_Depot.*')
//                ->get();
//        }
//        return response()->json(['depots' => $depots]);
//    }

//    public function getAllOutlet()
//    {
//        $conn = DB::connection('sqlsrv');
//        $sql = "SELECT DepotCode,DepotName FROM vw_Depot ORDER BY DepotCode";
//        $pdo = $conn->getPdo()->prepare($sql);
//        $pdo->execute();
//        return $pdo->fetchAll(\PDO::FETCH_ASSOC);
//    }

    public function dbConnection($outlet)
    {
//        $outletIP = OutletIP::where('DepotCode', $outlet)->first();
//        $ipAddress = $outletIP->IPAddress;
//        Config::set("database.connections.sqlsrv_eps", [
//            'driver' => 'sqlsrv',
//            'host' => $ipAddress,
//            'port' => 1433,
//            'database' => 'EPS',
//            'username' => 'sa',
//            'password' => 'flexiload',
//            'charset' => 'utf8',
//            'prefix' => '',
//            'prefix_indexes' => true,
//        ]);
    }
    public function appSupportingData()
    {
        try {
            $data = [];
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function imageUpload($image, $namePrefix, $destination)
    {

        list($type, $file) = explode(';', $image);
        list(, $extension) = explode('/', $type);
        list(, $file) = explode(',', $file);
        $fileNameToStore = $namePrefix . strtotime(Carbon::now()) . rand(0, 100000000) . '.' . $extension;
        $source = fopen($image, 'r');
        $destination = fopen($destination . $fileNameToStore, 'w');
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);
        return $fileNameToStore;
    }
}
