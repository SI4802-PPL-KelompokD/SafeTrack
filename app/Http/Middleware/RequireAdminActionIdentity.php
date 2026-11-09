<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAdminActionIdentity
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethodSafe()) {
            $request->merge([
                'admin_name' => trim((string) $request->input('admin_name')),
                'action_reason' => trim((string) $request->input('action_reason')),
            ]);
            $request->validate([
                'admin_name' => ['required', 'string', 'min:3', 'max:100'],
                'action_reason' => ['required', 'string', 'min:10', 'max:1000'],
            ], [
                'admin_name.required' => 'Nama petugas Admin wajib diisi.',
                'admin_name.min' => 'Nama petugas minimal 3 karakter.',
                'action_reason.required' => 'Alasan perubahan wajib diisi.',
                'action_reason.min' => 'Alasan minimal 10 karakter.',
            ]);
        }

        return $next($request);
    }
}
