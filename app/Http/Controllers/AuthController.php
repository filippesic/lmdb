<?php

namespace App\Http\Controllers;

use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
//        $http = new Client;
//        try {
//            $response = $http->post('http://25.32.37.187:8001/oauth/token', [
//                'form_params' => [
//                    'grant_type' => 'password',
//                    'client_id' => 2,
//                    'client_secret' => 'Ga7pkTVR7VkYWuxYdgkCUaokAQ9pFJ8nYjZIGCtH',
//                    'username' => $request->username,
//                    'password' => $request->password,
//                ],
//            ]);
//
//            //dd('here i am mate???');
//            return $response->getBody();
//            //return json_decode((string) $response->getBody(), true);
//
//        } catch (BadResponseException $e) {
//            if($e->getCode() === 400) {
//                return response()->json('Invalid Request. Please enter a username or a password', $e->getCode());
//            } else if($e->getCode() === 401) {
//                return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
//            }
//
//            return response()->json('Something went wrong on the fucking server Filipe!', $e->getCode());
//        }

        $login = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($login)) {
            return response(['message' => 'Invalid credentials mate!']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken]);

    }

    public function register()
    {
        try {
            request()->validate([
                'name' => 'required|min:3',
                'email' => 'required|email',
                'surname' => 'nullable',
                'password' => 'required|min:3',]);

            User::create([
                'name' => request('name'),
                'surname' => request('surname'),
                'email' => request('email'),
                'password' => Hash::make(request('password')),
            ]);

            return response(['message' => 'User successfully created!', 'status' => 200]);
        } catch (QueryException $exception) {
            $errorCode = $exception->errorInfo[1];
            return response(['message' => 'Houston, we have a duplicate entry problem']);
        }

    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
           $token->delete();
        });

        return response(['Logged out successfully', 200]);
    }

}
