<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->status === 'pending') {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->with('warning', 'Akun Anda masih menunggu persetujuan dari administrator. Harap tunggu notifikasi via email.');
        }

        if ($user->status === 'nonaktif') {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator untuk informasi lebih lanjut.');
        }

        if ($user->status === 'ditolak') {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun Anda ditolak. ' . ($user->alasan_tolak ?? ''));
        }

        return $next($request);
    }
}
