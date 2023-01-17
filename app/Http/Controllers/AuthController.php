<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    function __construct() {
        $this->middleware('auth:api')->except(['login', 'draw']);
    }
    
    function login(Request $request) {
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = Auth::user();
        $tokenResult = $user->createToken('Access Token');
        $token = $tokenResult->token;
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ], 200);
    }
    
    function logout(Request $request) {
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json(['message' => 'Logged out']);
    }
    
    function getdata() {
        $lat = '37.16147109102704';
        $lng = '-3.5912354132361344';
        $date = Carbon::now()->format('Y-m-d');
        $url = sprintf('https://api.sunrise-sunset.org/json?lat=%s&lng=%s&date=%s', $lat, $lng, $date);
        
        $response = Http::get($url);
        
        $sunData = $response->json();
        $sunset = $sunData['results']['sunset'];
        $sunrise = $sunData['results']['sunrise'];
        
        return ['sunrise' => $sunrise, 'sunset' => $sunset];
    }
    
    function interpolate() {
        $suntime = $this->getdata();
        $sunrise = Carbon::parse($suntime['sunrise'])->format('H:i:s');
        $sunset = Carbon::parse($suntime['sunset'])->format('H:i:s');
        $currentTime = Carbon::now()->format('H:i:s');
        $interpolateTime = (Carbon::parse($currentTime)->diffInSeconds(Carbon::parse($sunrise))) 
                            / (Carbon::parse($sunset)->diffInSeconds(Carbon::parse($sunrise)));
        $cos = cos($interpolateTime);
        $sin = sin($interpolateTime);
        return response()->json([
            'sunrise' => $sunrise, 
            'sunset' => $sunset, 
            'current' => $currentTime, 
            'interpolate' => $interpolateTime, 
            'cos' => $cos,
            'sin' => $sin,
            'sensor1' => rand(0, 100) / 100,
            'sensor2' => rand(0, 100) / 100,
            'sensor3' => rand(0, 100) / 100,
            'sensor4' => rand(0, 100) / 100
        ]);
    }
    
    function draw() {
        return view('draw', ['message' => 'hola']);
    }
}
