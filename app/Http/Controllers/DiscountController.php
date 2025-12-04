<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TempInvoice;
use Illuminate\Http\Request;
use App\Models\TempInvoiceDetails;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DiscountController extends Controller
{
    public function curlPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getDiscountData(Request $request)
    {
        $orderDetails = [];
        $products = $request->cart;

       if (count($products)) {
           foreach ($products as $product) {
               $orderDetails[] = [
                   "productcode" => $product['ProductCode'],
                   "brandcode" => '',
                   "unitprice" => floatval($product['UnitPrice']),
                   "unitvat" => floatval($product['VATPerc']),
                   "salesqty" => intval($product['Quantity']),
                   "stockid" => "",
                   "batchno" => ""
               ];
           }
       }

        $dataArray = [
            "ordermaster" => [
                [
                    "business" => "7",
                    "salestype" => "Primary",
                    "distributorcode" => "AZ",
                    "depotcode" => "",
                    "customercode" => "",
                    "orderdate" => Carbon::now()->format('Y-m-d'),
                    "ordertime" => Carbon::now()->format('Y-m-d H:i:s'),
                    "invoicediscount" => "",
                    "discount_test" => 1
                ]
            ],
            "orderdetails" => $orderDetails
        ];

        $url = 'http://discount.eacibd.com/index.php/syncdata/discount';
        // $url = 'http://10.32.72.60/discount/index.php/syncdata/discount';
        $response = $this->curlPost($url, ['data' => json_encode($dataArray)]);
        return response()->json(['data' => json_decode($response)], 200);
    }

    public function getSynchronousDiscountData(Request $request)
    {
        DB::beginTransaction();
        try {
            $authUser = JWTAuth::parseToken()->authenticate();

            DB::table('TempInvoiceDetails')
                ->where('InvoiceNo', $authUser['UserId'])
                ->where('HoldNo', 0)
                ->delete();

            DB::table('TempInvoice')
                ->where('InvoiceNo', $authUser['UserId'])
                ->where('HoldNo', 0)
                ->delete();

            $summary = $request->summary;
            $carts   = $request->cart;
            $customer   = $request->customer;

            $ipAddress = $request->ip();
            $getTerminalInfo = DB::table('Terminal_Web')->where('IPAddress', $ipAddress)->first();
            $TerminalID = $getTerminalInfo->TerminalID;

            $tempInvoice = new TempInvoice();
            $tempInvoice->InvoiceNo            = $authUser['UserId'];
            $tempInvoice->HoldNo               = 0;
            $tempInvoice->CustomerCode         = $customer['CustomerCode'] ?? '';
            $tempInvoice->TP                   = $summary['mrpTotal'];
            $tempInvoice->VAT                  = 0;
            $tempInvoice->Discount             = $summary['discount'];
            $tempInvoice->ManualDiscount       = 0;
            $tempInvoice->InvoiceDiscount      = $summary['invoiceDiscount'];
            $tempInvoice->LoyaltyDiscount      = $summary['totalLoyaltyDiscount'];
            $tempInvoice->OtherDiscountType    = '0';
            $tempInvoice->OtherDiscount        = 0;
            $tempInvoice->VATDiscount          = $summary['totalVatDiscount'];
            $tempInvoice->NET                  = $summary['total'];
            $tempInvoice->NSI                  = $summary['total'];
            $tempInvoice->InvoicePrint         = 'N';
            $tempInvoice->InvoicePrintTime     = null;
            $tempInvoice->SDVAT                = 0;
            $tempInvoice->SDVATDiscount        = 0;
            $tempInvoice->VATAdjustment        = 0;
            $tempInvoice->DiscountCalcStatus   = 'X'; // initial set to X, then W for waiting, R for ready
            $tempInvoice->InvoiceNoFinal       = '';
            $tempInvoice->ReceiveAmount        = 0;
            $tempInvoice->ReceiveCash          = 0;
            $tempInvoice->ReceiveCard          = 0;
            $tempInvoice->ReceiveCoupon        = 0;
            $tempInvoice->ReceiveGiftVouc      = 0;
            $tempInvoice->CreditNoteDiscount   = 0;
            $tempInvoice->CreditNoteAmount     = 0;
            $tempInvoice->CreditNoteTotal      = 0;
            $tempInvoice->Paid                 = 0;
            $tempInvoice->CashTaken            = 0;
            $tempInvoice->ChangeAmount         = 0;
            $tempInvoice->TerminalID           = $TerminalID ?? '';
            $tempInvoice->UserID               = $authUser['UserId'];
            $tempInvoice->ErrorMsg             = '';
            $tempInvoice->save();

            foreach($carts as $key => $cart){
                $details = new TempInvoiceDetails();
                $details->InvoiceNo        = $authUser['UserId'];
                $details->HoldNo           = 0;
                $details->ProductCode      = $cart['ProductCode'];
                $details->TransType        = $cart['InvoiceType'];
                $details->UnitPrice        = $cart['UnitPrice'];
                $details->VATRate          = $cart['VATPerc'] ?? 0;
                // $details->VATCalcType      = 'STD';
                $details->VATCalcType      = 'Inclusive';
                if (strtolower($details->VATCalcType) === 'inclusive') {
                    $details->UnitVAT = ($details->UnitPrice * $details->VATRate) / ($details->VATRate + 100);
                } 
                else {
                    $details->UnitVAT = ($details->UnitPrice * $details->VATRate) / 100;
                }
                $details->SalesTP          = $cart['UnitPrice'];
                $details->SalesVat         = $details->UnitVAT;
                $details->SalesQTYOld      = 0;
                $details->SalesQTY         = $cart['Quantity'];
                $details->BonusQTY         = 0;
                $details->ManBonusQTY      = 0;
                $details->TP               = $cart['TP'];
                $details->VAT              = $details->SalesQTY * $details->UnitVAT;
                $details->DiscountID       = '';
                $details->Discount         = 0;
                $details->ManualDiscount   = 0;

                $customerDiscount = $customer['DiscountPerc'] ?? 0;
                if (!empty($customerDiscount) && floatval($customerDiscount) > 0) {
                    $details->InvoiceDiscount = ($details->SalesTP * $details->SalesQTY) * (floatval($customerDiscount) / 100.0);
                }

                $details->LoyaltyDiscount  = $cart['LoyaltyDiscount'] ?? 0;
                $details->OtherDiscount    = 0;
                $details->VATDiscount      = 0;
                $details->NET              = 0;
                $details->NSI              = 0;
                $details->BonusOff         = '';
                $details->DiscGiven        = '';
                $details->DiscountReturn   = 0;
                $details->BonusFor         = '';
                $details->Clearance        = '';
                $details->VATDiscPerc      = 0;
                $details->MobileNo         = $customer['CustomerCode'] ?? '';
                $details->LineNumber       = $key + 1;
                $details->CardDiscount     = 0;
                $details->SDVATRate        = 0;
                $details->SDVAT            = 0;
                $details->SDVATDiscount    = 0;
                $details->VATAdjustment    = 0;
                $details->ScanTime         = null;
                $details->IsWeighingProduct= '';
    
                $details->save();
            }

            DB::commit();

            TempInvoice::where('InvoiceNo', $authUser['UserId'])->update(['DiscountCalcStatus' => 'W']);

            $maxWait   = 15; 
            $waited    = 0;
            $discountData = [];

            while ($waited < $maxWait) {
                $invoice = TempInvoice::where('InvoiceNo', $authUser['UserId'])
                    ->where('DiscountCalcStatus', 'R')
                    ->first();

                if ($invoice) {
                    $details = TempInvoiceDetails::query()
                                ->join('Product as p', 'p.ProductCode', '=', 'TempInvoiceDetails.ProductCode')
                                ->where('InvoiceNo', $authUser['UserId'])
                                ->select(
                                    'TempInvoiceDetails.*',
                                    'p.ProductName'
                                )
                                ->get();

                    foreach ($details as $d) {
                        $discountData[] = [
                            "productcode"   => $d->ProductCode,
                            "productname"   => $d->ProductName,
                            "discount"      => $d->Discount,
                            "mandiscount"   => $d->ManualDiscount,
                            "discount_id"   => $d->DiscountID,
                            "calculate_by"  => $d->BonusQTY > 0 ? "Bonus" : "Discount",
                            "bonusqty"  => $d->BonusQTY,
                            "invoicediscount" => $d->InvoiceDiscount,
                        ];
                    }
                    break;
                }

                sleep(1);
                $waited++;
            }

            if (empty($discountData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount calculation timed out. Please try again.'
                ], 408);
            }

            return response()->json([
                'success' => true,
                'discount' => $discountData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
