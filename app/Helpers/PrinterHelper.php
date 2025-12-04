<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

class PrinterHelper
{
    /**
     * Check if the printer is connected.
     *
     * @return bool
     */
    public static function isPrinterConnected($printerIpAddress)
    {
        // try{
        //     // $port = 'USB001';

        //     // $connection = @fsockopen($printerIpAddress, $port, $errno, $errstr, 5);
        //     // dd($connection);
        //     // return $connection;
        //     // if (!$connection) {
        //     //     return false; // Printer not reachable
        //     // } else {
        //     //     fclose($connection);
        //     //     return true; // Printer is reachable
        //     // }

        //     $ipAddress = request()->ip();

        //     $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
        //         $TerminalID = $getTerminalInfo->TerminalID;

        //     $profile = CapabilityProfile::load("simple"); 
        //             // $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.53/BIXOLON");
        //             $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);
        //             $printer = new Printer($connector, $profile);
                    
        //             dd($printer);
        //             /* Initialize */
        //             $printer->initialize();

        // }
        // catch(\Exception $e){
        //     dd($e);
        // }

        // // If you're using a different method or library to communicate with the printer,
        // // adjust this method accordingly

        try {
            // Attempt to connect to the printer
            $connector = new FilePrintConnector($printerIpAddress);
            $printer = new Printer($connector);
            $printer->close();
            
            return true; 
        } catch (\Exception $e) {
            return false; // Printer is not connected or an error occurred
        }
    }
}