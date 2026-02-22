<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'phone'            => ['required', 'string', 'max:20'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'email'      => $request->email,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password), // OWASP A02: bcrypt hash
            'role'       => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate(); // OWASP A07

        return redirect()->route('products.index');
    }
}
