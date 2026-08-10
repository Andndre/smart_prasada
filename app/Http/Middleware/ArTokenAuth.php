<?php

namespace App\Http\Middleware;

use App\Helper\TokenHelper;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ArTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $arToken = $request->query('arToken');

        // Debug log
        Log::debug('AR Token Middleware - Request:', [
            'url' => $request->fullUrl(),
            'token' => $arToken,
        ]);

        // If no token, check if user is authenticated
        if (! $arToken) {
            if (! Auth::check()) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        // Try to validate the HMAC token
        try {
            $tokenData = TokenHelper::verify($arToken);

            // Find the user
            $user = User::find($tokenData['user_id']);
            if (! $user) {
                throw new Exception('User not found');
            }

            // Token is valid, log the user in
            Auth::login($user);

            Log::info('AR Token validated successfully', [
                'user_id' => $user->id,
                'arToken' => $arToken,
            ]);

            // url()->current() hanya mengembalikan path — query string ikut terbuang.
            // Handoff lewat QR membawa parameter lain di query (kode responden, dan apa
            // pun yang ditambahkan kelak); tanpa disusun ulang, semuanya tertelan diam-diam
            // saat token ditukar sesi. Buang arToken-nya saja, pertahankan sisanya.
            $query = $request->except('arToken');

            return redirect()->to(
                url()->current().($query ? '?'.http_build_query($query) : '')
            );
        } catch (Exception $e) {
            Log::error('AR Token validation failed: '.$e->getMessage(), [
                'exception' => get_class($e),
                'token' => $arToken,
            ]);

            return redirect()->route('login')->with('error', 'Token AR tidak valid atau sudah kadaluarsa');
        }
    }
}
