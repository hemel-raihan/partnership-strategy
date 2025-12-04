<?php

namespace App\Http\Controllers;

use App\Helpers\PrinterHelper;
use App\Models\BusinessConfig;
use App\Models\Depot;
use App\Models\TempInvoice;
use App\Models\TempInvoiceDetails;
use App\Models\TempInvoiceTender;
use App\Models\TenderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;
use Symfony\Component\Console\Terminal;

class InvoiceController extends Controller
{
    public function allHoldInvoices()
    {
        try {
            $authUser = JWTAuth::parseToken()->authenticate();
            // DB::raw('CONCAT(InvoiceNo, " ", HoldNo) as HoldInvoice')
            $holdInvoices = TempInvoice::select('InvoiceNo','HoldNo')
                                        ->where('InvoiceNo',$authUser->UserId)
                                        ->where('HoldNo','!=',0)
                                        ->orderBy('InvoiceNo', 'desc')
                                        ->get();

            return response()->json(['data' => $holdInvoices], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function holdInvoice(Request $request)
    {
        try {
            $authUser = JWTAuth::parseToken()->authenticate();
            $userExist = TempInvoice::where('InvoiceNo',$authUser->UserId)->first();
            if(!$userExist){
                $holdNo = 1;
            }
            else{
                $holdInvoiceNo = TempInvoice::where('InvoiceNo',$authUser->UserId)->max('HoldNo');
                $holdNo = $holdInvoiceNo + 1;
            }

            $tempInvoice = new TempInvoice();
            $tempInvoice->InvoiceNo = $authUser->UserId;
            $tempInvoice->HoldNo = $holdNo;
            $tempInvoice->CustomerCode = $request->customerCode;
            $tempInvoice->TP = $request->summary['mrpTotal'];
            $tempInvoice->VAT = $request->summary['vatTax'];
            $tempInvoice->Discount = $request->summary['discount'];
            $tempInvoice->ManualDiscount = 0;
            $tempInvoice->InvoiceDiscount = 0;
            $tempInvoice->LoyaltyDiscount = 0;
            $tempInvoice->OtherDiscountType = 0; 
            $tempInvoice->OtherDiscount = 0;
            $tempInvoice->VATDiscount = 0;
            $tempInvoice->NET = $request->summary['mrpTotal'];
            $tempInvoice->NSI = $request->summary['total'];
            $tempInvoice->InvoicePrint = 'N';
            $tempInvoice->InvoicePrintTime = '';
            $tempInvoice->SDVAT = 0;
            $tempInvoice->SDVATDiscount = 0;
            $tempInvoice->save();

            $carts = $request->carts;

            foreach($carts as $cart){
                $tempInvoiceDetails = new TempInvoiceDetails();
                $tempInvoiceDetails->InvoiceNo = $authUser->UserId;
                $tempInvoiceDetails->HoldNo = $holdNo;
                $tempInvoiceDetails->ProductCode = $cart['ProductCode'];
                $tempInvoiceDetails->TransType = $cart['InvoiceType'];
                $tempInvoiceDetails->UnitPrice = $cart['UnitPrice'];
                $tempInvoiceDetails->UnitVAT = 0;
                $tempInvoiceDetails->SalesTP = $cart['UnitPrice'];
                $tempInvoiceDetails->SalesVat = 0;
                $tempInvoiceDetails->SalesQTYOld = 0;
                $tempInvoiceDetails->SalesQTY = $cart['Quantity'];
                $tempInvoiceDetails->BonusQTY = 0;
                $tempInvoiceDetails->ManBonusQTY = 0;
                $tempInvoiceDetails->TP = $cart['UnitPrice'];
                $tempInvoiceDetails->VAT = 0;
                $tempInvoiceDetails->DiscountID = 0;
                $tempInvoiceDetails->Discount = 0;
                $tempInvoiceDetails->ManualDiscount = 0;
                $tempInvoiceDetails->InvoiceDiscount = 0;
                $tempInvoiceDetails->LoyaltyDiscount = 0;
                $tempInvoiceDetails->OtherDiscount = 0;
                $tempInvoiceDetails->VATDiscount = 0;
                $tempInvoiceDetails->NET = $cart['UnitPrice'];
                $tempInvoiceDetails->NSI = $cart['UnitPrice'];
                $tempInvoiceDetails->BonusOff = 0;
                $tempInvoiceDetails->DiscGiven = 0;
                $tempInvoiceDetails->DiscountReturn = 0;
                $tempInvoiceDetails->BonusFor = 0;
                $tempInvoiceDetails->Clearance = 0;
                $tempInvoiceDetails->VATDiscPerc = 0;
                $tempInvoiceDetails->MobileNo = 0;
                $tempInvoiceDetails->LineNumber = 0;
                $tempInvoiceDetails->CardDiscount = 0;
                $tempInvoiceDetails->SDVATRate = 0;
                $tempInvoiceDetails->SDVAT = 0;
                $tempInvoiceDetails->SDVATDiscount = 0;
                $tempInvoiceDetails->VATRate = 0;
                $tempInvoiceDetails->IsWeighingProduct = 'N';
                $tempInvoiceDetails->save();
            }

            return response()->json(['msg' => 'Invoice hold Successfully'], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function recallInvoice(Request $request)
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
            $holdInvoice = $request->holdInvoice;
            $outletCode = $request->outletCode;

            $sql = "SP_RecallTempInvoice '$authUser->UserId','$holdInvoice', '$outletCode'";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            return response()->json(['data' => $res[1], 'data2' => $res[0]], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function allTenderswithCustomer(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $sql = "SP_CustomerTender";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());
            
            // if($customerCode){
            //     $customer = $res[0];
            //     $tender = $res[1];
            // }else{
            //     $customer = [];
            //     $tender = $res[0];
            // }

            return response()->json(['tender'=>$res[0]], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function saveInvoice(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } 
            catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $ipAddress = $request->ip();

            $existTerminalInfo = DB::table('Terminal_Web')->where('IPAddress', $ipAddress)->first();
            if(!$existTerminalInfo){
                return response()->json(['message' => "Terminal is missing. Please check your Terminal:"], 400);
            }

            $printerName = "192.168.101.74";
            if (!PrinterHelper::isPrinterConnected($printerName)) {
                return 'Printer is not connected or available.';
            }
    

            $carts = $request->carts;
            $freeItems = $request->freeItems;
            $tenders = $request->tenders;
            $authUser = JWTAuth::parseToken()->authenticate();

            foreach($tenders as $tender){
                if($tender['TenderValue'] || $tender['TenderRefValue']){
                    $tempTender = new TempInvoiceTender();
                    $tempTender->InvoiceNo = $authUser['UserId'];
                    $tempTender->HoldNo = 0;
                    $tempTender->TenderID = $tender['TenderID'];
                    $tempTender->TenderAmount = $tender['TenderValue'] ? $tender['TenderValue'] : 0;
                    $tempTender->RefNo = $tender['TenderRefValue'] ? $tender['TenderRefValue'] : '';
                    $tempTender->DepositNo = '';
                    $tempTender->syncStatus = 1;
                    $tempTender->save();
                }
            }

            if($authUser->grpSup == 1){
                $TerminalID = 'MIS-Hemel';
            }
            else{
                $getTerminalInfo = DB::table('Terminal_Web')->where('IPAddress', $ipAddress)->first();
                $TerminalID = $getTerminalInfo->TerminalID;
            }

            $sql = "EXEC SP_InsertInvoiceInfo_Web '$authUser->UserId' ,'$request->customerCode', '0', '0', '0', '0', '0', '$TerminalID', '$authUser->UserId'";

            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute(); 
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            try {
                $profile = CapabilityProfile::load("simple"); 
                // $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.74/BIXOLON");
                $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);
    
                $printer = new Printer($connector, $profile);
    
                /* Initialize */
                $printer->initialize();
                $printer->setFont(Printer::FONT_B);
                // $printer->setFont(Printer::FONT_A);
                $printer->setTextSize(1, 1);
    
                $businessConfig = BusinessConfig::first();
                $outlet = Depot::where('ActiveDepot','Y')->first();
                $outletAuth = JWTAuth::parseToken()->authenticate();

                $paidFromTender = array();
                foreach($tenders as $tender){
                    if($tender['TenderID'] == 'PAID'){
                        $cashPaid = $tender['TenderValue'];
                    }
                    if($tender['TenderID'] == 'RETN'){
                        $returnAmount = $tender['TenderValue'];
                    }
                    if($tender['TenderID'] == 'RDIF'){
                        $roundOff = $tender['TenderValue'];
                    }
                    if($tender['TenderID'] != 'RDIF' && $tender['TenderID'] != 'RETN' && $tender['TenderID'] != 'PAID' && $tender['TenderID'] != 'CASH'){
                        if($tender['TenderValue'] || $tender['TenderRefValue']){
                            $paidFromTender[] = array(
                                'TenderType' => $tender['TenderType'],
                                'TenderAmount' => $tender['TenderValue'],
                            );
                        }
                    }
                }

                $discountData = $request->discountData;
    
                // $printer->setPrintLeftMargin(20);
                $printer->setPrintWidth(1000);
    
                // Set center alignment for the header
                $printer->setJustification(Printer::JUSTIFY_CENTER);
    
                // Print the header
                $printer->text($businessConfig['HeaderTitle1'] . "\n");
                $printer->text($businessConfig['BrandTitle'] . "\n");
                $printer->text($businessConfig['CompanyName'] . "\n");
                $printer->text("Registered Address: " . $businessConfig['ShopAddress'] . "\n");
                $printer->text("Central VAT Reg. No. : " . $businessConfig['VatRegNo'] . "\n");
                $printer->text("".$outlet->DepotName.": ".$outlet->DepotCode."\n");
                // $printer->text("".$outlet->Address1."\n");

                $printer->text("------------------------ RETAIL INVOICE ------------------------\n");

                $lineWidth = 63;

                // 1st row: Cashier & Terminal ID
                $left1 = "Cashier: " . $outletAuth['UserName'];
                $right1 = "Terminal ID: " . $getTerminalInfo->Terminal;
                $space1 = $lineWidth - strlen($left1) - strlen($right1);
                $printer->text($left1 . str_repeat(' ', max(1, $space1)) . $right1 . "\n");

                // 2nd row: Invoice & Date
                $left2 = "Invoice No: " . $res[0][0]['InvoiceNo'];
                $right2 = "Date: " . date("m/d/Y H:i");
                $space2 = $lineWidth - strlen($left2) - strlen($right2);
                $printer->text($left2 . str_repeat(' ', max(1, $space2)) . $right2 . "\n");

                $printer->setJustification();

                $printer->text(str_repeat("-", $lineWidth) . "\n");
                $printer->text(sprintf(
                    "%-3s %-28s %10s %6s %12s\n",
                    "SL", "Description", "Price", "Qty", "Total"
                ));
                $printer->text(str_repeat("-", $lineWidth) . "\n");

                foreach ($carts as $index => $item) {
                    $printer->text(sprintf(
                        "%-3s %-28s %10.2f %6.2f %12.2f\n",
                        $index + 1,
                        substr($item['ProductName'], 0, 28),
                        $item['SalesTP'],
                        $item['Quantity'],
                        $item['Quantity'] * $item['SalesTP']
                    ));
                }
                $printer->text(str_repeat("-", $lineWidth) . "\n");

                $printer->setJustification(Printer::JUSTIFY_RIGHT);

                $totalWidth = 32;
                $valueWidth = 12;
                $dotLine = str_repeat('-', 18) . "\n";

                function printLine($printer, $label, $value, $totalWidth, $valueWidth) {
                    $label = substr($label, 0, $totalWidth - $valueWidth - 1);
                    $line = str_pad($label, $totalWidth - $valueWidth, ' ', STR_PAD_RIGHT) .
                            str_pad($value, $valueWidth, ' ', STR_PAD_LEFT);
                    $printer->text($line . "\n");
                }

                printLine($printer, 'Sub Total: ', number_format($request->summary['mrpTotal'], 2), $totalWidth, $valueWidth);
                printLine($printer, '(-) Discount: ', number_format($request->summary['discount'], 2), $totalWidth, $valueWidth);
                printLine($printer, 'After Discount: ', number_format($request->summary['afterDiscount'], 2), $totalWidth, $valueWidth);

                $printer->text($dotLine);

                $vatBase = number_format($request->summary['afterDiscount'], 2);
                // $vatAmount = number_format($request->summary['vatTax'], 2);
                // printLine($printer, 'VAT on ' . $vatBase, $vatAmount, $totalWidth, $valueWidth);
                // printLine($printer, 'After VAT: ', number_format($request->summary['afterVat'], 2), $totalWidth, $valueWidth);
                printLine($printer, '(+/-) Rounding: ', number_format($roundOff, 2), $totalWidth, $valueWidth);

                $printer->text($dotLine);

                printLine($printer, 'Net Payable: ', number_format($request->summary['totalRoundOff'], 2), $totalWidth, $valueWidth);

                $printer->text($dotLine);

                printLine($printer, 'CASH PAID: ', number_format($cashPaid, 2), $totalWidth, $valueWidth);
                printLine($printer, 'CHANGE AMOUNT: ', number_format($returnAmount, 2), $totalWidth, $valueWidth);

    
                $printer->setJustification();
                
                $printer->text("---------------------------------------------\n");
                $printer->text("**DISCOUNT ITEMS**\n");
                $printer->text("---------------------------------------------\n");

                if(count($discountData)){
                    foreach ($discountData as $index => $discountitem) {
                        $productDiscount = (float)($discountitem['discount'] ?? 0);
                        $invoiceDiscount = (float)($discountitem['invoicediscount'] ?? 0);

                        $totalDiscount = $productDiscount + $invoiceDiscount;

                        if ($totalDiscount <= 0) {
                            continue;
                        }

                        // Split the product name into multiple lines if it's too long
                        $productNameLines = wordwrap($discountitem['productname'], 30, "\n", true);
                    
                        // Split the lines into an array
                        $productNameLinesArray = explode("\n", $productNameLines);
                    
                        foreach ($productNameLinesArray as $lineIndex => $productNameLine) {
                            $paddingLength = 30 - strlen($productNameLine);
                            $padding = str_pad('', $paddingLength);
                    
                            $printer->text(sprintf(
                                "%-2s %-30s %7.2f\n",
                                $index + 1,
                                $productNameLine . (($lineIndex === 0) ? '' : $padding),
                                ($lineIndex === 0) ? number_format($totalDiscount, 2) : '' // Print discount value only for the first line of the product name
                            ));
                        }
                    }
                }

                $printer->text("---------------------------------------------\n");
                $printer->text("Your total savings today TK. : ".number_format($request->summary['discount'], 2)."\n");
                $printer->text("---------------------------------------------\n");
    
                // Set center alignment for the header
                $printer->setJustification(Printer::JUSTIFY_CENTER);
    
                $printer->text("**" . $businessConfig['FooterTitle1'] . "\n");
                $printer->text("Join the DREAM FACTORY at:\n");
                $printer->text($businessConfig['FooterTitle2'] . "\n");
                $printer->text("Please visit " . $businessConfig['FooterTitle3'] . " for home delivery.\n");
                $printer->text($businessConfig['FooterTitle4'] . "\n");
                $printer->text($businessConfig['FooterTitle5'] . "\n\n");
                $printer->text($businessConfig['FooterClosing'] . "\n");
    
                $printer->cut();
                $printer->pulse(0,100,500);
                $printer->close();
                
                
                // try{
                //     $serialPort = 'COM1';
                //     $serial = fopen($serialPort, 'w+');
    
                //     if ($serial) {
                //         // ESC/POS command to open the cash drawer
                //         $openDrawerCommand = "27,112,0,25,255";
    
                //         // Send the command to the printer
                //         fwrite($serial, $openDrawerCommand);
    
                //         // Close the serial port
                //         fclose($serial);
                //     } else {
                //         echo "Error opening serial port.";
                //     }
                // }catch (\Exception $exception){
                //     dd($exception->getMessage().'-'.$exception->getLine());
                // }
    
            }catch (\Exception $exception){
                dd($exception->getMessage().'-'.$exception->getLine());
            }

            $requestData = $request->all();
            $requestData['invoiceNo'] = $res[0];

            $invoiceData = json_encode($requestData);

            // Append invoice data to a file
            Storage::disk('invoices')->append('invoice_data.txt', $invoiceData);
            
            return response()->json(['invoiceNo'=> $res[0]], 200);
        } catch (\Exception $e) {
            TempInvoiceDetails::query()->delete();
            TempInvoice::query()->delete();
            TempInvoiceTender::query()->delete();
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function saveInvoiceOld(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $ipAddress = $request->ip();

            $existTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
            if(!$existTerminalInfo){
                return response()->json(['message' => "Terminal is missing. Please check your Terminal:"], 400);
            }

            $printerName = "192.168.101.74";
            if (!PrinterHelper::isPrinterConnected($printerName)) {
                return 'Printer is not connected or available.';
            }
    
            $carts = $request->carts;
            $freeItems = $request->freeItems;
            $tenders = $request->tenders;
            $authUser = JWTAuth::parseToken()->authenticate();

            foreach($tenders as $tender){
                if($tender['TenderValue'] || $tender['TenderRefValue']){
                    $tempTender = new TempInvoiceTender();
                    $tempTender->InvoiceNo = $authUser['UserId'];
                    $tempTender->HoldNo = 0;
                    $tempTender->TenderID = $tender['TenderID'];
                    $tempTender->TenderAmount = $tender['TenderValue'] ? $tender['TenderValue'] : 0;
                    $tempTender->RefNo = $tender['TenderRefValue'] ? $tender['TenderRefValue'] : '';
                    $tempTender->DepositNo = '';
                    $tempTender->syncStatus = 1;
                    $tempTender->save();
                }
            }

            $tempInvoice = new TempInvoice();
            $tempInvoice->InvoiceNo = $authUser['UserId'];
            $tempInvoice->HoldNo = 0;
            $tempInvoice->CustomerCode = $request->customerCode;
            $tempInvoice->TP = $request->summary['mrpTotal'];
            $tempInvoice->VAT = $request->summary['vatTax'];
            $tempInvoice->Discount = $request->summary['discount'];
            $tempInvoice->ManualDiscount = 0;
            $tempInvoice->InvoiceDiscount = $request->summary['invoiceDiscount'];
            $tempInvoice->LoyaltyDiscount = $request->summary['totalLoyaltyDiscount'];
            $tempInvoice->OtherDiscountType = 0;
            $tempInvoice->OtherDiscount = 0;
            $tempInvoice->VATDiscount = $request->summary['totalVatDiscount'];
            $tempInvoice->NET = $request->summary['total'];
            $tempInvoice->NSI = $request->summary['total'];
            $tempInvoice->InvoicePrint = 'N';
            $tempInvoice->InvoicePrintTime = '';
            $tempInvoice->SDVAT = 0;
            $tempInvoice->SDVATDiscount = 0;
            $tempInvoice->save();

            foreach($carts as $cart){
                $tempInvoiceDetails = new TempInvoiceDetails();
                $tempInvoiceDetails->InvoiceNo = $authUser['UserId'];
                $tempInvoiceDetails->HoldNo = 0;
                $tempInvoiceDetails->ProductCode = $cart['ProductCode'];
                $tempInvoiceDetails->TransType = $cart['InvoiceType'];
                $tempInvoiceDetails->UnitPrice = $cart['UnitPrice'];
                $tempInvoiceDetails->UnitVAT = $cart['UnitVat'];
                $tempInvoiceDetails->SalesTP = $cart['SalesTP'];
                $tempInvoiceDetails->SalesVat = $cart['UnitVat'];
                $tempInvoiceDetails->SalesQTYOld = 0;
                $tempInvoiceDetails->SalesQTY = $cart['Quantity'];
                $tempInvoiceDetails->BonusQTY = 0;
                $tempInvoiceDetails->ManBonusQTY = 0;
                $tempInvoiceDetails->TP = $cart['TP'];
                $tempInvoiceDetails->VAT = $cart['SalesVat'];
                $tempInvoiceDetails->DiscountID = $cart['DiscountID'] ?? '';
                $tempInvoiceDetails->Discount = $cart['Discount'];
                $tempInvoiceDetails->ManualDiscount = 0;
                $tempInvoiceDetails->InvoiceDiscount = 0;
                $tempInvoiceDetails->LoyaltyDiscount = $cart['LoyaltyDiscount'];
                $tempInvoiceDetails->OtherDiscount = 0;
                $tempInvoiceDetails->VATDiscount = $cart['VATDiscount'];
                $tempInvoiceDetails->NET = $cart['NSI'];
                $tempInvoiceDetails->NSI = $cart['NSI'];
                $tempInvoiceDetails->BonusOff = 0;
                $tempInvoiceDetails->DiscGiven = 0;
                $tempInvoiceDetails->DiscountReturn = 0;
                $tempInvoiceDetails->BonusFor = 0;
                $tempInvoiceDetails->Clearance = 0;
                $tempInvoiceDetails->VATDiscPerc = 0;
                $tempInvoiceDetails->MobileNo = 0;
                $tempInvoiceDetails->LineNumber = 0;
                $tempInvoiceDetails->CardDiscount = 0;
                $tempInvoiceDetails->SDVATRate = 0;
                $tempInvoiceDetails->SDVAT = 0;
                $tempInvoiceDetails->SDVATDiscount = 0;
                $tempInvoiceDetails->VATRate = $cart['VATPerc'];
                $tempInvoiceDetails->save();
            }

            if($freeItems){
                $existingDetails = TempInvoiceDetails::where('InvoiceNo', $authUser['UserId'])->where('HoldNo', 0)->get();
                foreach($freeItems as $item){
                    if(in_array($item['productcode'], array_column($existingDetails->toArray(), 'ProductCode'))){
                        TempInvoiceDetails::where('InvoiceNo', $authUser['UserId'])->where('HoldNo', 0)->where('ProductCode', $item['productcode'])
                        ->update([
                            'BonusQTY' => $item['bonusqty']
                        ]);
                    }
                    else{
                        $tempInvoiceDetails = new TempInvoiceDetails();
                        $tempInvoiceDetails->InvoiceNo = $authUser['UserId'];
                        $tempInvoiceDetails->HoldNo = 0;
                        $tempInvoiceDetails->ProductCode = $item['productcode'];
                        $tempInvoiceDetails->TransType = 'I';
                        $tempInvoiceDetails->UnitPrice = 0;
                        $tempInvoiceDetails->UnitVAT = 0;
                        $tempInvoiceDetails->SalesTP = 0;
                        $tempInvoiceDetails->SalesVat = 0;
                        $tempInvoiceDetails->SalesQTYOld = 0;
                        $tempInvoiceDetails->SalesQTY = 0;
                        $tempInvoiceDetails->BonusQTY = $item['bonusqty'];
                        $tempInvoiceDetails->ManBonusQTY = 0;
                        $tempInvoiceDetails->TP = 0;
                        $tempInvoiceDetails->VAT = 0;
                        $tempInvoiceDetails->DiscountID = $item['discount_id'];
                        $tempInvoiceDetails->Discount = 0;
                        $tempInvoiceDetails->ManualDiscount = 0;
                        $tempInvoiceDetails->InvoiceDiscount = 0;
                        $tempInvoiceDetails->LoyaltyDiscount = 0;
                        $tempInvoiceDetails->OtherDiscount = 0;
                        $tempInvoiceDetails->VATDiscount = 0;
                        $tempInvoiceDetails->NET = 0;
                        $tempInvoiceDetails->NSI = 0;
                        $tempInvoiceDetails->BonusOff = 0;
                        $tempInvoiceDetails->DiscGiven = 0;
                        $tempInvoiceDetails->DiscountReturn = 0;
                        $tempInvoiceDetails->BonusFor = 0;
                        $tempInvoiceDetails->Clearance = 0;
                        $tempInvoiceDetails->VATDiscPerc = 0;
                        $tempInvoiceDetails->MobileNo = 0;
                        $tempInvoiceDetails->LineNumber = 0;
                        $tempInvoiceDetails->CardDiscount = 0;
                        $tempInvoiceDetails->SDVATRate = 0;
                        $tempInvoiceDetails->SDVAT = 0;
                        $tempInvoiceDetails->SDVATDiscount = 0;
                        $tempInvoiceDetails->VATRate = 0;
                        $tempInvoiceDetails->save();
                    }
                }
            }

            if($authUser->grpSup == 1){
                $TerminalID = 'MIS-Hemel';
            }
            else{
                $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
                $TerminalID = $getTerminalInfo->TerminalID;
            }

            $sql = "EXEC SP_InsertInvoiceInfo '$authUser->UserId' ,'$request->customerCode', '0', '0', '0', '0', '0', '$TerminalID', '$authUser->UserId'";

            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute(); 
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            if($authUser->grpSup != 1){
                // $this->printInvoice($tenders, $carts, $request->summary, $res, $request->discountData, $getTerminalInfo, $authUser);
                try {
                    $profile = CapabilityProfile::load("simple"); 
                    // $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.74/BIXOLON");
                    $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);
        
                    $printer = new Printer($connector, $profile);
        
                    /* Initialize */
                    $printer->initialize();
                    $printer->setFont(Printer::FONT_B);
                    // $printer->setFont(Printer::FONT_A);
                    $printer->setTextSize(1, 1);
        
                    $businessConfig = BusinessConfig::first();
                    $outlet = Depot::where('ActiveDepot','Y')->first();
                    $outletAuth = JWTAuth::parseToken()->authenticate();
    
                    $paidFromTender = array();
                    foreach($tenders as $tender){
                        if($tender['TenderID'] == 'PAID'){
                            $cashPaid = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] == 'RETN'){
                            $returnAmount = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] == 'RDIF'){
                            $roundOff = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] != 'RDIF' && $tender['TenderID'] != 'RETN' && $tender['TenderID'] != 'PAID' && $tender['TenderID'] != 'CASH'){
                            if($tender['TenderValue'] || $tender['TenderRefValue']){
                                $paidFromTender[] = array(
                                    'TenderType' => $tender['TenderType'],
                                    'TenderAmount' => $tender['TenderValue'],
                                );
                            }
                        }
                    }
    
                    $discountData = $request->discountData;
        
                    // $printer->setPrintLeftMargin(20);
                    $printer->setPrintWidth(1000);
        
                    // Set center alignment for the header
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
        
                    // Print the header
                    $printer->text($businessConfig['HeaderTitle1'] . "\n");
                    $printer->text($businessConfig['BrandTitle'] . "\n");
                    $printer->text($businessConfig['CompanyName'] . "\n");
                    $printer->text("Registered Address: " . $businessConfig['ShopAddress'] . "\n");
                    $printer->text("Central VAT Reg. No. : " . $businessConfig['VatRegNo'] . "\n");
                    $printer->text("".$outlet->DepotName.": ".$outlet->DepotCode."\n");
                    $printer->text("".$outlet->Address1."\n");

                    $printer->text("------------------------ RETAIL INVOICE ------------------------\n");

                    $lineWidth = 63;

                    // 1st row: Cashier & Terminal ID
                    $left1 = "Cashier: " . $outletAuth['UserName'];
                    $right1 = "Terminal ID: " . $getTerminalInfo->Terminal;
                    $space1 = $lineWidth - strlen($left1) - strlen($right1);
                    $printer->text($left1 . str_repeat(' ', max(1, $space1)) . $right1 . "\n");

                    // 2nd row: Invoice & Date
                    $left2 = "Invoice No: " . $res[0][0]['InvoiceNo'];
                    $right2 = "Date: " . date("m/d/Y H:i");
                    $space2 = $lineWidth - strlen($left2) - strlen($right2);
                    $printer->text($left2 . str_repeat(' ', max(1, $space2)) . $right2 . "\n");

                    $printer->setJustification();

                    $printer->text(str_repeat("-", $lineWidth) . "\n");
                    $printer->text(sprintf(
                        "%-3s %-28s %10s %6s %12s\n",
                        "SL", "Description", "Price", "Qty", "Total"
                    ));
                    $printer->text(str_repeat("-", $lineWidth) . "\n");

                    foreach ($carts as $index => $item) {
                        $printer->text(sprintf(
                            "%-3s %-28s %10.2f %6.2f %12.2f\n",
                            $index + 1,
                            substr($item['ProductName'], 0, 28),
                            $item['SalesTP'],
                            $item['Quantity'],
                            $item['Quantity'] * $item['SalesTP']
                        ));
                    }
                    $printer->text(str_repeat("-", $lineWidth) . "\n");

                    $printer->setJustification(Printer::JUSTIFY_RIGHT);

                    $totalWidth = 32;
                    $valueWidth = 12;
                    $dotLine = str_repeat('-', 18) . "\n";

                    function printLine($printer, $label, $value, $totalWidth, $valueWidth) {
                        $label = substr($label, 0, $totalWidth - $valueWidth - 1);
                        $line = str_pad($label, $totalWidth - $valueWidth, ' ', STR_PAD_RIGHT) .
                                str_pad($value, $valueWidth, ' ', STR_PAD_LEFT);
                        $printer->text($line . "\n");
                    }

                    printLine($printer, 'Sub Total: ', number_format($request->summary['mrpTotal'], 2), $totalWidth, $valueWidth);
                    printLine($printer, '(-) Discount: ', number_format($request->summary['discount'], 2), $totalWidth, $valueWidth);
                    printLine($printer, 'After Discount: ', number_format($request->summary['afterDiscount'], 2), $totalWidth, $valueWidth);

                    $printer->text($dotLine);

                    $vatBase = number_format($request->summary['afterDiscount'], 2);
                    $vatAmount = number_format($request->summary['vatTax'], 2);
                    printLine($printer, 'VAT on ' . $vatBase, $vatAmount, $totalWidth, $valueWidth);
                    printLine($printer, 'After VAT: ', number_format($request->summary['afterVat'], 2), $totalWidth, $valueWidth);
                    printLine($printer, '(+/-) Rounding: ', number_format($roundOff, 2), $totalWidth, $valueWidth);

                    $printer->text($dotLine);

                    printLine($printer, 'Net Payable: ', number_format($request->summary['totalRoundOff'], 2), $totalWidth, $valueWidth);

                    $printer->text($dotLine);

                    printLine($printer, 'CASH PAID: ', number_format($cashPaid, 2), $totalWidth, $valueWidth);
                    printLine($printer, 'CHANGE AMOUNT: ', number_format($returnAmount, 2), $totalWidth, $valueWidth);

        
                    $printer->setJustification();
                    
                    $printer->text("---------------------------------------------\n");
                    $printer->text("**DISCOUNT ITEMS**\n");
                    $printer->text("---------------------------------------------\n");
    
                    // foreach ($discountData as $index => $discountitem) {
                    //     // Split the product name into multiple lines if it's too long
                    //     $productNameLines = wordwrap($discountitem['productname'], 30, "\n", true);
                    
                    //     // Split the lines into an array
                    //     $productNameLinesArray = explode("\n", $productNameLines);
                    
                    //     // Print each line of the product name along with the discount value
                    //     foreach ($productNameLinesArray as $lineIndex => $productNameLine) {
                    //         // Print the product name for the first line, otherwise pad with spaces to align with the previous lines
                    //         if ($lineIndex === 0) {
                    //             $productName = $productNameLine;
                    //         } else {
                    //             $productName = str_pad('', 30); // Padding with spaces
                    //         }
                    
                    //         // Print the formatted row containing the product name and discount value
                    //         $printer->text(sprintf(
                    //             "%-2s %-20s %7.2f\n",
                    //             $index + 1,
                    //             $productName,
                    //             ($lineIndex === 0) ? $discountitem['discount_value'] : null // Print discount value only for the first line of the product name
                    //         ));
                    //     }
                
                    // }
    
                    // if(count($discountData)){
                    //     foreach ($discountData as $index => $discountitem) {
                    //         // Split the product name into multiple lines if it's too long
                    //         $productNameLines = wordwrap($discountitem['productname'], 30, "\n", true);
                        
                    //         // Split the lines into an array
                    //         $productNameLinesArray = explode("\n", $productNameLines);
                        
                    //         // Print each line of the product name along with the discount value
                    //         foreach ($productNameLinesArray as $lineIndex => $productNameLine) {
                    //             // Determine the padding based on the length of the product name
                    //             $paddingLength = 30 - strlen($productNameLine);
                    //             $padding = str_pad('', $paddingLength); // Padding with spaces
                        
                    //             // Print the formatted row containing the product name and discount value
                    //             $printer->text(sprintf(
                    //                 "%-2s %-30s %7.2f\n",
                    //                 $index + 1,
                    //                 $productNameLine . (($lineIndex === 0) ? '' : $padding),
                    //                 ($lineIndex === 0) ? $discountitem['discount_value'] : null // Print discount value only for the first line of the product name
                    //             ));
                    //         }
                    //     }
                    // }
    
                    $printer->text("Your total savings today TK. : ".number_format($request->summary['discount'], 2)."\n");
                    $printer->text("------------------------------\n");
        
                    // Set center alignment for the header
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
        
                    $printer->text("**" . $businessConfig['FooterTitle1'] . "\n");
                    $printer->text("Join the DREAM FACTORY at:\n");
                    $printer->text($businessConfig['FooterTitle2'] . "\n");
                    $printer->text("Please visit " . $businessConfig['FooterTitle3'] . " for home delivery.\n");
                    $printer->text($businessConfig['FooterTitle4'] . "\n");
                    $printer->text($businessConfig['FooterTitle5'] . "\n\n");
                    $printer->text($businessConfig['FooterClosing'] . "\n");
        
                    $printer->cut();
                    $printer->pulse(0,100,500);
                    $printer->close();
                    
                    
                    // try{
                    //     $serialPort = 'COM1';
                    //     $serial = fopen($serialPort, 'w+');
        
                    //     if ($serial) {
                    //         // ESC/POS command to open the cash drawer
                    //         $openDrawerCommand = "27,112,0,25,255";
        
                    //         // Send the command to the printer
                    //         fwrite($serial, $openDrawerCommand);
        
                    //         // Close the serial port
                    //         fclose($serial);
                    //     } else {
                    //         echo "Error opening serial port.";
                    //     }
                    // }catch (\Exception $exception){
                    //     dd($exception->getMessage().'-'.$exception->getLine());
                    // }
        
                }catch (\Exception $exception){
                    dd($exception->getMessage().'-'.$exception->getLine());
                }
            }

            $requestData = $request->all();
            $requestData['invoiceNo'] = $res[0];

            $invoiceData = json_encode($requestData);

            // Append invoice data to a file
            Storage::disk('invoices')->append('invoice_data.txt', $invoiceData);
            
            return response()->json(['invoiceNo'=> $res[0]], 200);
        } catch (\Exception $e) {
            TempInvoiceDetails::query()->delete();
            TempInvoice::query()->delete();
            TempInvoiceTender::query()->delete();
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function saveInvoicetest(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $ipAddress = $request->ip();

            $existTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
            if(!$existTerminalInfo){
                return response()->json(['message' => "Terminal is missing. Please check your Terminal:"], 400);
            }

            $printerName = "192.168.101.74";
            if (!PrinterHelper::isPrinterConnected($printerName)) {
                return 'Printer is not connected or available.';
            }
    

            $carts = $request->carts;
            $freeItems = $request->freeItems;
            $tenders = $request->tenders;
            $authUser = JWTAuth::parseToken()->authenticate();

            foreach($tenders as $tender){
                if($tender['TenderValue'] || $tender['TenderRefValue']){
                    $tempTender = new TempInvoiceTender();
                    $tempTender->InvoiceNo = $authUser['UserId'];
                    $tempTender->HoldNo = 0;
                    $tempTender->TenderID = $tender['TenderID'];
                    $tempTender->TenderAmount = $tender['TenderValue'] ? $tender['TenderValue'] : 0;
                    $tempTender->RefNo = $tender['TenderRefValue'] ? $tender['TenderRefValue'] : '';
                    $tempTender->DepositNo = '';
                    $tempTender->syncStatus = 1;
                    $tempTender->save();
                }
            }

            $tempInvoice = new TempInvoice();
            $tempInvoice->InvoiceNo = $authUser['UserId'];
            $tempInvoice->HoldNo = 0;
            $tempInvoice->CustomerCode = $request->customerCode;
            $tempInvoice->TP = $request->summary['mrpTotal'];
            $tempInvoice->VAT = $request->summary['vatTax'];
            $tempInvoice->Discount = $request->summary['discount'];
            $tempInvoice->ManualDiscount = 0;
            $tempInvoice->InvoiceDiscount = $request->summary['invoiceDiscount'];
            $tempInvoice->LoyaltyDiscount = $request->summary['totalLoyaltyDiscount'];
            $tempInvoice->OtherDiscountType = 0;
            $tempInvoice->OtherDiscount = 0;
            $tempInvoice->VATDiscount = $request->summary['totalVatDiscount'];
            $tempInvoice->NET = $request->summary['total'];
            $tempInvoice->NSI = $request->summary['total'];
            $tempInvoice->InvoicePrint = 'N';
            $tempInvoice->InvoicePrintTime = '';
            $tempInvoice->SDVAT = 0;
            $tempInvoice->SDVATDiscount = 0;
            $tempInvoice->save();

            foreach($carts as $cart){
                $tempInvoiceDetails = new TempInvoiceDetails();
                $tempInvoiceDetails->InvoiceNo = $authUser['UserId'];
                $tempInvoiceDetails->HoldNo = 0;
                $tempInvoiceDetails->ProductCode = $cart['ProductCode'];
                $tempInvoiceDetails->TransType = $cart['InvoiceType'];
                $tempInvoiceDetails->UnitPrice = $cart['UnitPrice'];
                $tempInvoiceDetails->UnitVAT = $cart['UnitVat'];
                $tempInvoiceDetails->SalesTP = $cart['SalesTP'];
                $tempInvoiceDetails->SalesVat = $cart['UnitVat'];
                $tempInvoiceDetails->SalesQTYOld = 0;
                $tempInvoiceDetails->SalesQTY = $cart['Quantity'];
                $tempInvoiceDetails->BonusQTY = 0;
                $tempInvoiceDetails->ManBonusQTY = 0;
                $tempInvoiceDetails->TP = $cart['TP'];
                $tempInvoiceDetails->VAT = $cart['SalesVat'];
                $tempInvoiceDetails->DiscountID = $cart['DiscountID'] ? $cart['DiscountID'] : '';
                $tempInvoiceDetails->Discount = $cart['Discount'];
                $tempInvoiceDetails->ManualDiscount = 0;
                $tempInvoiceDetails->InvoiceDiscount = 0;
                $tempInvoiceDetails->LoyaltyDiscount = $cart['LoyaltyDiscount'];
                $tempInvoiceDetails->OtherDiscount = 0;
                $tempInvoiceDetails->VATDiscount = $cart['VATDiscount'];
                $tempInvoiceDetails->NET = $cart['NSI'];
                $tempInvoiceDetails->NSI = $cart['NSI'];
                $tempInvoiceDetails->BonusOff = 0;
                $tempInvoiceDetails->DiscGiven = 0;
                $tempInvoiceDetails->DiscountReturn = 0;
                $tempInvoiceDetails->BonusFor = 0;
                $tempInvoiceDetails->Clearance = 0;
                $tempInvoiceDetails->VATDiscPerc = 0;
                $tempInvoiceDetails->MobileNo = 0;
                $tempInvoiceDetails->LineNumber = 0;
                $tempInvoiceDetails->CardDiscount = 0;
                $tempInvoiceDetails->SDVATRate = 0;
                $tempInvoiceDetails->SDVAT = 0;
                $tempInvoiceDetails->SDVATDiscount = 0;
                $tempInvoiceDetails->VATRate = $cart['VATPerc'];
                $tempInvoiceDetails->save();
            }

            if($freeItems){
                $existingDetails = TempInvoiceDetails::where('InvoiceNo', $authUser['UserId'])->where('HoldNo', 0)->get();
                foreach($freeItems as $item){
                    if(in_array($item['productcode'], array_column($existingDetails->toArray(), 'ProductCode'))){
                        TempInvoiceDetails::where('InvoiceNo', $authUser['UserId'])->where('HoldNo', 0)->where('ProductCode', $item['productcode'])
                        ->update([
                            'BonusQTY' => $item['bonusqty']
                        ]);
                    }
                    else{
                        $tempInvoiceDetails = new TempInvoiceDetails();
                        $tempInvoiceDetails->InvoiceNo = $authUser['UserId'];
                        $tempInvoiceDetails->HoldNo = 0;
                        $tempInvoiceDetails->ProductCode = $item['productcode'];
                        $tempInvoiceDetails->TransType = 'I';
                        $tempInvoiceDetails->UnitPrice = 0;
                        $tempInvoiceDetails->UnitVAT = 0;
                        $tempInvoiceDetails->SalesTP = 0;
                        $tempInvoiceDetails->SalesVat = 0;
                        $tempInvoiceDetails->SalesQTYOld = 0;
                        $tempInvoiceDetails->SalesQTY = 0;
                        $tempInvoiceDetails->BonusQTY = $item['bonusqty'];
                        $tempInvoiceDetails->ManBonusQTY = 0;
                        $tempInvoiceDetails->TP = 0;
                        $tempInvoiceDetails->VAT = 0;
                        $tempInvoiceDetails->DiscountID = $item['discount_id'];
                        $tempInvoiceDetails->Discount = 0;
                        $tempInvoiceDetails->ManualDiscount = 0;
                        $tempInvoiceDetails->InvoiceDiscount = 0;
                        $tempInvoiceDetails->LoyaltyDiscount = 0;
                        $tempInvoiceDetails->OtherDiscount = 0;
                        $tempInvoiceDetails->VATDiscount = 0;
                        $tempInvoiceDetails->NET = 0;
                        $tempInvoiceDetails->NSI = 0;
                        $tempInvoiceDetails->BonusOff = 0;
                        $tempInvoiceDetails->DiscGiven = 0;
                        $tempInvoiceDetails->DiscountReturn = 0;
                        $tempInvoiceDetails->BonusFor = 0;
                        $tempInvoiceDetails->Clearance = 0;
                        $tempInvoiceDetails->VATDiscPerc = 0;
                        $tempInvoiceDetails->MobileNo = 0;
                        $tempInvoiceDetails->LineNumber = 0;
                        $tempInvoiceDetails->CardDiscount = 0;
                        $tempInvoiceDetails->SDVATRate = 0;
                        $tempInvoiceDetails->SDVAT = 0;
                        $tempInvoiceDetails->SDVATDiscount = 0;
                        $tempInvoiceDetails->VATRate = 0;
                        $tempInvoiceDetails->save();
                    }
                }
            }

            if($authUser->grpSup == 1){
                $TerminalID = 'MIS-Hemel';
            }
            else{
                $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();
                $TerminalID = $getTerminalInfo->TerminalID;
            }

            $sql = "EXEC SP_InsertInvoiceInfo '$authUser->UserId' ,'$request->customerCode', '0', '0', '0', '0', '0', '$TerminalID', '$authUser->UserId'";

            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute(); 
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            if($authUser->grpSup != 1){
                try {
                    $profile = CapabilityProfile::load("simple"); 
                    $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.74/BIXOLON");
                    // $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);
        
                    $printer = new Printer($connector, $profile);
        
                    /* Initialize */
                    $printer->initialize();
        
                    $printer->setTextSize(1, 1);
        
                    $businessConfig = BusinessConfig::first();
                    $outlet = Depot::where('ActiveDepot','Y')->first();
                    $outletAuth = JWTAuth::parseToken()->authenticate();
    
                    $paidFromTender = array();
                    foreach($tenders as $tender){
                        if($tender['TenderID'] == 'PAID'){
                            $cashPaid = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] == 'RETN'){
                            $returnAmount = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] == 'RDIF'){
                            $roundOff = $tender['TenderValue'];
                        }
                        if($tender['TenderID'] != 'RDIF' && $tender['TenderID'] != 'RETN' && $tender['TenderID'] != 'PAID' && $tender['TenderID'] != 'CASH'){
                            if($tender['TenderValue'] || $tender['TenderRefValue']){
                                $paidFromTender[] = array(
                                    'TenderType' => $tender['TenderType'],
                                    'TenderAmount' => $tender['TenderValue'],
                                );
                            }
                        }
                    }
    
                    $discountData = $request->discountData;
        
                    $printer->setPrintLeftMargin(20);
                    $printer->setPrintWidth(1000);
        
                    // Set center alignment for the header
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
        
                    // Print the header
                    $printer->text($businessConfig['HeaderTitle1'] . "\n");
                    $printer->text($businessConfig['BrandTitle'] . "\n");
                    $printer->text($businessConfig['CompanyName'] . "\n");
                    $printer->text("Registered Address: " . $businessConfig['ShopAddress'] . "\n");
                    $printer->text("Central VAT Reg. No. : " . $businessConfig['VatRegNo'] . "\n");
                    $printer->text("".$outlet->DepotName.": ".$outlet->DepotCode."\n");
                    $printer->text("".$outlet->Address1."\n");
        
                    // Print the retail invoice label
                    $printer->text("-----------------------------------------\n");
                    $printer->text("RETAIL INVOICE\n");
                    $printer->text("-----------------------------------------\n");
        
                    // Reset to left alignment for subsequent content
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
        
                    // Print cashier and invoice details
                    $printer->text("Cashier: " . $outletAuth['UserName'] . "\n");
                    $printer->text("Invoice Number: ".$res[0][0]['InvoiceNo']."\n");
                    
                    $printer->text("Terminal ID: ".$getTerminalInfo->Terminal."\n");
                    $printer->text("Date: " . date("m/d/Y H:i") . "\n");
    
                    $printer->setJustification();
    
                    // Print items table
                    $printer->text("---------------------------------------------\n");
                    $printer->text("SL Description         Price   Qty   Total\n");
                    $printer->text("---------------------------------------------\n");
        
                    foreach ($carts as $index => $item) {
                        $printer->text(sprintf(
                            "%-2s %-20s %7.2f %5.2f %7.2f\n",
                            $index + 1,
                            $item['ProductName'],
                            $item['SalesTP'],
                            $item['Quantity'],
                            $item['Quantity'] * $item['SalesTP']
                        ));
                        $printer->text("---------------------------------------------\n");
                    }
    
                    $subtotalLabel = "Sub Total";
                    $discountLabel = "(-)Discount";
                    $afterDiscountLabel = "After Discount";
                    $vatLabel = "VAT on " . $request->summary['afterDiscount'];
                    $afterVatLabel = "After Vat";
                    $roundingLabel = "(+/-)Rounding";
                    $netPayableLabel = "Net Payable";
    
                    $subtotalValue = number_format($request->summary['mrpTotal'], 2);
                    $discountValue = number_format($request->summary['discount'], 2);
                    $afterDiscountValue = $request->summary['afterDiscount'];
                    $vatTaxValue = number_format($request->summary['vatTax'], 2);
                    $afterVatValue = $request->summary['afterVat'];
                    $roundOffValue = $roundOff;
                    $totalRoundOffValue = $request->summary['totalRoundOff'];
    
                    $maxLength = max(
                        strlen($subtotalLabel),
                        strlen($discountLabel),
                        strlen($afterDiscountLabel),
                        strlen($vatLabel),
                        strlen($afterVatLabel),
                        strlen($roundingLabel),
                        strlen($netPayableLabel)
                    );
    
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
    
                    $printer->text(str_pad($subtotalLabel, $maxLength) . $subtotalValue . "\n");
                    $printer->text(str_pad($discountLabel, $maxLength) . $discountValue . "\n");
                    $printer->text(str_pad($afterDiscountLabel, $maxLength) . $afterDiscountValue . "\n");
                    $printer->text(str_pad($vatLabel, $maxLength) . $vatTaxValue . "\n");
                    $printer->text(str_pad($afterVatLabel, $maxLength) . $afterVatValue . "\n");
                    $printer->text(str_pad($roundingLabel, $maxLength) . $roundOffValue . "\n");
                    $printer->text(str_pad($netPayableLabel, $maxLength) . $totalRoundOffValue . "\n");
        
                    // Print cash paid and return amount
                    $printer->text("------------------------------\n");
                    if(count($paidFromTender) != 0){
                        foreach($paidFromTender as $tenderValue){
                            $printer->text("".$tenderValue['TenderType']."    " . $tenderValue['TenderAmount'] . "\n");    
                        }
                    }
                    $printer->text("CASH PAID     " . $cashPaid . "\n");
                    $printer->text("CHANGE AMOUNT " . $returnAmount . "\n");
    
        
                    // Print additional information and footer
                    $printer->setJustification();
                    
                    // $printer->text("------------------------------\n");
                    $printer->text("---------------------------------------------\n");
                    $printer->text("**DISCOUNT ITEMS**\n");
                    $printer->text("---------------------------------------------\n");
    
                    $printer->text("Your total savings today TK. : ".number_format($request->summary['discount'], 2)."\n");
                    $printer->text("------------------------------\n");
        
                    // Set center alignment for the header
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
        
                    $printer->text("**" . $businessConfig['FooterTitle1'] . "\n");
                    $printer->text("Join the DREAM FACTORY at:\n");
                    $printer->text($businessConfig['FooterTitle2'] . "\n");
                    $printer->text("Please visit " . $businessConfig['FooterTitle3'] . " for home delivery.\n");
                    $printer->text($businessConfig['FooterTitle4'] . "\n");
                    $printer->text($businessConfig['FooterTitle5'] . "\n\n");
                    $printer->text($businessConfig['FooterClosing'] . "\n");
        
                    $printer->cut();
                    $printer->pulse(0,100,500);
                    $printer->close();
        
                }catch (\Exception $exception){
                    dd($exception->getMessage().'-'.$exception->getLine());
                }
            }

            $requestData = $request->all();
            $requestData['invoiceNo'] = $res[0];

            $invoiceData = json_encode($requestData);

            // Append invoice data to a file
            Storage::disk('invoices')->append('invoice_data.txt', $invoiceData);
            
            return response()->json(['invoiceNo'=> $res[0]], 200);
        } catch (\Exception $e) {
            TempInvoiceDetails::query()->delete();
            TempInvoice::query()->delete();
            TempInvoiceTender::query()->delete();
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function testPrint(Request $request){
        try {
            $getTerminalInfo = DB::table('Terminal')->where('TerminalID','MIS-Hemel')->first();

            $profile = CapabilityProfile::load("simple");
            // $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.53/BIXOLON");
            $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);

            $printer = new Printer($connector, $profile);

            /* Initialize */
            $printer->initialize();

            $printer->setTextSize(1, 1);

            $businessConfig = BusinessConfig::first();
            // $outletAuth = JWTAuth::parseToken()->authenticate();

            $invoiceData = [
                ['ProductName' => 'Product A', 'UnitPrice' => 10.00, 'Quantity' => 2],
                ['ProductName' => 'Product B', 'UnitPrice' => 5.00, 'Quantity' => 1],
                ['ProductName' => 'Product C', 'UnitPrice' => 8.50, 'Quantity' => 3],
            ];

            $calculateSummary = [
                'mrpTotal' => 5000.00,
                'discount' => 5.00,
                'afterRoundOff' => 43.00,
                'cashPaid' => 45050.00,
                'returnAmount' => 7.00,
            ];

            $printer->setPrintLeftMargin(20);
            $printer->setPrintWidth(500);

            // Set center alignment for the header
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            // Print the header
            $printer->text($businessConfig['HeaderTitle1'] . "\n");
            $printer->text($businessConfig['BrandTitle'] . "\n");
            $printer->text($businessConfig['CompanyName'] . "\n");
            $printer->text("Registered Address: " . $businessConfig['ShopAddress'] . "\n");
            $printer->text("Central VAT Reg. No. : " . $businessConfig['VatRegNo'] . "\n");
            $printer->text("Uttara Outlet\n");
            $printer->text("House-80, Road-05, Block-C, Uttara, Dhaka\n");

            // Print the retail invoice label
            $printer->text("-----------------------------------------\n");
            $printer->text("RETAIL INVOICE\n");
            $printer->text("-----------------------------------------\n");

            $printer->setPrintLeftMargin(0);
            // $printer->setPrintRightMargin(0);

            // Reset to left alignment for subsequent content
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            // Print cashier and invoice details
            // $printer->text("Cashier: " . $outletAuth['UserName'] . "\n");
            $printer->text("Cashier:  hemel \n");
            $printer->text("Invoice Number: D05400000001\n");

            // Reset to right alignment for subsequent content
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            
            $printer->text("Terminal ID: D004POS2N\n");
            $printer->text("Date: " . date("m/d/Y H:i") . "\n");

            // Print items table
            $printer->text("-----------------------------------------\n");
            $printer->text("SL Description         Price   Qty   Total\n");
            $printer->text("-----------------------------------------\n");

            foreach ($invoiceData as $index => $item) {
                $printer->text(sprintf(
                    "%-2s %-20s %7.2f %5.2f %7.2f\n",
                    $index + 1,
                    $item['ProductName'],
                    $item['UnitPrice'],
                    $item['Quantity'],
                    $item['Quantity'] * $item['UnitPrice']
                ));
            }

            // Reset to right alignment for subsequent content
            $printer->setJustification(Printer::JUSTIFY_RIGHT);

            // Print subtotal, discount, and total
            $printer->text("-------------------------------\n");
            $printer->text("Sub Total                " . $calculateSummary['mrpTotal'] . "\n");
            $printer->text("(- Discount)               " . $calculateSummary['discount'] . "\n");
            $printer->text("Net Payable              " . $calculateSummary['afterRoundOff'] . "\n");

            // Print cash paid and return amount
            $printer->text("------------------------------\n");
            $printer->text("CASH PAID                " . $calculateSummary['cashPaid'] . "\n");
            $printer->text("CHANGE AMOUNT              " . $calculateSummary['returnAmount'] . "\n");

            // Print additional information and footer
            $printer->text("------------------------------\n");
            $printer->text("Your total savings today TK. : 450.00\n");
            $printer->text("------------------------------\n");

            // Set center alignment for the header
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            $printer->text("**" . $businessConfig['FooterTitle1'] . "\n");
            $printer->text("Join the DREAM FACTORY at:\n");
            $printer->text($businessConfig['FooterTitle2'] . "\n");
            $printer->text("Please visit " . $businessConfig['FooterTitle3'] . " for home delivery.\n");
            $printer->text($businessConfig['FooterTitle4'] . "\n");
            $printer->text($businessConfig['FooterTitle5'] . "\n\n");
            $printer->text($businessConfig['FooterClosing'] . "\n");

            $printer->cut();
            $printer->pulse(0,100,500);
            $printer->close();
			
			
			try{
				$serialPort = 'COM1';
				$serial = fopen($serialPort, 'w+');

				if ($serial) {
					// ESC/POS command to open the cash drawer
					$openDrawerCommand = "27,112,0,25,255";

					// Send the command to the printer
					fwrite($serial, $openDrawerCommand);

					// Close the serial port
					fclose($serial);
				} else {
					echo "Error opening serial port.";
				}
			}catch (\Exception $exception){
				dd($exception->getMessage().'-'.$exception->getLine());
			}

        }catch (\Exception $exception){
            dd($exception->getMessage().'-'.$exception->getLine());
        }
    }

    function title(Printer $printer, $text)
    {
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text("\n" . $text);
        $printer->selectPrintMode();
    }

    function testFileRead(Request $request)
    {
        $invoiceData = Storage::disk('invoices')->get('invoice_data.txt');
        $invoices = explode(PHP_EOL, $invoiceData);
        // Convert each line back to array
        $invoices = array_map('json_decode', $invoices);
        return response()->json($invoices);
    }
}
