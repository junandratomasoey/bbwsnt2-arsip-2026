<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with(['unitKerja', 'roles'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('email', 'ilike', "%{$request->search}%")
                  ->orWhere('nip', 'ilike', "%{$request->search}%");
            }))
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->unit_kerja_id, fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->role,          fn($q) => $q->role($request->role))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles      = Role::orderBy('name')->get();
        $unitKerjas = UnitKerja::aktif()->orderBy('tipe')->orderBy('nama')->get();
        $stats = [
            'total'    => User::count(),
            'aktif'    => User::aktif()->count(),
            'pending'  => User::pending()->count(),
            'nonaktif' => User::nonaktif()->count(),
        ];

        return view('admin.users.index', compact('query', 'roles', 'unitKerjas', 'stats'));
    }

    public function create()
    {
        $roles      = Role::where('name', '!=', 'superadmin')->orderBy('name')->get();
        $unitKerjas = UnitKerja::aktif()->orderBy('tipe')->orderBy('nama')->get();
        return view('admin.users.create', compact('roles', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'nip'           => 'nullable|string|max:18|unique:users,nip',
            'email'         => 'required|email|unique:users,email',
            'jabatan_struktural' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            'role'          => 'required|exists:roles,name',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            ...$validated,
            'password'          => Hash::make($validated['password']),
            'status'            => 'aktif',
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('superadmin.users.index')
            ->with('success', "Pengguna <strong>{$user->name}</strong> berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $user->load(['unitKerja', 'roles.permissions', 'approvedBy']);
        $auditLogs = \App\Models\AuditLog::where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(20)->get();
        return view('admin.users.show', compact('user', 'auditLogs'));
    }

    public function edit(User $user)
    {
        $roles      = Role::where('name', '!=', 'superadmin')->orderBy('name')->get();
        $unitKerjas = UnitKerja::aktif()->orderBy('tipe')->orderBy('nama')->get();
        return view('admin.users.create', compact('user', 'roles', 'unitKerjas'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Tidak dapat mengubah akun superadmin.');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'nip'           => "nullable|string|max:18|unique:users,nip,{$user->id}",
            'email'         => "required|email|unique:users,email,{$user->id}",
            'jabatan_struktural' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            'status'        => 'required|in:aktif,nonaktif',
            'role'          => 'nullable|exists:roles,name',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $user->update(array_filter([
            'name'               => $validated['name'],
            'nip'                => $validated['nip'],
            'email'              => $validated['email'],
            'jabatan_struktural' => $validated['jabatan_struktural'],
            'no_hp'              => $validated['no_hp'],
            'unit_kerja_id'      => $validated['unit_kerja_id'],
            'status'             => $validated['status'],
            'password'           => !empty($validated['password'])
                                    ? Hash::make($validated['password']) : null,
        ], fn($v) => $v !== null));

        if (!empty($validated['role']) && !$user->isSuperAdmin()) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', "Data <strong>{$user->name}</strong> berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) return back()->with('error', 'Akun superadmin tidak bisa dihapus.');
        if ($user->id === auth()->id()) return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        $nama = $user->name;
        $user->delete();
        return redirect()->route('superadmin.users.index')
            ->with('success', "Pengguna <strong>{$nama}</strong> berhasil dihapus.");
    }

    public function approvals(Request $request)
    {
        $pending = User::pending()->with('unitKerja')->latest()->paginate(20);
        $roles   = Role::where('name', '!=', 'superadmin')->orderBy('name')->get();
        return view('admin.users.approvals', compact('pending', 'roles'));
    }

    public function approve(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);

        $user->update([
            'status'      => 'aktif',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        $user->syncRoles([$request->role]);

        // Notifikasi ke user
        Notification::kirim($user->id, 'user.approved',
            'Akun Anda Disetujui',
            "Selamat! Akun Anda telah disetujui dengan role " . ucfirst(str_replace('_',' ',$request->role)) . ".",
            ['icon' => 'ti-circle-check', 'level' => 'success', 'action_url' => route('dashboard')]
        );

        return back()->with('success', "Akun <strong>{$user->name}</strong> disetujui sebagai " . ucfirst(str_replace('_',' ',$request->role)) . ".");
    }

    public function tolak(Request $request, User $user)
    {
        $request->validate(['alasan_tolak' => 'required|string|min:5']);
        $user->update(['status' => 'ditolak', 'alasan_tolak' => $request->alasan_tolak]);
        return back()->with('success', "Pendaftaran <strong>{$user->name}</strong> ditolak.");
    }

    public function nonaktifkan(User $user)
    {
        if ($user->isSuperAdmin()) return back()->with('error', 'Superadmin tidak bisa dinonaktifkan.');
        $user->update(['status' => 'nonaktif']);
        return back()->with('success', "Akun <strong>{$user->name}</strong> dinonaktifkan.");
    }

    public function aktifkan(User $user)
    {
        $user->update(['status' => 'aktif']);
        return back()->with('success', "Akun <strong>{$user->name}</strong> diaktifkan kembali.");
    }

    public function assignRole(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) return back()->with('error', 'Role superadmin tidak bisa diubah.');
        $request->validate(['role' => 'required|exists:roles,name']);
        $user->syncRoles([$request->role]);
        return back()->with('success', "Role <strong>{$user->name}</strong> diubah menjadi " . ucfirst(str_replace('_',' ',$request->role)) . ".");
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'no_hp'  => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        $user->update(array_filter([
            'name'     => $validated['name'],
            'no_hp'    => $validated['no_hp'],
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
        ], fn($v) => $v !== null));
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
