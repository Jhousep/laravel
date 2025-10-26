<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class IoTSafeAuth
{
/**
 * Verifica si el token de autenticaci n en el header de la solicitud es v lido.
 * Si el token es inv lido o expirado, devuelve una respuesta con un error y un c digo de estado 401.
 * Si el token es v lido, inyecta el usuario asociado al token en la solicitud y devuelve la siguiente solicitud en la cadena de ejecuci n.
 *
 * @param Request $request La solicitud HTTP actual.
 * @param Closure $next La siguiente solicitud en la cadena de ejecuci n.
 * @return mixed La respuesta HTTP final o la siguiente solicitud en la cadena de ejecuci n.
 */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = $matches[1];

        $user = $this->validateToken($token);
        if (!$user) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }

        // Inyecta el usuario en la request
        $request->merge(['user' => $user]);

        return $next($request);
    }

/**
 * Valida un token de autenticaci n JWT y devuelve el usuario asociado a l si es v lido.
 * Si el token es inv lido o expirado, devuelve null.
 *
 * @param string $token El token de autenticaci n JWT a validar.
 * @return User|null El usuario asociado al token si es v lido, null en caso contrario.
 */
    private function validateToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $expectedSig = base64_encode(hash_hmac('sha256', "$header.$payload", env('JWT_SECRET'), true));

        if ($signature !== $expectedSig) return null;

        $payloadDecoded = json_decode(base64_decode($payload), true);

        if (!$payloadDecoded || $payloadDecoded['exp'] < time()) return null;

        return User::find($payloadDecoded['sub']);
    }
}
