<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate(); // OWASP A07: Prevent Session Fixation

        Log::info('auth.login', [
            'email'    => $request->input('email'),
            'ip'       => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Redirect by role
        if (Auth::user()->isAdmin()) {
            return redirect()->intended(route('admin.orders.index'));
        }

        return redirect()->intended(route('products.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('auth.logout', ['user_id' => Auth::id()]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
