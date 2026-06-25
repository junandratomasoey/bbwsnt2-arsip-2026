<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isLocked()) {
            auth()->logout();
            $request->session()->invalidate();
            $until = $user->locked_until->format('H:i');
            return redirect()->route('login')
                ->with('error', "Akun terkunci karena terlalu banyak percobaan login. Coba lagi setelah {$until}.");
        }
        return $next($request);
    }
}
