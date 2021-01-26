<?php

namespace App\Services\Auth;

use App\Repositories\User\UserRepository;


class AuthServices{

    private $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function authenticate($request){
        return $request;
    }
}
