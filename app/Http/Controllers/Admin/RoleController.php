<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);
        return view('admin.roles.form', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:50|unique:roles,name',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'exists:permissions,name',
        ]);

        $name = strtolower(str_replace(' ', '_', trim($request->name)));
        $role = Role::create(['name' => $name, 'guard_name' => 'web']);
        if ($request->permissions) $role->syncPermissions($request->permissions);

        return redirect()->route('superadmin.roles.index')
            ->with('success', "Role <strong>{$name}</strong> berhasil dibuat.");
    }

    public function edit(Role $role)
    {
        if ($role->name === 'superadmin') return back()->with('error', 'Role superadmin tidak bisa diedit.');
        $permissions    = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0]);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('admin.roles.form', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') return back()->with('error', 'Role superadmin tidak bisa diubah.');
        $request->validate(['permissions' => 'nullable|array', 'permissions.*' => 'exists:permissions,name']);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('superadmin.roles.index')
            ->with('success', "Permission role <strong>{$role->name}</strong> berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        $protected = ['superadmin','admin_satker','arsiparis','operator_teknis','peminjam','viewer','pimpinan'];
        if (in_array($role->name, $protected)) return back()->with('error', "Role bawaan tidak bisa dihapus.");
        if ($role->users()->count() > 0) return back()->with('error', "Role masih digunakan oleh {$role->users()->count()} pengguna.");
        $role->delete();
        return redirect()->route('superadmin.roles.index')->with('success', "Role berhasil dihapus.");
    }

    public function show(Role $role)
    {
        $users = $role->users()->with('unitKerja')->paginate(15);
        return view('admin.roles.show', compact('role', 'users'));
    }
}
