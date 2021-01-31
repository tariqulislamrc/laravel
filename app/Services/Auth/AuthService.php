<?php


namespace App\Services\Auth;


use App\Events\UserLogin;
use App\Notifications\PasswordReset;
use App\Notifications\PasswordResetted;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    protected $user;
    protected $throttle;

    /**
     * Instantiate a new instance.
     *
     * @param UserService $user
     * @param LoginThrottleService $throttle
     */

    public function __construct(
        UserService $user,
        LoginThrottleService $throttle
    ) {
        $this->user       = $user;
        $this->throttle   = $throttle;
    }

    /**
     * Authenticate an user.
     *
     * @param array $params
     * @return array
     */
    public function auth($params = array())
    {

        $email_or_username = gv($params, 'email_or_username');

        $this->throttle->validate();

        $token = $this->validateLogin($params);

        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $auth_user = $this->user->findByEmail($email_or_username);
        } else {
            $auth_user = $this->user->findByUsername($email_or_username);
        }
        $this->validateStatus($auth_user);
        event(new UserLogin($auth_user));
       
        $auth_user = $auth_user->fresh();

        return compact('token','auth_user');
    }



    /**
     * Validate login credentials.
     *
     * @param array $params
     * @return auth token
     */
    public function validateLogin($params = array())
    {
        $email_or_username = gv($params, 'email_or_username');
        $password          = gv($params, 'password');

        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $credentials = array('email' => $email_or_username, 'password' => $password);
        } else {
            $credentials = array('username' => $email_or_username, 'password' => $password);
        }

        try {
            if (! $token = \JWTAuth::attempt($credentials)) {
                $this->throttle->update();

                throw ValidationException::withMessages(['email_or_username' => trans('auth.failed')]);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages(['email_or_username' => trans('general.something_wrong')]);
        }

        $this->throttle->clearCache();

        return $token;
    }

    /**
     * Validate authenticated user status.
     *
     * @param authenticated user
     * @return null
     */
    public function validateStatus($auth_user)
    {
        if ($auth_user->status === 'pending_activation') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.pending_activation')]);
        }

        if ($auth_user->status === 'pending_approval') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.pending_approval')]);
        }

        if ($auth_user->status === 'disapproved') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.not_activated')]);
        }

        if ($auth_user->status === 'banned') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.account_banned')]);
        }

        if ($auth_user->status != 'activated') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.not_activated')]);
        }

        $user_roles = $auth_user->getRoleNames()->all();

        return true;
    }


    /**
     * Check for reset password availability.
     *
     * @return null
     */
    public function validateResetPasswordStatus()
    {
        if (! config('config.reset_password')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }
    }

    /**
     * Validate user for reset password.
     *
     * @param email $email
     * @return User
     */
    public function validateUserAndStatusForResetPassword($email = null)
    {
        $user = $this->user->findByEmail($email);

        if (! $user) {
            throw ValidationException::withMessages(['email' => trans('passwords.user')]);
        }

        if ($user->status != 'activated') {
            throw ValidationException::withMessages(['email' => trans('passwords.account_not_activated')]);
        }

        return $user;
    }

    /**
     * Request password reset token of user.
     *
     * @param array
     * @return null
     * @throws ValidationException
     */
    public function password($params = array())
    {
        $email = gv($params, 'email');

        $this->validateResetPasswordStatus();

        $user = $this->validateUserAndStatusForResetPassword($email);

        $token = Str::uuid();
        \DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $user->notify(new PasswordReset($user, $token));
    }

    /**
     * Validate reset password token.
     *
     * @param string $token
     * @param null $email $email
     * @return null
     * @throws ValidationException
     */
    public function validateResetPasswordToken(string $token, $email = null)
    {
        if ($email) {
            $reset = \DB::table('password_resets')->where('email', '=', $email)->where('token', '=', $token)->first();
        } else {
            $reset = \DB::table('password_resets')->where('token', '=', $token)->first();
        }

        if (! $reset) {
            throw ValidationException::withMessages(['message' => trans('passwords.token')]);
        }

        if (date("Y-m-d H:i:s", strtotime($reset->created_at . "+".config('config.reset_password_token_lifetime')." minutes")) < date('Y-m-d H:i:s')) {
            throw ValidationException::withMessages(['email' => trans('passwords.token_expired')]);
        }
    }

    /**
     * Reset password of user.
     *
     * @param array
     * @return null
     * @throws ValidationException
     */
    public function reset($params = array())
    {
        $email = gv($params, 'email');
        $token = gv($params, 'token');
        $password = gv($params, 'password');

        $this->validateResetPasswordStatus();

        $user = $this->validateUserAndStatusForResetPassword($email);

        $this->validateResetPasswordToken($token, $email);

        $this->resetPassword($password, $user);

        \DB::table('password_resets')->where('email', '=', $email)->where('token', '=', $token)->delete();

        $user->notify(new PasswordResetted($user));
    }

    /**
     * Update user password.
     *
     * @param string $password
     * @param User $user
     * @return null
     */
    public function resetPassword($password, $user = null)
    {
        $user = ($user) ? : \Auth::user();
        $user->password = bcrypt($password);
        $user->save();
    }

    /**
     * Validate current password of user.
     *
     * @param string $password
     * @return null
     */
    public function validateCurrentPassword($password)
    {
        if (!\Hash::check($password, \Auth::user()->password)) {
            throw ValidationException::withMessages(['password' => trans('passwords.lock_screen_password_mismatch')]);
        }
    }
}
