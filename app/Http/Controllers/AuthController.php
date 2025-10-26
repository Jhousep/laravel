<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Valida las credenciales del usuario y devuelve un token de autenticaci n JWT si son v lidas.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //valida las credenciales
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //busca al usuario
        $user = User::where('email', $request->email)->first();

        //valida si el usuario existe y si la contraseña es correcta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        //genera el token
        $token = $this->generateToken($user);

        //devuelve el token
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    /**
     * Genera un token de autenticaci n JWT para el usuario dado.
     * El token contiene la siguiente informaci n:
     * - sub: el id del usuario
     * - role: el rol del usuario
     * - iat: la fecha de creaci n del token
     * - exp: la fecha de expiraci n del token
     *
     * @param User $user
     * @return string
     */
    private function generateToken($user)
    {
        $payload = [
            'sub' => $user->id,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + (env('JWT_EXP_HOURS', 2) * 3600)
        ];

        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payloadEncoded", env('JWT_SECRET'), true);
        $signatureEncoded = base64_encode($signature);

        return "$header.$payloadEncoded.$signatureEncoded";
    }
}
