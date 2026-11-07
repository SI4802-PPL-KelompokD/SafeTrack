<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService
    ) {}

    /**
     * Tampilkan formulir registrasi karyawan (UC-01)
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pengiriman formulir registrasi (UC-01).
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        try {
            $this->registrationService->register($request->validated());

            return redirect()->route('login')
                ->with('success', 'Registrasi Karyawan berhasil. Silakan masuk.');
        } catch (ValidationException $e) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors($e->errors());
        } catch (Throwable $e) {
            try {
                report($e);
            } catch (Throwable) {
                // Jangan biarkan kegagalan logging menghentikan redirect error ke pengguna
            }

            
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'system' => 'Terjadi gangguan sistem. Silakan coba lagi beberapa saat lagi.',
                ]);
        }
    }
}
