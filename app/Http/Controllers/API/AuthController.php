<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function credentials(Request $request)
    {
        return [
            'login' => $request->login,
            'password' => $request->password,
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        // once() : authentifie pour cette seule requête, sans ouvrir de session web ni
        // déclencher l'événement Login. Sinon le cookie de session renvoyé par un client
        // qui conserve ses cookies portait une session « web » incomplète (sans
        // auth_permissions) et les listes de l'API revenaient vides (#2754).
        if (! auth()->guard('web')->once($this->credentials($request))) {
            return response(['message' => 'This user does not exist, check your details'], 400);
        }

        $accessToken = auth()->user()->createToken(auth()->user()->login.' authToken '.now())->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken]);
    }
}
