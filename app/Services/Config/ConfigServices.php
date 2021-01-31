<?php

namespace App\Services\Config;

use App\Repositories\Config\ConfigRepository;


class ConfigServices{

    private $user;

    public function __construct(ConfigRepository $user)
    {
        $this->user = $user;
    }

    public function getConfig(){
        return [
            
        ];
    }
}
