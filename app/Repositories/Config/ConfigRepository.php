<?php

namespace App\Repositories\Config;

use App\Models\Config\Config;

class ConfigRepository{

    protected $model;

    public function __construct(Config $model)
    {
        $this->model = $model;
    }
}
