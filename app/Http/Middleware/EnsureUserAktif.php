<?php
// ============================================================
// app/Http/Middleware/EnsureUserAktif.php
// ============================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        return match($user->status) {
            'pending'  => $this->logout($request, 'warning',
                'Akun Anda masih menunggu persetujuan administrator.'),
            'nonaktif' => $this->logout($request, 'error',
                'Akun Anda telah dinonaktifkan. Hubungi administrator.'),
            'ditolak'  => $this->logout($request, 'error',
                'Pendaftaran Anda ditolak. ' . ($user->alasan_tolak ?? '')),
            default    => $next($request),
        };
    }

    private function logout(Request $request, string $type, string $message): Response
    {
        auth()->logout();
        $request->session()->invalidate();
        return redirect()->route('login')->with($type, $message);
    }
}
