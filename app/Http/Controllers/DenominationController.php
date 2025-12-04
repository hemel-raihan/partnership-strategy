<?php

namespace App\Http\Controllers;

use App\Models\DenominationDetails;
use App\Models\DenominationMaster;
use App\Models\Depot;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DenominationController extends Controller
{
    public function allNotes()
    {
        try {
            $notes = Note::where('Status', 'Y')->get();

            return response()->json(['data' => $notes], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function allDenominations(Request $request)
    {
        try {
            $authUser = JWTAuth::parseToken()->authenticate();
            $depot = Depot::where('ActiveDepot','Y')->first();
            $ipAddress = $request->ip();
            $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();

            $denomination = DenominationMaster::where('DepotCode', $depot->DepotCode)
                                                ->where('TerminalID', $getTerminalInfo->TerminalID)
                                                ->where('DenominationDate', Carbon::now()->format('Y-m-d'))
                                                ->where('CreatedBy', $authUser['UserId'])
                                                ->orderBy('CreatedAt', 'desc')
                                                ->first();
            if($denomination){
                if($denomination->DenominationType === 'Closing'){
                    $output = 'needToBeOpen';
                }
                else{
                    $output = 'needToBeClosed';
                }
            }
            else{
                $output = 'needToBeOpen';
            }
            $denominationDetails = '';
            if($denomination){
                $denominationDetails = DenominationDetails::where('DenominationID', $denomination->DenominationID)->get();
            }

            return response()->json(['data' => $output, 'denomination' => $denomination, 'details' => $denominationDetails], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function saveDenomination(Request $request)
    {
        try {
            $authUser = JWTAuth::parseToken()->authenticate();

            $ipAddress = $request->ip();
            $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
            $depot = Depot::where('ActiveDepot','Y')->first();

            $totalAmount = $request->netTotalAmount;
            $notes = $request->notes;
;
            $isDenomination = DenominationMaster::where('DepotCode', $depot->DepotCode)
                                                ->where('DenominationDate', Carbon::now()->format('Y-m-d'))
                                                ->where('TerminalID', $getTerminalInfo->TerminalID)
                                                ->where('CreatedBy', $authUser['UserId'])
                                                ->first();

            if($isDenomination){
                $lastDenomination = DenominationMaster::where('DepotCode', $depot->DepotCode)
                    ->where('TerminalID', $getTerminalInfo->TerminalID)
                    ->where('DenominationDate', Carbon::now()->format('Y-m-d'))
                    ->where('CreatedBy', $authUser['UserId'])
                    ->orderBy('CreatedAt', 'desc')
                    ->first();

                if ($lastDenomination && $lastDenomination->DenominationType === 'Closing') {
                    $DenominationType = 'Opening';
                } else {
                    $DenominationType = 'Closing';
                }
            }
            else{
                $DenominationType = 'Opening';
            }

            $serverDate = DB::select("SELECT GETDATE() AS currentDate");
            $currentDate = $serverDate[0]->currentDate;

            $denomination = new DenominationMaster();
            $denomination->DepotCode = $depot->DepotCode;
            $denomination->TerminalID = $getTerminalInfo->TerminalID;
            $denomination->DenominationType = $DenominationType;
            $denomination->DenominationDate = Carbon::now()->format('Y-m-d');
            $denomination->TotalAmount = $totalAmount;
            $denomination->CreatedBy = $authUser['UserId'];
            $denomination->CreatedAt = $currentDate;
            $denomination->save();

            foreach($notes as $note){
                if($note['totalAmount'] > 0){
                    $denominationDetails = new DenominationDetails();
                    $denominationDetails->DenominationID = $denomination->DenominationID;
                    $denominationDetails->NoteID = $note['NoteID'];
                    $denominationDetails->TerminalID = $getTerminalInfo->TerminalID;
                    $denominationDetails->NoteQuantity = $note['Quantity'];
                    $denominationDetails->save();
                }
            }

            return response()->json(['msg' => 'Cash saved Successfully'], 200);
        } catch (\Exception $e) {
            return $e->getMessage();
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function allInvoicesForClosing(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $authUser = JWTAuth::parseToken()->authenticate();
            $depot = Depot::where('ActiveDepot','Y')->first();
            $ipAddress = $request->ip();
            $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
            $businessDate = Carbon::now()->format('Y-m-d');
            $userID = $authUser['UserId'];

            $sql = "EXEC SP_InvoiceDataWithSumsForCashClose '$depot->DepotCode', '$getTerminalInfo->TerminalID', '$businessDate', '$userID'";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            return response()->json(['netDetails' => $res[0], 'invoiceInfo' => $res[1], 'msg' => 'Success fully generated data'], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }
}
