<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page with cabang data.
     */
    public function create(): Response
    {

        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $defaultRole = Role::query()->where('name', 'anggota')->firstOrFail();

        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'pagoruan_id' => 'nullable|exists:pagoruan,id',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $defaultRole->id,
            'pagoruan_id' => $request->pagoruan_id,
        ]);

        

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
