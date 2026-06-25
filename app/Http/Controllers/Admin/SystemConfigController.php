<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{
    public function index()
    {
        $configs = SystemConfig::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('superadmin.system-config.index', compact('configs'));
    }

    public function edit(SystemConfig $systemConfig)
    {
        return view('superadmin.system-config.edit', compact('systemConfig'));
    }

    public function update(Request $request, SystemConfig $systemConfig)
    {
        $request->validate(['value' => 'nullable|string|max:1000']);
        $systemConfig->update(['value' => $request->value, 'updated_by' => auth()->id()]);
        return redirect()->route('superadmin.system-config.index')
            ->with('success', "Konfigurasi <strong>{$systemConfig->label}</strong> berhasil diperbarui.");
    }
}
