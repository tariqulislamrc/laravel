<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Services\Config\ConfigServices;

class AuthController extends Controller
{
    private $service, $request, $config;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        AuthService $service,
        Request $request,
        ConfigServices $config
        )
    {
        $this->middleware('auth:api', ['except' => ['login']]);
        $this->service = $service;
        $this->request = $request;
        $this->config = $config;

    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {

        $auth = $this->service->auth($this->request->all());
        $auth_user = $auth['auth_user'];
        $token = $auth['token'];
        $auth_user->user_roles = $auth_user->roles()->pluck('name')->all();
        $auth_user->user_permissions = $auth_user->getAllPermissions()->pluck('name')->all();

        $config = $this->config->getConfig();

        activity('login')->log('login');

        return $this->success([
            'message' => 'You are successfully logged in.',
            'token' => $token,
            'user' => $auth_user,
            'config' => $config
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
