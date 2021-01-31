<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Config\ConfigServices;

class ConfigController extends Controller
{
    protected $service, $request;

    public function __construct(
        ConfigServices $service,
        Request $request
    ){
        $this->service = $service;
        $this->request = $request;
    }
    public function config(){
        return $this->ok($this->service->getConfig());
    }
}
