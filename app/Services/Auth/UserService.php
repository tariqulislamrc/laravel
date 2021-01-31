<?php


namespace App\Services\Auth;


use App\Repositories\Auth\UserRepository;

class UserService
{
    private $repo;
    /**
     * Instantiate a new instance.
     *
     * @param UserRepository $repo
     */
    public function __construct(
        UserRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * @param $email
     * @return \App\Models\User
     */
    public function findByEmail($email){
        return $this->repo->findByEmail($email);
    }

    public function findByUsername($username){
        return $this->repo->findByUsername($username);
    }

}
