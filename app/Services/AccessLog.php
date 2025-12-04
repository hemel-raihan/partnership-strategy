<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;

//use Illuminate\Support\Facades\Config;

class AccessLog
{
    public function log($userID, $message)
    {
        try {
            $data = (new DeviceInfo())->info();
            $log = new UserLog();
            $log->AppName = 'POS-Billing';
            $log->UserID = $userID;
            $log->TransactionTime = Carbon::now()->format('Y-m-d h:s');
            $log->TransactionMessage = $message;
            $log->Browser = $data['Browser'];
            $log->BrowserVersion = $data['BrowserVersion'];
            $log->Platform = $data['Platform'];
            $log->Device = $data['Device'];
            $log->AccessIP = $data['AccessIP'];
            $log->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
