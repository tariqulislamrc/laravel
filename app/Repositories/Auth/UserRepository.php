<?php


namespace App\Repositories\Auth;


use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserRepository
{
    /**
     * @var model
     */
    public $model;

    /**
     * UserRepository constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Find ip filter with given id or throw an error.
     *
     * @param integer $id
     * @return mixed
     * @throws ValidationException
     */

    public function findOrFail(int $id)
    {
        $model = $this->model->findOrFail($id);

        if (! $model) {
            throw ValidationException::withMessages(['message' => 'User not found']);
        }

        return $model;
    }

    /**
     * Find user by Email
     *
     * @param email $email
     * @return User
     */

    public function findByEmail($email = null) {
        return $this->model->with('roles')->filterByEmail($email, 1)->first();
    }

    /**
     * Find user by Username
     *
     * @param username $username
     * @return User
     */

    public function findByUsername($username = null) {
        return $this->model->with('roles')->filterByUsername($username, 1)->first();
    }
}
