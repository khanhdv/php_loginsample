<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use JWTAuth;
use JWTAuthException;
use App\User;
use Validator;
use Response;
use JWTFactory;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class UserController extends Controller
{
    use AuthenticatesUsers;
    public function __construct()
    {
        $this->user = new User;
    }
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        $token = null;
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'invalid_email_or_password',
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'failed_to_create_token',
            ]);
        }
        return response()->json([
            'response' => 'success',
            'result' => [
                'token' => $token,
            ],
        ]);
    }

    public function getUser(Request $request){
        $user = JWTAuth::toUser($request->token);        
        return response()->json(['result' => $user]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
            'tel' => $request->get('tel','000000'),
            'address' => $request->get('address','Hanoi'),
        ]);
        $user = User::first();

        return response()->json([
            'response' => 'success',
            'result' => [
                'user' => $user,
            ],
        ]);
    }

    protected function sendLoginResponse(Request $request){
        $this->clearLoginAttempts($request);

        $token = (string)$this->guard()->getToken();
        $expiration = $this->guard()->getPayload()->get('exp');
        //get user login
        $user = User::where(['email' => $request->get('email')])->first();

        return response()->json([
            'response' => 'success',
            'result' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }
     /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = JWTAuth::toUser($request->token);
        $validator = Validator::make($request->all(), [
            'address' => 'string|max:255',
            'tel'=> 'numeric|min:0|max:9999999999',
            'name' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $user->update($request->only('address','tel','name'));
        $user->save();
        return response()->json([
            'response' => 'success',
            'result' => [
                'user' => $user,
            ],
        ]);
    }
}
