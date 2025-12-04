<?php

namespace App\Services;
use Jenssegers\Agent\Agent;

class DeviceInfo
{
    public function info(){
        $agent = new Agent();
        $data = [];
        $data['Browser'] = $agent->browser();
        $data['BrowserVersion'] = $agent->version($agent->browser());
        $data['Platform'] = $agent->platform();
        $data['Device'] = $agent->device();
        $data['AccessIP'] = request()->ip();
        return $data;
    }
}
