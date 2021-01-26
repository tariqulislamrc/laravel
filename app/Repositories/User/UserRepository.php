<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository{

    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }
}
