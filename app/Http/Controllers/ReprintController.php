<?php

namespace App\Http\Controllers;

use App\Models\Terminal;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReprintController extends Controller
{
    public function allTerminals()
    {
        try {
            $terminals = Terminal::select('TerminalID','Terminal')
                                        ->where('Active', 'Y')
                                        ->get();

            return response()->json(['data' => $terminals], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function searchReprintInvoice(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $fromDate = $request->dateRange[0];
            $toDate = $request->dateRange[1];
            $fromTime = $request->timeFrom;
            $toTime = $request->timeTo;
            $terminal = $request->terminal;
            
            $mergeFromDate = new DateTime($fromDate .' ' .$fromTime);
            $mergeToDate = new DateTime($toDate .' ' .$toTime);

            $fromDateTime = $mergeFromDate->format('Y-m-d H:i');
            $toDateTime = $mergeToDate->format('Y-m-d H:i');

            $sql = "select InvoiceNo, InvoiceDate, NSI from Invoice where PrepareDate between '$fromDateTime' and '$toDateTime' and TerminalID = '$terminal'";

            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute();
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            return response()->json(['data' => $res[0], 'msg' => 'Success fully generated data'], 200);
        }
        catch(\Exception $e){
            dd($e->getMessage());
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }

    public function rePrintInvoice(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $conn = DB::connection('sqlsrv');
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                return response()->json(['message' => "Could not connect to the database.  Please check your configuration. error:" . $e], 400);
            }

            $carts = $request->carts;
            $freeItems = $request->freeItems;
            $tenders = $request->tenders;
            $authUser = JWTAuth::parseToken()->authenticate();

            $ipAddress = $request->ip();
            $getTerminalInfo = DB::table('Terminal')->where('IPAddress', $ipAddress)->first();

            $sql = "EXEC SP_InsertInvoiceInfo '$authUser->UserId' ,'$request->customerCode', '0', '0', '0', '0', '0', '$getTerminalInfo->TerminalID', '$authUser->UserId'";
            $pdo = $conn->getPdo()->prepare($sql);

            $pdo->execute(); 
            $res = array();

            do {
                $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
                array_push($res, $rows);
            } while ($pdo->nextRowset());

            try {
                $profile = CapabilityProfile::load("simple");
                // $connector = new WindowsPrintConnector("smb://administrator:faltoo@192.168.101.53/BIXOLON");
                $connector = new WindowsPrintConnector("smb://".$getTerminalInfo->AdministrativeUserID.":".$getTerminalInfo->AdministrativeUserPassword."@".$getTerminalInfo->IPAddress."/".$getTerminalInfo->PrinterName);
    
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
    
                // $printer->setPrintLeftMargin(0);
                // $printer->setPrintRightMargin(0);
    
                // Reset to left alignment for subsequent content
                $printer->setJustification(Printer::JUSTIFY_LEFT);
    
                // Print cashier and invoice details
                $printer->text("Cashier: " . $outletAuth['UserName'] . "\n");
                $printer->text("Invoice Number: ".$res[0][0]['InvoiceNo']."\n");
    
                // Reset to right alignment for subsequent content
                // $printer->setJustification(Printer::JUSTIFY_RIGHT);
                
                $printer->text("Terminal ID: ".$getTerminalInfo->Terminal."\n");
                $printer->text("Date: " . date("m/d/Y H:i") . "\n");
    
                // $printer->setPrintLeftMargin(0);
                // $printer->setJustification(Printer::JUSTIFY_CENTER);
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

                // Reset to right alignment for subsequent content
                // $printer->setJustification(Printer::JUSTIFY_RIGHT);
    
                // Print subtotal, discount, and total
                // $printer->text("-------------------------------\n");
                // $printer->text("Sub Total     " . $request->summary['mrpTotal'] . "\n");
                // $printer->text("(-)Discount  " . number_format($request->summary['discount'], 2) . "\n");
                // $printer->text("-------------------------------\n");
                // $printer->text("After Discount  " . $request->summary['afterDiscount'] . "\n");
                // $printer->text("VAT on ".$request->summary['afterDiscount'].": " . number_format($request->summary['vatTax'], 2) . "\n");
                // $printer->text("-------------------------------\n");
                // $printer->text("After Vat  " . $request->summary['afterVat'] . "\n");
                // $printer->text("(+/-)Rounding  " . $roundOff . "\n");
                // $printer->text("-------------------------------\n");
                // $printer->text("Net Payable   " . $request->summary['totalRoundOff'] . "\n");

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


            return response()->json(['invoiceNo'=> $res[0]], 200);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => "Oops! Something Went Wrong"], 400);
        }
    }
}
